<?php
/**
 * Inicialización del entorno de la aplicación.
 */

// 1. Cargar configuración base
$config = require dirname(__DIR__) . '/config/app.php';

// 2. Establecer zona horaria oficial (Trinidad, Beni, Bolivia)
date_default_timezone_set($config['timezone'] ?? 'America/La_Paz');

// 3. Inicializar el autocargador de clases
require_once __DIR__ . '/Core/Autoloader.php';
\App\Core\Autoloader::register();

// 4. Configurar e iniciar sesión segura
if (session_status() === PHP_SESSION_NONE) {
    session_name($config['session_name']);
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax'
    ]);
}

// 5. Inicializar Enrutador y registrar rutas
$router = new \App\Core\Router();

require_once dirname(__DIR__) . '/routes/web.php';
require_once dirname(__DIR__) . '/routes/api.php';

return $router;