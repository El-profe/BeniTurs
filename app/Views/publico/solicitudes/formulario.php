<div class="container py-5 my-2">
    <div class="row justify-content-center">
        <div class="col-xl-9 col-lg-10">
            
            <!-- Encabezado -->
            <div class="text-center mb-5">
                <span class="badge bg-warning text-dark fw-bold px-3 py-1 rounded-pill text-uppercase mb-2 shadow-sm" style="font-size: 0.75rem;">
                    <i class="bi bi-shop me-1"></i> Publicación Comercial
                </span>
                <h2 class="display-6 fw-bold text-dark mb-2">Haz que tu Negocio destaque en Trinidad</h2>
                <p class="text-muted mx-auto" style="max-width: 600px;">
                    Elige tu plan, selecciona tu categoría con un solo clic y te ayudamos a incorporar tu establecimiento.
                </p>
            </div>

            <div id="solicitudAlertContainer" aria-live="polite" tabindex="-1"></div>

            <form id="formSolicitudComercial" action="<?= htmlspecialchars($baseUrl) ?>/api/solicitudes/enviar" method="post">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <!-- Campo oculto que guarda la categoría elegida por los iconos -->
                <input type="hidden" name="id_categoria" id="id_categoria_seleccionada" value="" required>

                <!-- 1. SELECCIÓN VISUAL DE PLANES -->
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                    <h5 class="fw-bold text-dark mb-3">
                        <span class="badge bg-success-subtle text-success rounded-circle me-1">1</span> 
                        Selecciona tu Plan Comercial
                    </h5>
                    
                    <div class="row g-3">
                        <!-- Tarjeta Plan Mensual -->
                        <div class="col-md-6">
                            <label class="plan-card w-100 p-4 rounded-4 border position-relative cursor-pointer">
                                <input type="radio" name="plan_solicitado" value="MENSUAL" class="form-check-input plan-radio d-none" checked>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-light text-dark border rounded-pill px-3 py-1 fw-bold small">Flexibilidad</span>
                                    <i class="bi bi-calendar-check fs-4 text-success"></i>
                                </div>
                                <h4 class="fw-bold text-dark mb-1">Plan Mensual</h4>
                                <div class="d-flex align-items-baseline gap-1 my-2">
                                    <span class="display-6 fw-bold text-dark">Bs <?= number_format($tarifaMensual, 0) ?></span>
                                    <span class="text-muted small">/ mes</span>
                                </div>
                                <p class="text-muted extra-small mb-0">Facturación mes a mes. Cancela o renueva cuando gustes.</p>
                            </label>
                        </div>

                        <!-- Tarjeta Plan Anual (Destacado) -->
                        <div class="col-md-6">
                            <label class="plan-card w-100 p-4 rounded-4 border position-relative cursor-pointer featured-plan">
                                <span class="badge bg-warning text-dark position-absolute top-0 end-0 m-3 rounded-pill fw-bold" style="font-size: 0.7rem;">
                                    <i class="bi bi-stars"></i> Ahorras Bs 500
                                </span>
                                <input type="radio" name="plan_solicitado" value="ANUAL" class="form-check-input plan-radio d-none">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-success text-white rounded-pill px-3 py-1 fw-bold small">Recomendado</span>
                                </div>
                                <h4 class="fw-bold text-dark mb-1">Plan Anual</h4>
                                <div class="d-flex align-items-baseline gap-1 my-2">
                                    <span class="display-6 fw-bold text-success">Bs <?= number_format($tarifaAnual, 0) ?></span>
                                    <span class="text-muted small">/ año</span>
                                </div>
                                <p class="text-muted extra-small mb-0"><strong>2 meses gratis</strong> (Pagas 10 meses y recibes 12). Máxima visibilidad todo el año.</p>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- 2. SELECCIÓN VISUAL DE CATEGORÍA (ICONOS DE UN CLIC) -->
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                    <h5 class="fw-bold text-dark mb-3">
                        <span class="badge bg-success-subtle text-success rounded-circle me-1">2</span> 
                        ¿Cuál es el rubro de tu negocio? *
                    </h5>
                    
                    <div class="row g-2" id="gridCategoriasSelector">
                        <?php foreach ($categorias as $cat): ?>
                            <div class="col-6 col-md-4 col-lg-3">
                                <button type="button" class="btn-categoria-tile w-100 p-3 text-center rounded-4 border" data-id="<?= $cat['id_categoria'] ?>">
                                    <div class="icon-bubble-category mx-auto mb-2">
                                        <i class="bi <?= htmlspecialchars($cat['icono']) ?> fs-3"></i>
                                    </div>
                                    <span class="small fw-bold text-dark d-block text-truncate"><?= htmlspecialchars($cat['nombre']) ?></span>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <small id="categoriaErrorHelp" class="text-danger extra-small mt-2 d-none"><i class="bi bi-exclamation-circle me-1"></i>Debes seleccionar un rubro para continuar.</small>
                </div>

                <!-- 3. DATOS BÁSICOS RÁPIDOS -->
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white mb-4">
                    <h5 class="fw-bold text-dark mb-4">
                        <span class="badge bg-success-subtle text-success rounded-circle me-1">3</span> 
                        Información del Establecimiento
                    </h5>

                    <div class="row g-3">
                        <div class="col-md-7">
                            <label class="form-label fw-semibold small">Nombre de tu Negocio *</label>
                            <input type="text" name="nombre_establecimiento" class="form-control form-control-lg fs-6" placeholder="Ej. Restaurante Puerto Viejo" required>
                        </div>

                        <div class="col-md-5">
                            <label class="form-label fw-semibold small">Tu Teléfono / WhatsApp *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-success fw-bold"><i class="bi bi-whatsapp"></i></span>
                                <input type="tel" name="telefono_contacto" class="form-control form-control-lg fs-6" placeholder="Ej. 73912345" required>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small">¿Dónde queda ubicado en Trinidad? *</label>
                            <input type="text" name="direccion" class="form-control" placeholder="Ej. Av. 6 de Agosto casi esquina Cipriano Barace" required>
                        </div>

                        <!-- Atajos de Horario (Un solo clic) -->
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Horario de Atención (Elige un atajo o escribe):</label>
                            <div class="d-flex flex-wrap gap-2 mb-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill btn-horario-quick" data-horario="Almuerzo y Cena (11:30 a 15:00 y 18:30 a 23:30)">
                                    <i class="bi bi-clock me-1"></i> Almuerzo y Cena
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill btn-horario-quick" data-horario="Horario Continuo (08:00 a 20:00)">
                                    <i class="bi bi-sun me-1"></i> Continuo (08:00 a 20:00)
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill btn-horario-quick" data-horario="Tarde y Noche / Bar (18:00 a 02:00)">
                                    <i class="bi bi-moon me-1"></i> Noche (18:00 a 02:00)
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill btn-horario-quick" data-horario="Atención 24 Horas">
                                    <i class="bi bi-infinity me-1"></i> 24 Horas
                                </button>
                            </div>
                            <input type="text" name="horarios" id="inputHorarios" class="form-control form-control-sm" placeholder="O escribe tu horario específico...">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small">Breve descripción de lo que ofreces *</label>
                            <textarea name="descripcion" rows="2" class="form-control" placeholder="Ej. Especialidad en pacú al horno, keoperí tradicional y jugos de frutas de la región..." required></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Tu Nombre o de la persona de contacto *</label>
                            <input type="text" name="nombre_solicitante" class="form-control" placeholder="Ej. Carla Méndez" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Correo Electrónico (Opcional)</label>
                            <input type="email" name="email_contacto" class="form-control" placeholder="contacto@minegocio.bo">
                        </div>
                    </div>
                </div>

                <div class="text-end mb-5">
                    <button type="submit" id="btnEnviarSolicitud" class="btn btn-warning btn-lg rounded-pill px-5 py-3 fw-bold text-dark shadow">
                        <i class="bi bi-send-check-fill me-1"></i> Solicitar Publicación
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<style>
/* Estilos interactivos del nuevo formulario */
.cursor-pointer { cursor: pointer; }

.plan-card {
    background-color: #ffffff;
    transition: all 0.25s ease;
}
.plan-card:hover {
    border-color: #198754 !important;
    background-color: #fcfdfc;
}
.plan-card.active-plan {
    border-color: #198754 !important;
    border-width: 2px !important;
    background-color: #f1f8f3;
    box-shadow: 0 8px 20px rgba(25, 135, 84, 0.12);
}

.featured-plan {
    border-color: rgba(255, 193, 7, 0.5) !important;
}

/* Botones mosaico de categorías */
.btn-categoria-tile {
    background-color: #ffffff;
    transition: all 0.2s ease;
    border-color: #e9ecef;
}
.btn-categoria-tile:hover {
    transform: translateY(-2px);
    border-color: #198754;
}
.btn-categoria-tile.active-category {
    background-color: #0d3b18;
    border-color: #0d3b18;
}
.btn-categoria-tile.active-category span {
    color: #ffffff !important;
}
.btn-categoria-tile.active-category .icon-bubble-category {
    background-color: rgba(255, 193, 7, 0.2);
    color: #ffc107;
}

.icon-bubble-category {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background-color: #e8f5e9;
    color: #198754;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
