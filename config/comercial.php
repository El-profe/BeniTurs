<?php
// Secretos comerciales y notificaciones mediante variables de entorno o comercial.local.php
$config = [
    'banco'             => (string)env('BENITURS_BANCO', ''),
    'titular'           => (string)env('BENITURS_TITULAR', ''),
    'cuenta'            => (string)env('BENITURS_CUENTA', ''),
    'qr_url'            => (string)env('BENITURS_QR_URL', ''),
    'telegram_activo'   => (bool)env('TELEGRAM_ACTIVO', false),
    'telegram_token'    => (string)env('TELEGRAM_BOT_TOKEN', ''),
    'admin_chat_id'     => (string)env('TELEGRAM_ADMIN_CHAT_ID', ''),
    'telegram_admin_id' => (int)env('TELEGRAM_ADMIN_LOCAL_ID', 0),
    'webhook_secret'    => (string)env('TELEGRAM_WEBHOOK_SECRET', ''),
    'credential_key'    => (string)env('BENITURS_CREDENTIAL_KEY', ''),
];

$local = __DIR__ . '/comercial.local.php';
$localConfig = is_file($local) ? (require $local) : [];
$envConfig = array_filter($config, static fn($v) => $v !== '' && $v !== 0 && $v !== false);

return array_replace($config, $localConfig, $envConfig);
