<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__) . '/app/Core/Autoloader.php';
App\Core\Autoloader::register();
$config = require dirname(__DIR__) . '/config/app.php';
date_default_timezone_set($config['timezone']);
try {
    $resultado = App\Services\SolicitudService::purgarSolicitudesAbandonadas();
    echo json_encode($resultado, JSON_UNESCAPED_UNICODE) . PHP_EOL;
    exit($resultado['fallos_archivos'] ? 1 : 0);
} catch (Throwable $e) {
    fwrite(STDERR, 'Error de purga: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
