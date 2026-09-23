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
        $admin = \App\Core\Database::getConnection()->prepare('SELECT id_administrador FROM administradores WHERE id_administrador = ? AND activo = 1');
        $admin->execute([(int)$_SESSION['admin_id']]);
        if (!$admin->fetchColumn()) { http_response_code(403); exit('Acceso no autorizado.'); }
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
        $token = $_POST['csrf_token'] ?? null;
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !is_string($token) || !CsrfMiddleware::validarToken($token)) {
            $_SESSION['admin_error'] = 'Petición no permitida o sesión expirada.';
            header("Location: {$this->config['base_url']}/admin/solicitudes");
            exit();
        }

        $idSolicitud   = filter_var($_POST['id_solicitud'] ?? null, FILTER_VALIDATE_INT);
        $estado        = is_string($_POST['estado'] ?? null) ? trim($_POST['estado']) : '';
        $observaciones = is_string($_POST['observaciones_admin'] ?? null) ? trim($_POST['observaciones_admin']) : '';
        $idAdmin       = (int)$_SESSION['admin_id'];

        if (!$idSolicitud || $idSolicitud < 1 || $estado !== 'RECHAZADA' || mb_strlen($observaciones) > 2000) {
            $_SESSION['admin_error'] = 'Parámetros de resolución inválidos.';
            header("Location: {$this->config['base_url']}/admin/solicitudes");
            exit();
        }

        $stmt = \App\Core\Database::getConnection()->prepare("UPDATE solicitudes SET estado = 'RECHAZADA',
            id_administrador_revision = ?, observaciones_admin = ?, fecha_revision = NOW()
            WHERE id_solicitud = ? AND estado = 'PENDIENTE'");
        $stmt->execute([$idAdmin, $observaciones, $idSolicitud]);
        if ($stmt->rowCount() !== 1) {
            $_SESSION['admin_error'] = 'La solicitud ya fue procesada.';
            header("Location: {$this->config['base_url']}/admin/solicitudes");
            exit();
        }

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

    public function aprovisionar(): void {
        $token = $_POST['csrf_token'] ?? null;
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !is_string($token) || !CsrfMiddleware::validarToken($token)) {
            http_response_code(403);
            exit('Token de seguridad inválido.');
        }
        $id = filter_var($_POST['id_solicitud'] ?? null, FILTER_VALIDATE_INT);
        try {
            if (!$id || $id < 1) throw new \InvalidArgumentException('Solicitud inválida.');
            $resultado = \App\Services\SolicitudService::aprovisionarNegocioCompleto($id, (int)$_SESSION['admin_id']);
            $_SESSION['admin_flash'] = "Negocio activado: ficha #{$resultado['id_lugar']}, cuenta, pago confirmado y vigencia hasta {$resultado['fecha_vencimiento']}. Puedes enviar la bienvenida por WhatsApp.";
        } catch (\PDOException $e) {
            error_log('Error de aprovisionamiento: ' . $e->getMessage());
            $_SESSION['admin_error'] = 'No se pudo aprovisionar. Verifique si el usuario solicitado ya existe. No se guardaron cambios.';
        } catch (\RuntimeException | \InvalidArgumentException $e) {
            $_SESSION['admin_error'] = $e->getMessage();
        } catch (\Throwable $e) {
            error_log('Error de aprovisionamiento: ' . $e->getMessage());
            $_SESSION['admin_error'] = 'No se pudo completar el aprovisionamiento. No se guardaron cambios.';
        }
        header("Location: {$this->config['base_url']}/admin/solicitudes");
        exit();
    }

    public function comprobante(): void {
        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
        $s = $id ? $this->solicitudModel->buscarPorId($id) : null;
        try {
            if (!$s || empty($s['comprobante_archivo'])) throw new \RuntimeException('No disponible.');
            $ruta = \App\Services\ComprobanteService::ruta($s['comprobante_archivo']);
            $mime = \App\Services\ComprobanteService::validarImagen($ruta);
        } catch (\Throwable $e) {
            http_response_code(404);
            exit('Comprobante no disponible.');
        }
        header('Content-Type: ' . $mime);
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, no-store');
        header('Content-Security-Policy: default-src \'none\'; sandbox');
        header('Content-Length: ' . filesize($ruta));
        readfile($ruta);
        exit();
    }
}
