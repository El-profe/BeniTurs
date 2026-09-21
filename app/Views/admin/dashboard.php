<div class="container-fluid p-0">
    <!-- Indicadores (KPIs) -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="admin-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted extra-small fw-semibold text-uppercase d-block" style="font-size: 0.68rem;">Lugares</span>
                        <h3 class="fw-bold text-dark mt-1 mb-0"><?= $totalLugares ?></h3>
                        <small class="text-success extra-small d-none d-sm-block"><i class="bi bi-check2-circle"></i> Registrados</small>
                    </div>
                    <div class="widget-icon bg-success-subtle text-success flex-shrink-0">
                        <i class="bi bi-geo-alt-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="admin-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted extra-small fw-semibold text-uppercase d-block" style="font-size: 0.68rem;">Categorías</span>
                        <h3 class="fw-bold text-dark mt-1 mb-0"><?= $totalCategorias ?></h3>
                        <small class="text-muted extra-small d-none d-sm-block">Activas</small>
                    </div>
                    <div class="widget-icon bg-primary-subtle text-primary flex-shrink-0">
                        <i class="bi bi-tags-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="admin-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted extra-small fw-semibold text-uppercase d-block" style="font-size: 0.68rem;">Solicitudes</span>
                        <h3 class="fw-bold text-warning mt-1 mb-0"><?= $totalSolicitudes ?></h3>
                        <small class="text-muted extra-small d-none d-sm-block">Pendientes</small>
                    </div>
                    <div class="widget-icon bg-warning-subtle text-warning flex-shrink-0">
                        <i class="bi bi-inbox-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="admin-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted extra-small fw-semibold text-uppercase d-block" style="font-size: 0.68rem;">Tarifa Base</span>
                        <h3 class="fw-bold text-dark mt-1 mb-0">Bs <?= number_format($tarifaVigente, 0) ?></h3>
                        <small class="text-muted extra-small d-none d-sm-block">Por mes</small>
                    </div>
                    <div class="widget-icon bg-info-subtle text-info flex-shrink-0">
                        <i class="bi bi-cash-coin"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Fichas Recientes -->
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="admin-card overflow-hidden">
                <!-- Encabezado con flex-wrap para que el botón no se deforme -->
                <div class="p-3 border-bottom d-flex justify-content-between align-items-center bg-white gap-2">
                    <h6 class="fw-bold text-dark mb-0 text-truncate">
                        <i class="bi bi-clock-history me-1 text-success"></i> Últimas Fichas Registradas
                    </h6>
                    <a href="<?= htmlspecialchars($baseUrl) ?>/admin/lugares" class="btn btn-outline-success btn-sm rounded-pill px-3 text-nowrap flex-shrink-0" style="font-size: 0.78rem;">
                        Ver Todas <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>

                <!-- Tabla con scroll horizontal suave -->
                <div class="table-responsive">
                    <table class="table admin-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Lugar</th>
                                <th>Categoría</th>
                                <th>Tipo</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($ultimosLugares)): ?>
                                <?php foreach ($ultimosLugares as $l): ?>
                                    <tr>
                                        <td>
                                            <strong class="text-dark d-block"><?= htmlspecialchars($l['nombre']) ?></strong>
                                            <small class="text-muted">Trinidad, Beni</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border text-nowrap">
                                                <?= htmlspecialchars($l['categoria']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge <?= $l['tipo_lugar'] === 'PUBLICO' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning-emphasis border border-warning-subtle' ?> rounded-pill text-nowrap">
                                                <?= $l['tipo_lugar'] === 'PUBLICO' ? 'Público' : 'Comercio' ?>
                                            </span>
                                        </td>
                                        <td class="text-muted small text-nowrap">
                                            <?= date('d/m/Y', strtotime($l['created_at'])) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">No existen lugares registrados.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tarjeta Informativa Lateral -->
        <div class="col-lg-4">
            <div class="admin-card p-3 p-md-4">
                <h6 class="fw-bold text-dark mb-3"><i class="bi bi-shield-shaded me-2 text-warning"></i>Reglas del Directorio</h6>
                <ul class="list-unstyled small mb-3 d-flex flex-column gap-2 text-muted">
                    <li class="d-flex gap-2">
                        <i class="bi bi-check-circle-fill text-success fs-6"></i>
                        <span><strong>Atractivos Públicos:</strong> Gratuitos al aprobar y habilitar.</span>
                    </li>
                    <li class="d-flex gap-2">
                        <i class="bi bi-exclamation-circle-fill text-warning fs-6"></i>
                        <span><strong>Comercios:</strong> Ocultos hasta contar con pago confirmado vigente.</span>
                    </li>
                </ul>

                <div class="p-2 px-3 bg-light rounded-3 border">
                    <small class="text-muted d-block extra-small" style="font-size: 0.68rem;">ZONA HORARIA:</small>
                    <strong class="text-dark small"><i class="bi bi-globe-americas me-1 text-success"></i> America/La_Paz</strong>
                </div>
            </div>
        </div>
    </div>
</div>