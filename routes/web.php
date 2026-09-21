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

// Pagos y Vigencias (RF-22 al RF-29)
$router->get('/admin/pagos', [PagoController::class, 'index']);
$router->get('/admin/pagos/crear', [PagoController::class, 'crear']);
$router->post('/admin/pagos/guardar', [PagoController::class, 'guardar']);
$router->post('/admin/pagos/confirmar', [PagoController::class, 'confirmar']);
$router->post('/admin/pagos/anular', [PagoController::class, 'anular']);