<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Administrador;
use App\Middleware\CsrfMiddleware;

/**
 * Gestiona el inicio y cierre de sesión del administrador.
 */
class AuthController extends Controller {
    private Administrador $adminModel;

    public function __construct() {
        parent::__construct();
        $this->adminModel = new Administrador();
    }

    /**
     * Muestra el formulario de login o redirige al panel si ya hay sesión.
     */
    public function login(): void {
        if (!empty($_SESSION['admin_id'])) {
            header("Location: {$this->config['base_url']}/admin/dashboard");
            exit();
        }

        $error = $_SESSION['auth_error'] ?? null;
        unset($_SESSION['auth_error']);

        $csrfToken = CsrfMiddleware::obtenerToken();

        $this->render('auth/login', [
            'titulo'    => 'Acceso Administrativo',
            'error'     => $error,
            'csrfToken' => $csrfToken
        ], 'publico');
    }

    /**
     * Procesa las credenciales del formulario.
     */
    public function procesarLogin(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: {$this->config['base_url']}/admin/login");
            exit();
        }

        // 1. Validar Token CSRF
        $token = $_POST['csrf_token'] ?? '';
        if (!CsrfMiddleware::validarToken($token)) {
            $_SESSION['auth_error'] = 'Token de seguridad inválido o sesión expirada. Intente de nuevo.';
            header("Location: {$this->config['base_url']}/admin/login");
            exit();
        }

        $identificador = trim($_POST['usuario'] ?? '');
        $password = (string)($_POST['password'] ?? '');

        if (empty($identificador) || empty($password)) {
            $_SESSION['auth_error'] = 'Por favor complete todos los campos.';
            header("Location: {$this->config['base_url']}/admin/login");
            exit();
        }

        $admin = $this->adminModel->buscarPorIdentificador($identificador);

        // Solo la contraseña almacenada puede autenticar al administrador.
        $credencialesValidas = $admin && password_verify($password, $admin['password_hash']);

        if (!$credencialesValidas) {
            $_SESSION['auth_error'] = 'Credenciales de acceso incorrectas o cuenta deshabilitada.';
            header("Location: {$this->config['base_url']}/admin/login");
            exit();
        }

        // 2. Establecer sesión segura con regeneración de ID
        session_regenerate_id(true);
        $_SESSION['admin_id']     = $admin['id_administrador'];
        $_SESSION['admin_nombre'] = $admin['nombre'];
        $_SESSION['admin_user']   = $admin['usuario'];

        $this->adminModel->actualizarUltimoAcceso($admin['id_administrador']);

        header("Location: {$this->config['base_url']}/admin/dashboard");
        exit();
    }

    /**
     * Cierra la sesión activa.
     */
    public function logout(): void {
        unset($_SESSION['admin_id'], $_SESSION['admin_nombre'], $_SESSION['admin_user']);
        session_destroy();

        header("Location: {$this->config['base_url']}/admin/login");
        exit();
    }
}
