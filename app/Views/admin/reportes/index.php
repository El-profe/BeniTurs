<div class="container-fluid px-4 py-4">
    <!-- Encabezado con Botón de Impresión -->
    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Centro de Reportes y Análisis</h1>
            <p class="text-muted small mb-0">Consolidado de métricas, vigencias y movimientos financieros.</p>
        </div>
        <button onclick="window.print()" class="btn btn-outline-primary shadow-sm">
            <i class="bi bi-printer me-2"></i>Imprimir Informe
        </button>
    </div>

    <!-- Tarjetas de KPIs (Fila de Métricas) -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 border-start border-primary border-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1" style="font-size: 0.75rem;">Recaudación (Mes Actual)</div>
                            <div class="h5 mb-0 fw-bold text-gray-800">Bs. <?= number_format($kpis['recaudacion_mes'], 2) ?></div>
                        </div>
                        <div class="ms-3">
                            <i class="bi bi-cash-coin fs-2 text-primary opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 border-start border-success border-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1" style="font-size: 0.75rem;">Comercios Activos</div>
                            <div class="h5 mb-0 fw-bold text-gray-800"><?= $kpis['comercios_activos'] ?></div>
                        </div>
                        <div class="ms-3">
                            <i class="bi bi-shop fs-2 text-success opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 border-start border-warning border-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1" style="font-size: 0.75rem;">Por Vencer (7 días)</div>
                            <div class="h5 mb-0 fw-bold text-gray-800"><?= $kpis['por_vencer'] ?></div>
                        </div>
                        <div class="ms-3">
                            <i class="bi bi-exclamation-triangle fs-2 text-warning opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 border-start border-danger border-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1" style="font-size: 0.75rem;">Solicitudes Pendientes</div>
                            <div class="h5 mb-0 fw-bold text-gray-800"><?= $kpis['solicitudes_pendientes'] ?></div>
                        </div>
                        <div class="ms-3">
                            <i class="bi bi-clock-history fs-2 text-danger opacity-25"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros y Descargas -->
    <div class="row g-4 mb-4 d-print-none">
        <!-- Panel de Filtros -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 fw-bold text-dark"><i class="bi bi-filter me-2 text-primary"></i>Filtros de Análisis</h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="<?= $baseUrl ?>/admin/reportes" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Tipo de Reporte</label>
                            <select name="tipo_reporte" class="form-select form-select-sm">
                                <option value="financiero" <?= $filtros['tipo_reporte'] == 'financiero' ? 'selected' : '' ?>>Balance Financiero</option>
                                <option value="vigencias" <?= $filtros['tipo_reporte'] == 'vigencias' ? 'selected' : '' ?>>Estado de Vigencias</option>
                                <option value="directorio" <?= $filtros['tipo_reporte'] == 'directorio' ? 'selected' : '' ?>>Catálogo Directorio</option>
                                <option value="solicitudes" <?= $filtros['tipo_reporte'] == 'solicitudes' ? 'selected' : '' ?>>Registro Solicitudes</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Desde</label>
                            <input type="date" name="desde" class="form-control form-control-sm" value="<?= $filtros['desde'] ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Hasta</label>
                            <input type="date" name="hasta" class="form-control form-control-sm" value="<?= $filtros['hasta'] ?>">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="bi bi-search me-1"></i> Filtrar Previsualización
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Panel de Exportación -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 fw-bold text-dark"><i class="bi bi-download me-2 text-success"></i>Exportar Datos (CSV)</h6>
                </div>
                <div class="card-body d-flex flex-column gap-2">
                    <a href="<?= $baseUrl ?>/admin/reportes/exportar?tipo=financiero&desde=<?= $filtros['desde'] ?>&hasta=<?= $filtros['hasta'] ?>" class="btn btn-outline-dark btn-sm text-start">
                        <i class="bi bi-file-earmark-spreadsheet me-2 text-success"></i> Balance de Ingresos (Bs)
                    </a>
                    <a href="<?= $baseUrl ?>/admin/reportes/exportar?tipo=vigencias&id_categoria=<?= $filtros['id_categoria'] ?>" class="btn btn-outline-dark btn-sm text-start">
                        <i class="bi bi-file-earmark-spreadsheet me-2 text-primary"></i> Estado de Vigencias
                    </a>
                    <a href="<?= $baseUrl ?>/admin/reportes/exportar?tipo=directorio&id_categoria=<?= $filtros['id_categoria'] ?>" class="btn btn-outline-dark btn-sm text-start">
                        <i class="bi bi-file-earmark-spreadsheet me-2 text-info"></i> Catálogo de Fichas
                    </a>
                    <a href="<?= $baseUrl ?>/admin/reportes/exportar?tipo=solicitudes&desde=<?= $filtros['desde'] ?>&hasta=<?= $filtros['hasta'] ?>" class="btn btn-outline-dark btn-sm text-start">
                        <i class="bi bi-file-earmark-spreadsheet me-2 text-warning"></i> Registro de Solicitudes
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Previsualización -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-dark">Previsualización de Datos: <span class="text-primary text-uppercase"><?= $filtros['tipo_reporte'] ?></span></h6>
            <span class="badge bg-light text-dark border"><?= count($datosReporte) ?> Registros encontrados</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                    <thead class="table-light text-muted">
                        <?php if ($filtros['tipo_reporte'] == 'financiero'): ?>
                            <tr>
                                <th>Fecha</th>
                                <th>Comercio</th>
                                <th>Plan / Meses</th>
                                <th>Comprobante</th>
                                <th>Método</th>
                                <th class="text-end">Importe</th>
                            </tr>
                        <?php elseif ($filtros['tipo_reporte'] == 'vigencias'): ?>
                            <tr>
                                <th>Comercio / Categoría</th>
                                <th>Inicio</th>
                                <th>Vencimiento</th>
                                <th>Días Restantes</th>
                                <th class="text-center">Estado</th>
                            </tr>
                        <?php elseif ($filtros['tipo_reporte'] == 'directorio'): ?>
                            <tr>
                                <th>Nombre Establecimiento</th>
                                <th>Tipo</th>
                                <th>Categoría</th>
                                <th>Estados (H/A)</th>
                                <th class="text-center">Visible Web</th>
                            </tr>
                        <?php elseif ($filtros['tipo_reporte'] == 'solicitudes'): ?>
                            <tr>
                                <th>Fecha</th>
                                <th>Solicitante / Negocio</th>
                                <th>Plan Solicitado</th>
                                <th>Estado</th>
                                <th>Atendido Por</th>
                            </tr>
                        <?php endif; ?>
                    </thead>
                    <tbody>
                        <?php if (empty($datosReporte)): ?>
                            <tr>
                                <td colspan="10" class="text-center py-4 text-muted italic">No se encontraron datos para los filtros seleccionados.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($datosReporte as $row): ?>
                            <?php if ($filtros['tipo_reporte'] == 'financiero'): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($row['fecha_confirmacion'])) ?></td>
                                    <td class="fw-bold"><?= htmlspecialchars($row['comercio']) ?></td>
                                    <td><?= htmlspecialchars($row['plan']) ?> (<?= $row['meses_duracion'] ?> m)</td>
                                    <td class="text-monospace small"><?= htmlspecialchars($row['numero_comprobante'] ?? 'N/A') ?></td>
                                    <td><small><?= htmlspecialchars($row['metodo_pago']) ?></small></td>
                                    <td class="text-end fw-bold text-success">Bs. <?= number_format($row['monto'], 2) ?></td>
                                </tr>
                            <?php elseif ($filtros['tipo_reporte'] == 'vigencias'): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($row['nombre']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($row['categoria']) ?></small>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($row['fecha_inicio'])) ?></td>
                                    <td><?= date('d/m/Y', strtotime($row['fecha_vencimiento'])) ?></td>
                                    <td>
                                        <?php if ($row['dias_restantes'] > 0): ?>
                                            <span class="<?= $row['dias_restantes'] <= 7 ? 'text-warning fw-bold' : '' ?>">
                                                <?= $row['dias_restantes'] ?> días
                                            </span>
                                        <?php else: ?>
                                            <span class="text-danger fw-bold">Vencido</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php 
                                            $badgeClass = match($row['estado_vigencia']) {
                                                'Vigente' => 'bg-success',
                                                'Por Vencer' => 'bg-warning text-dark',
                                                'Vencida' => 'bg-danger',
                                                'Programada' => 'bg-info text-dark',
                                                default => 'bg-secondary'
                                            };
                                        ?>
                                        <span class="badge <?= $badgeClass ?>"><?= $row['estado_vigencia'] ?></span>
                                    </td>
                                </tr>
                            <?php elseif ($filtros['tipo_reporte'] == 'directorio'): ?>
                                <tr>
                                    <td class="fw-bold"><?= htmlspecialchars($row['nombre']) ?></td>
                                    <td>
                                        <span class="badge <?= $row['tipo_lugar'] == 'COMERCIAL' ? 'bg-light text-dark border' : 'bg-secondary' ?>">
                                            <?= $row['tipo_lugar'] ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($row['categoria']) ?></td>
                                    <td>
                                        <span class="badge <?= $row['habilitado'] ? 'bg-success' : 'bg-danger' ?>" title="Habilitado">H</span>
                                        <span class="badge <?= $row['aprobado'] ? 'bg-success' : 'bg-primary' ?>" title="Aprobado">A</span>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($row['es_visible']): ?>
                                            <i class="bi bi-eye-fill text-success fs-5"></i>
                                        <?php else: ?>
                                            <i class="bi bi-eye-slash-fill text-muted fs-5"></i>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php elseif ($filtros['tipo_reporte'] == 'solicitudes'): ?>
                                <tr>
                                    <td><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($row['nombre_negocio']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($row['nombre_solicitante']) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($row['plan_nombre'] ?? 'Personalizado') ?></td>
                                    <td>
                                        <?php 
                                            $sBadge = match($row['estado']) {
                                                'PENDIENTE' => 'bg-warning text-dark',
                                                'ACEPTADA' => 'bg-success',
                                                'RECHAZADA' => 'bg-danger',
                                                'EN_PROCESO' => 'bg-info text-dark',
                                                default => 'bg-secondary'
                                            };
                                        ?>
                                        <span class="badge <?= $sBadge ?>"><?= $row['estado'] ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($row['admin_nombre'] ?? 'Sin asignar') ?></td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white text-muted small py-2 d-print-none text-center">
            Este reporte es una vista preliminar. Para obtener el detalle completo, use la herramienta de descarga CSV.
        </div>
    </div>
</div>

<style>
@media print {
    .btn, .sidebar, header, nav, .d-print-none {
        display: none !important;
    }
    body {
        background-color: #fff !important;
        margin: 0;
        padding: 0;
    }
    .card {
        border: 1px solid #ddd !important;
        box-shadow: none !important;
    }
    .table-responsive {
        overflow: visible !important;
    }
}
</style>
