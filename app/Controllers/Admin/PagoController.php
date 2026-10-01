<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Pago;
use App\Models\Tarifa;
use App\Services\PagoService;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use PDO;

class PagoController extends Controller {
    private Pago $pagoModel;
    private Tarifa $tarifaModel;

    public function __construct() {
        parent::__construct();
        AuthMiddleware::autenticar();
        $admin = Database::getConnection()->prepare('SELECT id_administrador FROM administradores WHERE id_administrador = ? AND activo = 1');
        $admin->execute([(int)$_SESSION['admin_id']]);
        if (!$admin->fetchColumn()) { http_response_code(403); exit('Acceso no autorizado.'); }
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

        // Resumen financiero: solo pagos confirmados cuentan como ingresos reales.
        $totalConfirmado = (float)$db->query("SELECT COALESCE(SUM(monto), 0) FROM pagos WHERE estado = 'CONFIRMADO'")->fetchColumn();
        $pendiente = $db->query("SELECT COUNT(*) AS cantidad, COALESCE(SUM(monto), 0) AS monto FROM pagos WHERE estado = 'PENDIENTE'")->fetch(PDO::FETCH_ASSOC);
        $comerciosVigentes = (int)$db->query("SELECT COUNT(DISTINCT v.id_lugar) FROM vigencias v INNER JOIN lugares l ON l.id_lugar = v.id_lugar WHERE l.tipo_lugar = 'COMERCIAL' AND v.fecha_vencimiento >= CURDATE()")->fetchColumn();

        $nombresMeses = ['01' => 'Ene', '02' => 'Feb', '03' => 'Mar', '04' => 'Abr', '05' => 'May', '06' => 'Jun', '07' => 'Jul', '08' => 'Ago', '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dic'];
        $ingresosMeses = [];
        for ($i = 5; $i >= 0; $i--) {
            $fecha = strtotime("-$i months");
            $clave = date('Y-m', $fecha);
            $ingresosMeses[$clave] = ['label' => $nombresMeses[date('m', $fecha)] . ' ' . date('y', $fecha), 'total' => 0.0, 'pagos' => 0];
        }

        $stmtIngresos = $db->query("SELECT DATE_FORMAT(COALESCE(fecha_confirmacion, fecha_pago_declarada, created_at), '%Y-%m') AS mes, SUM(monto) AS total, COUNT(*) AS pagos FROM pagos WHERE estado = 'CONFIRMADO' GROUP BY mes");
        while ($fila = $stmtIngresos->fetch(PDO::FETCH_ASSOC)) {
            if (isset($ingresosMeses[$fila['mes']])) {
                $ingresosMeses[$fila['mes']]['total'] = (float)$fila['total'];
                $ingresosMeses[$fila['mes']]['pagos'] = (int)$fila['pagos'];
            }
        }

        $mesActual = date('Y-m');
        $mesAnterior = date('Y-m', strtotime('-1 month'));
        $recaudadoMes = $ingresosMeses[$mesActual]['total'] ?? 0.0;
        $recaudadoMesAnterior = $ingresosMeses[$mesAnterior]['total'] ?? 0.0;
        $variacionMensual = $recaudadoMesAnterior > 0
            ? (($recaudadoMes - $recaudadoMesAnterior) / $recaudadoMesAnterior) * 100
            : null;

        $mensaje = $_SESSION['admin_flash'] ?? null;
        $error   = $_SESSION['admin_error'] ?? null;
        unset($_SESSION['admin_flash'], $_SESSION['admin_error']);

        $this->render('admin/pagos/index', [
            'titulo'               => 'Gestión de Pagos y Mensualidades',
            'pagos'                => $pagos,
            'filtroActual'         => $filtroEstado,
            'proximosVencimientos' => $proximosVencimientos,
            'estadisticas'          => [
                'totalConfirmado'    => $totalConfirmado,
                'recaudadoMes'       => $recaudadoMes,
                'pendienteCantidad'  => (int)($pendiente['cantidad'] ?? 0),
                'pendienteMonto'     => (float)($pendiente['monto'] ?? 0),
                'comerciosVigentes'  => $comerciosVigentes,
                'variacionMensual'   => $variacionMensual,
            ],
            'graficoIngresos'       => [
                'labels'  => array_column($ingresosMeses, 'label'),
                'valores' => array_column($ingresosMeses, 'total'),
                'pagos'   => array_column($ingresosMeses, 'pagos'),
            ],
            'mensaje'              => $mensaje,
            'error'                => $error,
            'csrfToken'            => CsrfMiddleware::obtenerToken()
        ], 'admin');
    }

    public function crear(): void {
        $db = Database::getConnection();
        
        // Listar establecimientos comerciales con su ultimo vencimiento registrado
        $sqlComercios = "SELECT l.id_lugar, l.nombre, s.plan_solicitado,
                                (SELECT MAX(v.fecha_vencimiento) FROM vigencias v
                                 INNER JOIN pagos p ON p.id_pago = v.id_pago
                                 WHERE v.id_lugar = l.id_lugar AND p.estado = 'CONFIRMADO') AS ultimo_vencimiento
                         FROM lugares l
                         LEFT JOIN solicitudes s ON s.id_solicitud = l.id_solicitud_origen
                         WHERE l.tipo_lugar = 'COMERCIAL' 
                         ORDER BY l.nombre ASC";
        $comercios = $db->query($sqlComercios)->fetchAll(PDO::FETCH_ASSOC);
        
        $tarifa = $this->tarifaModel->obtenerTarifaVigente();
        $planesActivos = $this->tarifaModel->listarPlanesActivos();
        $idLugarSeleccionado = filter_var($_GET['id_lugar'] ?? null, FILTER_VALIDATE_INT) ?: 0;
        $error = $_SESSION['admin_error'] ?? null;
        unset($_SESSION['admin_error']);

        $this->render('admin/pagos/crear', [
            'titulo'              => 'Registrar Pago Comercial',
            'comercios'           => $comercios,
            'idLugarSeleccionado' => $idLugarSeleccionado,
            'tarifa'              => $tarifa,
            'planesActivos'       => $planesActivos,
            'error'               => $error,
            'csrfToken'           => CsrfMiddleware::obtenerToken()
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
        $idTarifa        = (int)($tarifa['id_tarifa'] ?? 1);
        $meses           = filter_var($_POST['meses_duracion'] ?? null, FILTER_VALIDATE_INT);
        $monto           = (float)($_POST['monto'] ?? 0);
        $fechaDeclarada  = trim($_POST['fecha_pago_declarada'] ?? date('Y-m-d'));
        $comprobante     = trim($_POST['numero_comprobante'] ?? '');
        $metodo          = trim($_POST['metodo_pago'] ?? 'Transferencia bancaria / QR');
        $observaciones   = trim($_POST['observaciones'] ?? '');
        $tipoPlan        = trim($_POST['tipo_plan_modalidad'] ?? 'ESTANDAR');

        if ($idLugar <= 0 || $monto <= 0 || !is_finite($monto) || !$tarifa || $meses === false || $meses < 1 || $meses > 120) {
            $_SESSION['admin_error'] = 'Seleccione un comercio e indique una cantidad de meses (1 a 120) y monto válidos.';
            header("Location: {$this->config['base_url']}/admin/pagos/crear");
            exit();
        }

        // Buscar si existe una tarifa específica registrada para esa duración exacta
        $idTarifaInput = (int)($_POST['id_tarifa'] ?? 0);
        if ($idTarifaInput > 0) {
            $planCoincidente = $this->tarifaModel->buscarPorId($idTarifaInput);
            if ($planCoincidente && (int)$planCoincidente['meses_duracion'] === $meses) {
                $idTarifa = (int)$planCoincidente['id_tarifa'];
            }
        }

        // Cálculo de desglose de ahorro comercial para auditoría interna
        $tarifaBaseMensual = (float)($tarifa['monto_mensual'] ?? 250.00);
        $precioRegular = round($meses * $tarifaBaseMensual, 2);
        $ahorroCalculado = max(0, round($precioRegular - $monto, 2));

        if ($ahorroCalculado > 0 || $tipoPlan === 'PERSONALIZADO') {
            $porcentajeAhorro = $precioRegular > 0 ? round(($ahorroCalculado / $precioRegular) * 100, 1) : 0;
            $etiquetaAhorro = sprintf(
                "[Plan Personalizado: %d mes(es) | Tarifa regular: Bs %.2f | Importe acordado: Bs %.2f | Ahorro: Bs %.2f (%.1f%%)]",
                $meses,
                $precioRegular,
                $monto,
                $ahorroCalculado,
                $porcentajeAhorro
            );
            $observaciones = !empty($observaciones) ? "{$etiquetaAhorro} {$observaciones}" : $etiquetaAhorro;
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

        $_SESSION['admin_flash'] = "Pago #{$idPago} registrado exitosamente por {$meses} mes(es) en estado PENDIENTE.";
        header("Location: {$this->config['base_url']}/admin/pagos");
        exit();
    }

    public function confirmar(): void {
        $token = $_POST['csrf_token'] ?? null;
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !is_string($token) || !CsrfMiddleware::validarToken($token)) {
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

    public function comprobante(): void {
        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
        $pago = $id ? $this->pagoModel->buscarPorId($id) : null;
        try {
            if (!$pago || empty($pago['comprobante_archivo'])) throw new \RuntimeException('No disponible.');
            $ruta = \App\Services\ComprobanteService::ruta($pago['comprobante_archivo']);
            $mime = \App\Services\ComprobanteService::validarImagen($ruta);
        } catch (\Throwable $e) { http_response_code(404); exit('Comprobante no disponible.'); }
        header('Content-Type: '.$mime);
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, no-store');
        header("Content-Security-Policy: default-src 'none'; sandbox");
        header('Content-Length: '.filesize($ruta));
        readfile($ruta);
        exit();
    }

    public function rechazarEliminar(): void {
        $token = $_POST['csrf_token'] ?? null;
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !is_string($token) || !CsrfMiddleware::validarToken($token)) {
            http_response_code(403); exit('Token de seguridad inválido.');
        }
        $id = filter_var($_POST['id_pago'] ?? null, FILTER_VALIDATE_INT);
        try {
            if (!$id || $id < 1) throw new \InvalidArgumentException('Pago inválido.');
            $res = \App\Services\RechazoNegocioService::eliminarRegistro($id, (int)$_SESSION['admin_id']);
            $_SESSION['admin_flash'] = 'Registro falso eliminado: ficha, cuenta, fotos, promociones y pagos pendientes.';
            if ($res['fallos_archivos']) $_SESSION['admin_error'] = 'Algunos archivos no pudieron retirarse del disco. Revise los permisos y el registro de errores.';
        } catch (\PDOException $e) {
            error_log('Error al rechazar negocio: '.$e->getMessage());
            $_SESSION['admin_error'] = 'No se pudo eliminar el registro. No se guardaron cambios.';
        } catch (\RuntimeException | \InvalidArgumentException $e) {
            $_SESSION['admin_error'] = $e->getMessage();
        }
        header("Location: {$this->config['base_url']}/admin/pagos?estado=PENDIENTE");
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
