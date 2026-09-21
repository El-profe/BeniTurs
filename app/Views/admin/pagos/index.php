<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold text-dark mb-1">Control de Pagos y Vigencias</h3>
            <p class="text-muted small mb-0">Auditoría manual de transferencias bancarias y cobertura de mensualidades</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <!-- Filtros de Estado -->
            <div class="btn-group rounded-pill shadow-sm" role="group">
                <a href="<?= htmlspecialchars($baseUrl) ?>/admin/pagos" class="btn btn-sm <?= empty($filtroActual) ? 'btn-success fw-bold' : 'btn-light border' ?>">Todos</a>
                <a href="<?= htmlspecialchars($baseUrl) ?>/admin/pagos?estado=PENDIENTE" class="btn btn-sm <?= $filtroActual === 'PENDIENTE' ? 'btn-warning text-dark fw-bold' : 'btn-light border' ?>">Pendientes</a>
                <a href="<?= htmlspecialchars($baseUrl) ?>/admin/pagos?estado=CONFIRMADO" class="btn btn-sm <?= $filtroActual === 'CONFIRMADO' ? 'btn-success fw-bold' : 'btn-light border' ?>">Confirmados</a>
            </div>

            <a href="<?= htmlspecialchars($baseUrl) ?>/admin/pagos/crear" class="btn btn-warning rounded-pill px-4 fw-bold text-dark shadow-sm">
                <i class="bi bi-plus-lg me-1"></i> Registrar Pago
            </a>
        </div>
    </div>

    <!-- Alertas de Publicaciones Próximas a Vencer (RF-29) -->
    <?php if (!empty($proximosVencimientos)): ?>
        <div class="alert alert-warning rounded-4 shadow-sm border-0 mb-4 p-3">
            <h6 class="fw-bold mb-2"><i class="bi bi-exclamation-triangle-fill me-2"></i>Publicaciones Próximas a Vencer (Siguientes 5 días):</h6>
            <ul class="mb-0 small ps-3">
                <?php foreach ($proximosVencimientos as $pv): ?>
                    <li>
                        <strong><?= htmlspecialchars($pv['nombre']) ?></strong> vence el 
                        <strong><?= date('d/m/Y', strtotime($pv['fecha_vencimiento'])) ?></strong> 
                        (Quedan <?= $pv['dias_restantes'] ?> días).
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-4 small mb-4 border-0 shadow-sm">
            <i class="bi bi-check-circle-fill me-2 fs-5"></i> <?= htmlspecialchars($mensaje) ?>
            <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-4 small mb-4 border-0 shadow-sm">
            <i class="bi bi-x-octagon-fill me-2 fs-5"></i> <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Tabla Principal de Pagos -->
    <div class="admin-card overflow-hidden">
        <div class="table-responsive">
            <table class="table admin-table align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 70px;">Pago #</th>
                        <th>Comercio</th>
                        <th>Importe</th>
                        <th>Fecha Declarada</th>
                        <th>Método / N° Transacción</th>
                        <th>Estado Pago</th>
                        <th>Vigencia Cubierta</th>
                        <th class="text-end" style="width: 150px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($pagos)): ?>
                        <?php foreach ($pagos as $p): ?>
                            <tr>
                                <td class="text-muted fw-bold">#<?= $p['id_pago'] ?></td>
                                <td>
                                    <strong class="text-dark d-block"><?= htmlspecialchars($p['nombre_establecimiento']) ?></strong>
                                    <?php if (!empty($p['observaciones'])): ?>
                                        <small class="text-muted extra-small d-block text-truncate" style="max-width: 220px;"><?= htmlspecialchars($p['observaciones']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold text-dark fs-6">
                                    Bs <?= number_format($p['monto'], 2) ?><small class="d-block text-muted"><?= (int)$p['meses_duracion'] ?> mes(es)</small>
                                </td>
                                <td class="small text-muted">
                                    <?= date('d/m/Y', strtotime($p['fecha_pago_declarada'])) ?>
                                </td>
                                <td>
                                    <span class="small d-block text-dark fw-medium"><?= htmlspecialchars($p['metodo_pago']) ?></span>
                                    <code class="extra-small text-muted"><?= htmlspecialchars($p['numero_comprobante'] ?: 'Sin comprobante') ?></code>
                                </td>
                                <td>
                                    <?php if ($p['estado'] === 'PENDIENTE'): ?>
                                        <span class="badge bg-warning text-dark rounded-pill px-3 py-1">Pendiente</span>
                                    <?php elseif ($p['estado'] === 'CONFIRMADO'): ?>
                                        <span class="badge bg-success rounded-pill px-3 py-1">Confirmado</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger rounded-pill px-3 py-1">Anulado</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($p['fecha_inicio'])): ?>
                                        <div class="small fw-semibold text-success">
                                            <?= date('d/m/Y', strtotime($p['fecha_inicio'])) ?> &rarr; <?= date('d/m/Y', strtotime($p['fecha_vencimiento'])) ?>
                                        </div>
                                        <span class="badge bg-light text-muted border extra-small" style="font-size: 0.65rem;">
                                            <?= htmlspecialchars($p['tipo_periodo']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted extra-small">Sin vigencia asignada</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if ($p['estado'] === 'PENDIENTE'): ?>
                                        <div class="d-inline-flex gap-1">
                                            <!-- Formulario Confirmar Pago (Transaccional) -->
                                            <form action="<?= htmlspecialchars($baseUrl) ?>/admin/pagos/confirmar" method="POST" class="d-inline m-0">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                                <input type="hidden" name="id_pago" value="<?= $p['id_pago'] ?>">
                                                <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 fw-bold" title="Confirmar abono y activar ficha">
                                                    <i class="bi bi-check2 me-1"></i> Confirmar
                                                </button>
                                            </form>

                                            <!-- Botón Anular -->
                                            <button type="button" class="btn btn-sm btn-outline-danger rounded-circle" data-bs-toggle="modal" data-bs-target="#modalAnular<?= $p['id_pago'] ?>" title="Anular comprobante" style="width: 32px; height: 32px; padding: 0;">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </div>

                                        <!-- Modal Anulación -->
                                        <div class="modal fade" id="modalAnular<?= $p['id_pago'] ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-sm">
                                                <div class="modal-content rounded-4 border-0 shadow">
                                                    <form action="<?= htmlspecialchars($baseUrl) ?>/admin/pagos/anular" method="POST">
                                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                                        <input type="hidden" name="id_pago" value="<?= $p['id_pago'] ?>">
                                                        <div class="modal-body p-4 text-center">
                                                            <i class="bi bi-exclamation-circle text-danger display-5 mb-2 d-block"></i>
                                                            <h6 class="fw-bold mb-2">¿Anular Pago #<?= $p['id_pago'] ?>?</h6>
                                                            <textarea name="motivo_anulacion" rows="2" class="form-control form-control-sm mb-3" placeholder="Motivo (ej. Fondos no acreditados)" required></textarea>
                                                            <button type="submit" class="btn btn-danger btn-sm rounded-pill w-100 fw-bold">Confirmar Anulación</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <small class="text-muted extra-small">Procesado</small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-cash-coin display-5 d-block mb-2"></i>
                                No hay registros de pagos con el filtro seleccionado.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>