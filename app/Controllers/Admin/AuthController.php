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
        // Conservar el origen del navegador (localhost o túnel) y su cookie de sesión.
        $this->config['base_url'] = rtrim(parse_url($this->config['base_url'], PHP_URL_PATH) ?: '', '/');
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
        $usuarioAnterior = $_SESSION['auth_usuario'] ?? '';
        unset($_SESSION['auth_usuario']);
        unset($_SESSION['auth_error']);

        $csrfToken = CsrfMiddleware::obtenerToken();

        $this->render('auth/login', [
            'titulo'    => 'Acceso Administrativo',
            'error'     => $error,
            'usuarioAnterior' => $usuarioAnterior,
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
        if (!is_string($token) || !CsrfMiddleware::validarToken($token)) {
            $_SESSION['auth_error'] = 'Token de seguridad inválido o sesión expirada. Intente de nuevo.';
            header("Location: {$this->config['base_url']}/admin/login");
            exit();
        }

        $identificador = is_string($_POST['usuario'] ?? null) ? trim($_POST['usuario']) : '';
        $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
        $_SESSION['auth_usuario'] = mb_substr($identificador, 0, 120);

        if ($identificador === '' || $password === '' || mb_strlen($identificador) > 120) {
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
        unset($_SESSION['auth_usuario'], $_SESSION['auth_error']);
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
