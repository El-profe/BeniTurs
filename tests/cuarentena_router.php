<?php
// Solo para el servidor PHP local iniciado por solicitudes.php.
$name = getenv('BENITURS_TEST_DB');
$tmp = getenv('BENITURS_TEST_DIR');
if (PHP_SAPI !== 'cli-server' || !preg_match('/\Abeniturs_test_[a-f0-9]{12}\z/', $name ?: '') || !$tmp || !is_dir($tmp)) {
    http_response_code(404); exit;
}
require dirname(__DIR__) . '/app/Core/Autoloader.php';
App\Core\Autoloader::register();
$config = require dirname(__DIR__) . '/config/database.php';
$db = new PDO("mysql:host={$config['host']};port={$config['port']};dbname=$name;charset=utf8mb4", $config['username'], $config['password'], [
    PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES=>false
]);
(new ReflectionProperty(App\Core\Database::class,'instance'))->setValue(null,$db);
(new ReflectionProperty(App\Services\ComprobanteService::class,'directorio'))->setValue(null,$tmp . '/comprobantes/');
(new ReflectionProperty(App\Services\ImagenService::class,'directorio'))->setValue(null,$tmp . '/fotos/');
session_save_path($tmp . '/sesiones');
$_SERVER['SCRIPT_NAME']='/index.php';
$router = require dirname(__DIR__) . '/app/bootstrap.php';
$router->dispatch();
