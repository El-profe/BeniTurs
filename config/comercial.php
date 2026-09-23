<?php
// Secretos mediante variables de entorno o comercial.local.php (fuera del repositorio).
$config = [
    'banco' => getenv('BENITURS_BANCO') ?: '',
    'titular' => getenv('BENITURS_TITULAR') ?: '',
    'cuenta' => getenv('BENITURS_CUENTA') ?: '',
    'qr_url' => getenv('BENITURS_QR_URL') ?: '',
    'telegram_token' => getenv('TELEGRAM_BOT_TOKEN') ?: '',
    'admin_chat_id' => getenv('TELEGRAM_ADMIN_CHAT_ID') ?: '',
    'telegram_admin_id' => (int)(getenv('TELEGRAM_ADMIN_LOCAL_ID') ?: 0),
    'webhook_secret' => getenv('TELEGRAM_WEBHOOK_SECRET') ?: '',
    'credential_key' => getenv('BENITURS_CREDENTIAL_KEY') ?: '',
];
$local = __DIR__ . '/comercial.local.php';
return is_file($local) ? array_replace($config, require $local) : $config;
