<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__) . '/app/Core/Autoloader.php';
App\Core\Autoloader::register();
$app = require dirname(__DIR__) . '/config/app.php';
try {
    // En desarrollo, la URL pública del túnel es distinta a la URL local del sitio.
    $url = $argv[1] ?? (rtrim($app['base_url'], '/') . '/telegram_webhook.php');
    App\Services\TelegramService::registrarWebhook($url);
    echo "Webhook registrado.\n";
} catch (Throwable $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}
