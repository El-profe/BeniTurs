<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold text-dark mb-1">Bandeja de Solicitudes Comerciales</h3>
            <p class="text-muted small mb-0">Revisión de solicitudes de negocios interesados en el directorio</p>
        </div>
        <!-- Filtros Rápidos por Estado -->
        <div class="btn-group rounded-pill shadow-sm" role="group">
            <a href="<?= htmlspecialchars($baseUrl) ?>/admin/solicitudes" class="btn btn-sm <?= empty($filtroActual) ? 'btn-success fw-bold' : 'btn-light border' ?>">Todas</a>
            <a href="<?= htmlspecialchars($baseUrl) ?>/admin/solicitudes?estado=PENDIENTE" class="btn btn-sm <?= $filtroActual === 'PENDIENTE' ? 'btn-warning text-dark fw-bold' : 'btn-light border' ?>">Pendientes</a>
            <a href="<?= htmlspecialchars($baseUrl) ?>/admin/solicitudes?estado=ACEPTADA" class="btn btn-sm <?= $filtroActual === 'ACEPTADA' ? 'btn-success fw-bold' : 'btn-light border' ?>">Aceptadas</a>
            <a href="<?= htmlspecialchars($baseUrl) ?>/admin/solicitudes?estado=RECHAZADA" class="btn btn-sm <?= $filtroActual === 'RECHAZADA' ? 'btn-danger fw-bold' : 'btn-light border' ?>">Rechazadas</a>
        </div>
    </div>

    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-4 small mb-4 border-0 shadow-sm">
            <i class="bi bi-check-circle-fill me-2 fs-5"></i> <?= htmlspecialchars($mensaje) ?>
            <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-4 small mb-4 border-0 shadow-sm">
            <i class="bi bi-exclamation-octagon-fill me-2 fs-5"></i> <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="admin-card overflow-hidden">
        <div class="table-responsive">
            <table class="table admin-table align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>Establecimiento</th>
                        <th>Categoría</th>
                        <th>Solicitante & Contacto</th>
                        <th>Fecha Envío</th>
                        <th>Estado</th>
                        <th class="text-end" style="width: 170px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($solicitudes)): ?>
                        <?php foreach ($solicitudes as $s): ?>
                            <tr>
                                <td class="text-muted fw-bold small">#<?= $s['id_solicitud'] ?></td>
                                <td>
                                    <strong class="text-dark d-block"><?= htmlspecialchars($s['nombre_establecimiento']) ?></strong>
                                    <small class="text-muted d-block text-truncate" style="max-width: 250px;">
                                        <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($s['direccion']) ?>
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border rounded-pill px-3 py-1">
                                        <?= htmlspecialchars($s['categoria']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-semibold small text-dark"><?= htmlspecialchars($s['nombre_solicitante']) ?></div>
                                    <small class="text-muted d-block">
                                        <i class="bi bi-whatsapp text-success me-1"></i><?= htmlspecialchars($s['telefono_contacto']) ?>
                                    </small>
                                </td>
                                <td class="text-muted small">
                                    <?= date('d/m/Y H:i', strtotime($s['created_at'])) ?>
                                </td>
                                <td>
                                    <?php if ($s['estado'] === 'PENDIENTE'): ?>
                                        <span class="badge bg-warning text-dark rounded-pill px-3 py-1">Pendiente</span>
                                    <?php elseif ($s['estado'] === 'ACEPTADA'): ?>
                                        <span class="badge bg-success rounded-pill px-3 py-1">Aceptada</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger rounded-pill px-3 py-1">Rechazada</span>
                                    <?php endif; ?>

                                    <?php if (!empty($s['id_lugar_creado'])): ?>
                                        <span class="badge bg-info-subtle text-info-emphasis rounded-pill d-block mt-1" style="font-size: 0.65rem;">
                                            Convertida a Ficha #<?= $s['id_lugar_creado'] ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        
                                        <!-- Botón Ver Detalles (Modal Bootstrap) -->
                                        <button type="button" class="btn btn-light btn-sm border rounded-circle" 
                                                data-bs-toggle="modal" data-bs-target="#modalDetalle<?= $s['id_solicitud'] ?>" 
                                                title="Ver detalle completo" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                            <i class="bi bi-eye"></i>
                                        </button>

                                        <!-- Botón Convertir en Ficha (Solo si está ACEPTADA y aún no fue convertida) -->
                                        <?php if ($s['estado'] === 'ACEPTADA' && empty($s['id_lugar_creado'])): ?>
                                            <form action="<?= htmlspecialchars($baseUrl) ?>/admin/solicitudes/convertir" method="POST" class="d-inline m-0">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                                <input type="hidden" name="id_solicitud" value="<?= $s['id_solicitud'] ?>">
                                                <button type="submit" class="btn btn-sm btn-success rounded-pill px-2 fw-semibold" title="Crear ficha comercial sin duplicar" style="font-size: 0.75rem;">
                                                    <i class="bi bi-arrow-right-circle me-1"></i> Ficha
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                    </div>
                                </td>
                            </tr>

                            <!-- Modal de Detalle y Resolución -->
                            <div class="modal fade" id="modalDetalle<?= $s['id_solicitud'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 rounded-4 shadow">
                                        <div class="modal-header bg-light border-0 py-3">
                                            <h5 class="modal-title fw-bold text-dark">Solicitud #<?= $s['id_solicitud'] ?></h5>
                                            <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <h6 class="fw-bold text-success mb-1"><?= htmlspecialchars($s['nombre_establecimiento']) ?></h6>
                                            <p class="text-muted extra-small mb-3">Categoría: <?= htmlspecialchars($s['categoria']) ?></p>

                                            <p class="small text-secondary mb-3" style="white-space: pre-line;"><?= htmlspecialchars($s['descripcion']) ?></p>

                                            <div class="p-3 bg-light rounded-3 small mb-3">
                                                <div class="mb-1"><strong>Dirección:</strong> <?= htmlspecialchars($s['direccion']) ?></div>
                                                <div class="mb-1"><strong>Horarios:</strong> <?= htmlspecialchars($s['horarios'] ?: 'No especificado') ?></div>
                                                <div class="mb-1"><strong>Contacto:</strong> <?= htmlspecialchars($s['nombre_solicitante']) ?></div>
                                                <div><strong>Teléfono:</strong> <?= htmlspecialchars($s['telefono_contacto']) ?></div>
                                            </div>

                                            <!-- Formulario de Cambio de Estado -->
                                            <form action="<?= htmlspecialchars($baseUrl) ?>/admin/solicitudes/resolver" method="POST">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                                <input type="hidden" name="id_solicitud" value="<?= $s['id_solicitud'] ?>">

                                                <div class="mb-3">
                                                    <label class="form-label fw-bold small">Decisión Administrativa:</label>
                                                    <select name="estado" class="form-select form-select-sm">
                                                        <option value="ACEPTADA" <?= $s['estado'] === 'ACEPTADA' ? 'selected' : '' ?>>Aceptar Solicitud</option>
                                                        <option value="RECHAZADA" <?= $s['estado'] === 'RECHAZADA' ? 'selected' : '' ?>>Rechazar Solicitud</option>
                                                    </select>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label fw-bold small">Observaciones Internas:</label>
                                                    <textarea name="observaciones_admin" rows="2" class="form-control form-control-sm" placeholder="Anotaciones de la llamada o motivos..."><?= htmlspecialchars($s['observaciones_admin'] ?? '') ?></textarea>
                                                </div>

                                                <button type="submit" class="btn btn-dark w-100 rounded-pill btn-sm fw-bold">
                                                    Guardar Resolución
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-6 d-block mb-2"></i>
                                No hay solicitudes registradas con el filtro seleccionado.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>