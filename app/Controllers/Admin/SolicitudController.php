<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Solicitud;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;

class SolicitudController extends Controller {
    private Solicitud $solicitudModel;

    public function __construct() {
        parent::__construct();
        AuthMiddleware::autenticar();
        $this->solicitudModel   = new Solicitud();
    }

    public function index(): void {
        $filtroEstado = trim($_GET['estado'] ?? '');
        $solicitudes  = $this->solicitudModel->listar($filtroEstado ?: null);
        $mensaje      = $_SESSION['admin_flash'] ?? null;
        $error        = $_SESSION['admin_error'] ?? null;
        unset($_SESSION['admin_flash'], $_SESSION['admin_error']);

        $this->render('admin/solicitudes/index', [
            'titulo'       => 'Revisión de Solicitudes Comerciales',
            'solicitudes'  => $solicitudes,
            'filtroActual' => $filtroEstado,
            'mensaje'      => $mensaje,
            'error'        => $error,
            'csrfToken'    => CsrfMiddleware::obtenerToken()
        ], 'admin');
    }

    /**
     * Resuelve administrativamente una solicitud (ACEPTADA o RECHAZADA) (RF-20)
     */
    public function resolver(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !CsrfMiddleware::validarToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['admin_error'] = 'Petición no permitida o sesión expirada.';
            header("Location: {$this->config['base_url']}/admin/solicitudes");
            exit();
        }

        $idSolicitud   = (int)($_POST['id_solicitud'] ?? 0);
        $estado        = trim($_POST['estado'] ?? '');
        $observaciones = trim($_POST['observaciones_admin'] ?? '');
        $idAdmin       = (int)$_SESSION['admin_id'];

        if ($idSolicitud === 0 || !in_array($estado, ['ACEPTADA', 'RECHAZADA'])) {
            $_SESSION['admin_error'] = 'Parámetros de resolución inválidos.';
            header("Location: {$this->config['base_url']}/admin/solicitudes");
            exit();
        }

        $this->solicitudModel->cambiarEstado($idSolicitud, $estado, $idAdmin, $observaciones);

        $_SESSION['admin_flash'] = "La solicitud #{$idSolicitud} ha sido marcada como {$estado}.";
        header("Location: {$this->config['base_url']}/admin/solicitudes");
        exit();
    }

    /**
     * Convierte una solicitud ACEPTADA en una Ficha Comercial sin duplicar (RF-21)
     */
    public function convertirAFicha(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !CsrfMiddleware::validarToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['admin_error'] = 'Token de seguridad inválido.';
            header("Location: {$this->config['base_url']}/admin/solicitudes");
            exit();
        }

        $idSolicitud = (int)($_POST['id_solicitud'] ?? 0);
        try {
            $idLugar = \App\Services\SolicitudService::convertirAFicha($idSolicitud, (int)$_SESSION['admin_id']);
            $_SESSION['admin_flash'] = "Ficha comercial #{$idLugar} creada correctamente.";
            header("Location: {$this->config['base_url']}/admin/lugares");
        } catch (\Throwable $e) {
            $_SESSION['admin_error'] = 'No se pudo convertir la solicitud: ' . $e->getMessage();
            header("Location: {$this->config['base_url']}/admin/solicitudes");
        }
        exit();
    }
}