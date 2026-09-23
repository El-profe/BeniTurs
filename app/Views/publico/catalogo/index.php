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

    <!-- Cuadrícula Dinámica con Fotografías -->
    <div class="row g-4" id="contenedorLugares">
        <?php if (!empty($lugares)): ?>
            <?php foreach ($lugares as $lug): ?>
                <div class="col-md-6 col-lg-4 item-lugar">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden place-card">
                        
                        <!-- Cabecera con Imagen de Referencia o Ilustración de Respaldo -->
                        <?php if (!empty($lug['imagen'])): ?>
                            <div class="position-relative" style="height: 200px; background-color: #092611;">
                                <img src="<?= htmlspecialchars($baseUrl) ?>/imagen?f=<?= urlencode($lug['imagen']) ?>" 
                                     alt="<?= htmlspecialchars($lug['nombre']) ?>" 
                                     class="w-100 h-100 object-fit-cover">
                                <span class="badge <?= $lug['tipo_lugar'] === 'PUBLICO' ? 'badge-publico' : 'badge-comercio' ?> position-absolute top-0 end-0 m-3 rounded-pill fw-bold shadow-sm">
                                    <?= $lug['tipo_lugar'] === 'PUBLICO' ? 'Gratuito' : 'Comercio' ?>
                                </span>
                            </div>
                        <?php else: ?>
                            <div class="card-header-place p-4 text-white text-center position-relative">
                                <span class="badge <?= $lug['tipo_lugar'] === 'PUBLICO' ? 'badge-publico' : 'badge-comercio' ?> position-absolute top-0 end-0 m-3 rounded-pill fw-bold">
                                    <?= $lug['tipo_lugar'] === 'PUBLICO' ? 'Gratuito' : 'Comercio' ?>
                                </span>
                                <div class="place-icon-bubble mx-auto mb-2 shadow-sm">
                                    <i class="bi <?= htmlspecialchars($lug['categoria_icono']) ?> fs-3 text-success"></i>
                                </div>
                                <h5 class="fw-bold mb-1 text-white text-truncate"><?= htmlspecialchars($lug['nombre']) ?></h5>
                                <span class="extra-small text-white-50 text-uppercase fw-semibold tracking-wide">
                                    <?= htmlspecialchars($lug['categoria']) ?>
                                </span>
                            </div>
                        <?php endif; ?>

                        <!-- Cuerpo de la Tarjeta -->
                        <div class="card-body p-4 d-flex flex-column">
                            <?php if (!empty($lug['imagen'])): ?>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-light text-success border border-success-subtle rounded-pill extra-small">
                                        <i class="bi <?= htmlspecialchars($lug['categoria_icono']) ?> me-1"></i> <?= htmlspecialchars($lug['categoria']) ?>
                                    </span>
                                </div>
                                <h5 class="fw-bold text-dark text-truncate mb-2"><?= htmlspecialchars($lug['nombre']) ?></h5>
                            <?php endif; ?>

                            <p class="card-text text-secondary small flex-grow-1 line-clamp-3 mb-3">
                                <?= htmlspecialchars(mb_strimwidth($lug['descripcion'], 0, 140, '...')) ?>
                            </p>

                            <?php if (!empty($lug['horario_atencion'])): ?>
                                <div class="place-meta border-top pt-3 mb-3 small">
                                    <div class="text-muted extra-small text-truncate">
                                        <i class="bi bi-clock me-1 text-warning"></i> <?= htmlspecialchars($lug['horario_atencion']) ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="d-flex gap-2">
                                <a href="<?= htmlspecialchars($baseUrl) ?>/catalogo/detalle?slug=<?= urlencode($lug['slug']) ?>" 
                                   class="btn btn-success rounded-pill w-100 fw-semibold btn-sm py-2">
                                    Ver Ficha <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                                <?php if (!empty($lug['whatsapp_contacto'])): 
                                    $wa = preg_replace('/[^0-9]/', '', $lug['whatsapp_contacto']);
                                ?>
                                    <a href="https://wa.me/<?= $wa ?>" target="_blank" rel="noopener noreferrer" 
                                       class="btn btn-outline-success rounded-pill btn-sm px-3" title="Contactar por WhatsApp">
                                        <i class="bi bi-whatsapp"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="bi bi-info-circle display-4 text-muted mb-3 d-block"></i>
                <h5 class="text-muted"><?= $categoriaSeleccionada !== null ? 'Todavía no hay lugares publicados en esta categoría.' : 'No existen lugares publicados disponibles en este momento.' ?></h5>
            </div>
        <?php endif; ?>
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
