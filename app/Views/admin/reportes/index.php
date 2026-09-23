<div class="container-fluid p-0">
    <!-- Encabezado con Botón Imprimir -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold text-dark mb-1">Centro de Reportes y Analítica</h3>
            <p class="text-muted small mb-0">Consolidado de ingresos, cobertura de planes comerciales y padrón turístico</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-none" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Imprimir Informe
            </button>
        </div>
    </div>

    <!-- 1. Cuadrícula de KPIs Financieros y Operativos -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="admin-card p-3 p-xl-4 h-100 border-start border-success border-4">
                <span class="text-muted extra-small fw-bold text-uppercase">Ingresos Totales (Histórico)</span>
                <h3 class="fw-bold text-dark mt-2 mb-1">Bs <?= number_format($kpis['total_recaudado'], 2) ?></h3>
                <small class="text-success extra-small fw-semibold"><i class="bi bi-shield-check me-1"></i>Abonos confirmados</small>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="admin-card p-3 p-xl-4 h-100 border-start border-primary border-4">
                <span class="text-muted extra-small fw-bold text-uppercase">Recaudación Mes Actual</span>
                <h3 class="fw-bold text-primary mt-2 mb-1">Bs <?= number_format($kpis['total_mes'], 2) ?></h3>
                <small class="text-muted extra-small">Mes en curso (<?= date('m/Y') ?>)</small>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="admin-card p-3 p-xl-4 h-100 border-start border-info border-4">
                <span class="text-muted extra-small fw-bold text-uppercase">Comercios al Día</span>
                <h3 class="fw-bold text-dark mt-2 mb-1"><?= $kpis['comercios_activos'] ?></h3>
                <small class="text-muted extra-small">Fichas con vigencia activa en web</small>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="admin-card p-3 p-xl-4 h-100 border-start border-warning border-4">
                <span class="text-muted extra-small fw-bold text-uppercase">Por Vencer (< 7 días)</span>
                <h3 class="fw-bold text-warning mt-2 mb-1"><?= $kpis['comercios_por_vencer'] ?></h3>
                <small class="text-muted extra-small">Requieren seguimiento de renovación</small>
            </div>
        </div>
    </div>

    <!-- 2. Centro de Descargas CSV / Excel -->
    <div class="admin-card p-4 mb-4">
        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-file-earmark-spreadsheet-fill text-success me-2"></i>Exportación de Padrones a CSV / Excel</h6>
        <div class="row g-2">
            <div class="col-sm-6 col-md-3">
                <a href="<?= htmlspecialchars($baseUrl) ?>/admin/reportes/exportar?tipo=financiero&desde=<?= urlencode($filtroDesde) ?>&hasta=<?= urlencode($filtroHasta) ?>" 
                   class="btn btn-outline-dark btn-sm w-100 py-2 rounded-3 text-start d-flex align-items-center justify-content-between">
                    <span><i class="bi bi-cash-stack text-success me-2"></i>Balance Ingresos</span>
                    <i class="bi bi-download small"></i>
                </a>
            </div>
            <div class="col-sm-6 col-md-3">
                <a href="<?= htmlspecialchars($baseUrl) ?>/admin/reportes/exportar?tipo=vigencias" 
                   class="btn btn-outline-dark btn-sm w-100 py-2 rounded-3 text-start d-flex align-items-center justify-content-between">
                    <span><i class="bi bi-calendar-range text-primary me-2"></i>Estado Vigencias</span>
                    <i class="bi bi-download small"></i>
                </a>
            </div>
            <div class="col-sm-6 col-md-3">
                <a href="<?= htmlspecialchars($baseUrl) ?>/admin/reportes/exportar?tipo=lugares" 
                   class="btn btn-outline-dark btn-sm w-100 py-2 rounded-3 text-start d-flex align-items-center justify-content-between">
                    <span><i class="bi bi-geo-alt text-info me-2"></i>Padrón de Lugares</span>
                    <i class="bi bi-download small"></i>
                </a>
            </div>
            <div class="col-sm-6 col-md-3">
                <a href="<?= htmlspecialchars($baseUrl) ?>/admin/reportes/exportar?tipo=solicitudes" 
                   class="btn btn-outline-dark btn-sm w-100 py-2 rounded-3 text-start d-flex align-items-center justify-content-between">
                    <span><i class="bi bi-inbox text-warning me-2"></i>Solicitudes Web</span>
                    <i class="bi bi-download small"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- 3. Filtro Rápido de Fechas -->
    <div class="admin-card p-3 mb-4">
        <form action="<?= htmlspecialchars($baseUrl) ?>/admin/reportes" method="GET" class="row g-2 align-items-end">
            <div class="col-sm-4 col-md-3">
                <label class="form-label extra-small fw-bold text-muted mb-1">Fecha Desde:</label>
                <input type="date" name="desde" value="<?= htmlspecialchars($filtroDesde) ?>" class="form-control form-control-sm">
            </div>
            <div class="col-sm-4 col-md-3">
                <label class="form-label extra-small fw-bold text-muted mb-1">Fecha Hasta:</label>
                <input type="date" name="hasta" value="<?= htmlspecialchars($filtroHasta) ?>" class="form-control form-control-sm">
            </div>
            <div class="col-sm-4 col-md-2">
                <button type="submit" class="btn btn-success btn-sm w-100 fw-bold">
                    <i class="bi bi-filter me-1"></i> Filtrar
                </button>
            </div>
            <?php if (!empty($filtroDesde) || !empty($filtroHasta)): ?>
                <div class="col-sm-12 col-md-2">
                    <a href="<?= htmlspecialchars($baseUrl) ?>/admin/reportes" class="btn btn-light border btn-sm w-100 text-muted">
                        Limpiar
                    </a>
                </div>
            <?php endif; ?>
        </form>
    </div>

    <!-- 4. Tablas Previsualizadoras -->
    <div class="row g-4">
        <!-- Balance Financiero Reciente -->
        <div class="col-lg-6">
            <div class="admin-card h-100 overflow-hidden">
                <div class="p-3 bg-white border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-wallet2 text-success me-2"></i>Últimos Abonos Confirmados</h6>
                    <span class="badge bg-light text-dark border"><?= count($pagos) ?> registros</span>
                </div>
                <div class="table-responsive" style="max-height: 420px;">
                    <table class="table admin-table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Establecimiento</th>
                                <th>Fecha</th>
                                <th>Importe</th>
                                <th>Comprobante</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($pagos)): ?>
                                <?php foreach (array_slice($pagos, 0, 15) as $p): ?>
                                    <tr>
                                        <td>
                                            <strong class="text-dark d-block small"><?= htmlspecialchars($p['establecimiento']) ?></strong>
                                            <small class="text-muted extra-small"><?= htmlspecialchars($p['categoria']) ?></small>
                                        </td>
                                        <td class="small text-muted"><?= date('d/m/Y', strtotime($p['fecha_pago_declarada'])) ?></td>
                                        <td class="fw-bold text-success small">Bs <?= number_format($p['monto'], 2) ?></td>
                                        <td><code class="extra-small text-muted"><?= htmlspecialchars($p['numero_comprobante'] ?: 'S/N') ?></code></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted small">No hay pagos registrados en este periodo.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Estado de Vigencias -->
        <div class="col-lg-6">
            <div class="admin-card h-100 overflow-hidden">
                <div class="p-3 bg-white border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-clock-history text-primary me-2"></i>Monitoreo de Suscripciones</h6>
                    <span class="badge bg-light text-dark border"><?= count($vigencias) ?> fichas</span>
                </div>
                <div class="table-responsive" style="max-height: 420px;">
                    <table class="table admin-table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Comercio</th>
                                <th>Vencimiento</th>
                                <th>Restante</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($vigencias)): ?>
                                <?php foreach (array_slice($vigencias, 0, 15) as $v): ?>
                                    <tr>
                                        <td>
                                            <strong class="text-dark d-block small"><?= htmlspecialchars($v['establecimiento']) ?></strong>
                                            <span class="badge bg-light text-muted border extra-small" style="font-size: 0.65rem;"><?= htmlspecialchars($v['tipo_periodo']) ?></span>
                                        </td>
                                        <td class="small text-muted"><?= date('d/m/Y', strtotime($v['fecha_vencimiento'])) ?></td>
                                        <td class="small fw-semibold <?= $v['dias_restantes'] <= 5 ? 'text-danger' : 'text-dark' ?>">
                                            <?= $v['dias_restantes'] >= 0 ? $v['dias_restantes'] . ' días' : 'Vencido' ?>
                                        </td>
                                        <td>
                                            <?php if ($v['estado_cobertura'] === 'VIGENTE'): ?>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill extra-small">Activo</span>
                                            <?php elseif ($v['estado_cobertura'] === 'POR_VENCER'): ?>
                                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill extra-small">Por Vencer</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill extra-small">Vencido</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted small">No hay datos de vigencias.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>