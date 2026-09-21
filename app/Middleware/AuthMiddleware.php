<?php
namespace App\Middleware;

/**
 * Restringe el acceso a las rutas administrativas únicamente a sesiones autorizadas.
 */
class AuthMiddleware {

    public static function autenticar(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['admin_id'])) {
            $config = require dirname(__DIR__, 2) . '/config/app.php';
            $baseUrl = rtrim($config['base_url'], '/');
            header("Location: {$baseUrl}/admin/login");
            exit();
        }
    }
}