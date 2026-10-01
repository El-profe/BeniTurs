<div class="container-fluid p-0">
    <!-- Encabezado Superior -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 fw-bold">
                    <i class="bi bi-sliders2 me-1"></i> Configuración Global
                </span>
                <span class="text-muted small">BeniTurs &bull; Catálogos Maestros</span>
            </div>
            <h1 class="h3 fw-bold text-dark mb-0">Parámetros y Catálogos del Sistema</h1>
            <small class="text-muted">Añade y administra categorías turísticas, municipios del departamento y planes comerciales de suscripción.</small>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="<?= htmlspecialchars($baseUrl) ?>/admin/lugares" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-2">
                <i class="bi bi-arrow-left me-1"></i> Volver a Lugares
            </a>
        </div>
    </div>

    <!-- Mensajes Flash -->
    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm mb-4 border-0" role="alert" style="background-color: #d1e7dd; color: #0f5132;">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill fs-5"></i>
                <span class="fw-semibold"><?= htmlspecialchars($mensaje) ?></span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm mb-4 border-0" role="alert" style="background-color: #f8d7da; color: #842029;">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                <span class="fw-semibold"><?= htmlspecialchars($error) ?></span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <!-- Resumen Superior en Tarjetas Modernas -->
    <div class="row g-3 mb-4">
        <div class="col-sm-4">
            <div class="card catalog-metric-card p-3 shadow-sm h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon-preview-box bg-success-subtle text-success">
                        <i class="bi bi-tags-fill"></i>
                    </div>
                    <div>
                        <span class="text-muted extra-small text-uppercase fw-bold d-block">Categorías</span>
                        <div class="d-flex align-items-baseline gap-2">
                            <h3 class="fw-bold mb-0 text-dark"><?= count($categorias) ?></h3>
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill extra-small fw-bold">
                                <?= count(array_filter($categorias, fn($c) => (int)$c['activo'] === 1)) ?> activas
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card catalog-metric-card p-3 shadow-sm h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon-preview-box bg-primary-subtle text-primary">
                        <i class="bi bi-geo-alt-fill"></i>
                    </div>
                    <div>
                        <span class="text-muted extra-small text-uppercase fw-bold d-block">Municipios</span>
                        <div class="d-flex align-items-baseline gap-2">
                            <h3 class="fw-bold mb-0 text-dark"><?= count($municipios) ?></h3>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill extra-small fw-bold">
                                <?= count(array_filter($municipios, fn($m) => (int)$m['activo'] === 1)) ?> activos
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card catalog-metric-card p-3 shadow-sm h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon-preview-box bg-warning-subtle text-warning-emphasis">
                        <i class="bi bi-cash-coin"></i>
                    </div>
                    <div>
                        <span class="text-muted extra-small text-uppercase fw-bold d-block">Planes Comerciales</span>
                        <div class="d-flex align-items-baseline gap-2">
                            <h3 class="fw-bold mb-0 text-dark"><?= count($planes) ?></h3>
                            <span class="badge bg-warning-subtle text-dark border border-warning-subtle rounded-pill extra-small fw-bold">
                                <?= count(array_filter($planes, fn($p) => (int)$p['activo'] === 1)) ?> vigentes
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navegación por Pestañas Principales (Segmented Control Personalizado) -->
    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
        <div class="card-header bg-white border-bottom p-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="nav-catalogos-container" id="catalogosTabs" role="tablist">
                    <button class="nav-link <?= $tabActivo === 'categorias' ? 'active' : '' ?>" 
                            id="tab-categorias-btn" 
                            data-bs-toggle="pill" 
                            data-bs-target="#tab-categorias" 
                            type="button" 
                            role="tab" 
                            aria-controls="tab-categorias" 
                            aria-selected="<?= $tabActivo === 'categorias' ? 'true' : 'false' ?>">
                        <i class="bi bi-tags"></i>
                        <span>Categorías</span>
                        <span class="tab-counter"><?= count($categorias) ?></span>
                    </button>
                    <button class="nav-link <?= $tabActivo === 'municipios' ? 'active' : '' ?>" 
                            id="tab-municipios-btn" 
                            data-bs-toggle="pill" 
                            data-bs-target="#tab-municipios" 
                            type="button" 
                            role="tab" 
                            aria-controls="tab-municipios" 
                            aria-selected="<?= $tabActivo === 'municipios' ? 'true' : 'false' ?>">
                        <i class="bi bi-geo-alt"></i>
                        <span>Municipios</span>
                        <span class="tab-counter"><?= count($municipios) ?></span>
                    </button>
                    <button class="nav-link <?= $tabActivo === 'planes' ? 'active' : '' ?>" 
                            id="tab-planes-btn" 
                            data-bs-toggle="pill" 
                            data-bs-target="#tab-planes" 
                            type="button" 
                            role="tab" 
                            aria-controls="tab-planes" 
                            aria-selected="<?= $tabActivo === 'planes' ? 'true' : 'false' ?>">
                        <i class="bi bi-cash-stack"></i>
                        <span>Planes y Tarifas</span>
                        <span class="tab-counter"><?= count($planes) ?></span>
                    </button>
                </div>
            </div>
        </div>

        <div class="card-body p-4">
            <div class="tab-content" id="catalogosTabsContent">
                
                <!-- ======================================================= -->
                <!-- PESTAÑA 1: CATEGORÍAS                                   -->
                <!-- ======================================================= -->
                <div class="tab-pane fade <?= $tabActivo === 'categorias' ? 'show active' : '' ?>" id="tab-categorias" role="tabpanel" aria-labelledby="tab-categorias-btn">
                    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                        <div>
                            <h4 class="h5 fw-bold text-dark mb-0">Categorías de Lugares y Negocios</h4>
                            <small class="text-muted">Clasificación que se muestra en los filtros circulares del catálogo público y en la creación de fichas.</small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="input-group input-group-sm" style="max-width: 250px;">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control bg-light border-start-0" id="filtroCategorias" placeholder="Buscar categoría...">
                            </div>
                            <button type="button" class="btn btn-success rounded-pill px-4 shadow-sm fw-semibold d-flex align-items-center gap-1" id="btnNuevaCategoria">
                                <i class="bi bi-plus-circle-fill"></i>
                                <span>Nueva Categoría</span>
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="tablaCategorias">
                            <thead class="table-light text-secondary extra-small text-uppercase">
                                <tr>
                                    <th scope="col" style="width: 60px;">Icono</th>
                                    <th scope="col">Nombre & Slug</th>
                                    <th scope="col">Tipo Predeterminado</th>
                                    <th scope="col">Descripción</th>
                                    <th scope="col" class="text-center">Fichas</th>
                                    <th scope="col" class="text-center">Estado</th>
                                    <th scope="col" class="text-end" style="width: 140px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($categorias)): ?>
                                    <?php foreach ($categorias as $cat): ?>
                                        <?php $esActiva = (int)$cat['activo'] === 1; ?>
                                        <tr class="fila-categoria <?= !$esActiva ? 'opacity-75 bg-light' : '' ?>" data-nombre="<?= strtolower(htmlspecialchars($cat['nombre'])) ?> <?= strtolower(htmlspecialchars($cat['slug'])) ?>">
                                            <td>
                                                <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm <?= $esActiva ? 'bg-success text-white' : 'bg-secondary text-white' ?>" style="width: 40px; height: 40px; font-size: 1.15rem;">
                                                    <i class="bi <?= htmlspecialchars($cat['icono'] ?? 'bi-tag') ?>"></i>
                                                </div>
                                            </td>
                                            <td>
                                                <strong class="d-block text-dark fw-bold"><?= htmlspecialchars($cat['nombre']) ?></strong>
                                                <code class="text-muted extra-small">/<?= htmlspecialchars($cat['slug']) ?></code>
                                            </td>
                                            <td>
                                                <?php if ($cat['tipo_defecto'] === 'COMERCIAL'): ?>
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 extra-small fw-bold">
                                                        <i class="bi bi-shop me-1"></i> Comercial
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 extra-small fw-bold">
                                                        <i class="bi bi-tree me-1"></i> Público
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small class="text-muted text-truncate d-inline-block" style="max-width: 280px;" title="<?= htmlspecialchars($cat['descripcion'] ?? '') ?>">
                                                    <?= htmlspecialchars($cat['descripcion'] ?? 'Sin descripción') ?>
                                                </small>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-light text-dark border rounded-pill px-3 py-1 fw-semibold">
                                                    <?= (int)($cat['total_lugares'] ?? 0) ?> lugares
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($esActiva): ?>
                                                    <span class="badge bg-success text-white rounded-pill px-3 py-1 extra-small fw-bold">Activa</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary text-white rounded-pill px-3 py-1 extra-small fw-bold">Inactiva</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end">
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" 
                                                            class="btn btn-outline-primary rounded-start-pill btn-editar-categoria"
                                                            data-id="<?= (int)$cat['id_categoria'] ?>"
                                                            data-nombre="<?= htmlspecialchars($cat['nombre']) ?>"
                                                            data-slug="<?= htmlspecialchars($cat['slug']) ?>"
                                                            data-descripcion="<?= htmlspecialchars($cat['descripcion'] ?? '') ?>"
                                                            data-icono="<?= htmlspecialchars($cat['icono'] ?? 'bi-tag') ?>"
                                                            data-tipo="<?= htmlspecialchars($cat['tipo_defecto']) ?>"
                                                            data-activo="<?= (int)$cat['activo'] ?>"
                                                            title="Editar categoría">
                                                        <i class="bi bi-pencil-fill"></i>
                                                    </button>
                                                    <form action="<?= htmlspecialchars($baseUrl) ?>/admin/catalogos/categorias/estado" method="POST" class="d-inline" onsubmit="return confirm('¿Seguro que deseas <?= $esActiva ? 'desactivar' : 'activar' ?> esta categoría?');">
                                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                                        <input type="hidden" name="id_categoria" value="<?= (int)$cat['id_categoria'] ?>">
                                                        <input type="hidden" name="activo" value="<?= $esActiva ? 0 : 1 ?>">
                                                        <button type="submit" 
                                                                class="btn <?= $esActiva ? 'btn-outline-danger' : 'btn-outline-success' ?> rounded-end-pill" 
                                                                title="<?= $esActiva ? 'Desactivar' : 'Activar' ?>">
                                                            <i class="bi <?= $esActiva ? 'bi-eye-slash-fill' : 'bi-check-circle-fill' ?>"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">No hay categorías registradas en el sistema.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ======================================================= -->
                <!-- PESTAÑA 2: MUNICIPIOS                                   -->
                <!-- ======================================================= -->
                <div class="tab-pane fade <?= $tabActivo === 'municipios' ? 'show active' : '' ?>" id="tab-municipios" role="tabpanel" aria-labelledby="tab-municipios-btn">
                    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                        <div>
                            <h4 class="h5 fw-bold text-dark mb-0">Municipios del Departamento del Beni</h4>
                            <small class="text-muted">Soporte territorial departamental con centrado automático en el mapa interactivo Leaflet.</small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="input-group input-group-sm" style="max-width: 250px;">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control bg-light border-start-0" id="filtroMunicipios" placeholder="Buscar municipio...">
                            </div>
                            <button type="button" class="btn btn-success rounded-pill px-4 shadow-sm fw-semibold d-flex align-items-center gap-1" id="btnNuevoMunicipio">
                                <i class="bi bi-plus-circle-fill"></i>
                                <span>Nuevo Municipio</span>
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="tablaMunicipios">
                            <thead class="table-light text-secondary extra-small text-uppercase">
                                <tr>
                                    <th scope="col" style="width: 50px;">#</th>
                                    <th scope="col">Municipio & Provincia</th>
                                    <th scope="col">Slug URL</th>
                                    <th scope="col">Coordenadas de Centrado (GPS)</th>
                                    <th scope="col" class="text-center">Fichas</th>
                                    <th scope="col" class="text-center">Estado</th>
                                    <th scope="col" class="text-end" style="width: 140px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($municipios)): ?>
                                    <?php foreach ($municipios as $mun): ?>
                                        <?php $esActivo = (int)$mun['activo'] === 1; ?>
                                        <tr class="fila-municipio <?= !$esActivo ? 'opacity-75 bg-light' : '' ?>" data-nombre="<?= strtolower(htmlspecialchars($mun['nombre'])) ?> <?= strtolower(htmlspecialchars($mun['provincia'])) ?>">
                                            <td>
                                                <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-1 small">
                                                    #<?= (int)$mun['id_municipio'] ?>
                                                </span>
                                            </td>
                                            <td>
                                                <strong class="d-block text-dark fw-bold"><?= htmlspecialchars($mun['nombre']) ?></strong>
                                                <small class="text-muted"><i class="bi bi-geo-alt me-1 text-success"></i>Provincia <?= htmlspecialchars($mun['provincia']) ?></small>
                                            </td>
                                            <td>
                                                <code class="text-muted extra-small">/<?= htmlspecialchars($mun['slug']) ?></code>
                                            </td>
                                            <td>
                                                <?php if (!empty($mun['latitud_defecto']) && !empty($mun['longitud_defecto'])): ?>
                                                    <span class="badge bg-light text-dark border rounded-pill px-3 py-1 extra-small">
                                                        <i class="bi bi-crosshair me-1 text-primary"></i>
                                                        <?= htmlspecialchars($mun['latitud_defecto']) ?>, <?= htmlspecialchars($mun['longitud_defecto']) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-light text-muted border rounded-pill px-3 py-1 extra-small">
                                                        Sin coordenadas
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-light text-dark border rounded-pill px-3 py-1 fw-semibold">
                                                    <?= (int)($mun['total_lugares'] ?? 0) ?> lugares
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($esActivo): ?>
                                                    <span class="badge bg-success text-white rounded-pill px-3 py-1 extra-small fw-bold">Activo</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary text-white rounded-pill px-3 py-1 extra-small fw-bold">Inactivo</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end">
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" 
                                                            class="btn btn-outline-primary rounded-start-pill btn-editar-municipio"
                                                            data-id="<?= (int)$mun['id_municipio'] ?>"
                                                            data-nombre="<?= htmlspecialchars($mun['nombre']) ?>"
                                                            data-provincia="<?= htmlspecialchars($mun['provincia']) ?>"
                                                            data-slug="<?= htmlspecialchars($mun['slug']) ?>"
                                                            data-latitud="<?= htmlspecialchars($mun['latitud_defecto'] ?? '') ?>"
                                                            data-longitud="<?= htmlspecialchars($mun['longitud_defecto'] ?? '') ?>"
                                                            data-activo="<?= (int)$mun['activo'] ?>"
                                                            title="Editar municipio">
                                                        <i class="bi bi-pencil-fill"></i>
                                                    </button>
                                                    <form action="<?= htmlspecialchars($baseUrl) ?>/admin/catalogos/municipios/estado" method="POST" class="d-inline" onsubmit="return confirm('¿Seguro que deseas <?= $esActivo ? 'desactivar' : 'activar' ?> este municipio?');">
                                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                                        <input type="hidden" name="id_municipio" value="<?= (int)$mun['id_municipio'] ?>">
                                                        <input type="hidden" name="activo" value="<?= $esActivo ? 0 : 1 ?>">
                                                        <button type="submit" 
                                                                class="btn <?= $esActivo ? 'btn-outline-danger' : 'btn-outline-success' ?> rounded-end-pill" 
                                                                title="<?= $esActivo ? 'Desactivar' : 'Activar' ?>">
                                                            <i class="bi <?= $esActivo ? 'bi-eye-slash-fill' : 'bi-check-circle-fill' ?>"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">No hay municipios registrados en el sistema.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ======================================================= -->
                <!-- PESTAÑA 3: PLANES Y TARIFAS                             -->
                <!-- ======================================================= -->
                <div class="tab-pane fade <?= $tabActivo === 'planes' ? 'show active' : '' ?>" id="tab-planes" role="tabpanel" aria-labelledby="tab-planes-btn">
                    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                        <div>
                            <h4 class="h5 fw-bold text-dark mb-0">Planes Comerciales y Tarifas de Suscripción</h4>
                            <small class="text-muted">Planes disponibles para comerciantes en solicitudes de incorporación y renovación de membresías.</small>
                        </div>
                        <button type="button" class="btn btn-success rounded-pill px-4 shadow-sm fw-semibold d-flex align-items-center gap-1" id="btnNuevoPlan">
                            <i class="bi bi-plus-circle-fill"></i>
                            <span>Nuevo Plan Comercial</span>
                        </button>
                    </div>

                    <div class="row g-4" id="gridPlanes">
                        <?php if (!empty($planes)): ?>
                            <?php foreach ($planes as $plan): ?>
                                <?php $esActivo = (int)$plan['activo'] === 1; ?>
                                <div class="col-md-6 col-xl-4 item-plan-card">
                                    <div class="card plan-pricing-card h-100 p-4 position-relative shadow-sm <?= !$esActivo ? 'opacity-75 bg-light' : '' ?>">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <span class="badge bg-warning-subtle text-dark border border-warning-subtle rounded-pill px-3 py-1 fw-bold">
                                                <i class="bi bi-award-fill text-warning me-1"></i> <?= htmlspecialchars($plan['codigo_plan']) ?>
                                            </span>
                                            <?php if ($esActivo): ?>
                                                <span class="badge bg-success text-white rounded-pill px-3 py-1 extra-small fw-bold">Activo</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary text-white rounded-pill px-3 py-1 extra-small fw-bold">Inactivo</span>
                                            <?php endif; ?>
                                        </div>

                                        <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($plan['nombre']) ?></h5>
                                        <div class="my-3 pb-3 border-bottom">
                                            <div class="d-flex align-items-baseline gap-1">
                                                <span class="text-muted small fw-bold">Bs</span>
                                                <span class="display-6 fw-bold text-success"><?= number_format((float)$plan['monto'], 2, '.', ',') ?></span>
                                                <span class="text-muted small">/ <?= (int)$plan['meses_duracion'] ?> <?= (int)$plan['meses_duracion'] === 1 ? 'mes' : 'meses' ?></span>
                                            </div>
                                            <small class="text-muted d-block mt-1">
                                                <i class="bi bi-calendar-check text-success me-1"></i> Vigente desde: <?= htmlspecialchars($plan['vigente_desde'] ?? 'Inmediata') ?>
                                            </small>
                                        </div>

                                        <p class="text-secondary small mb-4 flex-grow-1">
                                            <?= htmlspecialchars($plan['descripcion']) ?>
                                        </p>

                                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                            <div class="extra-small text-muted">
                                                <span><i class="bi bi-cash-stack text-success me-1"></i><?= (int)($plan['total_pagos'] ?? 0) ?> pagos</span> &bull; 
                                                <span><i class="bi bi-inbox-fill text-primary me-1"></i><?= (int)($plan['total_solicitudes'] ?? 0) ?> sol.</span>
                                            </div>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" 
                                                        class="btn btn-outline-primary rounded-start-pill btn-editar-plan"
                                                        data-id="<?= (int)$plan['id_tarifa'] ?>"
                                                        data-codigo="<?= htmlspecialchars($plan['codigo_plan']) ?>"
                                                        data-nombre="<?= htmlspecialchars($plan['nombre']) ?>"
                                                        data-monto="<?= htmlspecialchars($plan['monto']) ?>"
                                                        data-meses="<?= (int)$plan['meses_duracion'] ?>"
                                                        data-descripcion="<?= htmlspecialchars($plan['descripcion']) ?>"
                                                        data-vigente="<?= htmlspecialchars($plan['vigente_desde']) ?>"
                                                        data-activo="<?= (int)$plan['activo'] ?>"
                                                        title="Editar Plan">
                                                    <i class="bi bi-pencil-fill"></i>
                                                </button>
                                                <form action="<?= htmlspecialchars($baseUrl) ?>/admin/catalogos/planes/estado" method="POST" class="d-inline" onsubmit="return confirm('¿Seguro que deseas <?= $esActivo ? 'desactivar' : 'activar' ?> este plan?');">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                                    <input type="hidden" name="id_tarifa" value="<?= (int)$plan['id_tarifa'] ?>">
                                                    <input type="hidden" name="activo" value="<?= $esActivo ? 0 : 1 ?>">
                                                    <button type="submit" 
                                                            class="btn <?= $esActivo ? 'btn-outline-danger' : 'btn-outline-success' ?> rounded-end-pill" 
                                                            title="<?= $esActivo ? 'Desactivar' : 'Activar' ?>">
                                                        <i class="bi <?= $esActivo ? 'bi-eye-slash-fill' : 'bi-check-circle-fill' ?>"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12 text-center py-4 text-muted">No hay planes comerciales registrados.</div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- ======================================================================= -->
<!-- MODAL: CATEGORÍA (Crear / Editar)                                       -->
<!-- ======================================================================= -->
<div class="modal fade" id="modalCategoria" tabindex="-1" aria-labelledby="modalCategoriaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="<?= htmlspecialchars($baseUrl) ?>/admin/catalogos/categorias/guardar" method="POST" id="formCategoria">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="id_categoria" id="cat_id_categoria" value="0">

                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark" id="modalCategoriaLabel">
                        <i class="bi bi-tag-fill text-success me-2"></i>Nueva Categoría
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body py-3">
                    <div class="mb-3">
                        <label for="cat_nombre" class="form-label fw-semibold small text-dark">Nombre de la Categoría *</label>
                        <input type="text" class="form-control rounded-3" id="cat_nombre" name="nombre" required placeholder="Ej: Artesanías y Recuerdos">
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-sm-7">
                            <label for="cat_slug" class="form-label fw-semibold small text-dark">Slug URL (Identificador) *</label>
                            <input type="text" class="form-control rounded-3" id="cat_slug" name="slug" required placeholder="artesanias-recuerdos">
                            <small class="text-muted extra-small">Se autogenera a partir del nombre.</small>
                        </div>
                        <div class="col-sm-5">
                            <label for="cat_tipo_defecto" class="form-label fw-semibold small text-dark">Tipo Predeterminado *</label>
                            <select class="form-select rounded-3" id="cat_tipo_defecto" name="tipo_defecto">
                                <option value="COMERCIAL">Comercial</option>
                                <option value="PUBLICO">Público</option>
                            </select>
                        </div>
                    </div>

                    <!-- Selector de Icono con Vista Previa -->
                    <div class="mb-3">
                        <label for="cat_icono" class="form-label fw-semibold small text-dark d-flex justify-content-between">
                            <span>Icono Bootstrap Icons *</span>
                            <span class="extra-small text-muted">Ej: bi-bag-heart</span>
                        </label>
                        <div class="input-group mb-2">
                            <span class="input-group-text bg-light rounded-start-3" id="previewIconoContainer" style="width: 48px; text-align: center;">
                                <i class="bi bi-tag fs-5 text-success" id="previewIcono"></i>
                            </span>
                            <input type="text" class="form-control" id="cat_icono" name="icono" value="bi-tag" required placeholder="bi-shop">
                        </div>

                        <!-- Paleta de iconos rápidos recomendados -->
                        <div class="p-2 bg-light rounded-3 border">
                            <small class="text-muted extra-small d-block mb-1 fw-bold">Sugerencias rápidas:</small>
                            <div class="d-flex flex-wrap gap-1" id="iconosSugeridos">
                                <?php 
                                $iconos = ['bi-egg-fried', 'bi-building', 'bi-cup-straw', 'bi-compass', 'bi-water', 'bi-shop', 
                                           'bi-bus-front', 'bi-car-front', 'bi-tree', 'bi-camera', 'bi-palette', 'bi-bag-heart', 
                                           'bi-basket', 'bi-gift', 'bi-heart-pulse', 'bi-music-note-beamed', 'bi-stars'];
                                foreach ($iconos as $ico): ?>
                                    <button type="button" class="btn btn-sm btn-white border py-0 px-2 rounded-pill extra-small btn-pick-icon" data-icon="<?= $ico ?>">
                                        <i class="bi <?= $ico ?> me-1"></i><?= str_replace('bi-', '', $ico) ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="cat_descripcion" class="form-label fw-semibold small text-dark">Descripción</label>
                        <textarea class="form-control rounded-3" id="cat_descripcion" name="descripcion" rows="2" placeholder="Breve descripción del rubro turístico o comercial"></textarea>
                    </div>

                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="cat_activo" name="activo" value="1" checked>
                        <label class="form-check-label small fw-semibold text-dark" for="cat_activo">Categoría activa y visible</label>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-semibold shadow-sm" id="btnGuardarCategoria">
                        <i class="bi bi-check2-circle me-1"></i> Guardar Categoría
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ======================================================================= -->
<!-- MODAL: MUNICIPIO (Crear / Editar)                                       -->
<!-- ======================================================================= -->
<div class="modal fade" id="modalMunicipio" tabindex="-1" aria-labelledby="modalMunicipioLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="<?= htmlspecialchars($baseUrl) ?>/admin/catalogos/municipios/guardar" method="POST" id="formMunicipio">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="id_municipio" id="mun_id_municipio" value="0">

                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark" id="modalMunicipioLabel">
                        <i class="bi bi-geo-alt-fill text-success me-2"></i>Nuevo Municipio
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body py-3">
                    <div class="row g-3 mb-3">
                        <div class="col-sm-7">
                            <label for="mun_nombre" class="form-label fw-semibold small text-dark">Nombre del Municipio *</label>
                            <input type="text" class="form-control rounded-3" id="mun_nombre" name="nombre" required placeholder="Ej: Rurrenabaque" list="listaMunicipiosBeni">
                            <datalist id="listaMunicipiosBeni">
                                <option value="Trinidad" data-prov="Cercado" data-lat="-14.8333" data-lng="-64.9000">
                                <option value="Riberalta" data-prov="Vaca Díez" data-lat="-10.9833" data-lng="-66.1000">
                                <option value="Guayaramerín" data-prov="Vaca Díez" data-lat="-10.8256" data-lng="-65.3589">
                                <option value="Rurrenabaque" data-prov="José Ballivián" data-lat="-14.4411" data-lng="-67.5278">
                                <option value="San Borja" data-prov="José Ballivián" data-lat="-14.8589" data-lng="-66.8500">
                                <option value="Santa Ana del Yacuma" data-prov="Yacuma" data-lat="-13.7439" data-lng="-65.4269">
                                <option value="San Ignacio de Moxos" data-prov="Moxos" data-lat="-14.9961" data-lng="-65.6400">
                                <option value="San Javier" data-prov="Cercado" data-lat="-14.5833" data-lng="-64.9000">
                                <option value="Reyes" data-prov="José Ballivián" data-lat="-14.2961" data-lng="-67.3361">
                                <option value="Exaltación" data-prov="Yacuma" data-lat="-13.2750" data-lng="-65.2500">
                                <option value="Magdalena" data-prov="Iténez" data-lat="-13.2667" data-lng="-64.0667">
                                <option value="Baures" data-prov="Iténez" data-lat="-13.6500" data-lng="-63.6000">
                                <option value="Huacaraje" data-prov="Iténez" data-lat="-13.6000" data-lng="-63.9000">
                                <option value="Loreto" data-prov="Marbán" data-lat="-15.1833" data-lng="-64.7667">
                                <option value="San Andrés" data-prov="Marbán" data-lat="-15.2333" data-lng="-64.5500">
                                <option value="San Joaquín" data-prov="Mamoré" data-lat="-13.0417" data-lng="-64.6667">
                                <option value="San Ramón" data-prov="Mamoré" data-lat="-13.2667" data-lng="-64.6167">
                                <option value="Puerto Siles" data-prov="Mamoré" data-lat="-12.8000" data-lng="-65.0167">
                                <option value="Santa Rosa del Yacuma" data-prov="José Ballivián" data-lat="-14.1667" data-lng="-66.8000">
                            </datalist>
                        </div>
                        <div class="col-sm-5">
                            <label for="mun_provincia" class="form-label fw-semibold small text-dark">Provincia *</label>
                            <input type="text" class="form-control rounded-3" id="mun_provincia" name="provincia" required placeholder="Ej: José Ballivián" list="listaProvinciasBeni">
                            <datalist id="listaProvinciasBeni">
                                <option value="Cercado">
                                <option value="Vaca Díez">
                                <option value="José Ballivián">
                                <option value="Yacuma">
                                <option value="Moxos">
                                <option value="Marbán">
                                <option value="Mamoré">
                                <option value="Iténez">
                            </datalist>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="mun_slug" class="form-label fw-semibold small text-dark">Slug URL (Identificador) *</label>
                        <input type="text" class="form-control rounded-3" id="mun_slug" name="slug" required placeholder="rurrenabaque">
                        <small class="text-muted extra-small">Se autogenera a partir del nombre.</small>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <label for="mun_latitud" class="form-label fw-semibold small text-dark">Latitud por Defecto</label>
                            <input type="text" class="form-control rounded-3" id="mun_latitud" name="latitud_defecto" placeholder="-14.441100">
                        </div>
                        <div class="col-sm-6">
                            <label for="mun_longitud" class="form-label fw-semibold small text-dark">Longitud por Defecto</label>
                            <input type="text" class="form-control rounded-3" id="mun_longitud" name="longitud_defecto" placeholder="-67.527800">
                        </div>
                    </div>

                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="mun_activo" name="activo" value="1" checked>
                        <label class="form-check-label small fw-semibold text-dark" for="mun_activo">Municipio activo en el catálogo</label>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-semibold shadow-sm" id="btnGuardarMunicipio">
                        <i class="bi bi-check2-circle me-1"></i> Guardar Municipio
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ======================================================================= -->
<!-- MODAL: PLAN Y TARIFA (Crear / Editar)                                   -->
<!-- ======================================================================= -->
<div class="modal fade" id="modalPlan" tabindex="-1" aria-labelledby="modalPlanLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="<?= htmlspecialchars($baseUrl) ?>/admin/catalogos/planes/guardar" method="POST" id="formPlan">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="id_tarifa" id="plan_id_tarifa" value="0">

                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark" id="modalPlanLabel">
                        <i class="bi bi-cash-coin text-warning me-2"></i>Nuevo Plan Comercial
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body py-3">
                    <div class="row g-3 mb-3">
                        <div class="col-sm-5">
                            <label for="plan_codigo" class="form-label fw-semibold small text-dark">Código de Plan *</label>
                            <input type="text" class="form-control rounded-3 text-uppercase fw-bold" id="plan_codigo" name="codigo_plan" required placeholder="TRIMESTRAL">
                            <small class="text-muted extra-small">Mayúsculas sin espacios.</small>
                        </div>
                        <div class="col-sm-7">
                            <label for="plan_nombre" class="form-label fw-semibold small text-dark">Nombre Descriptivo *</label>
                            <input type="text" class="form-control rounded-3" id="plan_nombre" name="nombre" required placeholder="Plan Trimestral Impulso">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <label for="plan_monto" class="form-label fw-semibold small text-dark">Monto (Bs) *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light rounded-start-3 fw-bold">Bs</span>
                                <input type="number" step="0.01" min="1" class="form-control fw-bold" id="plan_monto" name="monto" required placeholder="700.00">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <label for="plan_meses" class="form-label fw-semibold small text-dark">Duración (Meses) *</label>
                            <input type="number" min="1" max="60" class="form-control rounded-3" id="plan_meses" name="meses_duracion" required value="3">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="plan_descripcion" class="form-label fw-semibold small text-dark">Descripción de Beneficios *</label>
                        <textarea class="form-control rounded-3" id="plan_descripcion" name="descripcion" rows="2" required placeholder="3 meses de catálogo interactivo, botón WhatsApp y difusión"></textarea>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <label for="plan_vigente" class="form-label fw-semibold small text-dark">Vigente Desde *</label>
                            <input type="date" class="form-control rounded-3" id="plan_vigente" name="vigente_desde" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-sm-6 d-flex align-items-center pt-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="plan_activo" name="activo" value="1" checked>
                                <label class="form-check-label small fw-semibold text-dark" for="plan_activo">Plan Activo</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-semibold text-white shadow-sm" id="btnGuardarPlan">
                        <i class="bi bi-check2-circle me-1"></i> Guardar Plan Comercial
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ======================================================================= -->
<!-- JAVASCRIPT DE INTERACCIÓN, FILTROS Y MODALES                           -->
<!-- ======================================================================= -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Sincronización de URL con pestañas activas
    const tabs = document.querySelectorAll('#catalogosTabs button[data-bs-toggle="pill"]');
    tabs.forEach(tab => {
        tab.addEventListener('shown.bs.tab', (e) => {
            const targetId = e.target.getAttribute('aria-controls');
            const tabName = targetId.replace('tab-', '');
            const newUrl = new URL(window.location);
            newUrl.searchParams.set('tab', tabName);
            window.history.replaceState({}, '', newUrl);
        });
    });

    // 2. Filtros de búsqueda en tiempo real
    const inputFiltroCat = document.getElementById('filtroCategorias');
    inputFiltroCat?.addEventListener('input', () => {
        const query = inputFiltroCat.value.toLowerCase().trim();
        document.querySelectorAll('#tablaCategorias tbody tr.fila-categoria').forEach(row => {
            const txt = row.getAttribute('data-nombre') || '';
            row.style.display = txt.includes(query) ? '' : 'none';
        });
    });

    const inputFiltroMun = document.getElementById('filtroMunicipios');
    inputFiltroMun?.addEventListener('input', () => {
        const query = inputFiltroMun.value.toLowerCase().trim();
        document.querySelectorAll('#tablaMunicipios tbody tr.fila-municipio').forEach(row => {
            const txt = row.getAttribute('data-nombre') || '';
            row.style.display = txt.includes(query) ? '' : 'none';
        });
    });

    // Helper: Generador de Slugs
    function slugify(text) {
        return text
            .toString()
            .toLowerCase()
            .trim()
            .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9 -]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-');
    }

    // =========================================================================
    // MODAL CATEGORÍA
    // =========================================================================
    const modalCatEl = document.getElementById('modalCategoria');
    const modalCat = new bootstrap.Modal(modalCatEl);
    const formCat = document.getElementById('formCategoria');
    const inputCatNombre = document.getElementById('cat_nombre');
    const inputCatSlug = document.getElementById('cat_slug');
    const inputCatIcono = document.getElementById('cat_icono');
    const previewIcono = document.getElementById('previewIcono');
    let autoSlugCat = true;

    document.getElementById('btnNuevaCategoria')?.addEventListener('click', () => {
        formCat.reset();
        document.getElementById('cat_id_categoria').value = '0';
        document.getElementById('modalCategoriaLabel').innerHTML = '<i class="bi bi-tag-fill text-success me-2"></i>Nueva Categoría';
        document.getElementById('btnGuardarCategoria').innerHTML = '<i class="bi bi-check2-circle me-1"></i> Guardar Categoría';
        previewIcono.className = 'bi bi-tag fs-5 text-success';
        document.getElementById('cat_activo').checked = true;
        autoSlugCat = true;
        modalCat.show();
    });

    inputCatNombre?.addEventListener('input', () => {
        if (autoSlugCat) {
            inputCatSlug.value = slugify(inputCatNombre.value);
        }
    });

    inputCatSlug?.addEventListener('focus', () => {
        autoSlugCat = false;
    });

    inputCatIcono?.addEventListener('input', () => {
        const val = inputCatIcono.value.trim();
        previewIcono.className = `bi ${val} fs-5 text-success`;
    });

    document.querySelectorAll('.btn-pick-icon').forEach(btn => {
        btn.addEventListener('click', () => {
            const ico = btn.getAttribute('data-icon');
            inputCatIcono.value = ico;
            previewIcono.className = `bi ${ico} fs-5 text-success`;
        });
    });

    document.querySelectorAll('.btn-editar-categoria').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('cat_id_categoria').value = btn.getAttribute('data-id');
            inputCatNombre.value = btn.getAttribute('data-nombre');
            inputCatSlug.value = btn.getAttribute('data-slug');
            document.getElementById('cat_descripcion').value = btn.getAttribute('data-descripcion');
            inputCatIcono.value = btn.getAttribute('data-icono');
            previewIcono.className = `bi ${btn.getAttribute('data-icono')} fs-5 text-success`;
            document.getElementById('cat_tipo_defecto').value = btn.getAttribute('data-tipo');
            document.getElementById('cat_activo').checked = btn.getAttribute('data-activo') === '1';

            document.getElementById('modalCategoriaLabel').innerHTML = '<i class="bi bi-pencil-fill text-primary me-2"></i>Editar Categoría';
            document.getElementById('btnGuardarCategoria').innerHTML = '<i class="bi bi-check2-circle me-1"></i> Actualizar Categoría';
            autoSlugCat = false;
            modalCat.show();
        });
    });

    // =========================================================================
    // MODAL MUNICIPIO
    // =========================================================================
    const modalMunEl = document.getElementById('modalMunicipio');
    const modalMun = new bootstrap.Modal(modalMunEl);
    const formMun = document.getElementById('formMunicipio');
    const inputMunNombre = document.getElementById('mun_nombre');
    const inputMunProvincia = document.getElementById('mun_provincia');
    const inputMunSlug = document.getElementById('mun_slug');
    const inputMunLat = document.getElementById('mun_latitud');
    const inputMunLng = document.getElementById('mun_longitud');
    let autoSlugMun = true;

    document.getElementById('btnNuevoMunicipio')?.addEventListener('click', () => {
        formMun.reset();
        document.getElementById('mun_id_municipio').value = '0';
        document.getElementById('modalMunicipioLabel').innerHTML = '<i class="bi bi-geo-alt-fill text-success me-2"></i>Nuevo Municipio';
        document.getElementById('btnGuardarMunicipio').innerHTML = '<i class="bi bi-check2-circle me-1"></i> Guardar Municipio';
        document.getElementById('mun_activo').checked = true;
        autoSlugMun = true;
        modalMun.show();
    });

    inputMunNombre?.addEventListener('input', () => {
        if (autoSlugMun) {
            inputMunSlug.value = slugify(inputMunNombre.value);
        }

        // Si coincide con alguna sugerencia del datalist, autocompletar provincia y coordenadas
        const opt = document.querySelector(`#listaMunicipiosBeni option[value="${inputMunNombre.value.trim()}"]`);
        if (opt) {
            if (opt.getAttribute('data-prov')) inputMunProvincia.value = opt.getAttribute('data-prov');
            if (opt.getAttribute('data-lat')) inputMunLat.value = opt.getAttribute('data-lat');
            if (opt.getAttribute('data-lng')) inputMunLng.value = opt.getAttribute('data-lng');
        }
    });

    inputMunSlug?.addEventListener('focus', () => {
        autoSlugMun = false;
    });

    document.querySelectorAll('.btn-editar-municipio').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('mun_id_municipio').value = btn.getAttribute('data-id');
            inputMunNombre.value = btn.getAttribute('data-nombre');
            inputMunProvincia.value = btn.getAttribute('data-provincia');
            inputMunSlug.value = btn.getAttribute('data-slug');
            inputMunLat.value = btn.getAttribute('data-latitud');
            inputMunLng.value = btn.getAttribute('data-longitud');
            document.getElementById('mun_activo').checked = btn.getAttribute('data-activo') === '1';

            document.getElementById('modalMunicipioLabel').innerHTML = '<i class="bi bi-pencil-fill text-primary me-2"></i>Editar Municipio';
            document.getElementById('btnGuardarMunicipio').innerHTML = '<i class="bi bi-check2-circle me-1"></i> Actualizar Municipio';
            autoSlugMun = false;
            modalMun.show();
        });
    });

    // =========================================================================
    // MODAL PLAN Y TARIFA
    // =========================================================================
    const modalPlanEl = document.getElementById('modalPlan');
    const modalPlan = new bootstrap.Modal(modalPlanEl);
    const formPlan = document.getElementById('formPlan');
    const inputPlanCodigo = document.getElementById('plan_codigo');

    document.getElementById('btnNuevoPlan')?.addEventListener('click', () => {
        formPlan.reset();
        document.getElementById('plan_id_tarifa').value = '0';
        document.getElementById('modalPlanLabel').innerHTML = '<i class="bi bi-cash-coin text-warning me-2"></i>Nuevo Plan Comercial';
        document.getElementById('btnGuardarPlan').innerHTML = '<i class="bi bi-check2-circle me-1"></i> Guardar Plan Comercial';
        document.getElementById('plan_activo').checked = true;
        document.getElementById('plan_vigente').value = new Date().toISOString().split('T')[0];
        modalPlan.show();
    });

    inputPlanCodigo?.addEventListener('input', () => {
        inputPlanCodigo.value = inputPlanCodigo.value.toUpperCase().replace(/[^A-Z0-9_]/g, '');
    });

    document.querySelectorAll('.btn-editar-plan').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('plan_id_tarifa').value = btn.getAttribute('data-id');
            inputPlanCodigo.value = btn.getAttribute('data-codigo');
            document.getElementById('plan_nombre').value = btn.getAttribute('data-nombre');
            document.getElementById('plan_monto').value = btn.getAttribute('data-monto');
            document.getElementById('plan_meses').value = btn.getAttribute('data-meses');
            document.getElementById('plan_descripcion').value = btn.getAttribute('data-descripcion');
            document.getElementById('plan_vigente').value = btn.getAttribute('data-vigente');
            document.getElementById('plan_activo').checked = btn.getAttribute('data-activo') === '1';

            document.getElementById('modalPlanLabel').innerHTML = '<i class="bi bi-pencil-fill text-primary me-2"></i>Editar Plan Comercial';
            document.getElementById('btnGuardarPlan').innerHTML = '<i class="bi bi-check2-circle me-1"></i> Actualizar Plan Comercial';
            modalPlan.show();
        });
    });
});
</script>
