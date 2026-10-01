<?php
// Solo para el servidor PHP local iniciado por solicitudes.php.
$name = getenv('BENITURS_TEST_DB');
$tmp = getenv('BENITURS_TEST_DIR');
if (PHP_SAPI !== 'cli-server' || !preg_match('/\Abeniturs_test_[a-f0-9]{12}\z/', $name ?: '') || !$tmp || !is_dir($tmp)) {
    http_response_code(404); exit;
}
require dirname(__DIR__) . '/app/Core/Autoloader.php';
App\Core\Autoloader::register();
putenv('TELEGRAM_BOT_TOKEN=123456:prueba_aislada');
putenv('TELEGRAM_ADMIN_CHAT_ID=123456');
(new ReflectionProperty(App\Services\TelegramService::class, 'transporte'))->setValue(null,
    static function (string $metodo, array $datos) use ($tmp): array {
        if (is_file($tmp . '/telegram_exito')) {
            if ($metodo !== 'sendPhoto' || !($datos['photo'] instanceof CURLFile)
                || !str_contains($datos['reply_markup'], 'aprobar_solicitud_')) {
                throw new RuntimeException('Mensaje de aprobación incompleto.');
            }
            return ['ok'=>true, 'result'=>['message_id'=>456789]];
        }
        throw new RuntimeException('Telegram no confirmó ' . $metodo . ' (HTTP 0, cURL 28).');
    });
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
