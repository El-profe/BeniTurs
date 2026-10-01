<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Middleware\AuthMiddleware;
use App\Services\AnaliticaVisitasService;
use PDO;

/**
 * Despacha la vista principal del panel administrativo con KPIs consolidados,
 * gráficos analíticos (Chart.js) y recordatorios proactivos de cobro y renovación.
 */
class DashboardController extends Controller {

    public function __construct() {
        parent::__construct();
        AuthMiddleware::autenticar();
    }

    public function index(): void {
        $db = Database::getConnection();

        // 1. Resumen numérico general (KPIs)
        $totalLugares = (int)$db->query("SELECT COUNT(*) FROM lugares")->fetchColumn();
        $totalCategorias = (int)$db->query("SELECT COUNT(*) FROM categorias WHERE activo = 1")->fetchColumn();
        $totalSolicitudes = (int)$db->query("SELECT COUNT(*) FROM solicitudes WHERE estado = 'PENDIENTE'")->fetchColumn();
        $tarifaVigente = (float)$db->query("SELECT monto FROM tarifas WHERE activo = 1 ORDER BY vigente_desde DESC LIMIT 1")->fetchColumn();
        if ($tarifaVigente <= 0) $tarifaVigente = 250.00;

        // Total histórico recaudado y mes actual
        // Salud de los comercios
        $totalComercios = (int)$db->query("SELECT COUNT(*) FROM lugares WHERE tipo_lugar = 'COMERCIAL'")->fetchColumn();
        $totalPublicos = (int)$db->query("SELECT COUNT(*) FROM lugares WHERE tipo_lugar = 'PUBLICO'")->fetchColumn();
        
        $comerciosActivos = (int)$db->query("SELECT COUNT(DISTINCT id_lugar) FROM vigencias WHERE fecha_vencimiento >= CURDATE()")->fetchColumn();
        $comerciosPorVencer = (int)$db->query("SELECT COUNT(DISTINCT id_lugar) FROM vigencias 
                                              WHERE fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 15 DAY)")->fetchColumn();
        $comerciosVencidos = (int)$db->query("SELECT COUNT(DISTINCT l.id_lugar) 
                                              FROM lugares l
                                              LEFT JOIN (
                                                  SELECT id_lugar, MAX(fecha_vencimiento) as max_venc
                                                  FROM vigencias GROUP BY id_lugar
                                              ) v ON l.id_lugar = v.id_lugar
                                              WHERE l.tipo_lugar = 'COMERCIAL' 
                                                AND (v.max_venc IS NULL OR v.max_venc < CURDATE())")->fetchColumn();

        // 2. Gráfico 1: Recaudación mensual de los últimos 6 meses
        $visitas = AnaliticaVisitasService::resumen();
        $graficoVisitas = AnaliticaVisitasService::ultimosSeisMeses();

        // 3. Gráfico 2: Distribución de lugares por categoría
        $stmtCat = $db->query("SELECT c.nombre, COUNT(l.id_lugar) as cantidad
                               FROM categorias c
                               LEFT JOIN lugares l ON c.id_categoria = l.id_categoria AND l.tipo_lugar = 'COMERCIAL'
                               WHERE c.activo = 1
                               GROUP BY c.id_categoria, c.nombre
                               ORDER BY cantidad DESC");
        $cats = $stmtCat->fetchAll(PDO::FETCH_ASSOC);
        $graficoCategorias = [
            'labels' => array_column($cats, 'nombre'),
            'valores' => array_map('intval', array_column($cats, 'cantidad'))
        ];

        // 4. Módulo de Recordatorios de Cobro y Renovación
        $sqlRecordatorios = "SELECT l.id_lugar, l.nombre, l.telefono_contacto, l.whatsapp_contacto, l.email_contacto,
                                    c.nombre AS categoria,
                                    v.fecha_vencimiento,
                                    DATEDIFF(v.fecha_vencimiento, CURDATE()) AS dias_restantes,
                                    COALESCE(p.monto, :tarifa) as monto_estimado
                             FROM lugares l
                             INNER JOIN categorias c ON l.id_categoria = c.id_categoria
                             LEFT JOIN (
                                 SELECT id_lugar, MAX(fecha_vencimiento) AS fecha_vencimiento
                                 FROM vigencias
                                 GROUP BY id_lugar
                             ) v ON l.id_lugar = v.id_lugar
                             LEFT JOIN (
                                 SELECT p1.id_lugar, p1.monto
                                 FROM pagos p1
                                 INNER JOIN (
                                     SELECT id_lugar, MAX(id_pago) as max_id
                                     FROM pagos WHERE estado = 'CONFIRMADO' GROUP BY id_lugar
                                 ) p2 ON p1.id_lugar = p2.id_lugar AND p1.id_pago = p2.max_id
                             ) p ON l.id_lugar = p.id_lugar
                             WHERE l.tipo_lugar = 'COMERCIAL'
                             ORDER BY (v.fecha_vencimiento IS NULL) DESC, v.fecha_vencimiento ASC";
        $stmtRec = $db->prepare($sqlRecordatorios);
        $stmtRec->execute([':tarifa' => $tarifaVigente]);
        $rawRecordatorios = $stmtRec->fetchAll(PDO::FETCH_ASSOC);

        $recordatoriosCobro = [];
        foreach ($rawRecordatorios as $r) {
            $telefono = trim($r['whatsapp_contacto'] ?: ($r['telefono_contacto'] ?: ''));
            $telefonoRaw = preg_replace('/\D/', '', $telefono);
            if (strlen($telefonoRaw) === 8) {
                $telefonoRaw = '591' . $telefonoRaw;
            }

            $dias = $r['dias_restantes'] !== null ? (int)$r['dias_restantes'] : null;
            $fechaFmt = !empty($r['fecha_vencimiento']) ? date('d/m/Y', strtotime($r['fecha_vencimiento'])) : 'Sin registro';

            // Redacción personalizada y profesional del mensaje según días restantes
            if ($dias === null) {
                $estado = 'SIN_PAGO';
                $badgeClase = 'bg-danger text-white';
                $badgeTexto = 'Sin vigencia';
                $mensaje = "Hola *{$r['nombre']}*, le saludamos del equipo de *BeniTurs Trinidad*. Notamos que aún no cuenta con una membresía comercial activa para su negocio en la guía turística. Para activar su ficha en el catálogo y mapa oficial, puede realizar su pago mensual de Bs " . number_format($r['monto_estimado'], 0) . " mediante QR o transferencia. ¿Desea que le enviemos los datos de pago?";
            } elseif ($dias < 0) {
                $estado = 'VENCIDO';
                $diasPasados = abs($dias);
                $badgeClase = 'bg-danger text-white';
                $badgeTexto = "Venció hace {$diasPasados}d";
                $mensaje = "Hola *{$r['nombre']}*, le saludamos cordialmente del equipo de *BeniTurs Trinidad*. Le recordamos que su membresía comercial en la guía turística y mapa oficial venció el {$fechaFmt}. Para reactivar la visibilidad de su negocio y promociones ante los turistas, puede realizar su renovación por transferencia o QR bancario. ¿Desea que le compartamos los datos de pago?";
            } elseif ($dias <= 7) {
                $estado = 'URGENTE';
                $badgeClase = 'bg-warning text-dark fw-bold animate-pulse';
                $badgeTexto = $dias === 0 ? '¡Vence HOY!' : "Vence en {$dias}d";
                $mensaje = "Hola *{$r['nombre']}*, le saludamos cordialmente del equipo de *BeniTurs Trinidad*. Le recordamos que su membresía comercial en la guía turística vence el {$fechaFmt} (en {$dias} días). Para mantener la visibilidad de su establecimiento sin ninguna interrupción, puede realizar su renovación anticipada por transferencia o QR bancario. ¿Gusta que le enviemos los datos de cuenta?";
            } elseif ($dias <= 15) {
                $estado = 'POR_VENCER';
                $badgeClase = 'bg-info-subtle text-info-emphasis border border-info-subtle';
                $badgeTexto = "Vence en {$dias}d";
                $mensaje = "Hola *{$r['nombre']}*, le saludamos cordialmente del equipo de *BeniTurs Trinidad*. Le recordamos que su membresía comercial en la plataforma turística vence el {$fechaFmt} (en {$dias} días). Para asegurar la continuidad de su ficha en el catálogo y mapa interactivo, ya puede gestionar su renovación. ¿Desea que le facilitemos los datos de pago?";
            } else {
                $estado = 'AL_DIA';
                $badgeClase = 'bg-success-subtle text-success border border-success-subtle';
                $badgeTexto = "Al día ({$dias}d)";
                $mensaje = "Hola *{$r['nombre']}*, le saludamos del equipo de *BeniTurs Trinidad*. Le agradecemos por formar parte de la guía turística oficial. Su membresía se encuentra al día con vencimiento el {$fechaFmt}. Quedamos atentos para cualquier actualización en su ficha o promociones.";
            }

            $waUrl = !empty($telefonoRaw) ? "https://wa.me/{$telefonoRaw}?text=" . rawurlencode($mensaje) : null;

            $recordatoriosCobro[] = [
                'id_lugar'             => (int)$r['id_lugar'],
                'nombre'               => $r['nombre'],
                'categoria'            => $r['categoria'],
                'telefono'             => $telefono,
                'telefono_raw'         => $telefonoRaw,
                'fecha_vencimiento'    => $fechaFmt,
                'dias_restantes'       => $dias,
                'estado'               => $estado,
                'badge_clase'          => $badgeClase,
                'badge_texto'          => $badgeTexto,
                'monto'                => (float)$r['monto_estimado'],
                'mensaje_recordatorio' => $mensaje,
                'wa_url'               => $waUrl
            ];
        }

        // 5. Últimas fichas registradas
        $ultimosLugares = $db->query("SELECT l.nombre, l.tipo_lugar, c.nombre AS categoria, l.created_at 
                                      FROM lugares l 
                                      INNER JOIN categorias c ON l.id_categoria = c.id_categoria 
                                      ORDER BY l.id_lugar DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

        $this->render('admin/dashboard', [
            'titulo'              => 'Dashboard & Analítica',
            'adminNombre'         => $_SESSION['admin_nombre'] ?? 'Administrador',
            'kpis' => [
                'totalLugares'       => $totalLugares,
                'totalComercios'     => $totalComercios,
                'totalPublicos'      => $totalPublicos,
                'comerciosActivos'   => $comerciosActivos,
                'comerciosPorVencer' => $comerciosPorVencer,
                'comerciosVencidos'  => $comerciosVencidos,
                'totalSolicitudes'   => $totalSolicitudes,
                'tarifaVigente'      => $tarifaVigente,
                'visitasHoy'         => $visitas['hoy'],
                'visitasMes'         => $visitas['mes'],
            ],
            'graficoVisitas'      => $graficoVisitas,
            'graficoCategorias'   => $graficoCategorias,
            'categoriasComercios' => $cats,
            'recordatoriosCobro'  => $recordatoriosCobro,
            'ultimosLugares'      => $ultimosLugares
        ], 'admin');
    }
}
