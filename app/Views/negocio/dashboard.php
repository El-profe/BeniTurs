<div class="container-fluid p-0">
    <!-- Encabezado de Bienvenida -->
    <div class="business-welcome mb-4 shadow-sm">
        <div class="business-welcome-copy">
            <span class="business-welcome-label">
                <i class="bi bi-shop me-1" aria-hidden="true"></i> Panel del Comercio &bull; <?= htmlspecialchars($lugar['municipio'] ?? 'Trinidad') ?>
            </span>
            <h1 class="text-white fw-bold mb-2"><?= htmlspecialchars($lugar['nombre']) ?></h1>
            <p class="mb-0">
                <?= htmlspecialchars($lugar['categoria'] ?? 'Establecimiento Comercial') ?> &bull; Gestiona tus fotos, promociones, ubicación en el mapa y kit de difusión digital.
            </p>
        </div>
        <div class="business-welcome-icon d-none d-md-grid" aria-hidden="true">
            <i class="bi bi-shop-window"></i>
        </div>
    </div>

    <!-- Mensajes Flash de Sesión -->
    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2 fs-5 align-middle"></i><?= htmlspecialchars($mensaje) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2 fs-5 align-middle"></i><?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <?php if ($pagoPendiente): ?>
        <div class="alert alert-warning rounded-4 p-4 mb-4 shadow-sm border-0 border-start border-warning border-5" role="status">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-warning-subtle text-warning p-3 fs-3 flex-shrink-0">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <div class="flex-grow-1">
                    <h5 class="fw-bold mb-1 text-dark">Estamos verificando tu pago</h5>
                    <p class="mb-0 small text-muted">Tu comprobante de pago está siendo auditado por la administración. Mientras tanto, puedes configurar tus fotos, horarios, perfil y promociones para que todo esté listo.</p>
                </div>
                <div class="flex-shrink-0 d-none d-sm-block">
                    <a href="<?= htmlspecialchars($baseUrl) ?>/negocio/renovar" class="btn btn-outline-dark btn-sm rounded-pill px-3 fw-semibold">
                        Ver detalle
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- MÓDULO 1: Semáforo Visual de Membresía y Cobertura -->
    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white membership-banner-card">
        <div class="row align-items-center gy-3">
            <div class="col-lg-8">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge <?= $estadoVigencia['badge_clase'] ?> rounded-pill px-3 py-1 fw-semibold text-uppercase small">
                        <i class="bi bi-shield-check me-1"></i> <?= htmlspecialchars($estadoVigencia['badge_texto']) ?>
                    </span>
                    <?php if ($esVisible): ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 small">
                            <i class="bi bi-eye-fill me-1"></i> Visible en Catálogo Público
                        </span>
                    <?php else: ?>
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3 py-1 small">
                            <i class="bi bi-eye-slash-fill me-1"></i> Ficha Pausada
                        </span>
                    <?php endif; ?>
                </div>

                <h4 class="fw-bold text-dark mb-1"><?= htmlspecialchars($estadoVigencia['titulo']) ?></h4>
                <p class="text-muted small mb-3"><?= htmlspecialchars($estadoVigencia['mensaje']) ?></p>

                <!-- Barra de Progreso de Cobertura -->
                <?php if ($fechaVencimiento && $diasRestantes !== null && $diasRestantes >= 0): ?>
                    <div class="d-flex justify-content-between align-items-center small mb-1">
                        <span class="text-muted fw-semibold">Vigencia activa</span>
                        <span class="fw-bold text-dark"><?= $diasRestantes ?> días restantes</span>
                    </div>
                    <div class="progress rounded-pill" style="height: 10px;">
                        <div class="progress-bar bg-<?= $estadoVigencia['color'] ?> progress-bar-striped progress-bar-animated" 
                             role="progressbar" 
                             style="width: <?= min(100, max(5, $estadoVigencia['progreso'])) ?>%;" 
                             aria-valuenow="<?= $estadoVigencia['progreso'] ?>" 
                             aria-valuemin="0" 
                             aria-valuemax="100"></div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-lg-4 text-lg-end">
                <a href="<?= htmlspecialchars($baseUrl) ?>/negocio/renovar" class="btn btn-success btn-lg rounded-pill px-4 py-2 fw-bold shadow-sm w-100 w-lg-auto">
                    <i class="bi bi-arrow-repeat me-1"></i> Gestionar Renovación
                </a>
                <small class="text-muted d-block mt-2 text-center text-lg-end extra-small">
                    Planes Mensual (Bs 250) o Anual (Bs 2.500 con ahorro)
                </small>
            </div>
        </div>
    </div>

    <!-- FILA PRINCIPAL (2 Columnas) -->
    <div class="row g-4 mb-4">
        <!-- COLUMNA IZQUIERDA: Salud de Perfil + Promociones + Acciones Rápidas -->
        <div class="col-xl-8">
            
            <!-- MÓDULO 2: Barra de Salud y Completitud del Perfil (0% a 100%) -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase d-block mb-1">
                            <i class="bi bi-speedometer2 text-success me-1"></i> Optimización de Ficha
                        </span>
                        <h5 class="fw-bold text-dark mb-0">Salud y Calidad de tu Perfil</h5>
                    </div>
                    <div class="text-end">
                        <span class="badge <?= $porcentajeSalud >= 80 ? 'bg-success' : ($porcentajeSalud >= 60 ? 'bg-warning text-dark' : 'bg-danger') ?> rounded-pill px-3 py-2 fw-bold fs-6">
                            <?= $porcentajeSalud ?>% Completo
                        </span>
                    </div>
                </div>

                <!-- Barra Visual de Completitud -->
                <div class="progress rounded-pill mb-3" style="height: 12px;">
                    <div class="progress-bar <?= $porcentajeSalud >= 80 ? 'bg-success' : ($porcentajeSalud >= 60 ? 'bg-warning' : 'bg-danger') ?>" 
                         role="progressbar" 
                         style="width: <?= $porcentajeSalud ?>%;" 
                         aria-valuenow="<?= $porcentajeSalud ?>" 
                         aria-valuemin="0" 
                         aria-valuemax="100"></div>
                </div>
                <p class="text-muted small mb-4">
                    <?= $porcentajeSalud === 100 
                        ? '¡Excelente! Tu perfil cuenta con toda la información clave para atraer y convencer a los turistas.' 
                        : 'Completa los puntos pendientes para mejorar el posicionamiento de tu negocio y recibir más visitas.' ?>
                </p>

                <!-- Checklist Interactivo de 5 Criterios -->
                <div class="list-group list-group-flush border rounded-4 overflow-hidden">
                    <?php foreach ($criteriosSalud as $criterio): ?>
                        <div class="list-group-item p-3 d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-2 gap-sm-3 <?= $criterio['cumplido'] ? 'bg-light-subtle' : 'bg-white' ?>">
                            <div class="d-flex align-items-start align-items-sm-center gap-3">
                                <?php if ($criterio['cumplido']): ?>
                                    <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center flex-shrink-0 mt-1 mt-sm-0" style="width: 32px; height: 32px;">
                                        <i class="bi bi-check-lg fw-bold"></i>
                                    </div>
                                <?php else: ?>
                                    <div class="rounded-circle bg-warning-subtle text-warning d-flex align-items-center justify-content-center flex-shrink-0 mt-1 mt-sm-0" style="width: 32px; height: 32px;">
                                        <i class="bi bi-exclamation"></i>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <h6 class="mb-1 mb-sm-0 fw-bold <?= $criterio['cumplido'] ? 'text-dark' : 'text-danger' ?>">
                                        <?= htmlspecialchars($criterio['titulo']) ?>
                                    </h6>
                                    <p class="text-muted extra-small mb-0"><?= htmlspecialchars($criterio['descripcion']) ?></p>
                                </div>
                            </div>
                            <div class="flex-shrink-0 align-self-end align-self-sm-center mt-1 mt-sm-0">
                                <?php if ($criterio['cumplido']): ?>
                                    <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1 extra-small">
                                        <i class="bi bi-check2"></i> Listo
                                    </span>
                                <?php else: ?>
                                    <a href="<?= htmlspecialchars($criterio['enlace']) ?>" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1 fw-semibold extra-small">
                                        <?= htmlspecialchars($criterio['texto_accion']) ?> &rarr;
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- MÓDULO 4: Gestor Rápido de Promociones Vigentes -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <div>
                        <span class="text-muted small fw-bold text-uppercase d-block mb-1">
                            <i class="bi bi-tag-fill text-warning me-1"></i> Ofertas para Turistas
                        </span>
                        <h5 class="fw-bold text-dark mb-0">Promociones y Descuentos Activos</h5>
                    </div>
                    <div>
                        <a href="<?= htmlspecialchars($baseUrl) ?>/negocio/promociones" class="btn btn-warning btn-sm rounded-pill px-3 fw-bold text-dark shadow-sm">
                            <i class="bi bi-plus-lg me-1"></i> Nueva Promoción
                        </a>
                    </div>
                </div>

                <?php if (empty($promocionesActivas)): ?>
                    <div class="text-center py-4 px-3 bg-light rounded-4 border border-dashed">
                        <i class="bi bi-tag text-muted display-5 d-block mb-2"></i>
                        <h6 class="fw-bold text-dark mb-1">No tienes promociones vigentes</h6>
                        <p class="text-muted small mb-3 mx-auto" style="max-width: 480px;">
                            Las ofertas y descuentos temporales aparecen con insignia destacada en el catálogo y atraen a turistas que buscan dónde comer, alojarse o disfrutar en Trinidad.
                        </p>
                        <a href="<?= htmlspecialchars($baseUrl) ?>/negocio/promociones" class="btn btn-outline-warning btn-sm rounded-pill px-3 fw-semibold text-dark">
                            <i class="bi bi-megaphone me-1"></i> Crear mi primera oferta
                        </a>
                    </div>
                <?php else: ?>
                    <div class="row g-3">
                        <?php foreach ($promocionesActivas as $promo): ?>
                            <?php 
                                $diasPromo = (int)(ceil((strtotime($promo['fecha_fin']) - strtotime(date('Y-m-d'))) / 86400));
                            ?>
                            <div class="col-md-6">
                                <div class="card h-100 border rounded-4 p-3 bg-light-subtle promo-card-item">
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                        <span class="badge bg-warning text-dark fw-bold rounded-pill px-3 py-1 small">
                                            <i class="bi bi-stars me-1"></i> <?= htmlspecialchars($promo['descuento_texto'] ?: 'Oferta Especial') ?>
                                        </span>
                                        <span class="badge bg-white text-secondary border rounded-pill px-2 py-1 extra-small">
                                            Vence en <?= $diasPromo ?>d
                                        </span>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1"><?= htmlspecialchars($promo['titulo']) ?></h6>
                                    <p class="text-muted small mb-2 text-truncate-2"><?= htmlspecialchars($promo['descripcion']) ?></p>
                                    <div class="d-flex justify-content-between align-items-center pt-2 mt-auto border-top border-light-subtle extra-small text-muted">
                                        <span>Del <?= date('d/m', strtotime($promo['fecha_inicio'])) ?> al <?= date('d/m/Y', strtotime($promo['fecha_fin'])) ?></span>
                                        <a href="<?= htmlspecialchars($baseUrl) ?>/negocio/promociones" class="text-success fw-semibold text-decoration-none">Editar</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Accesos Rápidos Principales -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <h5 class="fw-bold text-dark mb-3">
                    <i class="bi bi-grid-fill text-success me-2"></i>Herramientas del Perfil
                </h5>
                <div class="row g-2 g-sm-3">
                    <div class="col-6 col-md-3">
                        <a href="<?= htmlspecialchars($baseUrl) ?>/negocio/perfil" class="btn btn-light border rounded-4 p-2 p-sm-3 text-center text-sm-start d-flex flex-column flex-sm-row align-items-center gap-2 gap-sm-3 w-100 h-100 text-decoration-none shadow-sm-hover">
                            <div class="rounded-3 bg-primary-subtle text-primary p-2 fs-4 flex-shrink-0">
                                <i class="bi bi-pencil-square"></i>
                            </div>
                            <div>
                                <strong class="text-dark d-block extra-small fw-bold">Editar Perfil</strong>
                                <small class="text-muted extra-small d-none d-sm-block">Contacto y horarios</small>
                            </div>
                        </a>
                    </div>

                    <div class="col-6 col-md-3">
                        <a href="<?= htmlspecialchars($baseUrl) ?>/negocio/ubicacion" class="btn btn-light border rounded-4 p-2 p-sm-3 text-center text-sm-start d-flex flex-column flex-sm-row align-items-center gap-2 gap-sm-3 w-100 h-100 text-decoration-none shadow-sm-hover">
                            <div class="rounded-3 bg-danger-subtle text-danger p-2 fs-4 flex-shrink-0">
                                <i class="bi bi-geo-alt"></i>
                            </div>
                            <div>
                                <strong class="text-dark d-block extra-small fw-bold">Ubicación GPS</strong>
                                <small class="text-muted extra-small d-none d-sm-block">Mapa interactivo</small>
                            </div>
                        </a>
                    </div>

                    <div class="col-6 col-md-3">
                        <a href="<?= htmlspecialchars($baseUrl) ?>/negocio/fotos" class="btn btn-light border rounded-4 p-2 p-sm-3 text-center text-sm-start d-flex flex-column flex-sm-row align-items-center gap-2 gap-sm-3 w-100 h-100 text-decoration-none shadow-sm-hover">
                            <div class="rounded-3 bg-success-subtle text-success p-2 fs-4 flex-shrink-0">
                                <i class="bi bi-images"></i>
                            </div>
                            <div>
                                <strong class="text-dark d-block extra-small fw-bold">Galería Fotos</strong>
                                <small class="text-muted extra-small d-none d-sm-block"><?= $totalFotos ?> fotos subidas</small>
                            </div>
                        </a>
                    </div>

                    <div class="col-6 col-md-3">
                        <a href="<?= htmlspecialchars($baseUrl) ?>/negocio/renovar" class="btn btn-light border rounded-4 p-2 p-sm-3 text-center text-sm-start d-flex flex-column flex-sm-row align-items-center gap-2 gap-sm-3 w-100 h-100 text-decoration-none shadow-sm-hover">
                            <div class="rounded-3 bg-warning-subtle text-warning p-2 fs-4 flex-shrink-0">
                                <i class="bi bi-credit-card-2-front"></i>
                            </div>
                            <div>
                                <strong class="text-dark d-block extra-small fw-bold">Membresía</strong>
                                <small class="text-muted extra-small d-none d-sm-block">Renovación online</small>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- COLUMNA DERECHA: Miniatura en Vivo + Kit de Marketing Digital -->
        <div class="col-xl-4">
            
            <!-- MÓDULO 3: Miniatura en Vivo de la Tarjeta Turística -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold text-dark mb-0">
                        <i class="bi bi-phone text-primary me-1"></i> Vista Previa en Vivo
                    </h6>
                    <span class="badge bg-light text-muted border rounded-pill px-2 py-1 extra-small">
                        Así te ven los turistas
                    </span>
                </div>

                <!-- Simulación de Card del Catálogo -->
                <div class="card border rounded-4 overflow-hidden shadow-sm preview-business-card">
                    <div class="position-relative preview-img-container bg-dark" style="height: 180px;">
                        <?php if (!empty($lugar['imagen'])): ?>
                            <img src="<?= htmlspecialchars($baseUrl) ?>/imagen?f=<?= urlencode($lugar['imagen']) ?>" 
                                 alt="<?= htmlspecialchars($lugar['nombre']) ?>" 
                                 class="w-100 h-100 object-fit-cover"
                                 loading="lazy">
                        <?php else: ?>
                            <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center text-white-50 bg-secondary">
                                <i class="bi bi-camera display-5 mb-1"></i>
                                <span class="extra-small">Sin foto principal</span>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Badges Superpuestos -->
                        <span class="badge bg-warning text-dark fw-bold rounded-pill position-absolute top-0 start-0 m-3 px-3 py-1 shadow-sm small">
                            <?= htmlspecialchars($lugar['categoria'] ?? 'Comercial') ?>
                        </span>

                        <span class="badge <?= $esVisible ? 'bg-success' : 'bg-danger' ?> rounded-pill position-absolute bottom-0 end-0 m-3 px-2 py-1 shadow-sm extra-small">
                            <?= $esVisible ? '● En línea' : '● Pausado' ?>
                        </span>
                    </div>

                    <div class="card-body p-3">
                        <h6 class="fw-bold text-dark mb-1 text-truncate"><?= htmlspecialchars($lugar['nombre']) ?></h6>
                        <p class="text-muted extra-small mb-2 text-truncate">
                            <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                            <?= htmlspecialchars($lugar['direccion'] ?? 'Dirección no registrada') ?>
                        </p>
                        
                        <?php if (!empty($lugar['horario_atencion'])): ?>
                            <p class="text-muted extra-small mb-3">
                                <i class="bi bi-clock-fill text-warning me-1"></i>
                                <?= htmlspecialchars($lugar['horario_atencion']) ?>
                            </p>
                        <?php endif; ?>

                        <!-- Botones de Acción de Prueba -->
                        <div class="d-grid gap-2">
                            <?php 
                                $telRaw = preg_replace('/\D/', '', $lugar['whatsapp_contacto'] ?: ($lugar['telefono_contacto'] ?: ''));
                                if (strlen($telRaw) === 8) $telRaw = '591' . $telRaw;
                            ?>
                            <?php if (!empty($telRaw)): ?>
                                <a href="https://wa.me/<?= $telRaw ?>?text=<?= rawurlencode('Hola, vi su negocio en BeniTurs y deseo hacer una consulta.') ?>" 
                                   target="_blank" 
                                   class="btn btn-outline-success btn-sm rounded-pill fw-semibold py-1">
                                    <i class="bi bi-whatsapp me-1"></i> Probar botón de WhatsApp
                                </a>
                            <?php else: ?>
                                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill py-1 disabled">
                                    <i class="bi bi-whatsapp me-1"></i> Sin WhatsApp configurado
                                </button>
                            <?php endif; ?>

                            <a href="<?= htmlspecialchars($urlFichaPublica) ?>" target="_blank" class="btn btn-light border btn-sm rounded-pill fw-semibold py-1 text-muted">
                                <i class="bi bi-box-arrow-up-right me-1"></i> Abrir ficha pública completa
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MÓDULO 5: Kit de Difusión Digital (Enlace + Código QR) -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <div class="rounded-3 bg-warning-subtle text-warning p-2 fs-5">
                        <i class="bi bi-megaphone-fill"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark mb-0">Kit de Difusión Digital</h6>
                        <small class="text-muted extra-small">Promueve tu negocio ante más turistas</small>
                    </div>
                </div>

                <p class="text-muted small mb-3">
                    Comparte tu enlace oficial en tus redes sociales o imprime tu código QR para exhibirlo en tus mesas o mostrador.
                </p>

                <!-- Campo de Enlace con Copiado Rápido -->
                <label class="form-label extra-small fw-bold text-uppercase text-muted mb-1">Tu enlace en BeniTurs</label>
                <div class="input-group input-group-sm mb-3">
                    <input type="text" id="inputEnlaceFicha" class="form-control bg-light border-end-0 font-monospace extra-small" 
                           value="<?= htmlspecialchars($urlFichaPublica) ?>" readonly>
                    <button class="btn btn-success fw-semibold px-3" type="button" id="btnCopiarEnlaceFicha" title="Copiar enlace">
                        <i class="bi bi-clipboard me-1"></i> <span id="txtBtnCopiar">Copiar</span>
                    </button>
                </div>

                <!-- Botones del Kit -->
                <div class="d-grid gap-2">
                    <?php 
                        $mensajeCompartir = "¡Hola! Te invito a conocer nuestro establecimiento " . $lugar['nombre'] . " en la guía oficial de Trinidad y el Beni: " . $urlFichaPublica;
                    ?>
                    <a href="https://wa.me/?text=<?= rawurlencode($mensajeCompartir) ?>" 
                       target="_blank" 
                       class="btn btn-outline-success btn-sm rounded-pill fw-semibold py-2">
                        <i class="bi bi-whatsapp me-1 text-success"></i> Compartir por WhatsApp
                    </a>

                    <button type="button" class="btn btn-light border btn-sm rounded-pill fw-semibold py-2 text-dark shadow-sm-hover" 
                            data-bs-toggle="modal" data-bs-target="#modalQrNegocio">
                        <i class="bi bi-qr-code-scan me-1 text-primary"></i> Ver QR para Imprimir en Mesa
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: Código QR de Mesa para Imprimir -->
<div class="modal fade" id="modalQrNegocio" tabindex="-1" aria-labelledby="modalQrNegocioLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="modalQrNegocioLabel">
                    <i class="bi bi-qr-code text-primary me-2"></i>Código QR de Mesa
                </h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4 text-center">
                
                <!-- Área imprimible del QR -->
                <div id="areaQrImprimible" class="p-4 border rounded-4 bg-white shadow-sm mx-auto" style="max-width: 360px;">
                    <div class="mb-2">
                        <span class="badge bg-warning text-dark fw-bold rounded-pill px-3 py-1 small text-uppercase">
                            BeniTurs &bull; Trinidad
                        </span>
                    </div>
                    <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($lugar['nombre']) ?></h5>
                    <p class="text-muted extra-small mb-3"><?= htmlspecialchars($lugar['categoria'] ?? 'Directorio Turístico') ?></p>

                    <!-- Imagen QR Generada Dinámicamente -->
                    <?php 
                        $qrApiUrl = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&margin=10&data=" . urlencode($urlFichaPublica);
                    ?>
                    <div class="qr-container bg-white p-2 rounded-3 border d-inline-block mb-3">
                        <img src="<?= htmlspecialchars($qrApiUrl) ?>" 
                             alt="QR de <?= htmlspecialchars($lugar['nombre']) ?>" 
                             class="img-fluid rounded" 
                             style="width: 220px; height: 220px;">
                    </div>

                    <p class="text-dark small fw-semibold mb-1">
                        ¡Escanea con tu celular!
                    </p>
                    <p class="text-muted extra-small mb-0">
                        Consulta nuestro menú, galería de fotos, horarios y promociones en la guía turística oficial.
                    </p>
                </div>

                <p class="text-muted extra-small mt-3 mb-0">
                    Coloca este cartel en tus mesas, cartas o mostrador de atención.
                </p>
            </div>
            <div class="modal-footer border-0 pt-0 d-flex justify-content-between">
                <button type="button" class="btn btn-light rounded-pill px-3 small fw-semibold" data-bs-dismiss="modal">
                    Cerrar
                </button>
                <button type="button" class="btn btn-primary rounded-pill px-4 fw-semibold" onclick="imprimirQrCartel()">
                    <i class="bi bi-printer me-1"></i> Imprimir Cartel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Script del Dashboard para Copiado Rápido e Impresión -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const btnCopiar = document.getElementById('btnCopiarEnlaceFicha');
    const inputEnlace = document.getElementById('inputEnlaceFicha');
    const txtBtn = document.getElementById('txtBtnCopiar');

    if (btnCopiar && inputEnlace) {
        btnCopiar.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(inputEnlace.value);
                btnCopiar.classList.remove('btn-success');
                btnCopiar.classList.add('btn-dark');
                btnCopiar.innerHTML = '<i class="bi bi-check-lg me-1"></i> ¡Copiado!';
                setTimeout(() => {
                    btnCopiar.classList.remove('btn-dark');
                    btnCopiar.classList.add('btn-success');
                    btnCopiar.innerHTML = '<i class="bi bi-clipboard me-1"></i> Copiar';
                }, 2000);
            } catch (err) {
                inputEnlace.select();
                document.execCommand('copy');
                alert('Enlace copiado al portapapeles');
            }
        });
    }
});

function imprimirQrCartel() {
    const contenido = document.getElementById('areaQrImprimible').innerHTML;
    const ventanaImpresion = window.open('', '_blank', 'width=650,height=750');
    ventanaImpresion.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Imprimir QR - <?= htmlspecialchars(addslashes($lugar['nombre'])) ?></title>
            <link rel="stylesheet" href="<?= htmlspecialchars($baseUrl) ?>/assets/vendor/bootstrap/css/bootstrap.min.css">
            <style>
                body {
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    min-height: 100vh;
                    background: #f8f9fa;
                    font-family: system-ui, -apple-system, sans-serif;
                }
                .card-qr-print {
                    max-width: 400px;
                    border: 2px solid #20392e;
                    border-radius: 20px;
                    padding: 30px;
                    text-align: center;
                    background: #fff;
                    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
                }
                @media print {
                    body { background: #fff; }
                    .card-qr-print { border: 2px solid #000; box-shadow: none; }
                }
            </style>
        </head>
        <body onload="window.print(); window.close();">
            <div class="card-qr-print">
                ${contenido}
            </div>
        </body>
        </html>
    `);
    ventanaImpresion.document.close();
}
</script>
