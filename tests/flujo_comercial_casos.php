<?php
// Se ejecuta desde solicitudes.php sobre una base aleatoria aislada.
if (PHP_SAPI !== 'cli' || !isset($payload, $request, $db)) exit;
use App\Services\SolicitudService;
use App\Services\CredencialService;
use App\Services\TelegramService;

$antes = conteos($db);
[$status,$body] = $request('/solicitudes/enviar', $payload + ['monto_declarado'=>'1']);
$json = json_decode($body,true);
verificar($status===200 && ($json['success']??false) && isset($json['message']) && !isset($json['redirect']), 'Recepcion devuelve exito sin crear sesion de negocio');
$id = (int)$db->query('SELECT MAX(id_solicitud) FROM solicitudes')->fetchColumn();
$s = $db->query("SELECT * FROM solicitudes WHERE id_solicitud=$id")->fetch();
verificar(conteos($db)===$antes && $s['estado']==='PENDIENTE' && $s['id_lugar_creado']===null, 'Cuarentena: ninguna ficha, cuenta, publicacion, pago o vigencia antes de aprobar');
verificar((float)$s['monto_declarado']===250.0 && $s['password_hash_solicitado']==='', 'Precio calculado por servidor y sin credenciales previas');
verificar((int)$db->query('SELECT COUNT(*) FROM telegram_notificaciones')->fetchColumn()===1, 'Telegram se encola sin bloquear ni enviar en el POST');
[$status] = $request('/negocio/dashboard');
verificar($status===302, 'El solicitante aun no puede entrar al portal');

[$status,$login] = $request('/admin/login', null, 'admin');
preg_match('/name="csrf_token" value="([a-f0-9]+)"/', $login, $m);
[$status] = $request('/admin/login', ['csrf_token'=>$m[1], 'usuario'=>'auditor', 'password'=>'PruebaAdmin123!'], 'admin');
verificar($status===302,'Administrador inicia sesion');
[$status,$bandeja] = $request('/admin/solicitudes', null, 'admin');
preg_match('/name="csrf_token" value="([a-f0-9]+)"/', $bandeja, $m);
$adminToken=$m[1]??'';
verificar($status===200 && $adminToken!=='' && str_contains($bandeja,'Aprobar y Aprovisionar'), 'Bandeja permite aprobar solicitudes sin usuario solicitado');
[$status] = $request('/admin/solicitudes/aprovisionar', ['csrf_token'=>'invalido','id_solicitud'=>$id], 'admin');
verificar($status===403,'Aprovisionamiento protegido por CSRF');
[$status,$body] = $request('/admin/solicitudes/aprovisionar', ['csrf_token'=>$adminToken,'id_solicitud'=>$id], 'admin');
$resultado=json_decode($body,true);
verificar($status===200 && ($resultado['success']??false),'Aprobacion AJAX completa');
foreach(conteos($db) as $tabla=>$total) verificar($total===$antes[$tabla]+1,'Aprobacion crea exactamente un registro en '.$tabla);
$cuenta=$db->query('SELECT * FROM cuentas_negocio')->fetch();
parse_str(parse_url($resultado['whatsapp_url'],PHP_URL_QUERY),$query);
preg_match('/Clave: ([a-zA-Z0-9]{8})$/u',$query['text'],$clave);
verificar(isset($clave[1]) && password_verify($clave[1],$cuenta['password_hash']), 'Clave temporal de ocho caracteres corresponde al hash bcrypt');
verificar(str_starts_with($resultado['whatsapp_url'],'https://wa.me/59170000000?') && str_contains($query['text'],'/negocio/login'), 'WhatsApp contiene telefono normalizado, acceso y credenciales');
$s=$db->query("SELECT * FROM solicitudes WHERE id_solicitud=$id")->fetch();
verificar($s['id_cuenta_creada']==$cuenta['id_cuenta'] && $s['id_lugar_creado']==$cuenta['id_lugar'], 'Solicitud enlaza ficha y cuenta creadas');
verificar(!str_contains($s['credenciales_cifradas'],$clave[1]) && CredencialService::descifrar($s['credenciales_cifradas'])===$resultado['whatsapp_url'], 'Credenciales recuperables cifradas, nunca en texto plano');
$periodo=$db->query('SELECT * FROM vigencias')->fetch();
verificar($periodo['fecha_vencimiento']===App\Services\VigenciaService::sumarMesesCalendario(date('Y-m-d'),1),'Vigencia mensual correcta');
$despues=conteos($db);
[$status,$body] = $request('/admin/solicitudes/aprovisionar', ['csrf_token'=>$adminToken,'id_solicitud'=>$id], 'admin');
verificar($status===200 && json_decode($body,true)['ya_procesada'] && conteos($db)===$despues, 'Reintento de aprobacion idempotente');
[$status] = $request('/admin/solicitudes/resolver', ['csrf_token'=>$adminToken,'id_solicitud'=>$id,'estado'=>'RECHAZADA'], 'admin');
verificar($status===409,'No permite rechazar un negocio ya activado');
[$status,$img] = $request('/admin/solicitudes/comprobante?id='.$id, null, 'admin');
verificar($status===200 && $img===$png,'Comprobante real accesible al administrador');

$anual=array_replace($payload,['nombre_establecimiento'=>'Anual','plan_solicitado'=>'ANUAL','monto_declarado'=>'0']);
[$status] = $request('/solicitudes/enviar',$anual);
$idAnual=(int)$db->query('SELECT MAX(id_solicitud) FROM solicitudes')->fetchColumn();
$conteosPrevios=conteos($db);
// Fallo al final de la transaccion: todo debe revertirse, incluso estado y cuenta.
$db->exec("CREATE TRIGGER fallar_vigencia BEFORE INSERT ON vigencias FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Fallo de prueba'");
try { rechaza(fn()=>SolicitudService::aprovisionarNegocioCompleto($idAnual,1),'Fallo forzado revierte aprovisionamiento'); }
finally { $db->exec('DROP TRIGGER fallar_vigencia'); }
verificar(conteos($db)===$conteosPrevios && $db->query("SELECT estado FROM solicitudes WHERE id_solicitud=$idAnual")->fetchColumn()==='PENDIENTE', 'Rollback no deja registros ni marca aceptada');
$r=SolicitudService::aprovisionarNegocioCompleto($idAnual,null);
$pago=$db->query('SELECT * FROM pagos WHERE id_pago='.(int)$r['id_pago'])->fetch();
verificar((float)$pago['monto']===2500.0 && (int)$pago['meses_duracion']===12 && $r['fecha_vencimiento']===App\Services\VigenciaService::sumarMesesCalendario(date('Y-m-d'),12), 'Plan anual: Bs 2500 y doce meses');

[$status] = $request('/solicitudes/enviar',$payload);
$idRechazo=(int)$db->query('SELECT MAX(id_solicitud) FROM solicitudes')->fetchColumn();
$antesRechazo=conteos($db);
[$status] = $request('/admin/solicitudes/resolver',['csrf_token'=>$adminToken,'id_solicitud'=>$idRechazo,'estado'=>'RECHAZADA'],'admin');
verificar($status===200 && conteos($db)===$antesRechazo,'Rechazo no aprovisiona nada');
rechaza(fn()=>SolicitudService::aprovisionarNegocioCompleto($idRechazo,1),'Rechazada no se puede activar');

putenv('TELEGRAM_BOT_TOKEN=123456:token_de_prueba');
putenv('TELEGRAM_ADMIN_CHAT_ID=12345');
putenv('TELEGRAM_WEBHOOK_SECRET=secreto_de_prueba');
$update=['callback_query'=>['id'=>'cb1','from'=>['id'=>12345],'data'=>'aprobar_solicitud_'.$id,
    'message'=>['message_id'=>1,'chat'=>['id'=>12345,'type'=>'private']]]];
verificar(TelegramService::validarWebhook($update,'secreto_de_prueba')['id_solicitud']===$id,'Webhook acepta administrador y secreto validos sin hacer llamadas externas');
rechaza(fn()=>TelegramService::validarWebhook($update,'otro'),'Webhook rechaza secreto incorrecto');
$update['callback_query']['from']['id']=999;
rechaza(fn()=>TelegramService::validarWebhook($update,'secreto_de_prueba'),'Webhook rechaza otro usuario');
putenv('TELEGRAM_BOT_TOKEN');putenv('TELEGRAM_ADMIN_CHAT_ID');putenv('TELEGRAM_WEBHOOK_SECRET');

[$status,$login] = $request('/negocio/login');preg_match('/name="csrf_token" value="([a-f0-9]+)"/',$login,$m);
[$status] = $request('/negocio/login',['csrf_token'=>$m[1],'usuario'=>$cuenta['usuario'],'password'=>$clave[1]]);
verificar($status===302 && $db->query("SELECT credenciales_cifradas FROM solicitudes WHERE id_solicitud=$id")->fetchColumn()===null,'Primer acceso correcto elimina copia cifrada de entrega');
echo "Flujo comercial completo verificado sin enviar mensajes reales.\n";
