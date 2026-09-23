<?php
// Incluido por solicitudes.php dentro de una base, servidor y almacenamiento aislados.
if (PHP_SAPI !== 'cli' || !isset($request,$db,$tmp)) { http_response_code(404); exit; }
$antes=conteos($db);
$db->exec("CREATE TRIGGER fallo_registro BEFORE INSERT ON pagos FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Fallo de prueba'");
[$status]=$request('/api/solicitudes/enviar',$payload);
verificar($status===500 && $antes===conteos($db) && count(glob($tmp.'/comprobantes/*'))===0,'Registro atomico: fallo en pago revierte ficha/cuenta y limpia comprobante');
$db->exec('DROP TRIGGER fallo_registro');
[$status,$body]=$request('/api/solicitudes/enviar',$payload);
$json=json_decode($body,true);
if (!$json) throw new RuntimeException($body);
verificar($status===200 && $json['redirect']==='/negocio/dashboard','Registro devuelve redireccion al portal');
$cuenta=(new App\Models\CuentaNegocio())->buscarPorCredencial($payload['usuario_solicitado']);
$idLugar=(int)$cuenta['id_lugar'];
$pago=$db->query("SELECT * FROM pagos WHERE id_lugar=$idLugar")->fetch();
$idPago=(int)$pago['id_pago'];
verificar(password_verify($payload['password'],$cuenta['password_hash']) && $pago['estado']==='PENDIENTE' && (float)$pago['monto']===250.0 && (int)$pago['meses_duracion']===1,'Cuenta bcrypt y pago mensual pendiente');
verificar((int)$db->query('SELECT COUNT(*) FROM solicitudes')->fetchColumn()===0 && (int)$db->query('SELECT COUNT(*) FROM vigencias')->fetchColumn()===0,'Autoservicio no crea solicitudes ni vigencias prematuras');
$pub=$db->query("SELECT * FROM publicaciones WHERE id_lugar=$idLugar")->fetch();
verificar((int)$pub['aprobado']===1 && (int)$pub['habilitado']===1 && !App\Services\PublicacionService::esFichaVisible($idLugar),'Ficha aprobada y habilitada pero oculta por pago pendiente');
[$status,$html]=$request('/negocio/dashboard');
verificar($status===200 && str_contains($html,'está siendo verificado') && !str_contains($html,'Ficha Activa y Visible'),'Sesion automatica y dashboard pendiente');
$antes=conteos($db); $archivos=count(glob($tmp.'/comprobantes/*'));
[$status]=$request('/api/solicitudes/enviar',$payload);
verificar($status===422 && $antes===conteos($db) && $archivos===count(glob($tmp.'/comprobantes/*')),'Reintento/usuario duplicado no crea registros ni archivos');
[$status,$html]=$request('/negocio/fotos');
verificar($status===200,'Galeria disponible antes de confirmar');
[$status]=$request('/negocio/fotos/subir',['csrf_token'=>$token,'fotografia'=>new CURLFile($tmp.'/valido.png','image/png','foto.png'),'es_principal'=>'1']);
$foto=$db->query("SELECT * FROM fotografias WHERE id_lugar=$idLugar")->fetch();
verificar($status===302 && (bool)$foto,'Subida real de foto en borrador');
[$status,$image,$headers]=$request('/imagen?f='.urlencode($foto['nombre_archivo']));
verificar($status===200 && $image===$png && str_contains($headers,'no-store'),'Propietario ve su foto privada sin cache publico');
[$status]=$request('/imagen?f='.urlencode($foto['nombre_archivo']),null,'visitante');
verificar($status===403,'Visitante no puede leer fotos de borrador');
[$status,$html]=$request('/negocio/promociones');
verificar($status===200,'Promociones disponibles antes de confirmar');
$promo=['csrf_token'=>$token,'titulo'=>'Promo de prueba','descripcion'=>'Contenido de prueba','descuento_texto'=>'10%',
    'fecha_inicio'=>date('Y-m-d'),'fecha_fin'=>date('Y-m-d',strtotime('+10 days'))];
[$status]=$request('/negocio/promociones/guardar',$promo);
verificar($status===302 && (int)$db->query("SELECT COUNT(*) FROM promociones WHERE id_lugar=$idLugar")->fetchColumn()===1,'Puede preparar promociones antes de pagar');
[$status]=$request('/admin/pagos/comprobante?id='.$idPago);
verificar($status===302,'Comprobante reservado para administradores');
[$status,$html]=$request('/admin/login',null,'admin');
preg_match('/name="csrf_token" value="([a-f0-9]+)"/',$html,$match); $adminToken=$match[1]??'';
[$status]=$request('/admin/login',['csrf_token'=>$adminToken,'usuario'=>'auditor','password'=>'PruebaAdmin123!'],'admin');
verificar($status===302,'Login administrativo');
[$status,$html]=$request('/admin/pagos?estado=PENDIENTE',null,'admin');
verificar($status===200 && str_contains($html,'Ver comprobante') && str_contains($html,'Confirmar Abono') && str_contains($html,'Rechazar / Eliminar'),'Bandeja con auditoria de comprobante y acciones');
[$status,$image,$headers]=$request('/admin/pagos/comprobante?id='.$idPago,null,'admin');
verificar($status===200 && $image===$png && str_contains($headers,'nosniff'),'Previsualizacion administrativa privada');
[$status]=$request('/admin/pagos/confirmar',['csrf_token'=>'invalido','id_pago'=>$idPago],'admin');
verificar($db->query("SELECT estado FROM pagos WHERE id_pago=$idPago")->fetchColumn()==='PENDIENTE','Confirmacion protegida por CSRF');
$db->exec("CREATE TRIGGER fallo_vigencia BEFORE INSERT ON vigencias FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Fallo de prueba'");
rechaza(fn()=>App\Services\PagoService::confirmarPago($idPago,1),'Fallo al crear vigencia revierte confirmacion');
verificar($db->query("SELECT estado FROM pagos WHERE id_pago=$idPago")->fetchColumn()==='PENDIENTE','Pago sigue pendiente despues de rollback');
$db->exec('DROP TRIGGER fallo_vigencia');
$db->exec("UPDATE pagos SET fecha_pago_declarada=DATE_SUB(CURDATE(),INTERVAL 10 DAY) WHERE id_pago=$idPago");
[$status]=$request('/admin/pagos/confirmar',['csrf_token'=>$adminToken,'id_pago'=>$idPago],'admin');
$vigencia=$db->query("SELECT * FROM vigencias WHERE id_pago=$idPago")->fetch();
verificar($status===302 && $vigencia['fecha_inicio']===date('Y-m-d') && $vigencia['fecha_vencimiento']===App\Services\VigenciaService::sumarMesesCalendario(date('Y-m-d'),1),'Plan mensual empieza al confirmar sin perder dias de espera');
verificar(App\Services\PublicacionService::esFichaVisible($idLugar),'Confirmar hace visible la ficha');
[$status,$html]=$request('/negocio/dashboard');
verificar($status===200 && str_contains($html,'Ficha Activa y Visible en la Guía') && str_contains($html,date('d/m/Y',strtotime($vigencia['fecha_vencimiento']))),'Dashboard activo con fecha de vencimiento');
rechaza(fn()=>App\Services\PagoService::confirmarPago($idPago,1),'No confirmar dos veces');
rechaza(fn()=>App\Services\RechazoNegocioService::eliminarRegistro($idPago,1),'No eliminar un negocio con pago confirmado');
[$status,$image]=$request('/imagen?f='.urlencode($foto['nombre_archivo']),null,'visitante');
verificar($status===200 && $image===$png,'Foto accesible al publico despues de confirmar');

// Un segundo comercio puede entrar de inmediato, pero no acceder a borradores ajenos.
[$status,$html]=$request('/solicitar-incorporacion',null,'segundo');
preg_match('/name="csrf_token" value="([a-f0-9]+)"/',$html,$match); $segundoToken=$match[1];
[$status,$body]=$request('/api/solicitudes/enviar',array_replace($payload,['csrf_token'=>$segundoToken,'plan_solicitado'=>'ANUAL','usuario_solicitado'=>'anual_prueba']),'segundo');
$anual=(new App\Models\CuentaNegocio())->buscarPorCredencial('anual_prueba'); $lugarAnual=(int)$anual['id_lugar'];
$pagoAnual=$db->query("SELECT * FROM pagos WHERE id_lugar=$lugarAnual")->fetch();
$resultado=App\Services\PagoService::confirmarPago((int)$pagoAnual['id_pago'],1);
verificar((float)$pagoAnual['monto']===2500.0 && $resultado['fecha_vencimiento']===App\Services\VigenciaService::sumarMesesCalendario(date('Y-m-d'),12),'Plan anual confirma exactamente 12 meses');

// Un registro falso puede configurar contenido, luego se elimina todo al rechazarlo.
[$status,$html]=$request('/solicitar-incorporacion',null,'falso');
preg_match('/name="csrf_token" value="([a-f0-9]+)"/',$html,$match); $falsoToken=$match[1];
[$status]=$request('/api/solicitudes/enviar',array_replace($payload,['csrf_token'=>$falsoToken,'usuario_solicitado'=>'falso_prueba']),'falso');
$falso=(new App\Models\CuentaNegocio())->buscarPorCredencial('falso_prueba'); $lugarFalso=(int)$falso['id_lugar'];
$pagoFalso=$db->query("SELECT * FROM pagos WHERE id_lugar=$lugarFalso")->fetch(); $pagoFalsoId=(int)$pagoFalso['id_pago'];
[$status]=$request('/negocio/fotos/subir',['csrf_token'=>$falsoToken,'fotografia'=>new CURLFile($tmp.'/valido.png','image/png','foto.png')],'falso');
$fotoFalsa=$db->query("SELECT nombre_archivo FROM fotografias WHERE id_lugar=$lugarFalso")->fetchColumn();
[$status]=$request('/negocio/promociones/guardar',array_replace($promo,['csrf_token'=>$falsoToken]),'falso');
[$status]=$request('/imagen?f='.urlencode($fotoFalsa),null,'segundo');
verificar($status===403,'Otro negocio no puede ver fotos privadas ajenas');
$db->exec("CREATE TRIGGER fallo_eliminar BEFORE DELETE ON lugares FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Fallo de prueba'");
rechaza(fn()=>App\Services\RechazoNegocioService::eliminarRegistro($pagoFalsoId,1),'Fallo de eliminacion revierte cambios');
verificar(is_file($tmp.'/fotos/'.$fotoFalsa) && (bool)(new App\Models\CuentaNegocio())->buscarPorCredencial('falso_prueba'),'El rollback de eliminacion conserva cuenta y archivos');
$db->exec('DROP TRIGGER fallo_eliminar');
[$status]=$request('/admin/pagos/rechazar-eliminar',['csrf_token'=>'invalido','id_pago'=>$pagoFalsoId],'admin');
verificar($status===403,'Eliminar requiere CSRF');
[$status]=$request('/admin/pagos/rechazar-eliminar',['csrf_token'=>$adminToken,'id_pago'=>$pagoFalsoId],'admin');
verificar($status===302,'Rechazo desde administracion');
foreach (['lugares','cuentas_negocio','publicaciones','pagos','vigencias','fotografias','promociones'] as $tabla) {
    verificar((int)$db->query("SELECT COUNT(*) FROM $tabla WHERE id_lugar=$lugarFalso")->fetchColumn()===0,'Eliminacion completa: '.$tabla);
}
clearstatcache();
verificar(!is_file($tmp.'/fotos/'.$fotoFalsa) && !is_file(App\Services\ComprobanteService::ruta($pagoFalso['comprobante_archivo'])),'Elimina fisicamente fotos y comprobante');
verificar(is_file($tmp.'/fotos/'.$foto['nombre_archivo']) && (bool)(new App\Models\CuentaNegocio())->buscarPorCredencial($payload['usuario_solicitado']),'El rechazo no afecta otros negocios');
[$status]=$request('/negocio/dashboard',null,'falso');
verificar($status===302,'La sesion del negocio eliminado deja de tener acceso');
