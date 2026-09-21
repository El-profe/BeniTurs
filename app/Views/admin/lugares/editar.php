<div class="container py-2">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="fw-bold text-dark mb-0">Editar Ficha</h4>
                        <small class="text-muted"><?= htmlspecialchars($lugar['nombre']) ?></small>
                    </div>
                    <a href="<?= htmlspecialchars($baseUrl) ?>/admin/lugares" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                        <i class="bi bi-arrow-left me-1"></i> Volver
                    </a>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger rounded-3 small mb-4">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form action="<?= htmlspecialchars($baseUrl) ?>/admin/lugares/actualizar" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <input type="hidden" name="id_lugar" value="<?= $lugar['id_lugar'] ?>">

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold small">Nombre del Lugar / Establecimiento *</label>
                            <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($lugar['nombre']) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Tipo de Ficha *</label>
                            <select name="tipo_lugar" class="form-select" required>
                                <option value="PUBLICO" <?= $lugar['tipo_lugar'] === 'PUBLICO' ? 'selected' : '' ?>>Público (Gratuito)</option>
                                <option value="COMERCIAL" <?= $lugar['tipo_lugar'] === 'COMERCIAL' ? 'selected' : '' ?>>Comercial (De Pago)</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Categoría *</label>
                            <select name="id_categoria" class="form-select" required>
                                <?php foreach ($categorias as $c): ?>
                                    <option value="<?= $c['id_categoria'] ?>" <?= $lugar['id_categoria'] == $c['id_categoria'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($c['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Horario de Atención</label>
                            <input type="text" name="horario_atencion" class="form-control" value="<?= htmlspecialchars($lugar['horario_atencion'] ?? '') ?>">
                        </div>

                        <!-- Previsualización y Actualización de Fotografía -->
                        <div class="col-12">
                            <label class="form-label fw-semibold small text-success"><i class="bi bi-image me-1"></i> Fotografía Principal</label>
                            <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-3 border mb-2">
                                <?php if (!empty($lugar['imagen'])): ?>
                                    <img src="<?= htmlspecialchars($baseUrl) ?>/imagen?f=<?= urlencode($lugar['imagen']) ?>" 
                                         alt="Foto actual" class="rounded-3 border object-fit-cover" width="90" height="70">
                                    <div>
                                        <span class="small fw-bold d-block text-dark">Foto cargada en el servidor</span>
                                        <small class="text-muted extra-small">Selecciona otro archivo abajo si deseas reemplazarla.</small>
                                    </div>
                                <?php else: ?>
                                    <div class="icon-circle bg-secondary-subtle text-secondary" style="width: 50px; height: 50px;">
                                        <i class="bi bi-image"></i>
                                    </div>
                                    <small class="text-muted">Esta ficha aún no cuenta con una fotografía de referencia.</small>
                                <?php endif; ?>
                            </div>
                            <input type="file" name="fotografia" class="form-control" accept="image/jpeg,image/png,image/webp">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small">Descripción Completa *</label>
                            <textarea name="descripcion" rows="4" class="form-control" required><?= htmlspecialchars($lugar['descripcion']) ?></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Dirección Exacta *</label>
                            <input type="text" name="direccion" class="form-control" value="<?= htmlspecialchars($lugar['direccion']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Referencia de Ubicación</label>
                            <input type="text" name="referencia_ubicacion" class="form-control" value="<?= htmlspecialchars($lugar['referencia_ubicacion'] ?? '') ?>">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">WhatsApp</label>
                            <input type="text" name="whatsapp_contacto" class="form-control" value="<?= htmlspecialchars($lugar['whatsapp_contacto'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Teléfono</label>
                            <input type="text" name="telefono_contacto" class="form-control" value="<?= htmlspecialchars($lugar['telefono_contacto'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Email</label>
                            <input type="email" name="email_contacto" class="form-control" value="<?= htmlspecialchars($lugar['email_contacto'] ?? '') ?>">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small">Coordenadas GPS</label>
                            <input type="text" name="coordenadas_gps" class="form-control" value="<?= htmlspecialchars($lugar['coordenadas_gps'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top text-end">
                        <button type="submit" class="btn btn-success rounded-pill px-5 fw-bold shadow-sm">
                            <i class="bi bi-arrow-repeat me-1"></i> Actualizar Ficha y Foto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>