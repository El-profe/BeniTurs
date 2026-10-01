<?php
// Ejecutar: C:/xampp/php/php.exe tests/integracion_bd.php
// Crea una base aislada y la elimina al terminar; nunca prueba escrituras en la base de trabajo.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
if (!defined('ROOT_PATH')) define('ROOT_PATH', dirname(__DIR__));
if (!defined('APP_PATH')) define('APP_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'app');
require_once APP_PATH . '/bootstrap.php';

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
    check(count($db->query('SHOW TABLES')->fetchAll()) === 13, 'Instalacion nueva: 13 tablas y SQL valido');
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
        runSql($db, 'database/migrations/006_flujo_solicitud_comercial.sql');
        runSql($db, 'database/migrations/007_eliminar_credenciales_obsoletas.sql');
        runSql($db, 'database/migrations/008_reorganizar_base_datos.sql');
        runSql($db, 'database/migrations/009_escalar_a_13_tablas_municipios.sql');
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
    $reporteRef = new ReflectionClass(App\Controllers\Admin\ReporteController::class);
    $reporte = $reporteRef->newInstanceWithoutConstructor();
    $reporteRef->getProperty('db')->setValue($reporte, $db);
    $coberturas = $reporteRef->getMethod('obtenerReporteVigencias');
    $finanzas = $reporteRef->getMethod('obtenerReporteFinanciero');
    $filas = array_values(array_filter($coberturas->invoke($reporte), static fn($fila) => (int)$fila['id_lugar']===$id));
    check(count($filas)===1 && $filas[0]['fecha_vencimiento']===$r['fecha_vencimiento'],
        'Reportes muestran una cobertura por comercio usando la ultima renovacion');
    check(count(array_filter($finanzas->invoke($reporte, date('Y-m-d'), date('Y-m-d')),
        static fn($fila) => in_array((int)$fila['id_pago'], [$anual,$mensual], true)))===2,
        'Reporte financiero filtra por fecha de confirmacion');
    check($finanzas->invoke($reporte, '', '', 2147483647)===[], 'Reporte financiero respeta categoria');
    $_GET=['desde'=>'2026-02-30'];
    check($reporteRef->getMethod('leerFiltros')->invoke($reporte)[3]!==null, 'Reportes rechazan fechas imposibles');
    $_GET=['desde'=>'2026-09-20','hasta'=>'2026-09-01'];
    check($reporteRef->getMethod('leerFiltros')->invoke($reporte)[3]!==null, 'Reportes rechazan periodos invertidos');
    $_GET=[];
    $pago->anular($anual,'Prueba');
    $pago->anular($mensual,'Prueba');
    check(array_filter($coberturas->invoke($reporte), static fn($fila) => (int)$fila['id_lugar']===$id)===[], 'Reportes excluyen las vigencias de pagos anulados');
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
    $planCustom = $pago->registrar($base+['monto'=>1125,'meses_duracion'=>5]);
    check($pago->buscarPorId($planCustom)['meses_duracion']===5, 'Permite planes personalizados como 5 meses');
    mustFail(fn()=>$pago->registrar($base+['monto'=>250,'meses_duracion'=>0]), 'Rechaza duraciones no permitidas (0)');
    $db->exec("INSERT INTO solicitudes (nombre_establecimiento,id_categoria,plan_solicitado,nombre_solicitante,telefono_contacto,direccion,descripcion,estado) VALUES ('Prueba conversion',2,'ANUAL','Prueba','0','Prueba','Prueba','ACEPTADA')");
    $solicitud=(int)$db->lastInsertId();
    $count=$db->query('SELECT COUNT(*) FROM lugares')->fetchColumn();
    mustFail(fn()=>SolicitudService::convertirAFicha($solicitud,2147483647),'Conversion antigua no permite saltar la verificacion de pago');
    check($count===$db->query('SELECT COUNT(*) FROM lugares')->fetchColumn(),'No deja lugares incompletos');
    mustFail(fn()=>SolicitudService::convertirAFicha($solicitud,1),'Incluso el administrador debe usar el aprovisionamiento completo');
    check((new App\Models\Solicitud())->buscarPorId($solicitud)['plan_solicitado']==='ANUAL','Conserva plan solicitado para el formulario de pago');
} finally {
    if ($db->inTransaction()) $db->rollBack();
    $db->exec("DROP DATABASE `$name`");
}
