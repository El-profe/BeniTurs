<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo ?? 'Mi Negocio') ?> | Trinidad Negocios</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($baseUrl) ?>/assets/css/admin.css">
</head>
<body class="bg-light">

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar del Negocio -->
    <aside id="adminSidebar" style="background-color: #0b2214;">
        <div class="sidebar-header">
            <a href="<?= htmlspecialchars($baseUrl) ?>/negocio/dashboard" class="d-flex align-items-center gap-2 text-decoration-none">
                <div class="brand-icon-admin bg-warning text-dark">
                    <i class="bi bi-shop-window fs-5"></i>
                </div>
                <div class="lh-sm">
                    <span class="fs-6 fw-bold text-white d-block text-truncate" style="max-width: 170px;">
                        <?= htmlspecialchars($_SESSION['negocio_nombre'] ?? 'Mi Negocio') ?>
                    </span>
                    <small class="text-warning extra-small">Panel del Propietario</small>
                </div>
            </a>
        </div>

        <div class="sidebar-menu-wrapper">
            <div class="sidebar-label">Mi Establecimiento</div>
            <ul class="nav-admin mb-3">
                <li>
                    <a href="<?= htmlspecialchars($baseUrl) ?>/negocio/dashboard" class="nav-admin-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', 'dashboard') ? 'active' : '' ?>">
                        <i class="bi bi-speedometer2"></i>
                        <span>Resumen Ficha</span>
                    </a>
                </li>
                <li>
                    <a href="<?= htmlspecialchars($baseUrl) ?>/negocio/fotos" class="nav-admin-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', 'fotos') ? 'active' : '' ?>">
                        <i class="bi bi-images"></i>
                        <span>Fotos de la Ficha</span>
                    </a>
                </li>
                <li>
                    <a href="<?= htmlspecialchars($baseUrl) ?>/negocio/promociones" class="nav-admin-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', 'promociones') ? 'active' : '' ?>">
                        <i class="bi bi-tag-fill text-warning"></i>
                        <span>Promociones & Ofertas</span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="sidebar-footer">
            <a href="<?= htmlspecialchars($baseUrl) ?>/negocio/logout" class="btn btn-outline-danger btn-sm w-100 rounded-pill py-2">
                <i class="bi bi-box-arrow-right me-1"></i> Cerrar Sesión
            </a>
        </div>
    </aside>

    <!-- Topbar -->
    <header id="adminTopbar">
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-light border d-lg-none p-1 px-2 rounded-3 shadow-none" id="btnToggleSidebar">
                <i class="bi bi-list fs-5"></i>
            </button>
            <h5 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($_SESSION['negocio_nombre'] ?? 'Panel') ?></h5>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="<?= htmlspecialchars($baseUrl) ?>/" target="_blank" class="btn btn-outline-success btn-sm rounded-pill px-3">
                <i class="bi bi-globe me-1"></i> Ver en Guía
            </a>
        </div>
    </header>

    <main id="adminContent">
        <?= $content ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const sb = document.getElementById('adminSidebar');
            const tb = document.getElementById('btnToggleSidebar');
            const ov = document.getElementById('sidebarOverlay');
            function toggle() { sb.classList.toggle('show'); ov.classList.toggle('show'); }
            if (tb) tb.addEventListener('click', toggle);
            if (ov) ov.addEventListener('click', toggle);
        });
    </script>
</body>
</html>