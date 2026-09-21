<?php
/**
 * Trinidad Turismo - Front Controller
 * Único punto de acceso público para solicitudes web y API.
 */

declare(strict_types=1);

// Constantes globales de rutas físicas
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'app');
define('PUBLIC_PATH', __DIR__);

// Inicializar la aplicación y despachar la solicitud
$router = require APP_PATH . '/bootstrap.php';
$router->dispatch();