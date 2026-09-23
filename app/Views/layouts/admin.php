<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo ?? 'Panel') ?> | Trinidad Admin</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($baseUrl) ?>/assets/css/admin.css?v=<?= filemtime(dirname(__DIR__, 3) . '/public/assets/css/admin.css') ?>">
</head>
<body>

    <!-- Overlay móvil -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Barra Lateral (Sidebar) -->
    <aside id="adminSidebar" class="d-lg-none" aria-label="Menú de administración">
        <div class="sidebar-header">
            <a href="<?= htmlspecialchars($baseUrl) ?>/admin/dashboard" class="d-flex align-items-center gap-2 text-decoration-none">
                <div class="brand-icon-admin">
                    <i class="bi bi-compass-fill fs-5"></i>
                </div>
                <div class="lh-sm">
                    <span class="fs-5 fw-bold text-white d-block">Trinidad<span class="text-warning">Admin</span></span>
                    <small class="text-white-50 extra-small" style="font-size: 0.68rem;">Gestión Centralizada</small>
                </div>
            </a>
        </div>

        <div class="sidebar-menu-wrapper">
            <div class="sidebar-label">Principal</div>
            <ul class="nav-admin mb-3">
                <li>
                    <a href="<?= htmlspecialchars($baseUrl) ?>/admin/dashboard" class="nav-admin-link <?= (str_contains($_SERVER['REQUEST_URI'] ?? '', 'dashboard')) ? 'active' : '' ?>">
                        <i class="bi bi-grid-1x2-fill"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
            </ul>

            <div class="sidebar-label">Directorio Turístico</div>
            <ul class="nav-admin mb-3">
                <li>
                    <a href="<?= htmlspecialchars($baseUrl) ?>/admin/lugares" class="nav-admin-link <?= (str_contains($_SERVER['REQUEST_URI'] ?? '', 'lugares') && !str_contains($_SERVER['REQUEST_URI'] ?? '', 'crear')) ? 'active' : '' ?>">
                        <i class="bi bi-geo-alt-fill"></i>
                        <span>Lugares y Comercios</span>
                    </a>
                </li>
                <li>
                    <a href="<?= htmlspecialchars($baseUrl) ?>/admin/lugares/crear" class="nav-admin-link <?= (str_contains($_SERVER['REQUEST_URI'] ?? '', 'crear')) ? 'active' : '' ?>">
                        <i class="bi bi-plus-circle-fill"></i>
                        <span>Nueva Ficha</span>
                    </a>
                </li>
            </ul>

                        <div class="sidebar-label">Comercial & Finanzas</div>
            <ul class="nav-admin mb-3">
                <li>
                    <a href="<?= htmlspecialchars($baseUrl) ?>/admin/solicitudes" class="nav-admin-link <?= (str_contains($_SERVER['REQUEST_URI'] ?? '', 'solicitudes')) ? 'active' : '' ?>">
                        <i class="bi bi-inbox-fill"></i>
                        <span>Solicitudes</span>
                    </a>
                </li>
                <li>
                    <a href="<?= htmlspecialchars($baseUrl) ?>/admin/pagos" class="nav-admin-link <?= (str_contains($_SERVER['REQUEST_URI'] ?? '', 'pagos')) ? 'active' : '' ?>">
                        <i class="bi bi-cash-stack"></i>
                        <span>Pagos & Vigencias</span>
                    </a>
                </li>
                <li>
                    <a href="<?= htmlspecialchars($baseUrl) ?>/admin/reportes" class="nav-admin-link <?= (str_contains($_SERVER['REQUEST_URI'] ?? '', 'reportes')) ? 'active' : '' ?>">
                        <i class="bi bi-bar-chart-line-fill"></i>
                        <span>Reportes Analíticos</span>
                    </a>
                </li>
            </ul>

        </div>

        <div class="sidebar-footer">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2 overflow-hidden me-2">
                    <div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; min-width: 32px; font-size: 0.85rem;">
                        <?= strtoupper(substr($_SESSION['admin_nombre'] ?? 'A', 0, 1)) ?>
                    </div>
                    <div class="lh-1 text-truncate">
                        <small class="text-white fw-semibold d-block text-truncate"><?= htmlspecialchars($_SESSION['admin_nombre'] ?? 'Admin') ?></small>
                        <span class="text-white-50" style="font-size: 0.68rem;">Conectado</span>
                    </div>
                </div>
                <a href="<?= htmlspecialchars($baseUrl) ?>/admin/logout" class="btn btn-outline-danger btn-sm p-1 rounded-circle" title="Cerrar Sesión" style="width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;">
                    <i class="bi bi-power"></i>
                </a>
            </div>
        </div>
    </aside>

    <!-- Barra Superior Limpia (Sin solapamientos en celular) -->
    <header id="adminTopbar">
        <!-- Lado Izquierdo: Botón Menú + Título con truncado -->
        <div class="d-flex align-items-center gap-2 overflow-hidden topbar-title-box">
            <button class="btn btn-light border d-lg-none p-1 px-2 rounded-3 shadow-none flex-shrink-0" id="btnToggleSidebar" aria-label="Abrir Menú" aria-controls="adminSidebar" aria-expanded="false">
                <i class="bi bi-list fs-5 text-dark"></i>
            </button>
            <div class="lh-1 text-truncate">
                <h5 class="fw-bold mb-0 text-dark text-truncate fs-6 fs-md-5"><?= htmlspecialchars($titulo ?? 'Panel') ?></h5>
            </div>
        </div>

        <!-- Lado Derecho: Acceso al Sitio + Perfil -->
        <div class="d-flex align-items-center gap-2 flex-shrink-0">
            <!-- Botón adaptativo: Icono en celular / Botón completo en pantallas grandes -->
            <a href="<?= htmlspecialchars($baseUrl) ?>/" target="_blank" 
               class="btn btn-outline-success btn-sm rounded-pill px-2 px-sm-3 fw-semibold d-flex align-items-center gap-1 shadow-none" 
               title="Ver Catálogo Público">
                <i class="bi bi-box-arrow-up-right"></i>
                <span class="d-none d-md-inline">Ver Catálogo</span>
            </a>

            <!-- Dropdown del usuario -->
            <div class="dropdown">
                <button class="btn btn-light rounded-pill border d-flex align-items-center gap-1 gap-sm-2 px-2 px-sm-3 py-1 shadow-none" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle text-success fs-5"></i>
                    <span class="small fw-bold d-none d-sm-inline"><?= htmlspecialchars($_SESSION['admin_user'] ?? 'admin') ?></span>
                    <i class="bi bi-chevron-down extra-small text-muted d-none d-sm-inline"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 mt-2">
                    <li class="px-3 py-2 border-bottom">
                        <small class="text-muted d-block">Usuario Administrador</small>
                        <strong class="text-dark small"><?= htmlspecialchars($_SESSION['admin_nombre'] ?? 'Admin') ?></strong>
                    </li>
                    <li>
                        <a class="dropdown-item py-2" href="<?= htmlspecialchars($baseUrl) ?>/" target="_blank">
                            <i class="bi bi-globe me-2 text-muted"></i> Visitar Catálogo
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item py-2 text-danger" href="<?= htmlspecialchars($baseUrl) ?>/admin/logout">
                            <i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <nav class="admin-desktop-nav d-none d-lg-flex" aria-label="Navegación de administración">
            <a href="<?= htmlspecialchars($baseUrl) ?>/admin/dashboard" class="admin-top-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', 'dashboard') ? 'active' : '' ?>">
                <i class="bi bi-grid-1x2-fill" aria-hidden="true"></i> Dashboard
            </a>
            <a href="<?= htmlspecialchars($baseUrl) ?>/admin/lugares" class="admin-top-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', 'lugares') && !str_contains($_SERVER['REQUEST_URI'] ?? '', 'crear') ? 'active' : '' ?>">
                <i class="bi bi-geo-alt-fill" aria-hidden="true"></i> Lugares y Comercios
            </a>
            <a href="<?= htmlspecialchars($baseUrl) ?>/admin/lugares/crear" class="admin-top-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', 'crear') ? 'active' : '' ?>">
                <i class="bi bi-plus-circle-fill" aria-hidden="true"></i> Nueva Ficha
            </a>
            <a href="<?= htmlspecialchars($baseUrl) ?>/admin/solicitudes" class="admin-top-link <?= (str_contains($_SERVER['REQUEST_URI'] ?? '', 'solicitudes')) ? 'active' : '' ?>">
                <i class="bi bi-inbox-fill"></i>
                    <span>Solicitudes</span>
            </a>
                      <a href="<?= htmlspecialchars($baseUrl) ?>/admin/pagos" class="admin-top-link <?= (str_contains($_SERVER['REQUEST_URI'] ?? '', 'pagos')) ? 'active' : '' ?>">
                <i class="bi bi-cash-stack"></i>
                    <span>Pagos</span>
            </a>
            <a href="<?= htmlspecialchars($baseUrl) ?>/admin/reportes" class="admin-top-link <?= (str_contains($_SERVER['REQUEST_URI'] ?? '', 'reportes')) ? 'active' : '' ?>">
                <i class="bi bi-bar-chart-line-fill"></i>
                    <span>Reportes</span>
            </a>

        </nav>
    </header>

    <!-- Contenedor Principal Dinámico -->
    <main id="adminContent">
        <?= $content ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const sidebar = document.getElementById('adminSidebar');
            const toggleBtn = document.getElementById('btnToggleSidebar');
            const overlay = document.getElementById('sidebarOverlay');

            function setMenu(open) {
                sidebar.classList.toggle('show', open);
                overlay.classList.toggle('show', open);
                toggleBtn.setAttribute('aria-expanded', String(open));
                document.body.classList.toggle('sidebar-open', open);
            }

            function toggleMenu() {
                setMenu(!sidebar.classList.contains('show'));
            }

            if (toggleBtn) toggleBtn.addEventListener('click', toggleMenu);
            if (overlay) overlay.addEventListener('click', () => setMenu(false));
            document.addEventListener('keydown', event => {
                if (event.key === 'Escape' && sidebar.classList.contains('show')) {
                    setMenu(false);
                    toggleBtn.focus();
                }
            });
            window.matchMedia('(min-width: 992px)').addEventListener('change', event => {
                if (event.matches) setMenu(false);
            });
        });
    </script>
</body>
</html>
