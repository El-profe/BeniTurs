<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Pago;
use App\Models\Tarifa;
use App\Services\PagoService;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;

class PagoController extends Controller {
    private Pago $pagoModel;
    private Tarifa $tarifaModel;

    public function __construct() {
        parent::__construct();
        AuthMiddleware::autenticar();
        $this->pagoModel   = new Pago();
        $this->tarifaModel = new Tarifa();
    }

    public function index(): void {
        $filtroEstado = trim($_GET['estado'] ?? '');
        $pagos = $this->pagoModel->listar($filtroEstado ?: null);

        // Identificar publicaciones comerciales próximas a vencer (en los siguientes 5 días) (RF-29)
        $db = Database::getConnection();
        $sqlAlertas = "SELECT l.nombre, v.fecha_vencimiento, DATEDIFF(v.fecha_vencimiento, CURDATE()) AS dias_restantes
                       FROM vigencias v
                       INNER JOIN lugares l ON v.id_lugar = l.id_lugar
                       WHERE v.fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 5 DAY)
                       ORDER BY v.fecha_vencimiento ASC";
        $proximosVencimientos = $db->query($sqlAlertas)->fetchAll();

        $mensaje = $_SESSION['admin_flash'] ?? null;
        $error   = $_SESSION['admin_error'] ?? null;
        unset($_SESSION['admin_flash'], $_SESSION['admin_error']);

        $this->render('admin/pagos/index', [
            'titulo'               => 'Gestión de Pagos y Mensualidades',
            'pagos'                => $pagos,
            'filtroActual'         => $filtroEstado,
            'proximosVencimientos' => $proximosVencimientos,
            'mensaje'              => $mensaje,
            'error'                => $error,
            'csrfToken'            => CsrfMiddleware::obtenerToken()
        ], 'admin');
    }

    public function crear(): void {
        $db = Database::getConnection();
        
        // Listar únicamente establecimientos comerciales
        $comercios = $db->query("SELECT l.id_lugar, l.nombre, s.plan_solicitado FROM lugares l
            LEFT JOIN solicitudes s ON s.id_solicitud = l.id_solicitud_origen
            WHERE l.tipo_lugar = 'COMERCIAL' ORDER BY l.nombre ASC")->fetchAll();
        $tarifa = $this->tarifaModel->obtenerTarifaVigente();
        $error = $_SESSION['admin_error'] ?? null;
        unset($_SESSION['admin_error']);

        $this->render('admin/pagos/crear', [
            'titulo'    => 'Registrar Pago Pendiente',
            'comercios' => $comercios,
            'tarifa'    => $tarifa,
            'error'     => $error,
            'csrfToken' => CsrfMiddleware::obtenerToken()
        ], 'admin');
    }

    public function guardar(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !CsrfMiddleware::validarToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['admin_error'] = 'Sesión expirada o token inválido.';
            header("Location: {$this->config['base_url']}/admin/pagos/crear");
            exit();
        }

        $idLugar         = (int)($_POST['id_lugar'] ?? 0);
        $tarifa          = $this->tarifaModel->obtenerTarifaVigente();
        $idTarifa        = (int)($tarifa['id_tarifa'] ?? 0);
        $meses          = filter_var($_POST['meses_duracion'] ?? null, FILTER_VALIDATE_INT);
        $monto           = (float)($_POST['monto'] ?? 0);
        $fechaDeclarada  = trim($_POST['fecha_pago_declarada'] ?? date('Y-m-d'));
        $comprobante     = trim($_POST['numero_comprobante'] ?? '');
        $metodo          = trim($_POST['metodo_pago'] ?? 'Transferencia bancaria / QR');
        $observaciones   = trim($_POST['observaciones'] ?? '');

        if ($idLugar <= 0 || $monto <= 0 || !is_finite($monto) || !$tarifa || !in_array($meses, [1, 12], true)) {
            $_SESSION['admin_error'] = 'Seleccione un comercio e indique un monto válido.';
            header("Location: {$this->config['base_url']}/admin/pagos/crear");
            exit();
        }

        try {
            $lugar = (new \App\Models\Lugar())->buscarPorId($idLugar);
            if (!$lugar || $lugar['tipo_lugar'] !== 'COMERCIAL') {
                throw new \RuntimeException('Seleccione un establecimiento comercial válido.');
            }
            $fecha = \DateTimeImmutable::createFromFormat('!Y-m-d', $fechaDeclarada);
            if (!$fecha || $fecha->format('Y-m-d') !== $fechaDeclarada) {
                throw new \RuntimeException('La fecha declarada no es válida.');
            }
            $idPago = $this->pagoModel->registrar([
                'id_lugar'              => $idLugar,
                'id_tarifa'             => $idTarifa,
                'monto'                 => $monto,
                'meses_duracion'        => $meses,
                'fecha_pago_declarada'  => $fechaDeclarada,
                'numero_comprobante'    => $comprobante,
                'metodo_pago'           => $metodo,
                'observaciones'         => $observaciones
            ]);
        } catch (\Throwable $e) {
            $_SESSION['admin_error'] = 'No se pudo registrar el pago: ' . $e->getMessage();
            header("Location: {$this->config['base_url']}/admin/pagos/crear");
            exit();
        }

        $_SESSION['admin_flash'] = "Pago #{$idPago} registrado exitosamente en estado PENDIENTE.";
        header("Location: {$this->config['base_url']}/admin/pagos");
        exit();
    }

    public function confirmar(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !CsrfMiddleware::validarToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['admin_error'] = 'Token inválido.';
            header("Location: {$this->config['base_url']}/admin/pagos");
            exit();
        }

        $idPago = (int)($_POST['id_pago'] ?? 0);
        $idAdmin = (int)$_SESSION['admin_id'];

        try {
            $resultado = PagoService::confirmarPago($idPago, $idAdmin);
            $_SESSION['admin_flash'] = "¡Pago #{$idPago} CONFIRMADO! Vigencia generada del {$resultado['fecha_inicio']} al {$resultado['fecha_vencimiento']} ({$resultado['tipo_periodo']}). La visibilidad depende de la aprobación, habilitación y fechas de cobertura.";
        } catch (\Exception $e) {
            $_SESSION['admin_error'] = "Error al confirmar: " . $e->getMessage();
        }

        header("Location: {$this->config['base_url']}/admin/pagos");
        exit();
    }

    public function anular(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !CsrfMiddleware::validarToken($_POST['csrf_token'] ?? '')) {
            header("Location: {$this->config['base_url']}/admin/pagos");
            exit();
        }

        $idPago = (int)($_POST['id_pago'] ?? 0);
        $motivo = trim($_POST['motivo_anulacion'] ?? 'Comprobante no acreditado');

        $this->pagoModel->anular($idPago, $motivo);
        $_SESSION['admin_flash'] = "El pago #{$idPago} ha sido ANULADO.";
        header("Location: {$this->config['base_url']}/admin/pagos");
        exit();
    }
}
