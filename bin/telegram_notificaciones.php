<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__) . '/app/Core/Autoloader.php';
App\Core\Autoloader::register();
date_default_timezone_set('America/La_Paz');
if (!App\Services\TelegramService::configurado()) {
    fwrite(STDERR, "Configure TELEGRAM_BOT_TOKEN y TELEGRAM_ADMIN_CHAT_ID.\n");
    exit(1);
}
echo App\Services\TelegramService::procesarPendientes(20) . " notificaciones entregadas.\n";
