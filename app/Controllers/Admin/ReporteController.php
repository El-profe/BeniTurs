<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Middleware\AuthMiddleware;
use App\Models\Categoria;
use PDO;

class ReporteController extends Controller
{
    private PDO $db;

    public function __construct()
    {
        parent::__construct();
        AuthMiddleware::autenticar();
        $this->db = Database::getConnection();
    }

    /**
     * Vista principal de reportes con KPIs y previsualización.
     */
    public function index(): void
    {
        $filtros = [
            'desde' => $_GET['desde'] ?? date('Y-m-01'),
            'hasta' => $_GET['hasta'] ?? date('Y-m-t'),
            'id_categoria' => $_GET['id_categoria'] ?? null,
            'tipo_reporte' => $_GET['tipo_reporte'] ?? 'financiero'
        ];

        // 1. Carga de KPIs Consolidados
        $kpis = $this->obtenerKPIs();

        // 2. Carga de categorías para filtros
        $categoriaModel = new Categoria();
        $categorias = $categoriaModel->listarTodasActivas();

        // 3. Ejecutar consulta según el tipo seleccionado para la previsualización
        $datosReporte = [];
        switch ($filtros['tipo_reporte']) {
            case 'financiero':
                $datosReporte = $this->reporteFinanciero($filtros);
                break;
            case 'vigencias':
                $datosReporte = $this->reporteVigencias($filtros);
                break;
            case 'directorio':
                $datosReporte = $this->reporteDirectorio($filtros);
                break;
            case 'solicitudes':
                $datosReporte = $this->reporteSolicitudes($filtros);
                break;
        }

        $this->render('admin/reportes/index', [
            'titulo' => 'Centro de Reportes Analíticos',
            'kpis' => $kpis,
            'categorias' => $categorias,
            'filtros' => $filtros,
            'datosReporte' => $datosReporte
        ], 'admin');
    }

    /**
     * Despacha la exportación CSV según el tipo solicitado.
     */
    public function exportar(): void
    {
        $tipo = $_GET['tipo'] ?? 'financiero';
        $filtros = [
            'desde' => $_GET['desde'] ?? null,
            'hasta' => $_GET['hasta'] ?? null,
            'id_categoria' => $_GET['id_categoria'] ?? null
        ];

        switch ($tipo) {
            case 'financiero':
                $datos = $this->reporteFinanciero($filtros);
                $encabezados = ['ID Pago', 'Fecha Confirmación', 'Comercio', 'Plan', 'Meses', 'Comprobante', 'Método', 'Monto (Bs)'];
                $nombreArchivo = "Reporte_Financiero_" . date('Ymd') . ".csv";
                $procesados = array_map(fn($d) => [
                    $d['id_pago'], $d['fecha_confirmacion'], $d['comercio'], $d['plan'], $d['meses_duracion'],
                    $d['numero_comprobante'], $d['metodo_pago'], number_format($d['monto'], 2, '.', '')
                ], $datos);
                break;

            case 'vigencias':
                $datos = $this->reporteVigencias($filtros);
                $encabezados = ['Comercio', 'Categoría', 'Inicio Vigencia', 'Fin Vigencia', 'Estado', 'Días Restantes'];
                $nombreArchivo = "Reporte_Vigencias_" . date('Ymd') . ".csv";
                $procesados = array_map(fn($d) => [
                    $d['nombre'], $d['categoria'], $d['fecha_inicio'], $d['fecha_vencimiento'], $d['estado_vigencia'], $d['dias_restantes']
                ], $datos);
                break;

            case 'directorio':
                $datos = $this->reporteDirectorio($filtros);
                $encabezados = ['Nombre', 'Tipo', 'Categoría', 'Habilitado', 'Aprobado', 'Visible Web', 'Fecha Registro'];
                $nombreArchivo = "Reporte_Directorio_" . date('Ymd') . ".csv";
                $procesados = array_map(fn($d) => [
                    $d['nombre'], $d['tipo_lugar'], $d['categoria'], $d['habilitado'] ? 'SI' : 'NO', 
                    $d['aprobado'] ? 'SI' : 'NO', $d['es_visible'] ? 'SI' : 'NO', $d['created_at']
                ], $datos);
                break;

            case 'solicitudes':
                $datos = $this->reporteSolicitudes($filtros);
                $encabezados = ['ID', 'Fecha', 'Solicitante', 'Negocio', 'Plan Solicitado', 'Estado', 'Atendido Por'];
                $nombreArchivo = "Reporte_Solicitudes_" . date('Ymd') . ".csv";
                $procesados = array_map(fn($d) => [
                    $d['id_solicitud'], $d['created_at'], $d['nombre_solicitante'], $d['nombre_negocio'], 
                    $d['plan_nombre'], $d['estado'], $d['admin_nombre'] ?? 'N/A'
                ], $datos);
                break;

            default:
                die("Tipo de reporte no válido");
        }

        $this->exportarCsv($nombreArchivo, $encabezados, $procesados);
    }

    /**
     * Calcula KPIs principales del sistema.
     */
    private function obtenerKPIs(): array
    {
        // Total histórico confirmado
        $totalHistorico = $this->db->query("SELECT SUM(monto) FROM pagos WHERE estado = 'CONFIRMADO'")->fetchColumn() ?: 0;

        // Recaudación mes actual
        $mesActual = $this->db->query("SELECT SUM(monto) FROM pagos WHERE estado = 'CONFIRMADO' AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())")->fetchColumn() ?: 0;

        // Comercios activos (Tienen vigencia actual y están habilitados/aprobados)
        $sqlActivos = "SELECT COUNT(DISTINCT l.id_lugar) 
                       FROM lugares l 
                       INNER JOIN publicaciones p ON l.id_lugar = p.id_lugar
                       INNER JOIN vigencias v ON l.id_lugar = v.id_lugar
                       WHERE l.tipo_lugar = 'COMERCIAL' 
                       AND p.habilitado = 1 AND p.aprobado = 1
                       AND CURRENT_DATE() >= v.fecha_inicio AND CURRENT_DATE() < v.fecha_vencimiento";
        $comerciosActivos = $this->db->query($sqlActivos)->fetchColumn() ?: 0;

        // Por vencer en 7 días
        $sqlPorVencer = "SELECT COUNT(DISTINCT id_lugar) FROM vigencias 
                         WHERE fecha_vencimiento BETWEEN CURRENT_DATE() AND DATE_ADD(CURRENT_DATE(), INTERVAL 7 DAY)";
        $porVencer = $this->db->query($sqlPorVencer)->fetchColumn() ?: 0;

        // Solicitudes pendientes
        $solicitudesPendientes = $this->db->query("SELECT COUNT(*) FROM solicitudes WHERE estado = 'PENDIENTE'")->fetchColumn() ?: 0;

        return [
            'total_historico' => $totalHistorico,
            'recaudacion_mes' => $mesActual,
            'comercios_activos' => $comerciosActivos,
            'por_vencer' => $porVencer,
            'solicitudes_pendientes' => $solicitudesPendientes
        ];
    }

    private function reporteFinanciero(array $filtros): array
    {
        $sql = "SELECT p.id_pago, p.updated_at as fecha_confirmacion, l.nombre as comercio, 
                       t.nombre as plan, p.meses_duracion, p.numero_comprobante, p.metodo_pago, p.monto
                FROM pagos p
                JOIN lugares l ON p.id_lugar = l.id_lugar
                JOIN tarifas t ON p.id_tarifa = t.id_tarifa
                WHERE p.estado = 'CONFIRMADO'";
        
        $params = [];
        if (!empty($filtros['desde'])) {
            $sql .= " AND p.updated_at >= :desde";
            $params[':desde'] = $filtros['desde'] . ' 00:00:00';
        }
        if (!empty($filtros['hasta'])) {
            $sql .= " AND p.updated_at <= :hasta";
            $params[':hasta'] = $filtros['hasta'] . ' 23:59:59';
        }

        $sql .= " ORDER BY p.updated_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    private function reporteVigencias(array $filtros): array
    {
        $sql = "SELECT l.nombre, c.nombre as categoria, v.fecha_inicio, v.fecha_vencimiento,
                       DATEDIFF(v.fecha_vencimiento, CURRENT_DATE()) as dias_restantes,
                       CASE 
                            WHEN CURRENT_DATE() < v.fecha_inicio THEN 'Programada'
                            WHEN CURRENT_DATE() >= v.fecha_vencimiento THEN 'Vencida'
                            WHEN DATEDIFF(v.fecha_vencimiento, CURRENT_DATE()) <= 7 THEN 'Por Vencer'
                            ELSE 'Vigente'
                       END as estado_vigencia
                FROM vigencias v
                JOIN lugares l ON v.id_lugar = l.id_lugar
                JOIN categorias c ON l.id_categoria = c.id_categoria
                WHERE 1=1";
        
        $params = [];
        if (!empty($filtros['id_categoria'])) {
            $sql .= " AND l.id_categoria = :id_cat";
            $params[':id_cat'] = $filtros['id_categoria'];
        }

        $sql .= " ORDER BY v.fecha_vencimiento ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    private function reporteDirectorio(array $filtros): array
    {
        $sql = "SELECT l.nombre, l.tipo_lugar, c.nombre as categoria, pub.habilitado, pub.aprobado, l.created_at,
                       CASE WHEN (pub.habilitado = 1 AND pub.aprobado = 1) THEN 1 ELSE 0 END as es_visible
                FROM lugares l
                JOIN categorias c ON l.id_categoria = c.id_categoria
                JOIN publicaciones pub ON l.id_lugar = pub.id_lugar
                WHERE 1=1";
        
        $params = [];
        if (!empty($filtros['id_categoria'])) {
            $sql .= " AND l.id_categoria = :id_cat";
            $params[':id_cat'] = $filtros['id_categoria'];
        }

        $sql .= " ORDER BY l.nombre ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    private function reporteSolicitudes(array $filtros): array
    {
        $sql = "SELECT s.*, t.nombre as plan_nombre, a.nombre as admin_nombre
                FROM solicitudes s
                LEFT JOIN tarifas t ON s.id_tarifa = t.id_tarifa
                LEFT JOIN administradores a ON s.id_administrador_gestion = a.id_administrador
                WHERE 1=1";
        
        $params = [];
        if (!empty($filtros['desde'])) {
            $sql .= " AND s.created_at >= :desde";
            $params[':desde'] = $filtros['desde'] . ' 00:00:00';
        }
        if (!empty($filtros['hasta'])) {
            $sql .= " AND s.created_at <= :hasta";
            $params[':hasta'] = $filtros['hasta'] . ' 23:59:59';
        }

        $sql .= " ORDER BY s.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Utilidad para exportar datos a CSV con BOM UTF-8.
     */
    private function exportarCsv(string $nombreArchivo, array $encabezados, array $datos): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');

        $output = fopen('php://output', 'w');
        
        // Inyectar BOM UTF-8 para Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Encabezados
        fputcsv($output, $encabezados);

        // Datos
        foreach ($datos as $fila) {
            fputcsv($output, $fila);
        }

        fclose($output);
        exit();
    }
}
