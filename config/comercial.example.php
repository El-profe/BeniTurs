<?php
// Copiar a comercial.local.php si Apache no dispone de variables de entorno.
return [
    'banco' => '',
    'titular' => '',
    'cuenta' => '',
    'qr_url' => '', // URL pública de la imagen institucional real; no generar un QR ficticio.
    'telegram_token' => '',
    'admin_chat_id' => '', // ID personal del administrador; chat privado con el bot.
    'telegram_admin_id' => 0, // ID de administradores en MariaDB (opcional). NO es el ID de Telegram.
    'webhook_secret' => '', // Secreto aleatorio independiente del token del bot.
    'credential_key' => '', // Base64 de 32 bytes; vacío usa storage/private/credenciales.key.
];
