<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo ?? 'Inicio') ?> | <?= htmlspecialchars($appName ?? 'Trinidad Turismo') ?></title>
    <link rel="icon" type="image/png" href="<?= htmlspecialchars($baseUrl) ?>/assets/img/logo2.png?v=<?= filemtime(dirname(__DIR__, 3) . '/public/assets/img/logo2.png') ?>">
    <?php if (!empty($ogMeta)): ?>
        <meta property="og:title" content="<?= htmlspecialchars($ogMeta['title'] ?? '') ?>">
        <meta property="og:description" content="<?= htmlspecialchars($ogMeta['description'] ?? '') ?>">
        <meta property="og:type" content="website">
        <?php if (!empty($ogMeta['image'])): ?>
            <meta property="og:image" content="<?= htmlspecialchars($ogMeta['image']) ?>">
        <?php endif; ?>
        <meta property="og:url" content="<?= htmlspecialchars($ogMeta['url'] ?? '') ?>">
    <?php endif; ?>

    <!-- Bootstrap 5.3.3 CSS (Local) -->
    <link rel="stylesheet" href="<?= htmlspecialchars($baseUrl) ?>/assets/vendor/bootstrap/css/bootstrap.min.css?v=<?= filemtime(dirname(__DIR__, 3) . '/public/assets/vendor/bootstrap/css/bootstrap.min.css') ?>">
    <!-- Bootstrap Icons (Local) -->
    <link rel="stylesheet" href="<?= htmlspecialchars($baseUrl) ?>/assets/vendor/bootstrap-icons/bootstrap-icons.min.css?v=<?= filemtime(dirname(__DIR__, 3) . '/public/assets/vendor/bootstrap-icons/bootstrap-icons.min.css') ?>">
    <!-- Leaflet 1.9.4 CSS (Local) -->
    <link rel="stylesheet" href="<?= htmlspecialchars($baseUrl) ?>/assets/vendor/leaflet/leaflet.css?v=<?= filemtime(dirname(__DIR__, 3) . '/public/assets/vendor/leaflet/leaflet.css') ?>">
    <!-- Estilos propios -->
    <link rel="stylesheet" href="<?= htmlspecialchars($baseUrl) ?>/assets/css/app.css?v=<?= filemtime(dirname(__DIR__, 3) . '/public/assets/css/app.css') ?>">
</head>
<body class="d-flex flex-column min-vh-100 bg-light<?= !empty($heroPantallaCompleta) ? ' page-home' : '' ?>">

    <!-- Navegación Superior -->
    <nav class="navbar navbar-expand-xl navbar-dark navbar-trinidad sticky-top shadow-sm py-2">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="<?= htmlspecialchars($baseUrl) ?>/">
                <img src="<?= htmlspecialchars($baseUrl) ?>/assets/img/logo.png" 
                    alt="BeniTurs" 
                    class="navbar-logo"
                    height="42">
            </a>
            
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Abrir menú">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav mx-auto mb-2 mb-xl-0 fw-medium">
                    <li class="nav-item">
                        <a class="nav-link active" href="<?= htmlspecialchars($baseUrl) ?>/">
                            <i class="bi bi-house-door me-1"></i> Inicio
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= htmlspecialchars($baseUrl) ?>/?categoria=hospedaje-hoteles#explorar">
                            <i class="bi bi-building me-1" aria-hidden="true"></i> Hoteles
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= htmlspecialchars($baseUrl) ?>/#explorar" data-catalogo-todos>
                            <i class="bi bi-geo-alt me-1" aria-hidden="true"></i> Lugares
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= htmlspecialchars($baseUrl) ?>/#transporte">
                            <i class="bi bi-scooter me-1" aria-hidden="true"></i> Transporte
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white-50" href="<?= htmlspecialchars($baseUrl) ?>/#guia-trinidad">
                            <i class="bi bi-info-circle me-1"></i> Guía del Viajero
                        </a>
                    </li>
                </ul>

                <div class="d-flex align-items-center gap-2">
                    <a href="<?= htmlspecialchars($baseUrl) ?>/solicitar-incorporacion" class="btn btn-warning btn-sm rounded-pill fw-bold px-3 shadow-sm text-dark">
                        <i class="bi bi-shop me-1"></i> Publicar mi Negocio
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Contenido Dinámico -->
    <main class="flex-grow-1">
        <?= $content ?>
    </main>

    <!-- Pie de página -->
    <footer class="site-footer mt-5">
        <div class="container site-footer-inner">
            <div class="row g-4 justify-content-between">
                <div class="col-lg-5">
                    <div class="mb-3">
                        <img src="<?= htmlspecialchars($baseUrl) ?>/assets/img/logo.png" class="site-footer-logo" alt="BeniTurs">
                    </div>
                    <p class="text-white-50 small mb-3">
                        Plataforma digital dedicada a promover el turismo, la riqueza natural, la gastronomía típica y los comercios formales de la ciudad de Trinidad y la provincia Cercado en el departamento del Beni.
                    </p>
                    <span class="site-footer-location">
                        <i class="bi bi-geo-alt-fill text-danger me-1"></i> Trinidad &bull; Beni &bull; Bolivia
                    </span>
                </div>

                <div class="col-6 col-lg-3">
                    <h6 class="fw-bold text-white text-uppercase small mb-3">Accesos Rápidos</h6>
                    <ul class="list-unstyled small mb-0 d-flex flex-column gap-2 site-footer-links">
                        <li><a href="<?= htmlspecialchars($baseUrl) ?>/"><i class="bi bi-grid-3x3-gap me-2"></i> Catálogo completo</a></li>
                        <li><a href="<?= htmlspecialchars($baseUrl) ?>/#explorar"><i class="bi bi-compass me-2"></i> Atractivos turísticos</a></li>
                        <li><a href="<?= htmlspecialchars($baseUrl) ?>/#unirse"><i class="bi bi-shop me-2"></i> Incorporar negocio</a></li>
                        <li><a href="<?= htmlspecialchars($baseUrl) ?>/admin/login"><i class="bi bi-shield-lock me-2"></i> Acceso administrativo</a></li>
                    </ul>
                </div>

                <div class="col-6 col-lg-3">
                    <h6 class="fw-bold text-white text-uppercase small mb-3">Información Local</h6>
                    <p class="small text-white-50 mb-1"><i class="bi bi-clock me-2 text-warning"></i>Zona horaria: America/La_Paz</p>
                    <p class="small text-white-50 mb-1"><i class="bi bi-currency-exchange me-2 text-warning"></i>Moneda: Bolivianos (Bs)</p>
                    <p class="small text-white-50 mb-0"><i class="bi bi-thermometer-sun me-2 text-warning"></i>Clima: Tropical cálido húmedo</p>
                </div>
            </div>

            <div class="site-footer-bottom mt-4 pt-4 d-flex justify-content-between align-items-center flex-wrap gap-2 small">
                <span>&copy; <?= date('Y') ?> BeniTours · Trinidad, Beni, Bolivia.</span>
                <span><i class="bi bi-heart-fill me-1"></i>Hecho para conectar visitantes y destinos.</span>
            </div>
        </div>
    </footer>

    <!-- Bootstrap Bundle JS (Local) -->
    <script src="<?= htmlspecialchars($baseUrl) ?>/assets/vendor/bootstrap/js/bootstrap.bundle.min.js?v=<?= filemtime(dirname(__DIR__, 3) . '/public/assets/vendor/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>

    <!-- Leaflet 1.9.4 JS (Local) -->
    <script src="<?= htmlspecialchars($baseUrl) ?>/assets/vendor/leaflet/leaflet.js?v=<?= filemtime(dirname(__DIR__, 3) . '/public/assets/vendor/leaflet/leaflet.js') ?>"></script>

    <!-- Variables Globales y Scripts del Cliente -->
    <script>
        window.APP_CONFIG = {
            baseUrl: '<?= htmlspecialchars($baseUrl) ?>'
        };
    </script>
    <script src="<?= htmlspecialchars($baseUrl) ?>/assets/js/shared/http.js?v=<?= filemtime(dirname(__DIR__, 3) . '/public/assets/js/shared/http.js') ?>"></script>
    <script src="<?= htmlspecialchars($baseUrl) ?>/assets/js/shared/compresor-imagen.js?v=<?= filemtime(dirname(__DIR__, 3) . '/public/assets/js/shared/compresor-imagen.js') ?>"></script>
    <script src="<?= htmlspecialchars($baseUrl) ?>/assets/js/publico/catalogo.js?v=<?= filemtime(dirname(__DIR__, 3) . '/public/assets/js/publico/catalogo.js') ?>"></script>
    <script src="<?= htmlspecialchars($baseUrl) ?>/assets/js/publico/solicitudes.js?v=<?= filemtime(dirname(__DIR__, 3) . '/public/assets/js/publico/solicitudes.js') ?>"></script>
</body>
</html>
