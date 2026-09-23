<?php
// Pruebas HTTP reales: uploads, CSRF, sesiones y BD/archivos aislados.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__) . '/app/Core/Autoloader.php';
App\Core\Autoloader::register();
date_default_timezone_set('America/La_Paz');
putenv('BENITURS_CREDENTIAL_KEY=' . base64_encode(random_bytes(32)));
use App\Services\SolicitudService;
use App\Services\ComprobanteService;

function verificar(bool $ok, string $mensaje): void {
    if (!$ok) throw new RuntimeException($mensaje);
    echo "OK: $mensaje\n";
}
function rechaza(callable $accion, string $mensaje): void {
    try { $accion(); } catch (Throwable $e) { echo "OK: $mensaje\n"; return; }
    throw new RuntimeException($mensaje);
}
function conteos(PDO $db): array {
    $counts=[];
    foreach (['lugares','cuentas_negocio','publicaciones','pagos','vigencias'] as $table) {
        $counts[$table]=(int)$db->query("SELECT COUNT(*) FROM $table")->fetchColumn();
    }
    return $counts;
}
$config=require dirname(__DIR__).'/config/database.php';
$db=new PDO("mysql:host={$config['host']};port={$config['port']};charset=utf8mb4",$config['username'],$config['password'],[
    PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false
]);
$name='beniturs_test_'.bin2hex(random_bytes(6));
$tmp=sys_get_temp_dir().'/'.$name;
mkdir($tmp); mkdir($tmp.'/comprobantes'); mkdir($tmp.'/sesiones'); mkdir($tmp.'/fotos');
$db->exec("CREATE DATABASE `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$server=null;
try {
    $db->exec("USE `$name`");
    (new ReflectionProperty(App\Core\Database::class,'instance'))->setValue(null,$db);
    (new ReflectionProperty(ComprobanteService::class,'directorio'))->setValue(null,$tmp.'/comprobantes/');
    (new ReflectionProperty(App\Services\ImagenService::class,'directorio'))->setValue(null,$tmp.'/fotos/');
    $sql=file_get_contents(dirname(__DIR__).'/database/migrations/001_crear_tablas.sql');
    $sql=preg_replace('/CREATE DATABASE IF NOT EXISTS.*?;/s','',$sql);
    $sql=preg_replace('/^USE .*?;/m','',$sql);
    $db->exec($sql);
    $db->exec(file_get_contents(dirname(__DIR__).'/database/migrations/004_cuarentena_aprovisionamiento.sql'));
    $db->exec(file_get_contents(dirname(__DIR__).'/database/migrations/005_autoservicio.sql'));
    $db->exec(file_get_contents(dirname(__DIR__).'/database/migrations/006_flujo_solicitud_comercial.sql'));
    $db->exec(file_get_contents(dirname(__DIR__).'/database/migrations/006_flujo_solicitud_comercial.sql'));
    $db->prepare("INSERT INTO administradores (nombre,usuario,email,password_hash) VALUES ('Auditor','auditor','auditor@test.invalid',?)")
        ->execute([password_hash('PruebaAdmin123!',PASSWORD_BCRYPT)]);
    $db->exec("INSERT INTO categorias (nombre,slug,tipo_defecto) VALUES ('Comercial','comercial','COMERCIAL'),('Publico','publico','PUBLICO')");
    $db->exec("INSERT INTO tarifas (monto_mensual,descripcion,vigente_desde) VALUES (250,'Prueba','2020-01-01')");
    $png=base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII=');
    file_put_contents($tmp.'/valido.png',$png);
    file_put_contents($tmp.'/falso.jpg','<?php echo "no es una imagen";');
    file_put_contents($tmp.'/grande.png',$png.str_repeat('x',ComprobanteService::MAX_BYTES));
    $socket=stream_socket_server('tcp://127.0.0.1:0',$errno,$error);
    $address=stream_socket_get_name($socket,false); fclose($socket);
    $env=getenv(); $env['BENITURS_TEST_DB']=$name; $env['BENITURS_TEST_DIR']=$tmp;
    $server=proc_open([PHP_BINARY,'-d','upload_max_filesize=8M','-d','post_max_size=10M','-d','upload_tmp_dir='.$tmp,'-S',$address,__DIR__.'/cuarentena_router.php'],
        [0=>['pipe','r'],1=>['file',$tmp.'/server.log','a'],2=>['file',$tmp.'/server.log','a']],$pipes,dirname(__DIR__),$env);
    if (!is_resource($server)) throw new RuntimeException('No se pudo iniciar PHP HTTP.');
    fclose($pipes[0]);
    $request=static function(string $path, ?array $body=null, string $actor='publico') use ($address,$tmp): array {
        $curl=curl_init('http://'.$address.$path);
        curl_setopt_array($curl,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_HEADER=>true,CURLOPT_TIMEOUT=>15,
            CURLOPT_COOKIEFILE=>$tmp.'/'.$actor.'.cookies',CURLOPT_COOKIEJAR=>$tmp.'/'.$actor.'.cookies']);
        if ($body!==null) curl_setopt_array($curl,[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$body]);
        $raw=curl_exec($curl);
        if ($raw===false) { $error=curl_error($curl); curl_close($curl); throw new RuntimeException($error); }
        $status=curl_getinfo($curl,CURLINFO_HTTP_CODE); $size=curl_getinfo($curl,CURLINFO_HEADER_SIZE);
        curl_close($curl);
        return [$status,substr($raw,$size),substr($raw,0,$size)];
    };
    for ($i=0;$i<50;$i++) {
        try { [$status,$html]=$request('/solicitar-incorporacion'); break; }
        catch (Throwable $e) { if ($i===49) throw $e; usleep(100000); }
    }
    preg_match('/name="csrf_token" value="([a-f0-9]+)"/',$html,$match); $token=$match[1]??'';
    verificar($status===200 && $token!=='' && str_contains($html,'multipart/form-data'),'Formulario real con CSRF y archivo');
    $payload=['csrf_token'=>$token,'nombre_establecimiento'=>'Negocio de prueba','id_categoria'=>'1','plan_solicitado'=>'MENSUAL',
        'nombre_solicitante'=>'Persona de prueba','telefono_contacto'=>'+591 70000000','email_contacto'=>'',
        'direccion'=>'Direccion de prueba','descripcion'=>'Descripcion de prueba','horarios'=>'08:00 a 18:00',
        'usuario_solicitado'=>'negocio_prueba','password'=>' clave con espacios ', 'numero_comprobante'=>'OP-123',
        'comprobante'=>new CURLFile($tmp.'/valido.png','image/png','pago.png')];
    foreach ([
        ['csrf_token'=>'invalido'], ['id_categoria'=>'2'], ['telefono_contacto'=>'123'],
        ['nombre_solicitante'=>''], ['nombre_establecimiento'=>str_repeat('x',151)], ['plan_solicitado'=>'OTRO'],
        ['comprobante'=>new CURLFile($tmp.'/falso.jpg','image/jpeg','pago.jpg')],
        ['comprobante'=>new CURLFile($tmp.'/grande.png','image/png','pago.png')], ['comprobante'=>'']
    ] as $cambios) {
        [$status]=$request('/api/solicitudes/enviar',array_replace($payload,$cambios));
        verificar(in_array($status,[403,422],true),'Entrada invalida rechazada: '.array_key_first($cambios));
    }
    verificar((int)$db->query('SELECT COUNT(*) FROM solicitudes')->fetchColumn()===0 && count(glob($tmp.'/comprobantes/*'))===0,'Rechazos no dejan solicitudes ni archivos');
    unset($payload['usuario_solicitado'], $payload['password']);
    require __DIR__ . '/flujo_comercial_casos.php';
} finally {
    if (is_resource($server)) { proc_terminate($server); proc_close($server); }
    if ($db->inTransaction()) $db->rollBack();
    $db->exec("DROP DATABASE `$name`");
    // Solo se eliminan los archivos dentro del directorio aleatorio creado por esta prueba.
    foreach (['comprobantes','sesiones','fotos'] as $folder) {
        foreach (glob($tmp.'/'.$folder.'/*') ?: [] as $file) unlink($file);
        rmdir($tmp.'/'.$folder);
    }
    foreach (glob($tmp.'/*') ?: [] as $file) unlink($file);
    rmdir($tmp);
}
