<?php
use App\Controllers\Publico\CatalogoController;
use App\Controllers\Publico\ImagenController;
use App\Controllers\Publico\SolicitudController as PublicSolicitudController;
use App\Controllers\Admin\AuthController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\LugarController;
use App\Controllers\Admin\SolicitudController as AdminSolicitudController;
use App\Controllers\Admin\PagoController;

/**
 * Catálogo Público
 */
$router->get('/', [CatalogoController::class, 'index']);
$router->get('/catalogo', [CatalogoController::class, 'index']);
$router->get('/catalogo/detalle', [CatalogoController::class, 'detalle']);
$router->get('/imagen', [ImagenController::class, 'ver']);

/**
 * Solicitudes Públicas
 */
$router->get('/solicitar-incorporacion', [PublicSolicitudController::class, 'index']);

/**
 * Autenticación
 */
$router->get('/admin/login', [AuthController::class, 'login']);
$router->post('/admin/login', [AuthController::class, 'procesarLogin']);
$router->get('/admin/logout', [AuthController::class, 'logout']);

/**
 * Panel Administrativo
 */
$router->get('/admin/dashboard', [DashboardController::class, 'index']);

// Lugares
$router->get('/admin/lugares', [LugarController::class, 'index']);
$router->get('/admin/lugares/crear', [LugarController::class, 'crear']);
$router->post('/admin/lugares/guardar', [LugarController::class, 'guardar']);
$router->get('/admin/lugares/editar', [LugarController::class, 'editar']);
$router->post('/admin/lugares/actualizar', [LugarController::class, 'actualizar']);
$router->post('/admin/lugares/cambiar-habilitacion', [LugarController::class, 'cambiarHabilitacion']);

// Solicitudes
$router->get('/admin/solicitudes', [AdminSolicitudController::class, 'index']);
$router->post('/admin/solicitudes/resolver', [AdminSolicitudController::class, 'resolver']);
$router->post('/admin/solicitudes/convertir', [AdminSolicitudController::class, 'convertirAFicha']);
$router->post('/admin/solicitudes/aprovisionar', [AdminSolicitudController::class, 'aprovisionar']);
$router->get('/admin/solicitudes/comprobante', [AdminSolicitudController::class, 'comprobante']);

// Pagos y Vigencias (RF-22 al RF-29)
$router->get('/admin/pagos', [PagoController::class, 'index']);
$router->get('/admin/pagos/crear', [PagoController::class, 'crear']);
$router->post('/admin/pagos/guardar', [PagoController::class, 'guardar']);
$router->post('/admin/pagos/confirmar', [PagoController::class, 'confirmar']);
$router->post('/admin/pagos/anular', [PagoController::class, 'anular']);
$router->get('/admin/pagos/comprobante', [PagoController::class, 'comprobante']);
$router->post('/admin/pagos/rechazar-eliminar', [PagoController::class, 'rechazarEliminar']);

// Reportes Analíticos
$router->get('/admin/reportes', [\App\Controllers\Admin\ReporteController::class, 'index']);
$router->get('/admin/reportes/exportar', [\App\Controllers\Admin\ReporteController::class, 'exportar']);


// Portal de las cuentas aprovisionadas.
$router->get('/negocio/login', [\App\Controllers\Negocio\AuthController::class, 'login']);
$router->post('/negocio/login', [\App\Controllers\Negocio\AuthController::class, 'procesarLogin']);
$router->get('/negocio/logout', [\App\Controllers\Negocio\AuthController::class, 'logout']);
$router->get('/negocio/dashboard', [\App\Controllers\Negocio\PanelController::class, 'dashboard']);
$router->get('/negocio/fotos', [\App\Controllers\Negocio\PanelController::class, 'fotos']);
$router->get('/negocio/ubicacion', [\App\Controllers\Negocio\PanelController::class, 'ubicacion']);
$router->post('/negocio/ubicacion/guardar', [\App\Controllers\Negocio\PanelController::class, 'guardarUbicacion']);
$router->post('/negocio/fotos/subir', [\App\Controllers\Negocio\PanelController::class, 'subirFoto']);
$router->post('/negocio/fotos/eliminar', [\App\Controllers\Negocio\PanelController::class, 'eliminarFoto']);
$router->get('/negocio/promociones', [\App\Controllers\Negocio\PanelController::class, 'promociones']);
$router->post('/negocio/promociones/guardar', [\App\Controllers\Negocio\PanelController::class, 'guardarPromocion']);
$router->post('/negocio/promociones/estado', [\App\Controllers\Negocio\PanelController::class, 'estadoPromocion']);
