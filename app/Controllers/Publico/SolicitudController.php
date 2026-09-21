<?php
namespace App\Controllers\Publico;

use App\Core\Controller;
use App\Models\Categoria;
use App\Models\Solicitud;
use App\Middleware\CsrfMiddleware;

class SolicitudController extends Controller {
    private Categoria $categoriaModel;
    private Solicitud $solicitudModel;

    public function __construct() {
        parent::__construct();
        $this->categoriaModel = new Categoria();
        $this->solicitudModel = new Solicitud();
    }

    public function index(): void {
        $categorias = $this->categoriaModel->listarTodasActivas();
        
        $tarifaMensual = (float)($this->config['tarifa_base'] ?? 250.00);
        $tarifaAnual   = $tarifaMensual * 10; // 2 meses de descuento promocional

        $this->render('publico/solicitudes/formulario', [
            'titulo'        => 'Publica tu Negocio en Trinidad',
            'categorias'    => $categorias,
            'tarifaMensual' => $tarifaMensual,
            'tarifaAnual'   => $tarifaAnual,
            'csrfToken'     => CsrfMiddleware::obtenerToken()
        ], 'publico');
    }

    public function apiEnviar(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'error' => 'Método no permitido.'], 405);
        }

        $token = $_POST['csrf_token'] ?? '';
        if (!CsrfMiddleware::validarToken($token)) {
            $this->json(['success' => false, 'error' => 'Sesión expirada o token inválido.'], 403);
        }

        $nombreEstablecimiento = trim($_POST['nombre_establecimiento'] ?? '');
        $idCategoria           = (int)($_POST['id_categoria'] ?? 0);
        $planSolicitado        = in_array($_POST['plan_solicitado'] ?? '', ['MENSUAL', 'ANUAL']) ? $_POST['plan_solicitado'] : 'MENSUAL';
        $nombreSolicitante     = trim($_POST['nombre_solicitante'] ?? '');
        $telefonoContacto      = trim($_POST['telefono_contacto'] ?? '');
        $emailContacto         = trim($_POST['email_contacto'] ?? '');
        $direccion             = trim($_POST['direccion'] ?? '');
        $descripcion           = trim($_POST['descripcion'] ?? '');
        $horarios              = trim($_POST['horarios'] ?? '');

        if (empty($nombreEstablecimiento) || $idCategoria === 0 || empty($nombreSolicitante) || empty($telefonoContacto) || empty($direccion) || empty($descripcion)) {
            $this->json(['success' => false, 'error' => 'Por favor complete todos los datos obligatorios (*).'], 422);
        }

        if (!empty($emailContacto) && !filter_var($emailContacto, FILTER_VALIDATE_EMAIL)) {
            $this->json(['success' => false, 'error' => 'El correo electrónico no es válido.'], 422);
        }

        try {
            $idSolicitud = $this->solicitudModel->registrar([
                'nombre_establecimiento' => $nombreEstablecimiento,
                'id_categoria'          => $idCategoria,
                'plan_solicitado'       => $planSolicitado,
                'nombre_solicitante'    => $nombreSolicitante,
                'telefono_contacto'     => $telefonoContacto,
                'email_contacto'        => $emailContacto,
                'direccion'             => $direccion,
                'descripcion'           => $descripcion,
                'horarios'              => $horarios
            ]);

            $this->json([
                'success'      => true,
                'id_solicitud' => $idSolicitud,
                'mensaje'      => "¡Solicitud recibida! Te contactaremos al {$telefonoContacto} para coordinar la publicación bajo el Plan {$planSolicitado}."
            ]);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'error' => 'Error al guardar la solicitud: ' . $e->getMessage()], 500);
        }
    }
}