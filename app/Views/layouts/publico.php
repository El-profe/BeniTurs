<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo ?? 'Inicio') ?> | <?= htmlspecialchars($appName ?? 'Trinidad Turismo') ?></title>

    <!-- Bootstrap 5.3.3 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Estilos propios -->
    <link rel="stylesheet" href="<?= htmlspecialchars($baseUrl) ?>/assets/css/app.css?v=<?= filemtime(dirname(__DIR__, 3) . '/public/assets/css/app.css') ?>">
</head>
<body class="d-flex flex-column min-vh-100 bg-light<?= !empty($heroPantallaCompleta) ? ' page-home' : '' ?>">

    <!-- Navegación Superior -->
    <nav class="navbar navbar-expand-xl navbar-dark navbar-trinidad sticky-top shadow-sm py-2">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="<?= htmlspecialchars($baseUrl) ?>/">
                <div class="brand-badge">
                    <i class="bi bi-compass-fill fs-5"></i>
                </div>
                <div class="lh-sm">
                    <span class="fs-5 fw-bold text-white d-block">Beni<span class="text-warning">Turs</span></span>
                </div>
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
    <footer class="bg-dark text-white py-5 mt-5 border-top border-secondary">
        <div class="container">
            <div class="row g-4 justify-content-between">
                <div class="col-lg-5">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-compass-fill fs-4 text-warning"></i>
                        <h5 class="fw-bold mb-0 text-white">Trinidad<span class="text-warning">Turismo</span></h5>
                    </div>
                    <p class="text-white-50 small mb-3">
                        Plataforma digital dedicada a promover el turismo, la riqueza natural, la gastronomía típica y los comercios formales de la ciudad de Trinidad y la provincia Cercado en el departamento del Beni.
                    </p>
                    <span class="badge bg-secondary-subtle text-light border border-secondary px-3 py-2 rounded-pill small">
                        <i class="bi bi-geo-alt-fill text-danger me-1"></i> Trinidad &bull; Beni &bull; Bolivia
                    </span>
                </div>

                <div class="col-6 col-lg-3">
                    <h6 class="fw-bold text-white text-uppercase small mb-3">Accesos Rápidos</h6>
                    <ul class="list-unstyled small text-white-50 mb-0 d-flex flex-column gap-2">
                        <li><a href="<?= htmlspecialchars($baseUrl) ?>/" class="text-decoration-none text-white-50 hover-light"><i class="bi bi-chevron-right me-1 text-warning"></i> Catálogo Completo</a></li>
                        <li><a href="#explorar" class="text-decoration-none text-white-50 hover-light"><i class="bi bi-chevron-right me-1 text-warning"></i> Atractivos Turísticos</a></li>
                        <li><a href="#unirse" class="text-decoration-none text-white-50 hover-light"><i class="bi bi-chevron-right me-1 text-warning"></i> Incorporar Negocio</a></li>
                        <li><a href="<?= htmlspecialchars($baseUrl) ?>/admin/login" class="text-decoration-none text-white-50 hover-light"><i class="bi bi-shield-lock me-1 text-warning"></i> Acceso Administrativo</a></li>
                    </ul>
                </div>

                <div class="col-6 col-lg-3">
                    <h6 class="fw-bold text-white text-uppercase small mb-3">Información Local</h6>
                    <p class="small text-white-50 mb-1"><i class="bi bi-clock me-2 text-warning"></i>Zona horaria: America/La_Paz</p>
                    <p class="small text-white-50 mb-1"><i class="bi bi-currency-exchange me-2 text-warning"></i>Moneda: Bolivianos (Bs)</p>
                    <p class="small text-white-50 mb-0"><i class="bi bi-thermometer-sun me-2 text-warning"></i>Clima: Tropical cálido húmedo</p>
                </div>
            </div>

            <div class="border-top border-secondary mt-4 pt-4 text-center text-white-50 small">
                &copy; <?= date('Y') ?> Trinidad Turismo. Desarrollado con arquitectura MVC limpia y PHP en Trinidad, Beni.
            </div>
        </div>
    </footer>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Variables Globales y Scripts del Cliente -->
    <script>
        window.APP_CONFIG = {
            baseUrl: '<?= htmlspecialchars($baseUrl) ?>'
        };
    </script>
    <script src="<?= htmlspecialchars($baseUrl) ?>/assets/js/shared/http.js?v=<?= filemtime(dirname(__DIR__, 3) . '/public/assets/js/shared/http.js') ?>"></script>
    <script src="<?= htmlspecialchars($baseUrl) ?>/assets/js/publico/catalogo.js?v=<?= filemtime(dirname(__DIR__, 3) . '/public/assets/js/publico/catalogo.js') ?>"></script>
    <script src="<?= htmlspecialchars($baseUrl) ?>/assets/js/publico/solicitudes.js?v=<?= filemtime(dirname(__DIR__, 3) . '/public/assets/js/publico/solicitudes.js') ?>"></script>
</body>
</html>
