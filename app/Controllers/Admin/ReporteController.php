<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Middleware\AuthMiddleware;
use PDO;

class ReporteController extends Controller {
    private PDO $db;

    public function __construct() {
        parent::__construct();
        AuthMiddleware::autenticar();
        $this->db = Database::getConnection();
    }

    public function index(): void {
        $desde = trim($_GET['desde'] ?? '');
        $hasta = trim($_GET['hasta'] ?? '');
        $idCategoria = (int)($_GET['id_categoria'] ?? 0);

        // 1. Cálculo de KPIs Financieros y Operativos (Sin columnas updated_at)
        $kpis = $this->obtenerKPIs();

        // 2. Reporte de Transacciones Financieras
        $pagos = $this->obtenerReporteFinanciero($desde, $hasta);

        // 3. Reporte de Cobertura de Vigencias
        $vigencias = $this->obtenerReporteVigencias($idCategoria);

        // 4. Categorías para el filtro
        $categorias = $this->db->query("SELECT id_categoria, nombre FROM categorias WHERE activo = 1 ORDER BY nombre ASC")->fetchAll();

        $this->render('admin/reportes/index', [
            'titulo'      => 'Reportes Financieros y Analítica',
            'kpis'        => $kpis,
            'pagos'       => $pagos,
            'vigencias'   => $vigencias,
            'categorias'  => $categorias,
            'filtroDesde' => $desde,
            'filtroHasta' => $hasta,
            'filtroCat'   => $idCategoria
        ], 'admin');
    }

    /**
     * Calcula los KPIs consolidados asegurando compatibilidad con el esquema
     */
    private function obtenerKPIs(): array {
        // Total histórico confirmado
        $stmtTotal = $this->db->query("SELECT COALESCE(SUM(monto), 0) FROM pagos WHERE estado = 'CONFIRMADO'");
        $totalRecaudado = (float)$stmtTotal->fetchColumn();

        // Recaudación del mes actual (usando fecha_confirmacion o created_at)
        $sqlMes = "SELECT COALESCE(SUM(monto), 0) FROM pagos 
                   WHERE estado = 'CONFIRMADO' 
                     AND MONTH(COALESCE(fecha_confirmacion, fecha_pago_declarada, created_at)) = MONTH(CURRENT_DATE())
                     AND YEAR(COALESCE(fecha_confirmacion, fecha_pago_declarada, created_at)) = YEAR(CURRENT_DATE())";
        $totalMes = (float)$this->db->query($sqlMes)->fetchColumn();

        // Comercios con suscripción al día
        $sqlActivos = "SELECT COUNT(DISTINCT id_lugar) FROM vigencias WHERE fecha_vencimiento >= CURDATE()";
        $comerciosActivos = (int)$this->db->query($sqlActivos)->fetchColumn();

        // Comercios por vencer en los próximos 7 días
        $sqlPorVencer = "SELECT COUNT(DISTINCT id_lugar) FROM vigencias 
                         WHERE fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
        $comerciosPorVencer = (int)$this->db->query($sqlPorVencer)->fetchColumn();

        // Solicitudes comerciales pendientes de revisión
        $sqlPendientes = "SELECT COUNT(*) FROM solicitudes WHERE estado = 'PENDIENTE'";
        $solicitudesPendientes = (int)$this->db->query($sqlPendientes)->fetchColumn();

        return [
            'total_recaudado'        => $totalRecaudado,
            'total_mes'              => $totalMes,
            'comercios_activos'      => $comerciosActivos,
            'comercios_por_vencer'   => $comerciosPorVencer,
            'solicitudes_pendientes' => $solicitudesPendientes
        ];
    }

    private function obtenerReporteFinanciero(string $desde = '', string $hasta = ''): array {
        $where = ["p.estado = 'CONFIRMADO'"];
        $params = [];

        if (!empty($desde)) {
            $where[] = "DATE(p.fecha_pago_declarada) >= :desde";
            $params[':desde'] = $desde;
        }
        if (!empty($hasta)) {
            $where[] = "DATE(p.fecha_pago_declarada) <= :hasta";
            $params[':hasta'] = $hasta;
        }

        $sql = "SELECT p.id_pago, p.monto, p.fecha_pago_declarada, p.numero_comprobante, 
                       p.metodo_pago, p.fecha_confirmacion,
                       l.nombre AS establecimiento, c.nombre AS categoria
                FROM pagos p
                INNER JOIN lugares l ON p.id_lugar = l.id_lugar
                INNER JOIN categorias c ON l.id_categoria = c.id_categoria
                WHERE " . implode(' AND ', $where) . "
                ORDER BY p.id_pago DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    private function obtenerReporteVigencias(int $idCategoria = 0): array {
        $where = ["1 = 1"];
        $params = [];

        if ($idCategoria > 0) {
            $where[] = "l.id_categoria = :id_categoria";
            $params[':id_categoria'] = $idCategoria;
        }

        $sql = "SELECT v.id_vigencia, v.fecha_inicio, v.fecha_vencimiento, v.tipo_periodo,
                       l.nombre AS establecimiento, c.nombre AS categoria,
                       DATEDIFF(v.fecha_vencimiento, CURDATE()) AS dias_restantes,
                       CASE 
                           WHEN v.fecha_vencimiento < CURDATE() THEN 'VENCIDO'
                           WHEN v.fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 'POR_VENCER'
                           ELSE 'VIGENTE'
                       END AS estado_cobertura
                FROM vigencias v
                INNER JOIN lugares l ON v.id_lugar = l.id_lugar
                INNER JOIN categorias c ON l.id_categoria = c.id_categoria
                WHERE " . implode(' AND ', $where) . "
                ORDER BY v.fecha_vencimiento ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Despacho de archivos CSV con BOM UTF-8 para compatibilidad nativa con Excel
     */
    public function exportar(): void {
        $tipo = trim($_GET['tipo'] ?? 'financiero');
        $desde = trim($_GET['desde'] ?? '');
        $hasta = trim($_GET['hasta'] ?? '');

        match ($tipo) {
            'financiero' => $this->exportarFinanciero($desde, $hasta),
            'vigencias'  => $this->exportarVigencias(),
            'lugares'    => $this->exportarLugares(),
            'solicitudes'=> $this->exportarSolicitudes(),
            default      => die('Tipo de reporte no válido.')
        };
    }

    private function exportarFinanciero(string $desde, string $hasta): void {
        $datos = $this->obtenerReporteFinanciero($desde, $hasta);
        $filas = [];
        foreach ($datos as $d) {
            $filas[] = [
                'ID Pago'         => '#' . $d['id_pago'],
                'Establecimiento' => $d['establecimiento'],
                'Categoría'       => $d['categoria'],
                'Importe (Bs)'    => number_format((float)$d['monto'], 2, '.', ''),
                'Fecha Pago'      => date('d/m/Y', strtotime($d['fecha_pago_declarada'])),
                'N° Comprobante'  => $d['numero_comprobante'] ?: 'S/N',
                'Método'          => $d['metodo_pago'],
                'Fecha Confirm.'  => $d['fecha_confirmacion'] ? date('d/m/Y H:i', strtotime($d['fecha_confirmacion'])) : 'S/F'
            ];
        }
        $this->generarCsv('balance_financiero', ['ID Pago', 'Establecimiento', 'Categoría', 'Importe (Bs)', 'Fecha Pago', 'N° Comprobante', 'Método', 'Fecha Confirm.'], $filas);
    }

    private function exportarVigencias(): void {
        $datos = $this->obtenerReporteVigencias();
        $filas = [];
        foreach ($datos as $d) {
            $filas[] = [
                'ID'              => '#' . $d['id_vigencia'],
                'Establecimiento' => $d['establecimiento'],
                'Categoría'       => $d['categoria'],
                'Fecha Inicio'    => date('d/m/Y', strtotime($d['fecha_inicio'])),
                'Fecha Venc.'     => date('d/m/Y', strtotime($d['fecha_vencimiento'])),
                'Días Restantes'  => $d['dias_restantes'],
                'Tipo Período'    => $d['tipo_periodo'],
                'Estado'          => $d['estado_cobertura']
            ];
        }
        $this->generarCsv('control_vigencias', ['ID', 'Establecimiento', 'Categoría', 'Fecha Inicio', 'Fecha Venc.', 'Días Restantes', 'Tipo Período', 'Estado'], $filas);
    }

    private function exportarLugares(): void {
        $sql = "SELECT l.id_lugar, l.nombre, l.tipo_lugar, c.nombre AS categoria, 
                       l.telefono_contacto, l.direccion, p.aprobado, p.habilitado
                FROM lugares l
                INNER JOIN categorias c ON l.id_categoria = c.id_categoria
                LEFT JOIN publicaciones p ON p.id_lugar = l.id_lugar
                ORDER BY l.id_lugar ASC";
        $datos = $this->db->query($sql)->fetchAll();

        $filas = [];
        foreach ($datos as $d) {
            $filas[] = [
                'ID'          => $d['id_lugar'],
                'Nombre'      => $d['nombre'],
                'Tipo'        => $d['tipo_lugar'],
                'Categoría'   => $d['categoria'],
                'Teléfono'    => $d['telefono_contacto'] ?: 'S/N',
                'Dirección'   => $d['direccion'],
                'Aprobado'    => $d['aprobado'] ? 'SÍ' : 'NO',
                'Habilitado'  => $d['habilitado'] ? 'SÍ' : 'NO'
            ];
        }
        $this->generarCsv('padron_lugares', ['ID', 'Nombre', 'Tipo', 'Categoría', 'Teléfono', 'Dirección', 'Aprobado', 'Habilitado'], $filas);
    }

    private function exportarSolicitudes(): void {
        $sql = "SELECT s.id_solicitud, s.nombre_establecimiento, c.nombre AS categoria, 
                       s.plan_solicitado, s.nombre_solicitante, s.telefono_contacto, s.estado, s.created_at
                FROM solicitudes s
                INNER JOIN categorias c ON s.id_categoria = c.id_categoria
                ORDER BY s.id_solicitud DESC";
        $datos = $this->db->query($sql)->fetchAll();

        $filas = [];
        foreach ($datos as $d) {
            $filas[] = [
                'Solicitud #'   => $d['id_solicitud'],
                'Negocio'       => $d['nombre_establecimiento'],
                'Categoría'     => $d['categoria'],
                'Plan'          => $d['plan_solicitado'],
                'Solicitante'   => $d['nombre_solicitante'],
                'Teléfono'      => $d['telefono_contacto'],
                'Estado'        => $d['estado'],
                'Fecha Envío'   => date('d/m/Y H:i', strtotime($d['created_at']))
            ];
        }
        $this->generarCsv('registro_solicitudes', ['Solicitud #', 'Negocio', 'Categoría', 'Plan', 'Solicitante', 'Teléfono', 'Estado', 'Fecha Envío'], $filas);
    }

    private function generarCsv(string $nombreBase, array $cabeceras, array $filas): void {
        $archivo = $nombreBase . '_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $archivo . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $salida = fopen('php://output', 'w');

        // BOM UTF-8 para apertura correcta en Microsoft Excel
        fputs($salida, "\xEF\xBB\xBF");

        fputcsv($salida, $cabeceras, ';');

        foreach ($filas as $fila) {
            fputcsv($salida, array_values($fila), ';');
        }

        fclose($salida);
        exit();
    }
}