<?php
namespace App\Middleware;

class NegocioAuthMiddleware {
    public static function autenticar(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!self::esCuentaActualValida()) {
            unset($_SESSION['negocio_id'], $_SESSION['negocio_lugar_id'], $_SESSION['negocio_nombre'], $_SESSION['negocio_user']);
            $config = require dirname(__DIR__, 2) . '/config/app.php';
            $baseUrl = rtrim($config['base_url'], '/');
            header("Location: {$baseUrl}/negocio/login");
            exit();
        }
    }

    public static function esCuentaActualValida(): bool {
        if (empty($_SESSION['negocio_id']) || empty($_SESSION['negocio_lugar_id'])) return false;
        $stmt = \App\Core\Database::getConnection()->prepare('SELECT id_cuenta FROM cuentas_negocio
            WHERE id_cuenta = ? AND id_lugar = ? AND activo = 1');
        $stmt->execute([(int)$_SESSION['negocio_id'], (int)$_SESSION['negocio_lugar_id']]);
        return (bool)$stmt->fetchColumn();
    }
}
