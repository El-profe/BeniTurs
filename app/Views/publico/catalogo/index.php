<!-- Hero Principal con Buscador Central -->
<section class="hero-trinidad text-white position-relative">
    <video class="hero-video" autoplay muted loop playsinline preload="metadata" poster="<?= htmlspecialchars($baseUrl) ?>/assets/img/hero-poster.webp" aria-hidden="true">
        <source src="<?= htmlspecialchars($baseUrl) ?>/assets/video/video.mp4" type="video/mp4">
    </video>

<div class="container hero-content py-5">
    <div class="row justify-content-center text-center">
        <div class="col-xl-9">
            
            <!-- Badge: Visión departamental con sede en la capital -->
            <span class="badge bg-warning text-dark fw-bold px-3 py-2 rounded-pill text-uppercase mb-3 shadow-sm">
                <i class="bi bi-compass-fill me-1"></i> Portal Turístico del Beni · Trinidad
            </span>

            <!-- Título Principal: Escalable hacia todo el departamento -->
            <h1 class="display-4 fw-extrabold mb-3 text-white">
                Descubre los mejores destinos del <span class="text-warning text-highlight">Beni</span>
            </h1>

            <!-- Descripción: Conecta la capital con el potencial de la región -->
            <p class="lead text-white-50 mb-4 mx-auto" style="max-width: 720px;">
                Explora atractivos naturales, gastronomía amazónica, hospedaje y comercios locales en Trinidad y las principales rutas del departamento.
            </p>

            <!-- Barra de Búsqueda Principal (IDs intactos para JavaScript) -->
            <div class="search-box-hero mx-auto shadow-lg bg-white p-2 rounded-pill mb-3" style="max-width: 680px;">
                <div class="d-flex align-items-center">
                    <span class="ps-3 text-muted"><i class="bi bi-search fs-5"></i></span>
                    <input type="text" id="inputBuscarHero" class="form-control border-0 shadow-none px-3 py-2 bg-transparent" 
                           placeholder="¿Qué buscas en Trinidad o el Beni? (ej. Laguna Suárez, majadito, hotel...)">
                    <button id="btnLimpiarFiltros" class="btn btn-light rounded-pill btn-sm me-2 text-muted" title="Limpiar búsqueda">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
</section>

<!-- DIVISOR ORGÁNICO: OLA AMAZÓNICA Y BRÚJULA FLOTANTE -->
<div class="section-divider-wrapper position-relative">
    <!-- Ola Fluida SVG -->
    <div class="wave-divider">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 90" preserveAspectRatio="none">
            <path fill="#ffffff" fill-opacity="1" d="M0,32L48,42.7C96,53,192,75,288,74.7C384,75,480,53,576,42.7C672,32,768,32,864,42.7C960,53,1056,75,1152,69.3C1248,64,1344,32,1392,16L1440,0L1440,90L1392,90C1344,90,1248,90,1152,90C1056,90,960,90,864,90C768,90,672,90,576,90C480,90,384,90,288,90C192,90,96,90,48,90L0,90Z"></path>
        </svg>
    </div>
</div>

<!-- Catálogo y Filtros de Categorías Multicolor -->
<section class="container py-4" id="explorar">
    <!-- Encabezado Centrado -->
    <div class="text-center mb-3">
        <h2 class="fw-bold text-dark mb-1">Explorar el Beni y sus Destinos</h2>
        <p class="text-muted small mb-0">Selecciona tu municipio, rubro favorito o explora el mapa interactivo</p>
    </div>

    <!-- Filtro de Municipios del Beni -->
    <div class="d-flex justify-content-center align-items-center gap-2 mb-3">
        <div class="d-inline-flex align-items-center bg-white shadow-sm border rounded-pill px-3 py-1">
            <i class="bi bi-geo-alt-fill text-danger me-2"></i>
            <span class="text-secondary small fw-semibold me-2 d-none d-sm-inline">Municipio:</span>
            <select id="selectMunicipio" class="form-select form-select-sm border-0 shadow-none py-1 ps-1 pe-4 bg-transparent fw-bold text-dark" style="cursor: pointer;">
                <option value="">Todo el Departamento del Beni</option>
                <?php foreach ($municipios as $m): ?>
                    <option value="<?= (int)$m['id_municipio'] ?>"
                            data-lat="<?= htmlspecialchars((string)($m['latitud_defecto'] ?? '')) ?>"
                            data-lng="<?= htmlspecialchars((string)($m['longitud_defecto'] ?? '')) ?>"
                            <?= ($municipioSeleccionado === (int)$m['id_municipio']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($m['nombre']) ?> (<?= htmlspecialchars($m['provincia']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- Píldoras de Categoría + Botón GPS Cerca de Mí -->
    <div class="category-filter-container d-flex flex-wrap justify-content-center align-items-center gap-2 gap-md-3 mb-4 py-2">
        <!-- 1. Botón Todos -->
        <button type="button" class="chip-filter-icon cat-color-all<?= $categoriaSeleccionada === null ? ' active' : '' ?>" data-categoria="" title="Todos los lugares" aria-label="Todos los lugares">
            <i class="bi bi-grid-fill"></i>
        </button>

        <!-- 2. BOTÓN GPS: Cerca de Mí (Radio de 1 km) -->
        <button type="button" id="btnCercaDeMi" class="chip-filter-icon cat-color-geo" title="Lugares a menos de 1 km de mi ubicación" aria-label="Lugares a menos de 1 km">
            <i class="bi bi-geo-alt-fill"></i>
        </button>

        <!-- 3. Categorías Dinámicas de la BD -->
        <?php
        $categoriaColorMap = [
            'gastronomia'                  => 'cat-color-gastro', // Naranja Ámbar
            'hoteles-alojamientos'         => 'cat-color-hotel',  // Azul Cielo
            'vida-nocturna'                => 'cat-color-night',  // Violeta Neón
            'atractivos-turisticos'        => 'cat-color-nature', // Verde Esmeralda
            'balnearios-recreacion'        => 'cat-color-water',  // Turquesa / Cian
            'museos-y-patrimonio-cultural' => 'cat-color-gold',   // Dorado / Ámbar
            'comercio-artesanias'          => 'cat-color-shop',   // Rosa Coral
        ];
        foreach ($categorias as $c):
            $catSlug = $c['slug'] ?? '';
            $currentClass = $categoriaColorMap[$catSlug] ?? 'cat-color-nature';
            if (!isset($categoriaColorMap[$catSlug])) {
                $nom = mb_strtolower($c['nombre'] ?? '', 'UTF-8');
                if (str_contains($nom, 'museo') || str_contains($nom, 'cultur')) $currentClass = 'cat-color-gold';
                elseif (str_contains($nom, 'bar') || str_contains($nom, 'nocturn')) $currentClass = 'cat-color-night';
                elseif (str_contains($nom, 'gastro') || str_contains($nom, 'restauran')) $currentClass = 'cat-color-gastro';
                elseif (str_contains($nom, 'hotel') || str_contains($nom, 'alojam')) $currentClass = 'cat-color-hotel';
                elseif (str_contains($nom, 'balneari') || str_contains($nom, 'agua')) $currentClass = 'cat-color-water';
                elseif (str_contains($nom, 'comercio') || str_contains($nom, 'tienda')) $currentClass = 'cat-color-shop';
            }
        ?>
            <button type="button" class="chip-filter-icon <?= $currentClass ?><?= $categoriaSeleccionada === (int)$c['id_categoria'] ? ' active' : '' ?>"
                    data-categoria="<?= $c['id_categoria'] ?>"
                    title="<?= htmlspecialchars($c['nombre']) ?>"
                    aria-label="<?= htmlspecialchars($c['nombre']) ?>">
                <i class="bi <?= htmlspecialchars($c['icono']) ?>"></i>
            </button>
        <?php endforeach; ?>
    </div>

    <!-- Alerta de estado de geolocalización (Flotante y compacta) -->
    <div id="alertaGeolocalizacion" class="alert alert-info alert-dismissible fade d-none rounded-4 small mb-4 border-0 shadow-sm mx-auto text-center" style="max-width: 650px;" role="alert">
        <span id="alertaGeoTexto"></span>
        <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert"></button>
    </div>

    <!-- Barra de Herramientas: Contador y Selector de Modo de Vista -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4 px-1">
        <div class="d-flex align-items-center gap-2">
            <span id="contadorResultados" class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fw-semibold small">
                <?= count($lugares) ?> lugares encontrados
            </span>
        </div>
        <!-- Selector de Modo de Vista (Cuadrícula / Mapa) -->
        <div class="btn-group p-1 bg-white border rounded-pill shadow-sm view-switcher-group" role="group" aria-label="Modo de visualización">
            <button type="button" class="btn btn-sm rounded-pill px-3 py-1 fw-semibold active" id="btnVistaGrid" title="Ver en cuadrícula de tarjetas">
                <i class="bi bi-grid-fill me-1"></i> Cuadrícula
            </button>
            <button type="button" class="btn btn-sm rounded-pill px-3 py-1 fw-semibold text-secondary" id="btnVistaMapa" title="Ver mapa interactivo de Trinidad">
                <i class="bi bi-map-fill me-1"></i> Mapa Trinidad
            </button>
        </div>
    </div>

    <!-- Contenedor del Mapa Interactivo de Trinidad -->
    <div id="mapaCatalogoWrapper" class="d-none mb-4">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden position-relative map-catalog-card">
            <!-- Barra de control del mapa -->
            <div class="card-header bg-white py-2 px-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success text-white rounded-pill px-3 py-1 fw-semibold small">
                        <i class="bi bi-geo-alt-fill me-1"></i> Mapa General de Trinidad, Beni
                    </span>
                    <span id="mapaConteoLugares" class="badge bg-light text-secondary border rounded-pill px-2 py-1 extra-small"></span>
                    <span id="mapaBadgeRadio" class="badge bg-danger text-white rounded-pill px-2 py-1 extra-small d-none shadow-sm">
                        <i class="bi bi-broadcast-pin me-1"></i> Radio: 1 km
                    </span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" id="btnMapaCentrarTrinidad" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1 shadow-none" title="Centrar en el centro de Trinidad">
                        <i class="bi bi-crosshair me-1"></i> Centrar Trinidad
                    </button>
                    <button type="button" id="btnMapaMiUbicacion" class="btn btn-sm btn-success rounded-pill px-3 py-1 shadow-none" title="Ver mi ubicación GPS y lugares en 1 km">
                        <i class="bi bi-cursor-fill me-1"></i> Mi Ubicación (1 km)
                    </button>
                </div>
            </div>
            <!-- Canvas del mapa Leaflet -->
            <div id="mapaCatalogo" style="width: 100%; height: 530px; min-height: 400px; background-color: #e9ecef; z-index: 1;"></div>
            <!-- Leyenda rápida inferior -->
            <div class="card-footer bg-white py-2 px-3 border-top small text-muted d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex flex-wrap align-items-center gap-3">
                    <span class="fw-semibold text-dark"><i class="bi bi-palette-fill me-1 text-success"></i> Categorías:</span>
                    <span class="d-inline-flex align-items-center gap-1"><span class="badge-dot" style="background:#198754;"></span> Atractivos y Naturaleza</span>
                    <span class="d-inline-flex align-items-center gap-1"><span class="badge-dot" style="background:#fd7e14;"></span> Gastronomía</span>
                    <span class="d-inline-flex align-items-center gap-1"><span class="badge-dot" style="background:#0d6efd;"></span> Hoteles</span>
                    <span class="d-inline-flex align-items-center gap-1"><span class="badge-dot" style="background:#8b5cf6;"></span> Vida Nocturna</span>
                    <span class="d-inline-flex align-items-center gap-1"><span class="badge-dot" style="background:#eab308;"></span> Museos y Cultura</span>
                    <span class="d-inline-flex align-items-center gap-1"><span class="badge-dot" style="background:#06b6d4;"></span> Balnearios</span>
                </div>
                <div class="extra-small text-secondary">
                    <i class="bi bi-info-circle me-1 text-warning"></i> Clic en cualquier marcador para ver ficha o navegar con GPS
                </div>
            </div>
        </div>
    </div>

    <!-- Cuadrícula de Fichas de Lugares -->
    <div class="row g-4" id="gridLugaresCatalogo">
        <?php if (!empty($lugares)): ?>
            <?php foreach ($lugares as $l): ?>
                <?php
                    $icono = ($l['categoria_icono'] ?? '') ?: 'bi-geo-alt';
                    $descripcion = $l['descripcion'] ?? '';
                    if (mb_strlen($descripcion, 'UTF-8') > 140) {
                        $descripcion = mb_substr($descripcion, 0, 140, 'UTF-8') . '...';
                    }
                ?>
                <div class="col-md-6 col-lg-4 item-lugar tarjeta-lugar-col"
                     data-id="<?= (int)$l['id_lugar'] ?>"
                     data-nombre="<?= htmlspecialchars(mb_strtolower($l['nombre'], 'UTF-8')) ?>"
                     data-categoria="<?= (int)$l['id_categoria'] ?>"
                     data-municipio="<?= (int)($l['id_municipio'] ?? 1) ?>"
                     data-coordenadas="<?= htmlspecialchars($l['coordenadas_gps'] ?? '') ?>">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden position-relative place-card">
                        <?php if (!empty($l['imagen'])): ?>
                            <div class="position-relative" style="height: 200px; background-color: #092611;">
                                <img src="<?= htmlspecialchars($baseUrl) ?>/imagen?f=<?= urlencode($l['imagen']) ?>"
                                     alt="<?= htmlspecialchars($l['nombre']) ?>"
                                     class="w-100 h-100 object-fit-cover"
                                     loading="lazy"
                                     decoding="async">
                                <span class="badge bg-dark bg-opacity-75 text-white position-absolute top-0 start-0 m-3 rounded-pill fw-bold shadow-sm">
                                    <i class="bi <?= htmlspecialchars($icono) ?> me-1"></i> <?= htmlspecialchars($l['categoria'] ?? '') ?>
                                    <?php if (!empty($l['municipio'])): ?>
                                        · <i class="bi bi-geo-alt text-warning"></i> <?= htmlspecialchars($l['municipio']) ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                        <?php else: ?>
                            <div class="card-header-place p-4 text-white text-center position-relative">
                                <div class="place-icon-bubble mx-auto mb-2 shadow-sm">
                                    <i class="bi <?= htmlspecialchars($icono) ?> fs-3 text-success"></i>
                                </div>
                                <h5 class="fw-bold mb-1 text-white text-truncate"><?= htmlspecialchars($l['nombre']) ?></h5>
                                <span class="extra-small text-white-50 text-uppercase fw-semibold tracking-wide">
                                    <?= htmlspecialchars($l['categoria'] ?? '') ?>
                                    <?php if (!empty($l['municipio'])): ?>
                                        · <?= htmlspecialchars($l['municipio']) ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                        <?php endif; ?>
                        <div class="badge-distancia-container position-absolute top-0 end-0 m-3"></div>
                        <div class="card-body p-4 d-flex flex-column">
                            <?php if (!empty($l['imagen'])): ?>
                                <h5 class="fw-bold text-dark text-truncate mb-2"><?= htmlspecialchars($l['nombre']) ?></h5>
                            <?php endif; ?>
                            <p class="card-text text-secondary small flex-grow-1 line-clamp-3 mb-3">
                                <?= htmlspecialchars($descripcion) ?>
                            </p>
                            <?php if (!empty($l['horario_atencion'])): ?>
                                <div class="place-meta border-top pt-3 mb-3 small">
                                    <div class="text-muted extra-small text-truncate">
                                        <i class="bi bi-clock me-1 text-warning"></i> <?= htmlspecialchars($l['horario_atencion']) ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <div class="d-flex gap-2">
                                <a href="<?= htmlspecialchars($baseUrl) ?>/catalogo/detalle?slug=<?= urlencode($l['slug']) ?>"
                                   class="btn btn-success rounded-pill w-100 fw-semibold btn-sm py-2">
                                    Ver Ficha <i class="bi bi-arrow-right ms-1"></i>
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
    <!-- Datos iniciales de lugares en formato JSON para el mapa interactivo -->
    <script id="lugaresInicialesJson" type="application/json">
        <?= json_encode($lugares, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>
    </script>
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
