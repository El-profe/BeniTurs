<?php
$seccionActual = basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH));
$opciones = [['dashboard', 'Mi negocio', 'bi-shop'], ['fotos', 'Fotos', 'bi-images'], ['promociones', 'Promociones', 'bi-tag'], ['ubicacion', 'Ubicación', 'bi-geo-alt']];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo ?? 'Mi negocio') ?> | BeniTurs</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <?php if (!empty($mapaEditable)): ?>
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    <?php endif; ?>
    <link rel="stylesheet" href="<?= htmlspecialchars($baseUrl) ?>/assets/css/negocio.css?v=<?= filemtime(dirname(__DIR__, 3) . '/public/assets/css/negocio.css') ?>">
</head>
<body class="business-portal">
    <a class="visually-hidden-focusable" href="#businessContent">Ir al contenido</a>
    <header class="business-header">
        <div class="business-header-inner">
            <a class="business-brand" href="<?= htmlspecialchars($baseUrl) ?>/negocio/dashboard">
                <span class="business-brand-icon"><i class="bi bi-shop" aria-hidden="true"></i></span>
                <span><strong>Beni<span>Turs</span></strong><small>Espacio para tu negocio</small></span>
            </a>
            <div class="business-account">
                <span class="business-name"><?= htmlspecialchars($_SESSION['negocio_nombre'] ?? 'Mi negocio') ?></span>
                <a class="btn btn-outline-success btn-sm" href="<?= htmlspecialchars($baseUrl) ?>/negocio/logout"><i class="bi bi-box-arrow-right" aria-hidden="true"></i> Salir</a>
            </div>
        </div>
        <nav class="business-nav" aria-label="Menú del negocio">
            <?php foreach ($opciones as [$ruta, $etiqueta, $icono]): ?>
                <a href="<?= htmlspecialchars($baseUrl) ?>/negocio/<?= $ruta ?>" class="business-nav-link<?= $seccionActual === $ruta ? ' active' : '' ?>" <?= $seccionActual === $ruta ? 'aria-current="page"' : '' ?>>
                    <i class="bi <?= $icono ?>" aria-hidden="true"></i><span><?= $etiqueta ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
    </header>
    <main id="businessContent" class="business-content"><?= $content ?></main>
    <footer class="business-footer">BeniTurs · Tu negocio, más cerca de sus visitantes.</footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php if (!empty($mapaEditable)): ?>
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script src="<?= htmlspecialchars($baseUrl) ?>/assets/js/negocio/ubicacion.js?v=<?= filemtime(dirname(__DIR__, 3) . '/public/assets/js/negocio/ubicacion.js') ?>"></script>
    <?php endif; ?>
</body>
</html>
