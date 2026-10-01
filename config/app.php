<?php
/**
 * Configuración general del proyecto Trinidad Turismo / BeniTurs.
 * Accesible únicamente desde el backend.
 */

$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$isLocal = ($host === 'localhost' || str_starts_with($host, 'localhost:') || str_contains($host, '127.0.0.1'));

if ($isLocal) {
    // 1. Entorno local (Laragon / XAMPP): SIEMPRE usar rutas locales de localhost
    $envBaseUrl = 'http://' . $host . '/trinidad_turismo/public';
} else {
    // 2. Entorno remoto (InfinityFree, VPS, etc.)
    $envBaseUrl = env('BENITURS_BASE_URL');
    // Si no está configurada o se subió por error una URL de localhost, auto-detectar host remoto
    if (empty($envBaseUrl) || str_contains($envBaseUrl, 'localhost') || str_contains($envBaseUrl, '127.0.0.1')) {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
            ? 'https://' : 'http://';
        $envBaseUrl = rtrim($protocol . $host, '/');
    }
}

return [
    'app_name'     => 'Trinidad Turismo & Directorio Comercial',
    'app_env'      => (string)env('APP_ENV', $isLocal ? 'local' : 'production'),
    'base_url'     => rtrim($envBaseUrl, '/'),
    'timezone'     => 'America/La_Paz',
    'currency'     => 'Bs',
    'tarifa_base'  => 250.00,
    'version'      => '1.0.0',
    'session_name' => 'TRINI_TURISMO_SESSID'
];
