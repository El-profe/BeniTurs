<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold text-dark mb-0">Promociones y Ofertas</h4>
            <p class="text-muted small mb-0">Tus promociones aparecerán destacadas en tu ficha pública</p>
        </div>
        <button class="btn btn-warning rounded-pill px-4 fw-bold text-dark" data-bs-toggle="modal" data-bs-target="#modalNuevaPromo">
            <i class="bi bi-plus-lg me-1"></i> Nueva Promoción
        </button>
    </div>

    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-success rounded-4 small mb-4"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger rounded-4 small mb-4"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="row g-3">
        <?php if (!empty($promociones)): ?>
            <?php foreach ($promociones as $p): ?>
                <div class="col-md-6 col-xl-4">
                    <div class="admin-card p-4 h-100 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge bg-warning text-dark fw-bold rounded-pill px-3 py-1">
                                <?= htmlspecialchars($p['descuento_texto'] ?: 'Oferta Especial') ?>
                            </span>
                            <span class="badge <?= $p['activo'] ? 'bg-success' : 'bg-secondary' ?> rounded-pill">
                                <?= $p['activo'] ? 'Vigente' : 'Pausada' ?>
                            </span>
                        </div>

                        <h5 class="fw-bold text-dark mb-2"><?= htmlspecialchars($p['titulo']) ?></h5>
                        <p class="text-muted small flex-grow-1"><?= htmlspecialchars($p['descripcion']) ?></p>

                        <div class="border-top pt-2 mt-3 small text-muted d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-calendar3 me-1"></i> Hasta: <?= date('d/m/Y', strtotime($p['fecha_fin'])) ?></span>
                            
                            <form action="<?= htmlspecialchars($baseUrl) ?>/negocio/promociones/estado" method="POST" class="m-0">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                <input type="hidden" name="id_promocion" value="<?= $p['id_promocion'] ?>">
                                <input type="hidden" name="activo" value="<?= $p['activo'] ? '0' : '1' ?>">
                                <button type="submit" class="btn btn-sm btn-link text-decoration-none p-0 <?= $p['activo'] ? 'text-danger' : 'text-success' ?>">
                                    <?= $p['activo'] ? 'Pausar' : 'Activar' ?>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5 text-muted">
                <i class="bi bi-tag display-5 d-block mb-2"></i>
                No has publicado ninguna promoción todavía. Haz clic en "Nueva Promoción".
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Nueva Promoción -->
<div class="modal fade" id="modalNuevaPromo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 bg-light py-3">
                <h5 class="modal-title fw-bold text-dark">Lanzar Promoción</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= htmlspecialchars($baseUrl) ?>/negocio/promociones/guardar" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Título de la Promoción *</label>
                        <input type="text" name="titulo" class="form-control" placeholder="Ej. Jueves de Pacú 2x1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Texto de Descuento o Gancho</label>
                        <input type="text" name="descuento_texto" class="form-control" placeholder="Ej. 20% OFF, 2x1 o Menú Ejecutivo">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Fecha Inicio *</label>
                            <input type="date" name="fecha_inicio" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Fecha Fin *</label>
                            <input type="date" name="fecha_fin" class="form-control" value="<?= date('Y-m-d', strtotime('+15 days')) ?>" required>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold small">Descripción o Condiciones *</label>
                        <textarea name="descripcion" rows="3" class="form-control" placeholder="Ej. Válido solo en consumo en salón de 19:00 a 22:00..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold text-dark w-100">
                        Publicar Promoción
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>