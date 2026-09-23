<?php
namespace App\Controllers\Publico;

use App\Core\Controller;
use App\Models\Categoria;
use App\Middleware\CsrfMiddleware;
use App\Services\RegistroNegocioService;
use InvalidArgumentException;
use Throwable;

class SolicitudController extends Controller {
    public function index(): void {
        $categorias = array_filter((new Categoria())->listarTodasActivas(),
            static fn(array $categoria): bool => $categoria['tipo_defecto'] === 'COMERCIAL');
        $this->render('publico/solicitudes/formulario', [
            'titulo' => 'Publica tu negocio en BeniTurs',
            'categorias' => $categorias,
            'tarifaMensual' => 250.00,
            'tarifaAnual' => 2500.00,
            'csrfToken' => CsrfMiddleware::obtenerToken()
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
            $cuenta = RegistroNegocioService::registrar($_POST, $archivo);
        } catch (InvalidArgumentException $e) {
            $this->json(['success'=>false, 'error'=>$e->getMessage()], 422);
            return;
        } catch (Throwable $e) {
            error_log('Error al recibir solicitud: ' . $e->getMessage());
            $this->json(['success'=>false, 'error'=>'No se pudo guardar la solicitud. Intente nuevamente.'], 500);
            return;
        }
        session_regenerate_id(true);
        $_SESSION['negocio_id'] = $cuenta['id_cuenta'];
        $_SESSION['negocio_lugar_id'] = $cuenta['id_lugar'];
        $_SESSION['negocio_nombre'] = $cuenta['nombre_negocio'];
        $_SESSION['negocio_user'] = $cuenta['usuario'];
        $this->json(['success'=>true, 'redirect'=>'/negocio/dashboard',
            'mensaje'=>'Tu cuenta está lista. Ya puedes configurar tu ficha mientras verificamos el comprobante.']);
    }
}
