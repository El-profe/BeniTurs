<?php
namespace App\Controllers\Negocio;

use App\Core\Controller;
use App\Models\CuentaNegocio;
use App\Middleware\CsrfMiddleware;

class AuthController extends Controller {
    private CuentaNegocio $cuentaModel;

    public function __construct() {
        parent::__construct();
        // Conservar el origen del navegador (localhost o túnel) y su cookie de sesión.
        $this->config['base_url'] = rtrim(parse_url($this->config['base_url'], PHP_URL_PATH) ?: '', '/');
        $this->cuentaModel = new CuentaNegocio();
    }

    public function login(): void {
        if (!empty($_SESSION['negocio_id'])) {
            header("Location: {$this->config['base_url']}/negocio/dashboard");
            exit();
        }

        $error = $_SESSION['negocio_auth_error'] ?? null;
        $usuarioAnterior = $_SESSION['negocio_auth_usuario'] ?? '';
        unset($_SESSION['negocio_auth_usuario']);
        unset($_SESSION['negocio_auth_error']);

        $this->render('negocio/login', [
            'titulo'    => 'Portal de Negocios y Comercios',
            'error'     => $error,
            'usuarioAnterior' => $usuarioAnterior,
            'csrfToken' => CsrfMiddleware::obtenerToken()
        ], 'publico');
    }

    public function procesarLogin(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: {$this->config['base_url']}/negocio/login");
            exit();
        }

        $token = $_POST['csrf_token'] ?? '';
        if (!is_string($token) || !CsrfMiddleware::validarToken($token)) {
            $_SESSION['negocio_auth_error'] = 'Sesión expirada. Intente nuevamente.';
            header("Location: {$this->config['base_url']}/negocio/login");
            exit();
        }

        $usuario = is_string($_POST['usuario'] ?? null) ? trim($_POST['usuario']) : '';
        $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
        $_SESSION['negocio_auth_usuario'] = mb_substr($usuario, 0, 120);

        if ($usuario === '' || $password === '' || mb_strlen($usuario) > 120) {
            $_SESSION['negocio_auth_error'] = 'Ingrese usuario y contraseña.';
            header("Location: {$this->config['base_url']}/negocio/login");
            exit();
        }

        $cuenta = $this->cuentaModel->buscarPorCredencial($usuario);

        $valido = $cuenta && password_verify($password, $cuenta['password_hash']);

        if (!$valido) {
            $_SESSION['negocio_auth_error'] = 'Credenciales no válidas o cuenta inactiva.';
            header("Location: {$this->config['base_url']}/negocio/login");
            exit();
        }

        session_regenerate_id(true);
        unset($_SESSION['negocio_auth_usuario'], $_SESSION['negocio_auth_error']);
        $_SESSION['negocio_id']       = $cuenta['id_cuenta'];
        $_SESSION['negocio_lugar_id'] = $cuenta['id_lugar'];
        $_SESSION['negocio_nombre']   = $cuenta['nombre_negocio'];
        $_SESSION['negocio_user']     = $cuenta['usuario'];

        $this->cuentaModel->actualizarUltimoAcceso($cuenta['id_cuenta']);
        // La entrega ya se realizó: retirar la copia cifrada de la clave temporal.
        \App\Core\Database::getConnection()->prepare('UPDATE solicitudes SET credenciales_cifradas=NULL WHERE id_cuenta_creada=?')
            ->execute([$cuenta['id_cuenta']]);

        header("Location: {$this->config['base_url']}/negocio/dashboard");
        exit();
    }

    public function logout(): void {
        unset(
            $_SESSION['negocio_id'],
            $_SESSION['negocio_lugar_id'],
            $_SESSION['negocio_nombre'],
            $_SESSION['negocio_user']
        );
        header("Location: {$this->config['base_url']}/negocio/login");
        exit();
    }
}
