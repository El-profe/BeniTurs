<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Middleware\AuthMiddleware;
use App\Services\AnaliticaVisitasService;
use App\Services\PublicacionService;
use PDO;

class ReporteController extends Controller {
    private PDO $db;

    public function __construct() {
        parent::__construct();
        AuthMiddleware::autenticar();
        $this->db = Database::getConnection();
    }

    public function index(): void {
        $filtros = $this->leerFiltros();

        // 1. Cálculo de KPIs Financieros y Operativos
        $kpis = $this->obtenerKPIs();
        $analiticaFinanciera = $this->obtenerAnaliticaFinanciera();

        // 2. Reporte de Transacciones Financieras con búsqueda y método
        $pagos = $filtros['error'] ? [] : $this->obtenerReporteFinanciero(
            $filtros['desde'],
            $filtros['hasta'],
            $filtros['categoria'],
            $filtros['buscar'],
            $filtros['metodo']
        );

        // 3. Reporte de Cobertura de Vigencias con búsqueda y estado
        $vigencias = $this->obtenerReporteVigencias(
            $filtros['categoria'],
            $filtros['buscar'],
            $filtros['estado_vigencia']
        );

        // 4. Reporte y Ranking de Comercios por Antigüedad
        $comerciosAntiguedad = $this->obtenerReporteComerciosAntiguedad(
            $filtros['buscar'],
            $filtros['categoria']
        );

        // 5. Analítica de Donut por Categoría y Métodos de Pago
        $graficoCategorias = $this->obtenerAnaliticaCategorias();
        $metodosDisponibles = $this->obtenerMetodosPagoDisponibles();

        // 6. Analítica de Tráfico Turístico (visitas_diarias): horas, días, semanas y meses
        $analiticaVisitas = AnaliticaVisitasService::obtenerAnaliticaCompleta();

        // 7. Categorías activas para filtros
        $categorias = $this->db->query("SELECT id_categoria, nombre FROM categorias WHERE activo = 1 ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);

        $this->render('admin/reportes/index', [
            'titulo'              => 'Centro de Reportes y Analítica',
            'kpis'                => $kpis,
            'analiticaFinanciera' => $analiticaFinanciera,
            'pagos'               => $pagos,
            'vigencias'           => $vigencias,
            'comerciosAntiguedad' => $comerciosAntiguedad,
            'graficoCategorias'   => $graficoCategorias,
            'metodosDisponibles'  => $metodosDisponibles,
            'analiticaVisitas'    => $analiticaVisitas,
            'visitasResumen'      => $analiticaVisitas['resumen'],
            'graficoVisitas'      => $analiticaVisitas['meses'],
            'categorias'          => $categorias,
            'filtros'             => $filtros,
            'errorFiltros'        => $filtros['error']
        ], 'admin');
    }

    /**
     * Endpoint para obtener el informe individual de un comercio (para Modal o JSON)
     */
    public function comercioIndividual(): void {
        $idLugar = filter_var($_GET['id_lugar'] ?? 0, FILTER_VALIDATE_INT);
        if (!$idLugar || $idLugar <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de comercio no válido.']);
            exit();
        }

        // Datos del comercio
        $sqlLugar = "SELECT l.id_lugar, l.nombre, l.direccion, l.telefono_contacto, l.whatsapp_contacto, 
                            l.email_contacto, l.created_at, l.horario_atencion,
                            c.nombre AS categoria, p.aprobado, p.habilitado
                     FROM lugares l
                     INNER JOIN categorias c ON l.id_categoria = c.id_categoria
                     LEFT JOIN publicaciones p ON p.id_lugar = l.id_lugar
                     WHERE l.id_lugar = :id AND l.tipo_lugar = 'COMERCIAL' LIMIT 1";
        $stmtLugar = $this->db->prepare($sqlLugar);
        $stmtLugar->execute([':id' => $idLugar]);
        $comercio = $stmtLugar->fetch(PDO::FETCH_ASSOC);

        if (!$comercio) {
            http_response_code(404);
            echo json_encode(['error' => 'Comercio no encontrado.']);
            exit();
        }

        // Historial completo de pagos
        $sqlPagos = "SELECT p.id_pago, p.monto, p.fecha_pago_declarada, p.numero_comprobante, 
                            p.metodo_pago, p.estado, p.fecha_confirmacion, p.meses_duracion, p.observaciones,
                            p.comprobante_archivo,
                            a.nombre AS admin_confirmacion
                     FROM pagos p
                     LEFT JOIN administradores a ON p.id_administrador_confirmacion = a.id_administrador
                     WHERE p.id_lugar = :id
                     ORDER BY p.id_pago DESC";
        $stmtPagos = $this->db->prepare($sqlPagos);
        $stmtPagos->execute([':id' => $idLugar]);
        $pagos = $stmtPagos->fetchAll(PDO::FETCH_ASSOC);

        // Vigencias
        $sqlVigencias = "SELECT v.id_vigencia, v.fecha_inicio, v.fecha_vencimiento, v.tipo_periodo,
                                DATEDIFF(v.fecha_vencimiento, CURDATE()) AS dias_restantes
                         FROM vigencias v
                         WHERE v.id_lugar = :id
                         ORDER BY v.fecha_vencimiento DESC";
        $stmtVig = $this->db->prepare($sqlVigencias);
        $stmtVig->execute([':id' => $idLugar]);
        $vigencias = $stmtVig->fetchAll(PDO::FETCH_ASSOC);

        // Antigüedad y métricas de fidelidad
        $antiguedad = self::calcularAntiguedadTexto($comercio['created_at']);
        $totalAportado = 0.0;
        $pagosConfirmados = 0;
        $pagosPendientes = 0;
        foreach ($pagos as $p) {
            if ($p['estado'] === 'CONFIRMADO') {
                $totalAportado += (float)$p['monto'];
                $pagosConfirmados++;
            } elseif ($p['estado'] === 'PENDIENTE') {
                $pagosPendientes++;
            }
        }

        $vigenciaActual = $vigencias[0] ?? null;
        $estadoCobertura = 'SIN_COBERTURA';
        if ($vigenciaActual) {
            if ($vigenciaActual['dias_restantes'] < 0) {
                $estadoCobertura = 'VENCIDO';
            } elseif ($vigenciaActual['dias_restantes'] <= 7) {
                $estadoCobertura = 'POR_VENCER';
            } else {
                $estadoCobertura = 'VIGENTE';
            }
        }

        $respuesta = [
            'comercio' => [
                'id_lugar'          => (int)$comercio['id_lugar'],
                'nombre'            => $comercio['nombre'],
                'categoria'         => $comercio['categoria'],
                'direccion'         => $comercio['direccion'] ?: 'No especificada',
                'telefono'          => $comercio['telefono_contacto'] ?: 'S/N',
                'whatsapp'          => $comercio['whatsapp_contacto'] ?: 'S/N',
                'email'             => $comercio['email_contacto'] ?: 'S/N',
                'fecha_registro'    => date('d/m/Y', strtotime($comercio['created_at'])),
                'antiguedad_texto'  => $antiguedad['texto'],
                'dias_antiguedad'   => $antiguedad['dias_totales'],
                'publicado'         => ($comercio['aprobado'] && $comercio['habilitado'])
            ],
            'resumen_financiero' => [
                'total_aportado'    => $totalAportado,
                'pagos_confirmados' => $pagosConfirmados,
                'pagos_pendientes'  => $pagosPendientes,
                'ticket_promedio'   => $pagosConfirmados > 0 ? ($totalAportado / $pagosConfirmados) : 0.0,
                'estado_cobertura'  => $estadoCobertura,
                'vigencia_hasta'    => $vigenciaActual ? date('d/m/Y', strtotime($vigenciaActual['fecha_vencimiento'])) : 'Sin registro',
                'dias_restantes'    => $vigenciaActual['dias_restantes'] ?? null
            ],
            'pagos' => array_map(function($p) {
                return [
                    'id_pago'             => (int)$p['id_pago'],
                    'monto'               => (float)$p['monto'],
                    'fecha_pago'          => date('d/m/Y', strtotime($p['fecha_pago_declarada'])),
                    'fecha_confirmacion'  => $p['fecha_confirmacion'] ? date('d/m/Y H:i', strtotime($p['fecha_confirmacion'])) : null,
                    'numero_comprobante'  => $p['numero_comprobante'] ?: 'S/N',
                    'metodo_pago'         => $p['metodo_pago'],
                    'estado'              => $p['estado'],
                    'meses_duracion'      => (int)$p['meses_duracion'],
                    'admin_confirmacion'  => $p['admin_confirmacion'] ?: 'Sistema',
                    'observaciones'       => $p['observaciones'] ?: '',
                    'tiene_comprobante'   => !empty($p['comprobante_archivo'])
                ];
            }, $pagos),
            'vigencias' => $vigencias
        ];

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
        exit();
    }

    /**
     * Calcula los KPIs consolidados asegurando compatibilidad con el esquema
     */
    private function obtenerKPIs(): array {
        // Total histórico confirmado
        $stmtTotal = $this->db->query("SELECT COALESCE(SUM(monto), 0) FROM pagos WHERE estado = 'CONFIRMADO'");
        $totalRecaudado = (float)$stmtTotal->fetchColumn();

        // Recaudación del mes actual
        $sqlMes = "SELECT COALESCE(SUM(monto), 0) FROM pagos 
                   WHERE estado = 'CONFIRMADO' 
                     AND MONTH(COALESCE(fecha_confirmacion, fecha_pago_declarada, created_at)) = MONTH(CURRENT_DATE())
                     AND YEAR(COALESCE(fecha_confirmacion, fecha_pago_declarada, created_at)) = YEAR(CURRENT_DATE())";
        $totalMes = (float)$this->db->query($sqlMes)->fetchColumn();

        // Tarifa vigente para proyecciones
        $tarifaVigente = (float)$this->db->query("SELECT monto FROM tarifas WHERE activo = 1 ORDER BY vigente_desde DESC LIMIT 1")->fetchColumn();
        if ($tarifaVigente <= 0) $tarifaVigente = 250.00;

        // Comercios con suscripción al día
        $sqlActivos = "SELECT COUNT(*) FROM lugares l JOIN publicaciones pub ON pub.id_lugar=l.id_lugar
            WHERE l.tipo_lugar='COMERCIAL' AND " . PublicacionService::getSqlCondicionVisibilidad();
        $comerciosActivos = (int)$this->db->query($sqlActivos)->fetchColumn();

        // Comercios por vencer en los próximos 7 días
        $todasVigencias = $this->obtenerReporteVigencias();
        $comerciosPorVencer = count(array_filter($todasVigencias,
            static fn(array $v): bool => $v['estado_cobertura'] === 'POR_VENCER'));

        // Comercios vencidos
        $comerciosVencidos = count(array_filter($todasVigencias,
            static fn(array $v): bool => $v['estado_cobertura'] === 'VENCIDO'));

        // Solicitudes comerciales pendientes de revisión
        $sqlPendientes = "SELECT COUNT(*) FROM solicitudes WHERE estado = 'PENDIENTE'";
        $solicitudesPendientes = (int)$this->db->query($sqlPendientes)->fetchColumn();

        // Proyección de ingresos del mes: lo recaudado + (comercios por vencer * tarifa)
        $proyeccionMes = $totalMes + ($comerciosPorVencer * $tarifaVigente);

        // Deuda en mora estimada: comercios vencidos * tarifa
        $carteraEnMora = $comerciosVencidos * $tarifaVigente;

        return [
            'total_recaudado'        => $totalRecaudado,
            'total_mes'              => $totalMes,
            'tarifa_vigente'         => $tarifaVigente,
            'proyeccion_mes'         => $proyeccionMes,
            'cartera_en_mora'        => $carteraEnMora,
            'comercios_activos'      => $comerciosActivos,
            'comercios_por_vencer'   => $comerciosPorVencer,
            'comercios_vencidos'     => $comerciosVencidos,
            'solicitudes_pendientes' => $solicitudesPendientes
        ];
    }

    /** Indicadores globales de pagos */
    private function obtenerAnaliticaFinanciera(): array {
        $estados = $this->db->query("SELECT estado, COUNT(*) AS cantidad, COALESCE(SUM(monto), 0) AS monto FROM pagos GROUP BY estado")->fetchAll(PDO::FETCH_ASSOC);
        $porEstado = [];
        foreach ($estados as $estado) {
            $porEstado[$estado['estado']] = ['cantidad' => (int)$estado['cantidad'], 'monto' => (float)$estado['monto']];
        }

        $nombresMeses = ['01' => 'Ene', '02' => 'Feb', '03' => 'Mar', '04' => 'Abr', '05' => 'May', '06' => 'Jun', '07' => 'Jul', '08' => 'Ago', '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dic'];
        $meses = [];
        for ($i = 5; $i >= 0; $i--) {
            $fecha = strtotime("-$i months");
            $clave = date('Y-m', $fecha);
            $meses[$clave] = ['label' => $nombresMeses[date('m', $fecha)] . ' ' . date('y', $fecha), 'monto' => 0.0, 'cantidad' => 0];
        }

        $filas = $this->db->query("SELECT DATE_FORMAT(COALESCE(fecha_confirmacion, fecha_pago_declarada, created_at), '%Y-%m') AS mes, COALESCE(SUM(monto), 0) AS monto, COUNT(*) AS cantidad FROM pagos WHERE estado = 'CONFIRMADO' GROUP BY mes")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($filas as $fila) {
            if (isset($meses[$fila['mes']])) {
                $meses[$fila['mes']]['monto'] = (float)$fila['monto'];
                $meses[$fila['mes']]['cantidad'] = (int)$fila['cantidad'];
            }
        }

        $claveActual = date('Y-m');
        $claveAnterior = date('Y-m', strtotime('-1 month'));
        $montoActual = $meses[$claveActual]['monto'] ?? 0.0;
        $montoAnterior = $meses[$claveAnterior]['monto'] ?? 0.0;
        $variacion = $montoAnterior > 0 ? (($montoActual - $montoAnterior) / $montoAnterior) * 100 : null;
        $confirmados = $porEstado['CONFIRMADO'] ?? ['cantidad' => 0, 'monto' => 0.0];
        $pendientes = $porEstado['PENDIENTE'] ?? ['cantidad' => 0, 'monto' => 0.0];
        $anulados = $porEstado['ANULADO'] ?? ['cantidad' => 0, 'monto' => 0.0];
        $totalOperaciones = $confirmados['cantidad'] + $pendientes['cantidad'] + $anulados['cantidad'];

        return [
            'mes_actual'        => $montoActual,
            'mes_anterior'      => $montoAnterior,
            'variacion'         => $variacion,
            'pendiente_monto'   => $pendientes['monto'],
            'pendiente_cantidad'=> $pendientes['cantidad'],
            'anulado_monto'     => $anulados['monto'],
            'anulado_cantidad'  => $anulados['cantidad'],
            'ticket_promedio'   => $confirmados['cantidad'] > 0 ? $confirmados['monto'] / $confirmados['cantidad'] : 0.0,
            'tasa_confirmacion' => $totalOperaciones > 0 ? ($confirmados['cantidad'] / $totalOperaciones) * 100 : 0.0,
            'grafico' => [
                'labels' => array_column($meses, 'label'),
                'montos' => array_column($meses, 'monto'),
                'pagos'  => array_column($meses, 'cantidad'),
            ],
        ];
    }

    /**
     * Lee y normaliza los filtros de búsqueda, fechas, categorías y presets
     */
    private function leerFiltros(): array {
        $desde = trim($_GET['desde'] ?? '');
        $hasta = trim($_GET['hasta'] ?? '');
        $cat = filter_var($_GET['id_categoria'] ?? 0, FILTER_VALIDATE_INT);
        $buscar = trim($_GET['buscar'] ?? '');
        $metodo = trim($_GET['metodo'] ?? '');
        $estadoVigencia = trim($_GET['estado_vigencia'] ?? '');
        $tab = trim($_GET['tab'] ?? 'finanzas');
        $preset = trim($_GET['preset'] ?? '');

        // Aplicación de Presets temporales rápidos
        if ($preset === 'hoy') {
            $desde = date('Y-m-d');
            $hasta = date('Y-m-d');
        } elseif ($preset === 'semana') {
            $desde = date('Y-m-d', strtotime('monday this week'));
            $hasta = date('Y-m-d');
        } elseif ($preset === 'mes') {
            $desde = date('Y-m-01');
            $hasta = date('Y-m-d');
        } elseif ($preset === 'mes_anterior') {
            $desde = date('Y-m-01', strtotime('first day of last month'));
            $hasta = date('Y-m-t', strtotime('last day of last month'));
        } elseif ($preset === '90dias') {
            $desde = date('Y-m-d', strtotime('-90 days'));
            $hasta = date('Y-m-d');
        } elseif ($preset === 'anio') {
            $desde = date('Y-01-01');
            $hasta = date('Y-m-d');
        } elseif ($preset === 'todo') {
            $desde = '';
            $hasta = '';
        }

        $error = null;
        foreach ([$desde, $hasta] as $fecha) {
            if (!is_string($fecha)) { $error = 'Introduce fechas válidas.'; break; }
            if ($fecha === '') continue;
            $d = \DateTimeImmutable::createFromFormat('!Y-m-d', $fecha);
            if (!$d || $d->format('Y-m-d') !== $fecha) $error = 'Introduce fechas válidas.';
        }

        $desde = is_string($desde) ? $desde : '';
        $hasta = is_string($hasta) ? $hasta : '';
        if (!$error && $desde !== '' && $hasta !== '' && $desde > $hasta) {
            $error = 'La fecha inicial no puede ser posterior a la final.';
        }
        if ($cat === false || $cat < 0) {
            $error = 'Selecciona una categoría válida.';
        }

        return [
            'desde'           => $desde,
            'hasta'           => $hasta,
            'categoria'       => max(0, (int)$cat),
            'buscar'          => $buscar,
            'metodo'          => $metodo,
            'estado_vigencia' => $estadoVigencia,
            'tab'             => in_array($tab, ['finanzas', 'vigencias', 'comercios', 'visitas'], true) ? $tab : 'finanzas',
            'preset'          => $preset,
            'error'           => $error
        ];
    }

    /**
     * Reporte financiero con buscador por texto y método de pago
     */
    private function obtenerReporteFinanciero(string $desde = '', string $hasta = '', int $idCategoria = 0, string $buscar = '', string $metodo = ''): array {
        $where = ["p.estado = 'CONFIRMADO'"];
        $params = [];

        if (!empty($desde)) {
            $where[] = "DATE(COALESCE(p.fecha_confirmacion, p.fecha_pago_declarada, p.created_at)) >= :desde";
            $params[':desde'] = $desde;
        }
        if (!empty($hasta)) {
            $where[] = "DATE(COALESCE(p.fecha_confirmacion, p.fecha_pago_declarada, p.created_at)) <= :hasta";
            $params[':hasta'] = $hasta;
        }
        if ($idCategoria > 0) {
            $where[] = 'l.id_categoria = :categoria';
            $params[':categoria'] = $idCategoria;
        }
        if (!empty($buscar)) {
            $where[] = "(l.nombre LIKE :b1 OR p.numero_comprobante LIKE :b2 OR l.telefono_contacto LIKE :b3 OR l.whatsapp_contacto LIKE :b4)";
            $params[':b1'] = "%{$buscar}%";
            $params[':b2'] = "%{$buscar}%";
            $params[':b3'] = "%{$buscar}%";
            $params[':b4'] = "%{$buscar}%";
        }
        if (!empty($metodo)) {
            $where[] = "p.metodo_pago = :metodo";
            $params[':metodo'] = $metodo;
        }

        $sql = "SELECT p.id_pago, p.monto, p.fecha_pago_declarada, p.numero_comprobante,
                       COALESCE(p.fecha_confirmacion, p.fecha_pago_declarada, p.created_at) AS fecha_reporte,
                       p.metodo_pago, p.fecha_confirmacion, p.comprobante_archivo,
                       l.id_lugar, l.nombre AS establecimiento, c.nombre AS categoria
                FROM pagos p
                INNER JOIN lugares l ON p.id_lugar = l.id_lugar
                INNER JOIN categorias c ON l.id_categoria = c.id_categoria
                WHERE " . implode(' AND ', $where) . "
                ORDER BY p.id_pago DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Reporte de vigencias con buscador y filtro por estado
     */
    private function obtenerReporteVigencias(int $idCategoria = 0, string $buscar = '', string $estadoFiltro = ''): array {
        $where = [
            "l.tipo_lugar = 'COMERCIAL'",
            "p.estado = 'CONFIRMADO'",
            "NOT EXISTS (SELECT 1 FROM vigencias nueva JOIN pagos pn ON pn.id_pago=nueva.id_pago
                WHERE nueva.id_lugar=v.id_lugar AND pn.estado='CONFIRMADO'
                AND (nueva.fecha_vencimiento > v.fecha_vencimiento OR
                (nueva.fecha_vencimiento = v.fecha_vencimiento AND nueva.id_vigencia > v.id_vigencia)))"
        ];
        $params = [];

        if ($idCategoria > 0) {
            $where[] = "l.id_categoria = :id_categoria";
            $params[':id_categoria'] = $idCategoria;
        }
        if (!empty($buscar)) {
            $where[] = "(l.nombre LIKE :vb1 OR l.telefono_contacto LIKE :vb2 OR l.whatsapp_contacto LIKE :vb3)";
            $params[':vb1'] = "%{$buscar}%";
            $params[':vb2'] = "%{$buscar}%";
            $params[':vb3'] = "%{$buscar}%";
        }

        $sql = "SELECT v.id_vigencia, v.fecha_inicio, v.fecha_vencimiento, v.tipo_periodo,
                       l.id_lugar, l.nombre AS establecimiento, l.telefono_contacto, l.whatsapp_contacto, c.nombre AS categoria,
                       DATEDIFF(v.fecha_vencimiento, CURDATE()) AS dias_restantes,
                       CASE 
                           WHEN v.fecha_vencimiento <= CURDATE() THEN 'VENCIDO'
                           WHEN v.fecha_inicio > CURDATE() AND NOT EXISTS (
                               SELECT 1 FROM vigencias actual JOIN pagos pa ON pa.id_pago=actual.id_pago
                               WHERE actual.id_lugar=v.id_lugar AND pa.estado='CONFIRMADO'
                               AND actual.fecha_inicio <= CURDATE() AND actual.fecha_vencimiento > CURDATE()
                           ) THEN 'PROGRAMADA'
                           WHEN v.fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 'POR_VENCER'
                           ELSE 'VIGENTE'
                       END AS estado_cobertura
                FROM vigencias v
                INNER JOIN pagos p ON p.id_pago = v.id_pago
                INNER JOIN lugares l ON v.id_lugar = l.id_lugar
                INNER JOIN categorias c ON l.id_categoria = c.id_categoria
                WHERE " . implode(' AND ', $where) . "
                ORDER BY v.fecha_vencimiento ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($estadoFiltro)) {
            $resultados = array_values(array_filter($resultados, function($item) use ($estadoFiltro) {
                return $item['estado_cobertura'] === $estadoFiltro;
            }));
        }

        return $resultados;
    }

    /**
     * Reporte cronológico de comercios: calcula la antigüedad exacta,
     * total aportado y ranking de pioneros.
     */
    private function obtenerReporteComerciosAntiguedad(string $buscar = '', int $idCategoria = 0): array {
        $where = ["l.tipo_lugar = 'COMERCIAL'"];
        $params = [];

        if (!empty($buscar)) {
            $where[] = "(l.nombre LIKE :ab1 OR l.telefono_contacto LIKE :ab2 OR l.whatsapp_contacto LIKE :ab3)";
            $params[':ab1'] = "%{$buscar}%";
            $params[':ab2'] = "%{$buscar}%";
            $params[':ab3'] = "%{$buscar}%";
        }
        if ($idCategoria > 0) {
            $where[] = "l.id_categoria = :categoria";
            $params[':categoria'] = $idCategoria;
        }

        $sql = "SELECT l.id_lugar, l.nombre AS establecimiento, l.direccion, l.telefono_contacto, l.whatsapp_contacto,
                       l.created_at AS fecha_registro,
                       c.id_categoria, c.nombre AS categoria,
                       MIN(v.fecha_inicio) AS primera_vigencia,
                       MAX(v.fecha_vencimiento) AS ultima_vigencia,
                       DATEDIFF(MAX(v.fecha_vencimiento), CURDATE()) AS dias_restantes,
                       COUNT(DISTINCT p.id_pago) AS total_pagos_confirmados,
                       COALESCE(SUM(CASE WHEN p.estado = 'CONFIRMADO' THEN p.monto ELSE 0 END), 0) AS total_aportado,
                       DATEDIFF(CURDATE(), DATE(l.created_at)) AS dias_antiguedad,
                       CASE 
                           WHEN MAX(v.fecha_vencimiento) IS NULL THEN 'SIN_PAGO'
                           WHEN MAX(v.fecha_vencimiento) <= CURDATE() THEN 'VENCIDO'
                           WHEN MAX(v.fecha_vencimiento) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 'POR_VENCER'
                           ELSE 'VIGENTE'
                       END AS estado_suscripcion
                FROM lugares l
                INNER JOIN categorias c ON l.id_categoria = c.id_categoria
                LEFT JOIN vigencias v ON l.id_lugar = v.id_lugar
                LEFT JOIN pagos p ON l.id_lugar = p.id_lugar AND p.estado = 'CONFIRMADO'
                WHERE " . implode(' AND ', $where) . "
                GROUP BY l.id_lugar, l.nombre, l.direccion, l.telefono_contacto, l.whatsapp_contacto, l.created_at, c.id_categoria, c.nombre
                ORDER BY l.created_at ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $comercios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $ranking = 1;
        foreach ($comercios as &$c) {
            $c['ranking'] = $ranking++;
            $calc = self::calcularAntiguedadTexto($c['fecha_registro']);
            $c['antiguedad_texto'] = $calc['texto'];
            $c['antiguedad_dias'] = $calc['dias_totales'];
            $c['es_pionero'] = ($c['ranking'] <= 3);
        }
        unset($c);

        return $comercios;
    }

    /**
     * Datos para el gráfico de Dona (Doughnut) de ingresos por categoría
     */
    private function obtenerAnaliticaCategorias(): array {
        $sql = "SELECT c.nombre, COALESCE(SUM(p.monto), 0) AS total, COUNT(p.id_pago) AS cantidad
                FROM categorias c
                JOIN lugares l ON c.id_categoria = l.id_categoria
                JOIN pagos p ON l.id_lugar = p.id_lugar AND p.estado = 'CONFIRMADO'
                GROUP BY c.id_categoria, c.nombre
                HAVING total > 0
                ORDER BY total DESC";
        $filas = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $paleta = [
            '#198754', '#0d6efd', '#ffc107', '#0dcaf0', 
            '#6f42c1', '#d63384', '#fd7e14', '#20c997'
        ];

        $labels = [];
        $montos = [];
        $cantidades = [];
        $colores = [];

        foreach ($filas as $i => $f) {
            $labels[] = $f['nombre'];
            $montos[] = (float)$f['total'];
            $cantidades[] = (int)$f['cantidad'];
            $colores[] = $paleta[$i % count($paleta)];
        }

        return [
            'labels'     => $labels,
            'montos'     => $montos,
            'cantidades' => $cantidades,
            'colores'    => $colores,
            'total'      => array_sum($montos)
        ];
    }

    /**
     * Métodos de pago existentes para el selector
     */
    private function obtenerMetodosPagoDisponibles(): array {
        return $this->db->query("SELECT DISTINCT metodo_pago FROM pagos WHERE metodo_pago IS NOT NULL AND metodo_pago != '' ORDER BY metodo_pago ASC")->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Calcula la antigüedad en texto amigable a partir de una fecha
     */
    public static function calcularAntiguedadTexto(string $fecha): array {
        try {
            $inicio = new \DateTime($fecha);
            $hoy = new \DateTime();
            $diff = $inicio->diff($hoy);
            $texto = '';
            if ($diff->y > 0) {
                $texto = $diff->y . ' año' . ($diff->y > 1 ? 's' : '') . ($diff->m > 0 ? ', ' . $diff->m . ' m' : '');
            } elseif ($diff->m > 0) {
                $texto = $diff->m . ' mes' . ($diff->m > 1 ? 'es' : '') . ($diff->d > 0 ? ', ' . $diff->d . ' d' : '');
            } else {
                $texto = max(1, $diff->d) . ' día' . ($diff->d > 1 ? 's' : '');
            }
            return [
                'texto'        => $texto,
                'dias_totales' => $diff->days,
                'anios'        => $diff->y,
                'meses'        => $diff->m,
                'dias'         => $diff->d
            ];
        } catch (\Exception $e) {
            return ['texto' => 'N/D', 'dias_totales' => 0, 'anios' => 0, 'meses' => 0, 'dias' => 0];
        }
    }

    /**
     * Despacho de archivos CSV con BOM UTF-8
     */
    public function exportar(): void {
        $tipo = is_string($_GET['tipo'] ?? null) ? $_GET['tipo'] : 'financiero';
        $filtros = $this->leerFiltros();
        if ($filtros['error']) { http_response_code(422); exit($filtros['error']); }

        match ($tipo) {
            'financiero' => $this->exportarFinanciero($filtros['desde'], $filtros['hasta'], $filtros['categoria'], $filtros['buscar'], $filtros['metodo']),
            'vigencias'  => $this->exportarVigencias($filtros['categoria'], $filtros['buscar'], $filtros['estado_vigencia']),
            'antiguedad' => $this->exportarAntiguedad($filtros['buscar'], $filtros['categoria']),
            'comercio'   => $this->exportarComercioIndividual((int)($_GET['id_lugar'] ?? 0)),
            'lugares'    => $this->exportarLugares(),
            'solicitudes'=> $this->exportarSolicitudes(),
            default      => die('Tipo de reporte no válido.')
        };
    }

    private function exportarFinanciero(string $desde, string $hasta, int $categoria, string $buscar = '', string $metodo = ''): void {
        $datos = $this->obtenerReporteFinanciero($desde, $hasta, $categoria, $buscar, $metodo);
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

    private function exportarVigencias(int $categoria, string $buscar = '', string $estadoVigencia = ''): void {
        $datos = $this->obtenerReporteVigencias($categoria, $buscar, $estadoVigencia);
        $filas = [];
        foreach ($datos as $d) {
            $filas[] = [
                'ID'              => '#' . $d['id_vigencia'],
                'Establecimiento' => $d['establecimiento'],
                'Categoría'       => $d['categoria'],
                'Teléfono'        => $d['telefono_contacto'] ?: ($d['whatsapp_contacto'] ?: 'S/N'),
                'Fecha Inicio'    => date('d/m/Y', strtotime($d['fecha_inicio'])),
                'Fecha Venc.'     => date('d/m/Y', strtotime($d['fecha_vencimiento'])),
                'Días Restantes'  => $d['dias_restantes'],
                'Tipo Período'    => $d['tipo_periodo'],
                'Estado'          => $d['estado_cobertura']
            ];
        }
        $this->generarCsv('control_vigencias', ['ID', 'Establecimiento', 'Categoría', 'Teléfono', 'Fecha Inicio', 'Fecha Venc.', 'Días Restantes', 'Tipo Período', 'Estado'], $filas);
    }

    private function exportarAntiguedad(string $buscar = '', int $categoria = 0): void {
        $datos = $this->obtenerReporteComerciosAntiguedad($buscar, $categoria);
        $filas = [];
        foreach ($datos as $d) {
            $filas[] = [
                'Ranking'         => '#' . $d['ranking'],
                'Establecimiento' => $d['establecimiento'],
                'Categoría'       => $d['categoria'],
                'Fecha Registro'  => date('d/m/Y', strtotime($d['fecha_registro'])),
                'Antigüedad'      => $d['antiguedad_texto'],
                'Días Activo'     => $d['antiguedad_dias'],
                'Pagos Realizados'=> $d['total_pagos_confirmados'],
                'Total Invertido' => number_format((float)$d['total_aportado'], 2, '.', ''),
                'Estado Actual'   => $d['estado_suscripcion'],
                'Teléfono'        => $d['telefono_contacto'] ?: ($d['whatsapp_contacto'] ?: 'S/N')
            ];
        }
        $this->generarCsv('ranking_antiguedad_comercios', ['Ranking', 'Establecimiento', 'Categoría', 'Fecha Registro', 'Antigüedad', 'Días Activo', 'Pagos Realizados', 'Total Invertido (Bs)', 'Estado Actual', 'Teléfono'], $filas);
    }

    private function exportarComercioIndividual(int $idLugar): void {
        if ($idLugar <= 0) die('Comercio no especificado.');

        $stmtL = $this->db->prepare("SELECT l.nombre, c.nombre AS categoria FROM lugares l JOIN categorias c ON l.id_categoria=c.id_categoria WHERE l.id_lugar = :id LIMIT 1");
        $stmtL->execute([':id' => $idLugar]);
        $lugar = $stmtL->fetch(PDO::FETCH_ASSOC);
        if (!$lugar) die('Comercio no encontrado.');

        $stmtP = $this->db->prepare("SELECT id_pago, monto, fecha_pago_declarada, numero_comprobante, metodo_pago, estado, fecha_confirmacion FROM pagos WHERE id_lugar = :id ORDER BY id_pago DESC");
        $stmtP->execute([':id' => $idLugar]);
        $pagos = $stmtP->fetchAll(PDO::FETCH_ASSOC);

        $filas = [];
        foreach ($pagos as $p) {
            $filas[] = [
                'ID Pago'         => '#' . $p['id_pago'],
                'Monto (Bs)'      => number_format((float)$p['monto'], 2, '.', ''),
                'Fecha Pago'      => date('d/m/Y', strtotime($p['fecha_pago_declarada'])),
                'N° Comprobante'  => $p['numero_comprobante'] ?: 'S/N',
                'Método'          => $p['metodo_pago'],
                'Estado'          => $p['estado'],
                'Fecha Confirm.'  => $p['fecha_confirmacion'] ? date('d/m/Y H:i', strtotime($p['fecha_confirmacion'])) : 'Pendiente'
            ];
        }
        $nombreSlug = preg_replace('/[^a-zA-Z0-9_-]/', '_', $lugar['nombre']);
        $this->generarCsv('estado_cuenta_' . $nombreSlug, ['ID Pago', 'Monto (Bs)', 'Fecha Pago', 'N° Comprobante', 'Método', 'Estado', 'Fecha Confirm.'], $filas);
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
            $fila = array_map(static function ($valor) {
                return is_string($valor) && preg_match('/^[\s]*[=+@-]/u', $valor) ? "'" . $valor : $valor;
            }, array_values($fila));
            fputcsv($salida, $fila, ';');
        }

        fclose($salida);
        exit();
    }
}
