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
        header('Cache-Control: private, no-store');
        header('Referrer-Policy: no-referrer');
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

    private function validarPeticion(): int {
        $token = $_POST['csrf_token'] ?? null;
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !is_string($token) || !CsrfMiddleware::validarToken($token)) {
            $this->json(['success'=>false,'error'=>'Sesión expirada. Recarga la página.'], 403);
        }
        $id = filter_var($_POST['id_solicitud'] ?? null, FILTER_VALIDATE_INT);
        if (!$id || $id < 1) $this->json(['success'=>false,'error'=>'Solicitud inválida.'], 422);
        header('Cache-Control: private, no-store');
        return $id;
    }

    public function resolver(): void {
        $id = $this->validarPeticion();
        $observaciones = $_POST['observaciones_admin'] ?? '';
        if (($_POST['estado'] ?? '') !== 'RECHAZADA' || !is_string($observaciones) || mb_strlen($observaciones) > 2000) {
            $this->json(['success'=>false,'error'=>'Resolución inválida.'], 422);
        }
        try {
            \App\Services\SolicitudService::rechazar($id, (int)$_SESSION['admin_id'], trim($observaciones));
        } catch (\PDOException $e) {
            error_log('No se pudo rechazar la solicitud #' . $id);
            $this->json(['success'=>false,'error'=>'No se pudo guardar la resolución.'], 500);
        } catch (\RuntimeException $e) {
            $this->json(['success'=>false,'error'=>$e->getMessage()], 409);
        }
        $this->json(['success'=>true,'estado'=>'RECHAZADA','message'=>'Solicitud rechazada.']);
    }

    public function convertirAFicha(): void {
        $this->json(['success'=>false,'error'=>'Utiliza Aprobar y Aprovisionar después de verificar el pago.'], 409);
    }

    public function aprovisionar(): void {
        $id = $this->validarPeticion();
        try {
            $resultado = \App\Services\SolicitudService::aprovisionarNegocioCompleto($id, (int)$_SESSION['admin_id']);
        } catch (\PDOException $e) {
            error_log('Error al aprovisionar solicitud #' . $id);
            $this->json(['success'=>false,'error'=>'No se pudo completar el aprovisionamiento. No se guardaron cambios.'], 500);
        } catch (\RuntimeException | \InvalidArgumentException $e) {
            $this->json(['success'=>false,'error'=>$e->getMessage()], 409);
        } catch (\Throwable $e) {
            error_log('Error al aprovisionar solicitud #' . $id);
            $this->json(['success'=>false,'error'=>'No se pudo completar el aprovisionamiento.'], 500);
        }
        $this->json(['success'=>true,'estado'=>'ACEPTADA','message'=>'Negocio activado. Puedes entregar las credenciales por WhatsApp.'] + $resultado);
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
