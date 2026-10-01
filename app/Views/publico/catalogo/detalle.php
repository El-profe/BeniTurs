<?php
    $fotos = $fotografias ?? [];
    if (empty($fotos) && !empty($lugar['imagen'])) {
        $fotos = [['nombre_archivo' => $lugar['imagen']]];
    }
    $variasFotos = count($fotos) > 1;
    $queryMaps = urlencode(!empty($mapa) ? $mapa['coordenadas'] : $lugar['nombre'] . ', Trinidad, Beni, Bolivia');
?>
<div class="container py-4 my-2">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= htmlspecialchars($baseUrl) ?>/" class="text-success text-decoration-none"><i class="bi bi-house-door"></i> Inicio</a></li>
            <li class="breadcrumb-item"><a href="<?= htmlspecialchars($baseUrl) ?>/#explorar" class="text-success text-decoration-none">Catálogo</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($lugar['nombre']) ?></li>
        </ol>
    </nav>

    <div class="row g-4 align-items-start">
        <!-- Fotografías y ubicación -->
        <div class="col-lg-7 place-detail-media-column">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
                <?php if (!empty($fotos)): ?>
                    <div id="carruselLugar" class="carousel slide" data-bs-ride="carousel" data-bs-interval="2500" role="region" aria-roledescription="carrusel" aria-label="Fotografías de <?= htmlspecialchars($lugar['nombre']) ?>">
                        <?php if ($variasFotos): ?>
                            <div class="carousel-indicators">
                                <?php foreach ($fotos as $indice => $foto): ?>
                                    <button type="button" data-bs-target="#carruselLugar" data-bs-slide-to="<?= $indice ?>"<?= $indice === 0 ? ' class="active" aria-current="true"' : '' ?> aria-label="Fotografía <?= $indice + 1 ?>"></button>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <div class="carousel-inner">
                            <?php foreach ($fotos as $indice => $foto): ?>
                                <div class="carousel-item<?= $indice === 0 ? ' active' : '' ?>">
                                    <img src="<?= htmlspecialchars($baseUrl) ?>/imagen?f=<?= urlencode($foto['nombre_archivo']) ?>"
                                         alt="<?= htmlspecialchars($lugar['nombre']) ?> — fotografía <?= $indice + 1 ?> de <?= count($fotos) ?>"
                                         class="d-block w-100 object-fit-cover place-detail-photo"
                                         <?= $indice === 0 ? 'loading="eager" fetchpriority="high"' : 'loading="lazy" decoding="async"' ?>>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if ($variasFotos): ?>
                            <button class="carousel-control-prev" type="button" data-bs-target="#carruselLugar" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon bg-dark rounded-circle p-3" aria-hidden="true"></span>
                                <span class="visually-hidden">Fotografía anterior</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#carruselLugar" data-bs-slide="next">
                                <span class="carousel-control-next-icon bg-dark rounded-circle p-3" aria-hidden="true"></span>
                                <span class="visually-hidden">Fotografía siguiente</span>
                            </button>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="p-5 text-center hero-beni text-white">
                        <div class="place-icon-bubble mx-auto mb-3 shadow">
                            <i class="bi <?= htmlspecialchars($lugar['categoria_icono'] ?? 'bi-geo-alt') ?> fs-1 text-success" aria-hidden="true"></i>
                        </div>
                        <p class="mb-0">Fotografías no disponibles por el momento</p>
                    </div>
                <?php endif; ?>
            </div>

            <section class="card border-0 shadow-sm rounded-4 p-4 bg-white" aria-labelledby="tituloUbicacion">
                <h2 id="tituloUbicacion" class="h5 fw-bold text-dark mb-3"><i class="bi bi-geo-alt me-2 text-danger" aria-hidden="true"></i>Ubicación</h2>
                <p class="mb-1 text-dark fw-semibold"><?= htmlspecialchars($lugar['direccion'] ?? '') ?></p>
                <?php if (!empty($lugar['referencia_ubicacion'])): ?>
                    <p class="text-muted small mb-3"><i class="bi bi-info-circle me-1" aria-hidden="true"></i> Referencia: <?= htmlspecialchars($lugar['referencia_ubicacion']) ?></p>
                <?php endif; ?>
                <?php if (!empty($mapa)): ?>
                    <div class="place-map rounded-4 overflow-hidden border mt-3 mb-3">
                        <iframe src="<?= htmlspecialchars($mapa['url'], ENT_QUOTES, 'UTF-8') ?>"
                                title="Mapa de ubicación de <?= htmlspecialchars($lugar['nombre'], ENT_QUOTES, 'UTF-8') ?>"
                                width="100%" height="300" loading="lazy"
                                referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                    </div>
                <?php else: ?>
                    <p class="text-muted small mt-3 mb-2">La ubicación exacta en el mapa todavía no está disponible.</p>
                <?php endif; ?>
                <a href="https://www.google.com/maps/search/?api=1&amp;query=<?= $queryMaps ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-success rounded-pill px-4 mt-2 align-self-start">
                    <i class="bi bi-map-fill me-2" aria-hidden="true"></i> Cómo llegar
                </a>
            </section>
        </div>

        <!-- Información del lugar -->
        <div class="col-lg-5 place-detail-info-column">
            <section class="card border-0 shadow-sm rounded-4 p-4 p-xl-5 bg-white place-detail-info" aria-labelledby="nombreLugar">
                <h1 id="nombreLugar" class="h2 fw-bold text-dark mb-3"><?= htmlspecialchars($lugar['nombre']) ?></h1>
                <div class="mb-4">
                    <span class="badge bg-light text-success border border-success-subtle rounded-pill px-3 py-2">
                        <i class="bi <?= htmlspecialchars($lugar['categoria_icono'] ?? 'bi-geo-alt') ?> me-1" aria-hidden="true"></i> <?= htmlspecialchars($lugar['categoria'] ?? '') ?>
                    </span>
                </div>
                <h2 class="h5 fw-bold text-dark mb-3">Descripción</h2>
                <p class="text-secondary mb-4" style="white-space: pre-line;"><?= htmlspecialchars($lugar['descripcion'] ?? '') ?></p>
                <div class="border-top pt-4 mb-4">
                    <h2 class="h5 fw-bold text-dark mb-2"><i class="bi bi-clock me-2 text-warning" aria-hidden="true"></i>Horarios de atención</h2>
                    <p class="text-secondary mb-0" style="white-space: pre-line;"><?= htmlspecialchars(($lugar['horario_atencion'] ?? '') ?: 'Sin horario específico / Abierto al público') ?></p>
                </div>
                <?php
                    $whatsappRaw = preg_replace('/\D/', '', $lugar['whatsapp_contacto'] ?? '');
                    if (strlen($whatsappRaw) === 8) {
                        $whatsappRaw = '591' . $whatsappRaw;
                    }
                    $mensajeWa = rawurlencode("Hola, vi la información de " . $lugar['nombre'] . " en BeniTurs y quisiera hacer una consulta.");
                    $telefonoRaw = preg_replace('/[^\d+]/', '', $lugar['telefono_contacto'] ?? '');
                ?>
                <div class="border-top pt-4 mb-4">
                    <h2 class="h5 fw-bold text-dark mb-3"><i class="bi bi-chat-dots me-2 text-success" aria-hidden="true"></i>Contacto Directo</h2>
                    <div class="d-grid gap-2">
                        <?php if (!empty($whatsappRaw)): ?>
                            <a href="https://wa.me/<?= $whatsappRaw ?>?text=<?= $mensajeWa ?>" target="_blank" rel="noopener noreferrer" class="btn btn-success rounded-pill py-2 fw-semibold shadow-sm d-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-whatsapp fs-5"></i> Contactar por WhatsApp
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($telefonoRaw)): ?>
                            <a href="tel:<?= $telefonoRaw ?>" class="btn btn-outline-dark rounded-pill py-2 fw-semibold d-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-telephone-fill"></i> Llamar: <?= htmlspecialchars($lugar['telefono_contacto']) ?>
                            </a>
                        <?php endif; ?>
                        <?php if (empty($whatsappRaw) && empty($telefonoRaw)): ?>
                            <p class="text-muted small mb-0"><i class="bi bi-info-circle me-1"></i> Puedes visitar este lugar directamente en la dirección indicada.</p>
                        <?php endif; ?>
                        <button type="button" class="btn btn-light rounded-pill py-2 text-secondary fw-semibold border d-flex align-items-center justify-content-center gap-2 mt-1" id="btnCompartirLugar">
                            <i class="bi bi-share-fill text-primary"></i> Compartir este lugar
                        </button>
                    </div>
                </div>

                <?php if (!empty($promociones)): ?>
                    <div class="border-top pt-4 mb-4">
                        <h2 class="h5 fw-bold text-dark mb-3"><i class="bi bi-tag-fill text-warning me-2" aria-hidden="true"></i>Promociones activas</h2>
                        <?php foreach ($promociones as $pr): ?>
                            <div class="p-3 rounded-3 border bg-warning-subtle border-warning-subtle mb-2">
                                <strong class="text-dark d-block"><?= htmlspecialchars($pr['titulo']) ?></strong>
                                <p class="text-secondary small mb-2"><?= htmlspecialchars($pr['descripcion']) ?></p>
                                <span class="badge bg-warning text-dark rounded-pill px-3 py-2"><?= htmlspecialchars($pr['descuento_texto'] ?: 'Oferta') ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <a href="<?= htmlspecialchars($baseUrl) ?>/#explorar" class="btn btn-light btn-sm w-100 rounded-pill text-muted">
                    <i class="bi bi-arrow-left me-1" aria-hidden="true"></i> Volver al Directorio
                </a>
            </section>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const btnCompartir = document.getElementById('btnCompartirLugar');
    if (btnCompartir) {
        btnCompartir.addEventListener('click', async () => {
            const titulo = <?= json_encode($lugar['nombre'], JSON_UNESCAPED_UNICODE) ?>;
            const url = window.location.href;
            if (navigator.share) {
                try {
                    await navigator.share({
                        title: titulo + ' | BeniTurs',
                        text: '¡Conoce ' + titulo + ' en Trinidad, Beni a través de BeniTurs!',
                        url: url
                    });
                } catch (e) { /* Cancelado por el usuario */ }
            } else {
                navigator.clipboard.writeText(url).then(() => {
                    const original = btnCompartir.innerHTML;
                    btnCompartir.innerHTML = '<i class="bi bi-check-lg text-success"></i> ¡Enlace copiado!';
                    setTimeout(() => {
                        btnCompartir.innerHTML = original;
                    }, 2500);
                });
            }
        });
    }
});
</script>
