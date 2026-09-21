<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Middleware\AuthMiddleware;

/**
 * Despacha la vista principal del panel administrativo con KPIs consolidados.
 */
class DashboardController extends Controller {

    public function __construct() {
        parent::__construct();
        AuthMiddleware::autenticar();
    }

    public function index(): void {
        $db = Database::getConnection();

        // Resumen numérico del directorio
        $totalLugares = (int)$db->query("SELECT COUNT(*) FROM lugares")->fetchColumn();
        $totalCategorias = (int)$db->query("SELECT COUNT(*) FROM categorias WHERE activo = 1")->fetchColumn();
        $totalSolicitudes = (int)$db->query("SELECT COUNT(*) FROM solicitudes WHERE estado = 'PENDIENTE'")->fetchColumn();
        $tarifaVigente = (float)$db->query("SELECT monto_mensual FROM tarifas WHERE activo = 1 ORDER BY vigente_desde DESC LIMIT 1")->fetchColumn();

        $ultimosLugares = $db->query("SELECT l.nombre, l.tipo_lugar, c.nombre AS categoria, l.created_at 
                                      FROM lugares l 
                                      INNER JOIN categorias c ON l.id_categoria = c.id_categoria 
                                      ORDER BY l.id_lugar DESC LIMIT 5")->fetchAll();

        $this->render('admin/dashboard', [
            'titulo'           => 'Panel de Control Principal',
            'adminNombre'      => $_SESSION['admin_nombre'] ?? 'Administrador',
            'totalLugares'     => $totalLugares,
            'totalCategorias'  => $totalCategorias,
            'totalSolicitudes' => $totalSolicitudes,
            'tarifaVigente'    => $tarifaVigente ?: 250.00,
            'ultimosLugares'   => $ultimosLugares
        ], 'admin');
    }
}