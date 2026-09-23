<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__) . '/app/Core/Autoloader.php';
App\Core\Autoloader::register();
$app = require dirname(__DIR__) . '/config/app.php';
try {
    App\Services\TelegramService::registrarWebhook(rtrim($app['base_url'], '/') . '/telegram_webhook.php');
    echo "Webhook registrado.\n";
} catch (Throwable $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}
