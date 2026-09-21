<div class="container py-2">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold text-dark mb-0">Registrar Nueva Ficha</h4>
                    <a href="<?= htmlspecialchars($baseUrl) ?>/admin/lugares" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                        <i class="bi bi-arrow-left me-1"></i> Volver
                    </a>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger rounded-3 small mb-4">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <!-- Formulario con enctype para envío de archivos -->
                <form action="<?= htmlspecialchars($baseUrl) ?>/admin/lugares/guardar" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold small">Nombre del Lugar / Establecimiento *</label>
                            <input type="text" name="nombre" class="form-control" placeholder="Ej. Museo Ictícola del Beni" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Tipo de Ficha *</label>
                            <select name="tipo_lugar" class="form-select" required>
                                <option value="PUBLICO">Público (Gratuito)</option>
                                <option value="COMERCIAL">Comercial (De Pago)</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Categoría *</label>
                            <select name="id_categoria" class="form-select" required>
                                <option value="">Seleccione una categoría</option>
                                <?php foreach ($categorias as $c): ?>
                                    <option value="<?= $c['id_categoria'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Horario de Atención</label>
                            <input type="text" name="horario_atencion" class="form-control" placeholder="Ej. Lunes a Viernes de 08:00 a 18:00">
                        </div>

                        <!-- Campo para subir Fotografía de Referencia -->
                        <div class="col-12">
                            <label class="form-label fw-semibold small text-success"><i class="bi bi-image me-1"></i> Fotografía Principal de Referencia</label>
                            <input type="file" name="fotografia" class="form-control" accept="image/jpeg,image/png,image/webp">
                            <small class="text-muted extra-small">Admite JPG, PNG y WEBP (Máx. 5 MB). La imagen se mostrará en el catálogo y en la ficha de detalle.</small>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small">Descripción Completa *</label>
                            <textarea name="descripcion" rows="4" class="form-control" placeholder="Información atractiva, qué ofrece, historia o platos representativos..." required></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Dirección Exacta *</label>
                            <input type="text" name="direccion" class="form-control" placeholder="Ej. Campus Universitario UAB, Av. Dr. Antonio Vaca Díez" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Referencia de Ubicación</label>
                            <input type="text" name="referencia_ubicacion" class="form-control" placeholder="Ej. Ingreso principal del campus universitario">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">WhatsApp (para turistas)</label>
                            <input type="text" name="whatsapp_contacto" class="form-control" placeholder="Ej. 59170000000">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Teléfono Fijo / Celular</label>
                            <input type="text" name="telefono_contacto" class="form-control" placeholder="Ej. 34620000">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Email de Contacto</label>
                            <input type="email" name="email_contacto" class="form-control" placeholder="contacto@lugar.bo">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small">Coordenadas GPS (Opcional)</label>
                            <input type="text" name="coordenadas_gps" class="form-control" placeholder="Ej. -14.8333, -64.9000">
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top text-end">
                        <button type="submit" class="btn btn-success rounded-pill px-5 fw-bold shadow-sm">
                            <i class="bi bi-save me-1"></i> Guardar Ficha con Imagen
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>