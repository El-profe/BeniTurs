<?php
$seccionActual = basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH));
$opciones = [
    ['dashboard', 'Mi negocio', 'bi-shop'],
    ['perfil', 'Editar Perfil', 'bi-pencil-square'],
    ['fotos', 'Fotos', 'bi-images'],
    ['promociones', 'Promociones', 'bi-tag'],
    ['ubicacion', 'Ubicación', 'bi-geo-alt'],
    ['renovar', 'Renovación', 'bi-arrow-repeat']
];
$nombreNegocio = $_SESSION['negocio_nombre'] ?? 'Mi negocio';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo ?? 'Mi negocio') ?> | BeniTurs</title>
    <link rel="icon" type="image/png" href="<?= htmlspecialchars($baseUrl) ?>/assets/img/logo2.png?v=<?= filemtime(dirname(__DIR__, 3) . '/public/assets/img/logo2.png') ?>">
    <!-- Bootstrap 5.3.3 CSS (Local) -->
    <link rel="stylesheet" href="<?= htmlspecialchars($baseUrl) ?>/assets/vendor/bootstrap/css/bootstrap.min.css?v=<?= filemtime(dirname(__DIR__, 3) . '/public/assets/vendor/bootstrap/css/bootstrap.min.css') ?>">
    <!-- Bootstrap Icons (Local) -->
    <link rel="stylesheet" href="<?= htmlspecialchars($baseUrl) ?>/assets/vendor/bootstrap-icons/bootstrap-icons.min.css?v=<?= filemtime(dirname(__DIR__, 3) . '/public/assets/vendor/bootstrap-icons/bootstrap-icons.min.css') ?>">
    <?php if (!empty($mapaEditable)): ?>
        <!-- Leaflet 1.9.4 CSS (Local) -->
        <link rel="stylesheet" href="<?= htmlspecialchars($baseUrl) ?>/assets/vendor/leaflet/leaflet.css?v=<?= filemtime(dirname(__DIR__, 3) . '/public/assets/vendor/leaflet/leaflet.css') ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="<?= htmlspecialchars($baseUrl) ?>/assets/css/negocio.css?v=<?= filemtime(dirname(__DIR__, 3) . '/public/assets/css/negocio.css') ?>">
</head>
<body class="business-portal">
    <a class="visually-hidden-focusable" href="#businessContent">Ir al contenido</a>

    <header class="business-header">
        <div class="business-header-inner">
            <!-- Marca y Logo -->
            <a class="business-brand" href="<?= htmlspecialchars($baseUrl) ?>/negocio/dashboard" title="BeniTurs - Espacio para tu negocio">
                <div class="business-brand-logo-badge">
                    <img src="<?= htmlspecialchars($baseUrl) ?>/assets/img/logo.png" 
                         alt="BeniTurs" 
                         class="business-brand-logo"
                         height="32">
                </div>
                <div class="business-brand-info d-none d-sm-block">
                    <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle extra-small fw-semibold">Portal Negocios</span>
                    <small class="text-muted d-block extra-small">Espacio para tu negocio</small>
                </div>
            </a>

            <!-- Acciones de Cabecera en Computadora -->
            <div class="business-account d-none d-lg-flex">
                <span class="business-name"><?= htmlspecialchars($nombreNegocio) ?></span>
                <a class="btn btn-outline-success btn-sm" href="<?= htmlspecialchars($baseUrl) ?>/negocio/logout">
                    <i class="bi bi-box-arrow-right me-1" aria-hidden="true"></i> Salir
                </a>
            </div>

            <!-- Botón Hamburguesa para Modo Celular (Abre Barra Lateral) -->
            <div class="d-flex align-items-center gap-2 d-lg-none">
                <button class="btn btn-light border shadow-sm rounded-pill px-3 py-2 d-flex align-items-center gap-2 business-menu-toggle" 
                        type="button" 
                        data-bs-toggle="offcanvas" 
                        data-bs-target="#sidebarNegocioMobile" 
                        aria-controls="sidebarNegocioMobile"
                        aria-label="Abrir menú de navegación">
                    <i class="bi bi-list fs-5 text-dark"></i>
                    <span class="small fw-bold text-dark d-none d-sm-inline">Menú</span>
                </button>
            </div>
        </div>

        <!-- Menú de Computadora: Visible solo en pantallas grandes (>= 992px) -->
        <nav class="business-nav d-none d-lg-flex" aria-label="Menú del negocio">
            <?php foreach ($opciones as [$ruta, $etiqueta, $icono]): ?>
                <a href="<?= htmlspecialchars($baseUrl) ?>/negocio/<?= $ruta ?>" 
                   class="business-nav-link<?= $seccionActual === $ruta ? ' active' : '' ?>" 
                   <?= $seccionActual === $ruta ? 'aria-current="page"' : '' ?>>
                    <i class="bi <?= $icono ?>" aria-hidden="true"></i><span><?= $etiqueta ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
    </header>

    <!-- BARRA LATERAL PARA CELULAR (Offcanvas Sidebar) -->
    <div class="offcanvas offcanvas-start border-0 shadow-lg mobile-business-sidebar d-lg-none" 
         tabindex="-1" 
         id="sidebarNegocioMobile" 
         aria-labelledby="sidebarNegocioMobileLabel">
        
        <!-- Cabecera de la Barra Lateral -->
        <div class="offcanvas-header border-bottom p-3">
            <div class="d-flex align-items-center gap-2" id="sidebarNegocioMobileLabel">
                <div class="business-brand-logo-badge">
                    <img src="<?= htmlspecialchars($baseUrl) ?>/assets/img/logo.png" 
                         alt="BeniTurs" 
                         class="business-brand-logo"
                         height="28">
                </div>
                <div>
                    <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle extra-small fw-bold">Panel Comercial</span>
                    <small class="text-muted d-block extra-small">Espacio para tu negocio</small>
                </div>
            </div>
            <button type="button" class="btn-close shadow-none" data-bs-dismiss="offcanvas" aria-label="Cerrar menú"></button>
        </div>

        <!-- Cuerpo de la Barra Lateral -->
        <div class="offcanvas-body p-3 d-flex flex-column justify-content-between">
            <div>
                <!-- Identificación del Local -->
                <div class="card bg-light-subtle border-0 rounded-4 p-3 mb-3">
                    <span class="text-muted extra-small fw-bold text-uppercase d-block mb-1">Negocio Conectado</span>
                    <h6 class="fw-bold text-dark mb-0 text-truncate">
                        <i class="bi bi-building text-success me-1"></i> <?= htmlspecialchars($nombreNegocio) ?>
                    </h6>
                </div>

                <!-- Lista de Navegación Vertical -->
                <div class="sidebar-menu-list mb-4">
                    <span class="text-muted extra-small fw-bold text-uppercase px-2 mb-2 d-block">Navegación</span>
                    <div class="d-flex flex-column gap-1">
                        <?php foreach ($opciones as [$ruta, $etiqueta, $icono]): ?>
                            <?php $esActivo = ($seccionActual === $ruta); ?>
                            <a href="<?= htmlspecialchars($baseUrl) ?>/negocio/<?= $ruta ?>" 
                               class="sidebar-nav-item rounded-3 p-3 text-decoration-none d-flex align-items-center justify-content-between <?= $esActivo ? 'active text-white' : 'text-dark' ?>">
                                <div class="d-flex align-items-center gap-3">
                                    <i class="bi <?= $icono ?> fs-5 <?= $esActivo ? 'text-white' : 'text-success' ?>"></i>
                                    <span class="fw-semibold small"><?= $etiqueta ?></span>
                                </div>
                                <i class="bi bi-chevron-right <?= $esActivo ? 'text-white-50' : 'text-muted' ?> extra-small"></i>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Enlace Rápido al Catálogo Público -->
                <?php if (!empty($_SESSION['negocio_lugar_id'])): ?>
                    <div class="p-2 border-top">
                        <a href="<?= htmlspecialchars($baseUrl) ?>/" target="_blank" class="btn btn-outline-secondary btn-sm rounded-pill w-100 py-2 d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-compass-fill text-warning"></i>
                            <span class="small fw-semibold">Ver Catálogo General</span>
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pie de la Barra Lateral con Salir -->
            <div class="pt-3 border-top">
                <a href="<?= htmlspecialchars($baseUrl) ?>/negocio/logout" class="btn btn-danger-subtle text-danger border border-danger-subtle rounded-pill w-100 py-2 fw-semibold d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Cerrar Sesión</span>
                </a>
                <p class="text-center text-muted extra-small mt-3 mb-0">
                    BeniTurs &bull; Santísima Trinidad, Beni
                </p>
            </div>
        </div>
    </div>

    <!-- Contenido Principal -->
    <main id="businessContent" class="business-content"><?= $content ?></main>

    <footer class="business-footer">BeniTurs · Tu negocio, más cerca de sus visitantes.</footer>

    <!-- Bootstrap Bundle JS (Local) -->
    <script src="<?= htmlspecialchars($baseUrl) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js?v=<?= filemtime(dirname(__DIR__, 3) . '/public/assets/vendor/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
    <?php if (!empty($mapaEditable)): ?>
        <!-- Leaflet 1.9.4 JS (Local) -->
        <script src="<?= htmlspecialchars($baseUrl) ?>/assets/vendor/leaflet/leaflet.js?v=<?= filemtime(dirname(__DIR__, 3) . '/public/assets/vendor/leaflet/leaflet.js') ?>"></script>
        <script src="<?= htmlspecialchars($baseUrl) ?>/assets/js/negocio/ubicacion.js?v=<?= filemtime(dirname(__DIR__, 3) . '/public/assets/js/negocio/ubicacion.js') ?>"></script>
    <?php endif; ?>
</body>
</html>
