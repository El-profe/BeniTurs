<?php
namespace App\Controllers\Publico;

use App\Core\Controller;
use App\Models\Categoria;
use App\Models\Municipio;
use App\Models\Tarifa;
use App\Middleware\CsrfMiddleware;
use App\Services\SolicitudEntradaService;
use App\Services\TelegramService;
use InvalidArgumentException;
use Throwable;

class SolicitudController extends Controller {
    public function index(): void {
        $categorias = array_filter((new Categoria())->listarTodasActivas(),
            static fn(array $categoria): bool => $categoria['tipo_defecto'] === 'COMERCIAL');
        $municipios = (new Municipio())->listarActivos();
        $planes = (new Tarifa())->listarPlanesActivos();

        $this->render('publico/solicitudes/formulario', [
            'titulo'        => 'Publica tu negocio en BeniTurs',
            'categorias'    => $categorias,
            'municipios'    => $municipios,
            'planes'        => $planes,
            'cobro'         => require dirname(__DIR__, 3) . '/config/comercial.php',
            'csrfToken'     => CsrfMiddleware::obtenerToken()
        ], 'publico');
    }

    public function apiEnviar(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success'=>false, 'error'=>'Método no permitido.'], 405);
            return;
        }
        $token = $_POST['csrf_token'] ?? null;
        if (!is_string($token) || !CsrfMiddleware::validarToken($token)) {
            $this->json(['success'=>false, 'error'=>'Sesión expirada o carga demasiado grande. Recargue el formulario; el comprobante admite hasta 5 MB.'], 403);
            return;
        }
        try {
            $archivo = $_FILES['comprobante'] ?? [];
            if (!is_array($archivo)) throw new InvalidArgumentException('Adjunte un comprobante válido.');
            $idSolicitud = SolicitudEntradaService::recibir($_POST, $archivo);
        } catch (InvalidArgumentException $e) {
            $this->json(['success'=>false, 'error'=>$e->getMessage()], 422);
            return;
        } catch (Throwable $e) {
            error_log('Error al recibir solicitud: ' . $e->getMessage());
            $this->json(['success'=>false, 'error'=>'No se pudo guardar la solicitud. Intente nuevamente.'], 500);
            return;
        }
        // La solicitud ya está confirmada: un fallo de Telegram no debe deshacerla.
        try {
            TelegramService::procesarPendientes(1, $idSolicitud);
        } catch (Throwable $e) {
            error_log('Telegram: solicitud #' . $idSolicitud . ' guardada; entrega pendiente.');
        }
        $this->json(['success'=>true,
            'message'=>'Solicitud enviada con éxito. Verificaremos tu abono en breve.']);
    }
}
