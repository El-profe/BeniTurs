<div class="container py-4 my-2">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= htmlspecialchars($baseUrl) ?>/" class="text-success text-decoration-none"><i class="bi bi-house-door"></i> Inicio</a></li>
            <li class="breadcrumb-item"><a href="<?= htmlspecialchars($baseUrl) ?>/" class="text-success text-decoration-none">Catálogo</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($lugar['nombre']) ?></li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Columna Izquierda: Información y Fotografía de Referencia -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
                
                <!-- Imagen de Portada Principal -->
                <?php if (!empty($lugar['imagen'])): ?>
                    <div class="position-relative" style="height: 380px; width: 100%; background-color: #092611;">
                        <img src="<?= htmlspecialchars($baseUrl) ?>/imagen?f=<?= urlencode($lugar['imagen']) ?>" 
                             alt="<?= htmlspecialchars($lugar['nombre']) ?>" 
                             class="w-100 h-100 object-fit-cover">
                    </div>
                <?php else: ?>
                    <!-- Imagen de respaldo si aún no tiene foto subida -->
                    <div class="p-5 text-center hero-beni text-white">
                        <div class="place-icon-bubble mx-auto mb-3 shadow">
                            <i class="bi <?= htmlspecialchars($lugar['categoria_icono'] ?? 'bi-geo-alt') ?> fs-1 text-success"></i>
                        </div>
                        <h4 class="fw-bold mb-0 text-white"><?= htmlspecialchars($lugar['nombre']) ?></h4>
                        <small class="text-white-50">Fotografía no disponible temporalmente</small>
                    </div>
                <?php endif; ?>

                <div class="p-4 p-md-5">
                    <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                        <span class="badge bg-light text-success border border-success-subtle rounded-pill px-3 py-2">
                            <i class="bi <?= htmlspecialchars($lugar['categoria_icono']) ?> me-1"></i> <?= htmlspecialchars($lugar['categoria']) ?>
                        </span>
                        <span class="badge <?= $lugar['tipo_lugar'] === 'PUBLICO' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-primary-subtle text-primary border border-primary-subtle' ?> rounded-pill px-3 py-2">
                            <?= $lugar['tipo_lugar'] === 'PUBLICO' ? 'Atractivo Turístico Gratuito' : 'Establecimiento Comercial' ?>
                        </span>
                    </div>

                    <h1 class="display-6 fw-bold text-dark mb-4"><?= htmlspecialchars($lugar['nombre']) ?></h1>
                    

                    <?php if (!empty($promociones)): ?>
                        <div class="mb-4">
                            <h5 class="fw-bold text-dark mb-3"><i class="bi bi-tag-fill text-warning me-2"></i>Promociones Activas</h5>
                            <div class="row g-2">
                                <?php foreach ($promociones as $pr): ?>
                                    <div class="col-12">
                                        <div class="p-3 rounded-3 border bg-warning-subtle border-warning-subtle d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong class="text-dark d-block"><?= htmlspecialchars($pr['titulo']) ?></strong>
                                                <small class="text-muted"><?= htmlspecialchars($pr['descripcion']) ?></small>
                                            </div>
                                            <span class="badge bg-warning text-dark fw-bold rounded-pill px-3 py-2">
                                                <?= htmlspecialchars($pr['descuento_texto'] ?: 'Oferta') ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>








                    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-file-text me-2 text-success"></i>Descripción</h5>
                    <p class="text-secondary leading-relaxed mb-4 fs-6" style="white-space: pre-line;">
                        <?= htmlspecialchars($lugar['descripcion']) ?>
                    </p>

                    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-geo-alt me-2 text-danger"></i>Ubicación y Referencias</h5>
                    <p class="mb-1 text-dark fw-semibold"><?= htmlspecialchars($lugar['direccion']) ?></p>
                    <?php if (!empty($lugar['referencia_ubicacion'])): ?>
                        <p class="text-muted small mb-3"><i class="bi bi-info-circle me-1"></i> Referencia: <?= htmlspecialchars($lugar['referencia_ubicacion']) ?></p>
                    <?php endif; ?>

                    <?php if (!empty($mapa)): ?>
                        <div class="place-map rounded-4 overflow-hidden border mt-3 mb-3">
                            <iframe
                                src="<?= htmlspecialchars($mapa['url'], ENT_QUOTES, 'UTF-8') ?>"
                                title="Mapa de ubicación de <?= htmlspecialchars($lugar['nombre'], ENT_QUOTES, 'UTF-8') ?>"
                                width="100%" height="340" loading="lazy"
                                referrerpolicy="strict-origin-when-cross-origin"
                                allowfullscreen></iframe>
                        </div>
                    <?php else: ?>
                        <p class="text-muted small mt-3 mb-2">
                            <i class="bi bi-pin-map me-1" aria-hidden="true"></i>
                            La ubicación exacta en el mapa todavía no está disponible.
                        </p>
                    <?php endif; ?>

                    <!-- Botón Google Maps -->
                    <?php 
                        $queryMaps = urlencode($lugar['nombre'] . ', Trinidad, Beni, Bolivia');
                        if (!empty($mapa)) {
                            $queryMaps = urlencode($mapa['coordenadas']);
                        }
                    ?>
                    <a href="https://www.google.com/maps/search/?api=1&query=<?= $queryMaps ?>" 
                       target="_blank" rel="noopener noreferrer" 
                       class="btn btn-outline-success rounded-pill px-4 mt-2">
                        <i class="bi bi-map-fill me-2"></i> Abrir en Google Maps / Cómo llegar
                    </a>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Canales de Contacto y Horarios -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white sticky-top" style="top: 90px;">
                <h5 class="fw-bold text-dark mb-3 border-bottom pb-2">Información del Lugar</h5>

                <div class="mb-3">
                    <span class="text-muted small d-block">Horarios de Atención:</span>
                    <strong class="text-dark">
                        <i class="bi bi-clock me-1 text-warning"></i> <?= htmlspecialchars($lugar['horario_atencion'] ?: 'Sin horario específico / Abierto al público') ?>
                    </strong>
                </div>

                <?php if (!empty($lugar['whatsapp_contacto'])): ?>
                    <?php 
                        $waNum = preg_replace('/[^0-9]/', '', $lugar['whatsapp_contacto']);
                        $waUrl = "https://wa.me/{$waNum}?text=" . urlencode("Hola, vi su publicación en Trinidad Turismo y deseo consultar información.");
                    ?>
                    <div class="mb-3">
                        <a href="<?= $waUrl ?>" target="_blank" rel="noopener noreferrer" class="btn btn-success w-100 py-2 rounded-pill fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-whatsapp fs-5"></i> Contactar por WhatsApp
                        </a>
                    </div>
                <?php endif; ?>

                <?php if (!empty($lugar['telefono_contacto'])): ?>
                    <div class="mb-2 text-muted small">
                        <i class="bi bi-telephone-fill me-1 text-primary"></i> Teléfono: <strong><?= htmlspecialchars($lugar['telefono_contacto']) ?></strong>
                    </div>
                <?php endif; ?>

                <?php if (!empty($lugar['email_contacto'])): ?>
                    <div class="mb-2 text-muted small text-truncate">
                        <i class="bi bi-envelope-fill me-1 text-danger"></i> Email: <a href="mailto:<?= htmlspecialchars($lugar['email_contacto']) ?>"><?= htmlspecialchars($lugar['email_contacto']) ?></a>
                    </div>
                <?php endif; ?>

                <div class="mt-4 pt-3 border-top text-center">
                    <a href="<?= htmlspecialchars($baseUrl) ?>/" class="btn btn-light btn-sm w-100 rounded-pill text-muted">
                        <i class="bi bi-arrow-left me-1"></i> Volver al Directorio
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
