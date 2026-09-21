<?php
namespace App\Middleware;

/**
 * Genera y valida tokens CSRF para solicitudes POST.
 */
class CsrfMiddleware {

    /**
     * Retorna el token de sesión actual o genera uno criptográficamente seguro.
     */
    public static function obtenerToken(): string {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    /**
     * Valida el token recibido en la petición contra el almacenado en sesión.
     */
    public static function validarToken(?string $token): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $sessionToken = $_SESSION['csrf_token'] ?? '';

        if (empty($sessionToken) || empty($token)) {
            return false;
        }

        return hash_equals($sessionToken, $token);
    }
}