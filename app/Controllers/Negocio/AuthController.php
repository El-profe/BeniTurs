<?php
namespace App\Controllers\Negocio;

use App\Core\Controller;
use App\Models\CuentaNegocio;
use App\Middleware\CsrfMiddleware;

class AuthController extends Controller {
    private CuentaNegocio $cuentaModel;

    public function __construct() {
        parent::__construct();
        $this->cuentaModel = new CuentaNegocio();
    }

    public function login(): void {
        if (!empty($_SESSION['negocio_id'])) {
            header("Location: {$this->config['base_url']}/negocio/dashboard");
            exit();
        }

        $error = $_SESSION['negocio_auth_error'] ?? null;
        unset($_SESSION['negocio_auth_error']);

        $this->render('negocio/login', [
            'titulo'    => 'Portal de Negocios y Comercios',
            'error'     => $error,
            'csrfToken' => CsrfMiddleware::obtenerToken()
        ], 'publico');
    }

    public function procesarLogin(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: {$this->config['base_url']}/negocio/login");
            exit();
        }

        if (!CsrfMiddleware::validarToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['negocio_auth_error'] = 'Sesión expirada. Intente nuevamente.';
            header("Location: {$this->config['base_url']}/negocio/login");
            exit();
        }

        $usuario = trim($_POST['usuario'] ?? '');
        $password = (string)($_POST['password'] ?? '');

        if (empty($usuario) || empty($password)) {
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
