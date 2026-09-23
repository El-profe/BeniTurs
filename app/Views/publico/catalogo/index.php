<!-- Hero Principal con Buscador Central -->
<section class="hero-trinidad text-white position-relative">
    <video class="hero-video" autoplay muted loop playsinline preload="metadata" aria-hidden="true">
        <source src="<?= htmlspecialchars($baseUrl) ?>/assets/video/video2.mp4" type="video/mp4">
    </video>
    <div class="hero-overlay" aria-hidden="true"></div>

    <div class="container hero-content py-5">
        <div class="row justify-content-center text-center">
            <div class="col-xl-9">
                <span class="badge bg-warning text-dark fw-bold px-3 py-2 rounded-pill text-uppercase mb-3 shadow-sm">
                    <i class="bi bi-sun-fill me-1"></i> La Ciudad de la Santísima Trinidad
                </span>
                <h1 class="display-4 fw-extrabold mb-3 text-white">
                    Descubre los mejores lugares de <span class="text-warning text-highlight">Trinidad</span>
                </h1>
                <p class="lead text-white-50 mb-4 mx-auto" style="max-width: 700px;">
                    Encuentra atractivos turísticos naturales, balnearios, los mejores pescados de río, keoperí, hoteles y vida nocturna en la capital del Beni.
                </p>

                <!-- Barra de Búsqueda Principal -->
                <div class="search-box-hero mx-auto shadow-lg bg-white p-2 rounded-pill mb-3">
                    <div class="d-flex align-items-center">
                        <span class="ps-3 text-muted"><i class="bi bi-search fs-5"></i></span>
                        <input type="text" id="inputBuscarHero" class="form-control border-0 shadow-none px-3 py-2 bg-transparent" 
                               placeholder="¿Qué estás buscando? (ej. Laguna Suárez, majadito, hotel, bar...)">
                        <button id="btnLimpiarFiltros" class="btn btn-light rounded-pill btn-sm me-2 text-muted" title="Limpiar búsqueda">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<!-- Catálogo con Tarjetas Fotográficas -->
<section class="container py-5" id="explorar">
    <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">Explorar por Categoría</h2>
            <p class="text-muted small mb-0">Selecciona para filtrar los atractivos y establecimientos al instante</p>
        </div>
        <div>
            <span id="contadorResultados" class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fw-semibold">
                <?= count($lugares) ?> lugares encontrados
            </span>
        </div>
    </div>

    <!-- Píldoras de Categoría (Chips) -->
    <div class="d-flex flex-wrap gap-2 mb-4 category-chips-container pb-2">
        <button class="chip-filter<?= $categoriaSeleccionada === null ? ' active' : '' ?>" data-categoria="" aria-label="Todos" title="Todos">
            <i class="bi bi-grid-fill" aria-hidden="true"></i>
        </button>
        <?php foreach ($categorias as $c): ?>
            <button class="chip-filter<?= $categoriaSeleccionada === (int)$c['id_categoria'] ? ' active' : '' ?>" data-categoria="<?= $c['id_categoria'] ?>" aria-label="<?= htmlspecialchars($c['nombre']) ?>" title="<?= htmlspecialchars($c['nombre']) ?>">
                <i class="bi <?= htmlspecialchars($c['icono']) ?>" aria-hidden="true"></i>
            </button>
        <?php endforeach; ?>
    </div>

    <!-- Barra de Búsqueda y Botón Cerca de Mí -->
    <div class="row align-items-center g-2 mb-4">
        <div class="col-md-7 col-lg-8">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                <input type="text" id="inputBuscarLugar" class="form-control border-start-0 ps-0" placeholder="Buscar por nombre, especialidad o dirección..." aria-label="Buscar lugares">
            </div>
        </div>
        <div class="col-md-5 col-lg-4 text-md-end">
            <!-- BOTÓN GEOLOCALIZACIÓN -->
            <button type="button" id="btnCercaDeMi" class="btn btn-outline-success w-100 rounded-pill fw-semibold shadow-sm d-inline-flex align-items-center justify-content-center gap-2">
                <i class="bi bi-geo-alt-fill"></i>
                <span id="btnCercaDeMiTexto">Lugares cerca de mí</span>
            </button>
        </div>
    </div>

    <!-- Alerta de estado de geolocalización (Oculta por defecto) -->
    <div id="alertaGeolocalizacion" class="alert alert-info alert-dismissible fade d-none rounded-4 small mb-4 border-0 shadow-sm" role="alert">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-compass-fill fs-5 text-primary"></i>
            <span id="alertaGeoTexto"></span>
        </div>
        <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>

    <!-- Contenedor compatible con los filtros actuales del catálogo -->
    <div id="contenedorLugares">
        <!-- Contenedor de la Cuadrícula de Tarjetas -->
        <div class="row g-4" id="gridLugaresCatalogo">
            <?php if (!empty($lugares)): ?>
                <?php foreach ($lugares as $l): ?>
                    <!-- Elemento de Tarjeta con data-coordenadas -->
                    <div class="col-md-6 col-lg-4 tarjeta-lugar-col item-lugar"
                         data-id="<?= (int)$l['id_lugar'] ?>"
                         data-nombre="<?= htmlspecialchars(mb_strtolower($l['nombre'], 'UTF-8')) ?>"
                         data-categoria="<?= (int)$l['id_categoria'] ?>"
                         data-coordenadas="<?= htmlspecialchars($l['coordenadas_gps'] ?? '') ?>">
                        <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden position-relative card-hover-turismo place-card">
                            <!-- Imagen de Portada -->
                            <div class="position-relative" style="height: 200px; background-color: #e9ecef;">
                                <?php $fotoPrincipal = $l['foto_principal'] ?? $l['imagen'] ?? ''; ?>
                                <?php if (!empty($fotoPrincipal)): ?>
                                    <img src="<?= htmlspecialchars($baseUrl) ?>/imagen?f=<?= urlencode($fotoPrincipal) ?>"
                                         class="w-100 h-100 object-fit-cover" alt="<?= htmlspecialchars($l['nombre']) ?>">
                                <?php else: ?>
                                    <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted">
                                        <i class="bi bi-image display-6"></i>
                                    </div>
                                <?php endif; ?>

                                <!-- Categoría Badge -->
                                <span class="badge bg-dark bg-opacity-75 text-white position-absolute top-0 start-0 m-3 rounded-pill small px-3 py-1">
                                    <i class="bi <?= htmlspecialchars($l['categoria_icono'] ?? 'bi-geo-alt') ?> me-1"></i>
                                    <?= htmlspecialchars($l['categoria'] ?? 'General') ?>
                                </span>

                                <!-- Badge Dinámico de Distancia (Se inyecta por JS) -->
                                <div class="badge-distancia-container position-absolute top-0 end-0 m-3"></div>
                            </div>

                            <!-- Cuerpo de la Tarjeta -->
                            <div class="card-body p-4 d-flex flex-column">
                                <h5 class="fw-bold text-dark mb-1 text-truncate" title="<?= htmlspecialchars($l['nombre']) ?>">
                                    <?= htmlspecialchars($l['nombre']) ?>
                                </h5>
                                <small class="text-muted mb-2 d-block text-truncate">
                                    <i class="bi bi-geo-alt me-1 text-danger"></i> <?= htmlspecialchars($l['direccion'] ?? '') ?>
                                </small>
                                <p class="text-secondary small mb-3 flex-grow-1" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                    <?= htmlspecialchars($l['descripcion'] ?? '') ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center border-top pt-3 mt-auto">
                                    <?php if (!empty($l['telefono_contacto']) || !empty($l['whatsapp_contacto'])): ?>
                                        <?php
                                            $wa = preg_replace('/[^0-9]/', '', ($l['whatsapp_contacto'] ?? '') ?: ($l['telefono_contacto'] ?? ''));
                                            if (strlen($wa) === 8) $wa = '591' . $wa;
                                        ?>
                                        <a href="https://wa.me/<?= $wa ?>" target="_blank" rel="noopener noreferrer"
                                           class="btn btn-sm btn-outline-success rounded-pill px-3 fw-semibold">
                                            <i class="bi bi-whatsapp me-1"></i> Contactar
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted extra-small">Sin contacto directo</span>
                                    <?php endif; ?>
                                    <a href="<?= htmlspecialchars($baseUrl) ?>/catalogo/detalle?slug=<?= urlencode($l['slug']) ?>"
                                       class="btn btn-sm btn-success rounded-pill px-3 fw-semibold">
                                        Ver Ficha &rarr;
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5 text-muted">
                    <i class="bi bi-compass display-4 d-block mb-2"></i>
                    No hay lugares disponibles en este momento.
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Guía del Viajero -->
<section class="bg-white py-5 border-top border-bottom" id="guia-trinidad">
    <div class="container py-3">
        <div class="text-center mb-5">
            <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fw-bold text-uppercase small mb-2">
                Consejos Útiles
            </span>
            <h2 class="fw-bold text-dark">¿Llegando por primera vez a Trinidad?</h2>
            <p class="text-muted small mx-auto" style="max-width: 600px;">
                Información práctica para moverte por la ciudad con total tranquilidad y disfrutar de la hospitalidad beniana.
            </p>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 border-0 bg-light rounded-4 p-4 text-center guide-card guide-card-transport" id="transporte">
                    <div class="icon-circle bg-warning-subtle text-warning mx-auto mb-3">
                        <i class="bi bi-scooter fs-2"></i>
                    </div>
                    <h5 class="fw-bold text-dark">Transporte en Mototaxi</h5>
                    <p class="text-muted small mb-0">
                        El medio más tradicional y rápido son los mototaxis y los "toritos". La tarifa estándar dentro del casco cívico oscila entre Bs 3 y Bs 5 por carrera.
                    </p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100 border-0 bg-light rounded-4 p-4 text-center guide-card guide-card-food">
                    <div class="icon-circle bg-success-subtle text-success mx-auto mb-3">
                        <i class="bi bi-cup-hot-fill fs-2"></i>
                    </div>
                    <h5 class="fw-bold text-dark">Sabores Imperdibles</h5>
                    <p class="text-muted small mb-0">
                        No te vayas de Trinidad sin probar el pacú frito de río, el majadito de charque con plátano maduro, el keoperí al horno y los masacos de yuca o plátano.
                    </p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100 border-0 bg-light rounded-4 p-4 text-center guide-card guide-card-sunset">
                    <div class="icon-circle bg-info-subtle text-info mx-auto mb-3">
                        <i class="bi bi-water fs-2"></i>
                    </div>
                    <h5 class="fw-bold text-dark">Atardeceres Benianos</h5>
                    <p class="text-muted small mb-0">
                        La Laguna Suárez (a 5 km del centro) y Puerto Ballivián sobre el río Ibare son los mejores puntos para contemplar la caída del sol con un refresco tradicional de somó.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Banner CTA -->
<section class="container py-5" id="unirse">
    <div class="card border-0 rounded-5 p-4 p-md-5 hero-cta text-white shadow-lg overflow-hidden position-relative">
        <div class="row align-items-center gy-4 position-relative" style="z-index: 2;">
            <div class="col-lg-8 text-center text-lg-start">
                <span class="badge bg-warning text-dark fw-bold px-3 py-2 rounded-pill text-uppercase mb-3">
                    Directorio Comercial
                </span>
                <h2 class="display-6 fw-bold mb-2">¿Tienes un restaurante, bar o negocio en Trinidad?</h2>
                <p class="lead text-white-50 mb-0">
                    Aparece ante cientos de personas y turistas que visitan la plataforma a diario por solo <strong>Bs 250 al mes</strong>.
                </p>
            </div>
            <div class="col-lg-4 text-center text-lg-end">
                <a href="<?= htmlspecialchars($baseUrl) ?>/solicitar-incorporacion" class="btn btn-warning btn-lg rounded-pill fw-bold px-4 py-3 shadow-sm text-dark">
                    <i class="bi bi-send-check-fill me-1"></i> Solicitar Publicación
                </a>
            </div>
        </div>
    </div>
</section>
