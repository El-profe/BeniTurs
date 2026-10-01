<?php
/**
 * Inicialización del entorno de la aplicación BeniTurs / Trinidad Turismo.
 */

// 1. Definición del helper universal env() y carga de archivo .env si existe
if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed {
        $val = getenv($key);
        if ($val === false || $val === null) {
            $val = $_ENV[$key] ?? $_SERVER[$key] ?? null;
        }
        if ($val === null || $val === false) {
            return $default;
        }
        return match (strtolower((string)$val)) {
            'true', '(true)' => true,
            'false', '(false)' => false,
            'empty', '(empty)' => '',
            'null', '(null)' => null,
            default => $val,
        };
    }
}

$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile) && is_readable($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines !== false) {
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (str_contains($line, '=')) {
                [$k, $v] = explode('=', $line, 2);
                $k = trim($k);
                $v = trim($v);
                if (preg_match('/^"(.*)"$/s', $v, $m) || preg_match("/^'(.*)'$/s", $v, $m)) {
                    $v = $m[1];
                }
                putenv("{$k}={$v}");
                $_ENV[$k] = $v;
                $_SERVER[$k] = $v;
            }
        }
    }
}

// 2. Configuración de errores según el entorno
$appEnv = env('APP_ENV', 'local');
if ($appEnv === 'production') {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('log_errors', '1');
    $logDir = dirname(__DIR__) . '/storage/logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    ini_set('error_log', $logDir . '/app_error.log');
} else {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
}

// 3. Cargar configuración base
$config = require dirname(__DIR__) . '/config/app.php';

// 4. Establecer zona horaria oficial (Trinidad, Beni, Bolivia)
date_default_timezone_set($config['timezone'] ?? 'America/La_Paz');

// 5. Inicializar el autocargador de clases
require_once __DIR__ . '/Core/Autoloader.php';
\App\Core\Autoloader::register();

// 6. Configurar e iniciar sesión segura
if (session_status() === PHP_SESSION_NONE) {
    session_name($config['session_name'] ?? 'TRINI_TURISMO_SESSID');

    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'cookie_secure'   => $isHttps,
        'use_strict_mode' => 1
    ]);
}

// 7. Inicializar Enrutador y registrar rutas
$router = new \App\Core\Router();

require_once dirname(__DIR__) . '/routes/web.php';
require_once dirname(__DIR__) . '/routes/api.php';

return $router;