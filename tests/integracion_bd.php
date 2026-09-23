<?php
// Ejecutar: C:/xampp/php/php.exe tests/integracion_bd.php
// Crea una base aislada y la elimina al terminar; nunca prueba escrituras en la base de trabajo.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__) . '/app/Core/Autoloader.php';
App\Core\Autoloader::register();
date_default_timezone_set('America/La_Paz');

use App\Core\Database;
use App\Models\Lugar;
use App\Models\Pago;
use App\Models\Vigencia;
use App\Services\PagoService;
use App\Services\PublicacionService;
use App\Services\SolicitudService;

function check(bool $ok, string $message): void {
    if (!$ok) throw new RuntimeException($message);
    echo "OK: $message\n";
}
function mustFail(callable $action, string $message): void {
    try { $action(); } catch (Throwable $e) { echo "OK: $message\n"; return; }
    throw new RuntimeException($message);
}
function runSql(PDO $db, string $file): void {
    $sql = file_get_contents(dirname(__DIR__) . '/' . $file);
    // El instalador no debe cambiar la base aislada.
    $sql = preg_replace('/CREATE DATABASE IF NOT EXISTS.*?;/s', '', $sql);
    $sql = preg_replace('/^USE .*?;/m', '', $sql);
    $db->exec($sql);
}

$config = require dirname(__DIR__) . '/config/database.php';
$db = new PDO("mysql:host={$config['host']};port={$config['port']};charset=utf8mb4", $config['username'], $config['password'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false
]);
$name = 'trinidad_test_' . bin2hex(random_bytes(6));
$db->exec("CREATE DATABASE `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
try {
    $db->exec("USE `$name`");
    $property = new ReflectionProperty(Database::class, 'instance');
    $property->setValue(null, $db);
    runSql($db, 'database/migrations/001_crear_tablas.sql');
    check(count($db->query('SHOW TABLES')->fetchAll()) === 11, 'Instalacion nueva: 11 tablas y SQL valido');
    // Reiniciar exclusivamente la base aislada para probar el volcado anterior.
    $db->exec("DROP DATABASE `$name`");
    $db->exec("CREATE DATABASE `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $db->exec("USE `$name`");
    runSql($db, 'database/seeds/trinidad_turismo_db.sql');
    $before = $db->query('SELECT COUNT(*) FROM pagos')->fetchColumn();
    for ($i = 0; $i < 2; $i++) {
        runSql($db, 'database/migrations/002_completar_esquema.sql');
        runSql($db, 'database/migrations/003_duracion_pagos.sql');
        runSql($db, 'database/migrations/004_cuarentena_aprovisionamiento.sql');
        runSql($db, 'database/migrations/005_autoservicio.sql');
    }
    check($before === $db->query('SELECT COUNT(*) FROM pagos')->fetchColumn(), 'Migraciones repetibles conservan pagos');
    $lugares = new Lugar();
    check(count($lugares->listarPublicos(null, 'Plaza')) > 0, 'Buscador con texto y PDO nativo');
    check(count($lugares->listarPublicos(1, 'Plaza')) > 0, 'Buscador con categoria y texto');
    $db->exec("INSERT INTO lugares (id_categoria,nombre,slug,descripcion,direccion,tipo_lugar) VALUES (2,'Prueba','prueba-integracion','Prueba','Prueba','COMERCIAL')");
    $id = (int)$db->lastInsertId();
    $db->prepare('INSERT INTO publicaciones (id_lugar,aprobado,habilitado,motivo_suspension) VALUES (?,0,0,?)')->execute([$id,'Suspension de prueba']);
    $pago = new Pago();
    $base = ['id_lugar'=>$id,'id_tarifa'=>1,'fecha_pago_declarada'=>date('Y-m-d')];
    $anual = $pago->registrar($base + ['monto'=>2000, 'meses_duracion'=>12]);
    $resultado = PagoService::confirmarPago($anual,1);
    check($resultado['fecha_vencimiento'] === App\Services\VigenciaService::sumarMesesCalendario(date('Y-m-d'),12), 'Plan anual explicito con importe menor a 2500');
    $pub=$db->query("SELECT * FROM publicaciones WHERE id_lugar=$id")->fetch();
    check((int)$pub['aprobado']===0 && (int)$pub['habilitado']===0 && $pub['motivo_suspension']==='Suspension de prueba', 'Confirmar no aprueba ni levanta una suspension');
    mustFail(fn()=>PagoService::confirmarPago($anual,1), 'No confirmar dos veces el mismo pago');
    $mensual = $pago->registrar($base + ['monto'=>3000, 'meses_duracion'=>1]);
    $r = PagoService::confirmarPago($mensual,1);
    check($r['fecha_inicio']===$resultado['fecha_vencimiento'] && $r['fecha_vencimiento']===App\Services\VigenciaService::sumarMesesCalendario($r['fecha_inicio'],1), 'Plan mensual explicito con importe mayor a 2500 y renovacion continua');
    $pago->anular($anual,'Prueba');
    $pago->anular($mensual,'Prueba');
    check((new Vigencia())->obtenerUltimoVencimiento($id)===null, 'Pagos anulados no prolongan renovaciones');
    $db->exec("UPDATE publicaciones SET aprobado=1,habilitado=1 WHERE id_lugar=$id");
    $checkVisibility = function(bool $expected) use ($lugares, $id): void {
        $rows=array_column($lugares->listarParaAdmin(),null,'id_lugar');
        check((bool)$rows[$id]['es_visible']===$expected && PublicacionService::esFichaVisible($id)===$expected, 'Visibilidad administrativa coincide con catalogo');
    };
    $checkVisibility(false);
    $futuro=$pago->registrar(array_replace($base,['fecha_pago_declarada'=>date('Y-m-d',strtotime('+2 months')),'monto'=>250,'meses_duracion'=>1]));
    PagoService::confirmarPago($futuro,1);
    $checkVisibility(false);
    $db->exec("UPDATE vigencias SET fecha_inicio=CURDATE(),fecha_vencimiento=DATE_ADD(CURDATE(),INTERVAL 1 MONTH) WHERE id_pago=$futuro");
    $checkVisibility(true);
    $pendiente=$pago->registrar($base+['monto'=>250,'meses_duracion'=>1]);
    mustFail(fn()=>PagoService::confirmarPago($pendiente,2147483647), 'Error de confirmacion revierte el pago');
    check($pago->buscarPorId($pendiente)['estado']==='PENDIENTE','Pago fallido conserva estado pendiente');
    mustFail(fn()=>$pago->registrar($base+['monto'=>250,'meses_duracion'=>3]), 'Rechaza duraciones no permitidas');
    $db->exec("INSERT INTO solicitudes (nombre_establecimiento,id_categoria,plan_solicitado,nombre_solicitante,telefono_contacto,direccion,descripcion,estado) VALUES ('Prueba conversion',2,'ANUAL','Prueba','0','Prueba','Prueba','ACEPTADA')");
    $solicitud=(int)$db->lastInsertId();
    $count=$db->query('SELECT COUNT(*) FROM lugares')->fetchColumn();
    mustFail(fn()=>SolicitudService::convertirAFicha($solicitud,2147483647),'Fallo de publicacion revierte conversion completa');
    check($count===$db->query('SELECT COUNT(*) FROM lugares')->fetchColumn(),'No deja lugares incompletos');
    $nuevo=SolicitudService::convertirAFicha($solicitud,1);
    check((int)$lugares->buscarPorId($nuevo)['id_solicitud_origen']===$solicitud,'Conversion vincula solicitud y publicacion');
    mustFail(fn()=>SolicitudService::convertirAFicha($solicitud,1),'Conversion duplicada rechazada');
    check((new App\Models\Solicitud())->buscarPorId($solicitud)['plan_solicitado']==='ANUAL','Conserva plan solicitado para el formulario de pago');
} finally {
    if ($db->inTransaction()) $db->rollBack();
    $db->exec("DROP DATABASE `$name`");
}
