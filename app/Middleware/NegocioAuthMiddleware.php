<?php
namespace App\Middleware;

class NegocioAuthMiddleware {
    public static function autenticar(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['negocio_id']) || empty($_SESSION['negocio_lugar_id'])) {
            $config = require dirname(__DIR__, 2) . '/config/app.php';
            $baseUrl = rtrim($config['base_url'], '/');
            header("Location: {$baseUrl}/negocio/login");
            exit();
        }
    }
}