<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Editar Perfil del Negocio</h1>
            <p class="text-muted small mb-0">Mantén actualizados tus datos de contacto, horarios y descripción para los turistas.</p>
        </div>
        <a href="<?= htmlspecialchars($baseUrl) ?>/negocio/dashboard" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> Volver al Panel
        </a>
    </div>

    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($mensaje) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
        <form action="<?= htmlspecialchars($baseUrl) ?>/negocio/perfil/guardar" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

            <!-- Datos no editables directamente por el usuario (Identidad) -->
            <div class="row g-3 mb-4 p-3 bg-light rounded-4 border">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-muted">Nombre del Establecimiento</label>
                    <input type="text" class="form-control bg-white" value="<?= htmlspecialchars($lugar['nombre']) ?>" disabled readonly>
                    <small class="text-muted extra-small">Para cambiar el nombre o razón social, contacta a la administración.</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-muted">Categoría</label>
                    <input type="text" class="form-control bg-white" value="<?= htmlspecialchars($categoria['nombre'] ?? 'Comercial') ?>" disabled readonly>
                    <small class="text-muted extra-small">Categoría asignada en tu suscripción.</small>
                </div>
            </div>

            <!-- Canales de Contacto Directo -->
            <h5 class="fw-bold text-dark mb-3"><i class="bi bi-chat-left-dots text-success me-2"></i>Canales de Contacto Directo</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="whatsapp_contacto" class="form-label small fw-semibold">Número de WhatsApp (Consultas y Reservas) *</label>
                    <div class="input-group">
                        <span class="input-group-text bg-success-subtle text-success border-end-0"><i class="bi bi-whatsapp"></i></span>
                        <input type="text" id="whatsapp_contacto" name="whatsapp_contacto" class="form-control" 
                               value="<?= htmlspecialchars($lugar['whatsapp_contacto'] ?? '') ?>" 
                               placeholder="Ej. 70000000 o +591 70000000" maxlength="30" required>
                    </div>
                    <small class="text-muted extra-small">Se usará en el botón directo de WhatsApp de tu ficha pública.</small>
                </div>

                <div class="col-md-6">
                    <label for="telefono_contacto" class="form-label small fw-semibold">Teléfono Fijo o Móvil de Llamadas</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-secondary border-end-0"><i class="bi bi-telephone-fill"></i></span>
                        <input type="text" id="telefono_contacto" name="telefono_contacto" class="form-control" 
                               value="<?= htmlspecialchars($lugar['telefono_contacto'] ?? '') ?>" 
                               placeholder="Ej. 34621234" maxlength="30">
                    </div>
                </div>

                <div class="col-md-6">
                    <label for="email_contacto" class="form-label small fw-semibold">Correo Electrónico Comercial</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-secondary border-end-0"><i class="bi bi-envelope-fill"></i></span>
                        <input type="email" id="email_contacto" name="email_contacto" class="form-control" 
                               value="<?= htmlspecialchars($lugar['email_contacto'] ?? '') ?>" 
                               placeholder="contacto@negocio.com" maxlength="120">
                    </div>
                </div>

                <div class="col-md-6">
                    <label for="horario_atencion" class="form-label small fw-semibold">Horarios de Atención al Público</label>
                    <div class="input-group">
                        <span class="input-group-text bg-warning-subtle text-warning border-end-0"><i class="bi bi-clock-fill"></i></span>
                        <input type="text" id="horario_atencion" name="horario_atencion" class="form-control" 
                               value="<?= htmlspecialchars($lugar['horario_atencion'] ?? '') ?>" 
                               placeholder="Ej. Lunes a Sábado: 08:00 a 22:00" maxlength="200">
                    </div>
                </div>
            </div>

            <!-- Ubicación y Descripción -->
            <h5 class="fw-bold text-dark mb-3"><i class="bi bi-card-text text-primary me-2"></i>Información del Establecimiento</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="direccion" class="form-label small fw-semibold">Dirección Física *</label>
                    <input type="text" id="direccion" name="direccion" class="form-control" 
                           value="<?= htmlspecialchars($lugar['direccion'] ?? '') ?>" 
                           placeholder="Ej. Calle Cipriano Barace entre La Paz y Sucre" maxlength="255" required>
                </div>

                <div class="col-md-6">
                    <label for="referencia_ubicacion" class="form-label small fw-semibold">Punto de Referencia</label>
                    <input type="text" id="referencia_ubicacion" name="referencia_ubicacion" class="form-control" 
                           value="<?= htmlspecialchars($lugar['referencia_ubicacion'] ?? '') ?>" 
                           placeholder="Ej. Frente a la plaza principal, lado del banco" maxlength="255">
                </div>

                <div class="col-12">
                    <label for="descripcion" class="form-label small fw-semibold">Descripción del Establecimiento y Servicios *</label>
                    <textarea id="descripcion" name="descripcion" rows="5" class="form-control" 
                              maxlength="2000" required><?= htmlspecialchars($lugar['descripcion'] ?? '') ?></textarea>
                    <small class="text-muted extra-small">Describe tus especialidades, menú, comodidades, formas de pago, etc. Máximo 2.000 caracteres.</small>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                <a href="<?= htmlspecialchars($baseUrl) ?>/negocio/dashboard" class="btn btn-light rounded-pill px-4">Cancelar</a>
                <button type="submit" class="btn btn-success rounded-pill px-5 fw-semibold shadow-sm">
                    <i class="bi bi-save2 me-1"></i> Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>
