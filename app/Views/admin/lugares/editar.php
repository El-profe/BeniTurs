<div class="container-fluid p-0">
    <!-- Encabezado Superior -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-secondary-subtle text-secondary px-2 py-1 rounded-pill small">#<?= (int)$lugar['id_lugar'] ?></span>
                <?php if ($lugar['tipo_lugar'] === 'COMERCIAL'): ?>
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 fw-bold">
                        <i class="bi bi-shop me-1"></i> Ficha Comercial
                    </span>
                <?php else: ?>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 fw-bold">
                        <i class="bi bi-tree me-1"></i> Ficha Pública
                    </span>
                <?php endif; ?>
            </div>
            <h1 class="h3 fw-bold text-dark mb-0"><?= htmlspecialchars($lugar['nombre']) ?></h1>
            <small class="text-muted">Edición de contenido, clasificación turística y galería de fotos.</small>
        </div>

        <div class="d-flex align-items-center gap-2">
            <?php if (!empty($lugar['slug'])): ?>
                <a href="<?= htmlspecialchars($baseUrl) ?>/catalogo/detalle?slug=<?= urlencode($lugar['slug']) ?>" 
                   target="_blank" 
                   rel="noopener"
                   class="btn btn-outline-success btn-sm rounded-pill px-3 py-2 shadow-sm d-flex align-items-center gap-1">
                    <i class="bi bi-box-arrow-up-right"></i>
                    <span>Ver en Catálogo</span>
                </a>
            <?php endif; ?>
            <a href="<?= htmlspecialchars($baseUrl) ?>/admin/lugares" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-2">
                <i class="bi bi-arrow-left me-1"></i> Volver al Listado
            </a>
        </div>
    </div>

    <!-- Alertas Flash -->
    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2 fs-5"></i><?= htmlspecialchars($mensaje) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i><?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <!-- Formulario Principal de Edición -->
    <form id="formEditarFicha" action="<?= htmlspecialchars($baseUrl) ?>/admin/lugares/actualizar" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" id="csrfTokenGlobal" value="<?= htmlspecialchars($csrfToken) ?>">
        <input type="hidden" name="id_lugar" id="idLugarGlobal" value="<?= (int)$lugar['id_lugar'] ?>">

        <div class="row g-4">
            <!-- COLUMNA PRINCIPAL (Galería y Descripción) -->
            <div class="col-lg-8">
                <!-- Tarjeta 1: Galería Multimedia (Requisito Principal del Usuario) -->
                <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4" id="seccion-galeria">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom flex-wrap gap-2">
                        <div>
                            <h4 class="h5 fw-bold text-dark mb-0">
                                <i class="bi bi-images text-success me-2"></i>Galería de Fotografías
                            </h4>
                            <small class="text-muted">Gestiona las imágenes de esta ficha: elimina las que ya no uses, define la portada o sube nuevas.</small>
                        </div>
                        <span class="badge bg-success rounded-pill px-3 py-2 fw-semibold" id="conteoFotosGaleria">
                            <?= count($fotografias) ?> foto(s) registrada(s)
                        </span>
                    </div>

                    <!-- Cuadrícula de Fotografías Actuales -->
                    <div class="row g-3 mb-4" id="gridFotografiasActuales">
                        <?php if (!empty($fotografias)): ?>
                            <?php foreach ($fotografias as $foto): ?>
                                <?php $esPortada = (int)$foto['es_principal'] === 1; ?>
                                <div class="col-6 col-sm-4 col-md-3 item-foto-galeria" id="card-foto-<?= (int)$foto['id_fotografia'] ?>">
                                    <div class="card h-100 border rounded-4 overflow-hidden shadow-sm position-relative foto-card-inner">
                                        <!-- Imagen -->
                                        <div class="position-relative foto-thumbnail-container" style="height: 140px; background-color: #092611;">
                                            <img src="<?= htmlspecialchars($baseUrl) ?>/imagen?f=<?= urlencode($foto['nombre_archivo']) ?>" 
                                                 alt="Fotografía de <?= htmlspecialchars($lugar['nombre']) ?>" 
                                                 class="w-100 h-100 object-fit-cover"
                                                 loading="lazy">
                                            
                                            <!-- Badge de Portada -->
                                            <div class="badge-portada-wrapper position-absolute top-0 start-0 m-2">
                                                <?php if ($esPortada): ?>
                                                    <span class="badge bg-success text-white shadow-sm rounded-pill px-2 py-1 extra-small fw-bold">
                                                        <i class="bi bi-star-fill text-warning me-1"></i> Portada
                                                    </span>
                                                <?php endif; ?>
                                            </div>

                                            <!-- Botón de Eliminar (Acción Rápida) -->
                                            <button type="button" 
                                                    class="btn btn-danger btn-sm rounded-circle p-1 position-absolute top-0 end-0 m-2 shadow btn-eliminar-foto-ajax" 
                                                    data-id-foto="<?= (int)$foto['id_fotografia'] ?>"
                                                    title="Eliminar esta fotografía"
                                                    style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">
                                                <i class="bi bi-trash3-fill extra-small"></i>
                                            </button>
                                        </div>

                                        <!-- Pie de Tarjeta de Foto con Acciones -->
                                        <div class="p-2 text-center bg-white border-top">
                                            <?php if (!$esPortada): ?>
                                                <button type="button" 
                                                        class="btn btn-outline-warning text-dark btn-sm rounded-pill w-100 py-1 extra-small fw-semibold btn-establecer-portada-ajax"
                                                        data-id-foto="<?= (int)$foto['id_fotografia'] ?>"
                                                        title="Hacer foto principal de la ficha">
                                                    <i class="bi bi-star me-1 text-warning"></i> Portada
                                                </button>
                                            <?php else: ?>
                                                <span class="text-success extra-small fw-bold d-block py-1">
                                                    <i class="bi bi-check2-circle me-1"></i> Foto Principal
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12" id="estadoVacioGaleria">
                                <div class="text-center p-4 bg-light rounded-4 border border-dashed">
                                    <div class="rounded-circle bg-white shadow-sm d-inline-flex align-items-center justify-content-center p-3 text-secondary mb-2" style="width: 60px; height: 60px;">
                                        <i class="bi bi-image fs-3"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1">Sin Fotografías Registradas</h6>
                                    <p class="text-muted small mb-0">Esta ficha aún no cuenta con imágenes. Puedes seleccionar y subir varias a continuación.</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Zona de Carga para Aumentar Más Imágenes -->
                    <div class="p-3 bg-light rounded-4 border">
                        <label class="form-label fw-bold text-dark small mb-2 d-flex align-items-center gap-2">
                            <i class="bi bi-cloud-arrow-up-fill text-success fs-5"></i>
                            <span>Añadir Más Fotografías a la Galería</span>
                        </label>
                        
                        <div class="dropzone-area p-4 text-center rounded-3 bg-white border border-2 border-dashed mb-2" id="dropzoneFotos" style="cursor: pointer;">
                            <i class="bi bi-images display-6 text-success d-block mb-2"></i>
                            <strong class="d-block text-dark">Haz clic aquí para seleccionar imágenes</strong>
                            <small class="text-muted d-block mb-1">Puedes seleccionar varias fotos al mismo tiempo manteniendo presionada la tecla Ctrl / Shift.</small>
                            <span class="badge bg-light text-secondary border px-2 py-1 extra-small">JPG, PNG o WEBP &bull; Máximo 5 MB por imagen</span>
                            <input type="file" 
                                   id="inputFotografiasNuevas" 
                                   name="fotografias[]" 
                                   multiple 
                                   class="d-none" 
                                   accept="image/jpeg,image/png,image/webp">
                        </div>

                        <!-- Previsualización de Fotos Nuevas Seleccionadas -->
                        <div id="previewFotosNuevasContainer" class="d-none mt-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="extra-small fw-bold text-uppercase text-muted" id="conteoNuevasFotos">Imágenes listas para subir</span>
                                <button type="button" class="btn btn-link text-danger text-decoration-none extra-small p-0" id="btnLimpiarNuevasFotos">
                                    <i class="bi bi-x-circle me-1"></i> Descartar selección
                                </button>
                            </div>
                            <div class="row g-2" id="gridPreviewNuevasFotos"></div>
                            <small class="text-muted extra-small d-block mt-2">
                                <i class="bi bi-info-circle me-1"></i> Estas nuevas fotografías se subirán y guardarán al presionar el botón <strong>Guardar Cambios</strong>.
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta 2: Descripción y Contenido Turístico -->
                <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
                    <h4 class="h5 fw-bold text-dark mb-3 pb-2 border-bottom">
                        <i class="bi bi-card-text text-success me-2"></i>Descripción & Atractivos
                    </h4>

                    <div class="mb-3">
                        <label for="descripcion" class="form-label fw-semibold small text-dark">Descripción Completa *</label>
                        <textarea id="descripcion" 
                                  name="descripcion" 
                                  rows="6" 
                                  class="form-control" 
                                  placeholder="Describe las características principales, platillos, servicios o atractivos del lugar..."
                                  required><?= htmlspecialchars($lugar['descripcion']) ?></textarea>
                        <small class="text-muted extra-small">Explica qué hace único a este lugar para los turistas y visitantes.</small>
                    </div>

                    <div>
                        <label for="referencia_ubicacion" class="form-label fw-semibold small text-dark">Puntos de Referencia para Llegar</label>
                        <input type="text" 
                               id="referencia_ubicacion" 
                               name="referencia_ubicacion" 
                               class="form-control" 
                               value="<?= htmlspecialchars($lugar['referencia_ubicacion'] ?? '') ?>"
                               placeholder="Ej. A dos cuadras de la plaza principal, frente al Banco Unión">
                        <small class="text-muted extra-small">Ayuda a los visitantes a ubicarse con facilidad en la ciudad.</small>
                    </div>
                </div>
            </div>

            <!-- COLUMNA LATERAL (Clasificación, Contacto y Ubicación) -->
            <div class="col-lg-4">
                <!-- Tarjeta 3: Datos de Identidad y Clasificación -->
                <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
                    <h4 class="h5 fw-bold text-dark mb-3 pb-2 border-bottom">
                        <i class="bi bi-bookmark-star text-success me-2"></i>Clasificación
                    </h4>

                    <div class="mb-3">
                        <label for="nombre" class="form-label fw-semibold small text-dark">Nombre del Establecimiento *</label>
                        <input type="text" 
                               id="nombre" 
                               name="nombre" 
                               class="form-control fw-bold" 
                               value="<?= htmlspecialchars($lugar['nombre']) ?>" 
                               required>
                    </div>

                    <div class="mb-3">
                        <label for="tipo_lugar" class="form-label fw-semibold small text-dark">Tipo de Ficha *</label>
                        <select id="tipo_lugar" name="tipo_lugar" class="form-select" required>
                            <option value="COMERCIAL" <?= $lugar['tipo_lugar'] === 'COMERCIAL' ? 'selected' : '' ?>>Comercial (De Pago)</option>
                            <option value="PUBLICO" <?= $lugar['tipo_lugar'] === 'PUBLICO' ? 'selected' : '' ?>>Público (Gratuito / Institucional)</option>
                        </select>
                        <small class="text-muted extra-small">Determina las reglas de membresía y visibilidad.</small>
                    </div>

                    <div class="mb-3">
                        <label for="id_municipio" class="form-label fw-semibold small text-dark">Municipio del Beni *</label>
                        <select id="id_municipio" name="id_municipio" class="form-select" required>
                            <?php foreach ($municipios as $m): ?>
                                <option value="<?= (int)$m['id_municipio'] ?>" <?= ((int)($lugar['id_municipio'] ?? 1) === (int)$m['id_municipio']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($m['nombre']) ?> (<?= htmlspecialchars($m['provincia']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="id_categoria" class="form-label fw-semibold small text-dark">Categoría Turística *</label>
                        <select id="id_categoria" name="id_categoria" class="form-select" required>
                            <?php foreach ($categorias as $c): ?>
                                <option value="<?= (int)$c['id_categoria'] ?>" <?= (int)$lugar['id_categoria'] === (int)$c['id_categoria'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="horario_atencion" class="form-label fw-semibold small text-dark">Horario de Atención</label>
                        <input type="text" 
                               id="horario_atencion" 
                               name="horario_atencion" 
                               class="form-control" 
                               value="<?= htmlspecialchars($lugar['horario_atencion'] ?? '') ?>"
                               placeholder="Ej. Lun - Sáb: 08:00 - 22:00">
                    </div>
                </div>

                <!-- Tarjeta 4: Canales de Contacto Directo -->
                <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
                    <h4 class="h5 fw-bold text-dark mb-3 pb-2 border-bottom">
                        <i class="bi bi-telephone text-success me-2"></i>Canales de Contacto
                    </h4>

                    <div class="mb-3">
                        <label for="whatsapp_contacto" class="form-label fw-semibold small text-dark">
                            <i class="bi bi-whatsapp text-success me-1"></i> WhatsApp
                        </label>
                        <input type="text" 
                               id="whatsapp_contacto" 
                               name="whatsapp_contacto" 
                               class="form-control" 
                               value="<?= htmlspecialchars($lugar['whatsapp_contacto'] ?? '') ?>"
                               placeholder="Ej. 76891234 o +591 76891234">
                        <small class="text-muted extra-small">Los turistas podrán chatear con el local con 1 clic.</small>
                    </div>

                    <div class="mb-3">
                        <label for="telefono_contacto" class="form-label fw-semibold small text-dark">
                            <i class="bi bi-telephone me-1 text-primary"></i> Teléfono / Celular
                        </label>
                        <input type="text" 
                               id="telefono_contacto" 
                               name="telefono_contacto" 
                               class="form-control" 
                               value="<?= htmlspecialchars($lugar['telefono_contacto'] ?? '') ?>"
                               placeholder="Ej. 34621234 o 71234567">
                    </div>

                    <div>
                        <label for="email_contacto" class="form-label fw-semibold small text-dark">
                            <i class="bi bi-envelope me-1 text-secondary"></i> Correo Electrónico
                        </label>
                        <input type="email" 
                               id="email_contacto" 
                               name="email_contacto" 
                               class="form-control" 
                               value="<?= htmlspecialchars($lugar['email_contacto'] ?? '') ?>"
                               placeholder="contacto@negocio.com">
                    </div>
                </div>

                <!-- Tarjeta 5: Ubicación Geográfica & GPS -->
                <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
                    <h4 class="h5 fw-bold text-dark mb-3 pb-2 border-bottom">
                        <i class="bi bi-geo-alt text-success me-2"></i>Ubicación & GPS
                    </h4>

                    <div class="mb-3">
                        <label for="direccion" class="form-label fw-semibold small text-dark">Dirección Física *</label>
                        <input type="text" 
                               id="direccion" 
                               name="direccion" 
                               class="form-control" 
                               value="<?= htmlspecialchars($lugar['direccion']) ?>" 
                               required
                               placeholder="Ej. Calle 18 de Noviembre Nº 420">
                    </div>

                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="coordenadas_gps" class="form-label fw-semibold small text-dark mb-0">Coordenadas GPS</label>
                            <button type="button" class="btn btn-link btn-sm text-success text-decoration-none p-0 extra-small" id="btnCapturarGpsAdmin">
                                <i class="bi bi-crosshair me-1"></i> Mi GPS actual
                            </button>
                        </div>
                        <input type="text" 
                               id="coordenadas_gps" 
                               name="coordenadas_gps" 
                               class="form-control" 
                               value="<?= htmlspecialchars($lugar['coordenadas_gps'] ?? '') ?>"
                               placeholder="-14.8333, -64.9000">
                        <small class="text-muted extra-small">Formato: Latitud, Longitud (ej. -14.8333, -64.9000)</small>
                    </div>
                </div>

                <!-- Tarjeta 5.5: Portal Comercial y Credenciales de Acceso (Nivel 4) -->
                <?php if ($lugar['tipo_lugar'] === 'COMERCIAL'): ?>
                    <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4" id="tarjetaCredencialesComercio">
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                            <h4 class="h5 fw-bold text-dark mb-0">
                                <i class="bi bi-shield-lock-fill text-success me-2"></i>Acceso Comercial
                            </h4>
                            <?php if ($cuentaNegocio): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 fw-bold">
                                    <i class="bi bi-check-circle-fill me-1"></i> Cuenta Activa
                                </span>
                            <?php else: ?>
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-1 fw-bold">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Sin Cuenta
                                </span>
                            <?php endif; ?>
                        </div>

                        <?php if ($cuentaNegocio): ?>
                            <div class="p-3 bg-light rounded-3 mb-3 border">
                                <div class="row g-2 small">
                                    <div class="col-6">
                                        <span class="text-muted d-block extra-small text-uppercase fw-bold">Usuario:</span>
                                        <code class="fs-6 fw-bold text-success" id="cuentaUsuarioDisplay"><?= htmlspecialchars($cuentaNegocio['usuario']) ?></code>
                                    </div>
                                    <div class="col-6 text-end">
                                        <span class="text-muted d-block extra-small text-uppercase fw-bold">Cobertura:</span>
                                        <span class="fw-bold text-dark"><?= $ultimoVencimiento ? date('d/m/Y', strtotime($ultimoVencimiento)) : 'Sin vigencia' ?></span>
                                    </div>
                                    <div class="col-12 mt-2 pt-2 border-top">
                                        <span class="text-muted extra-small">Último acceso: <?= $cuentaNegocio['ultimo_acceso'] ? date('d/m/Y H:i', strtotime($cuentaNegocio['ultimo_acceso'])) : 'Aún no ha iniciado sesión' ?></span>
                                    </div>
                                </div>
                            </div>

                            <button type="button" 
                                    class="btn btn-warning rounded-pill w-100 py-2 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2" 
                                    id="btnRestablecerClaveEditar" 
                                    data-id-lugar="<?= (int)$lugar['id_lugar'] ?>">
                                <i class="bi bi-key-fill fs-5"></i>
                                <span>Restablecer y Enviar Clave</span>
                            </button>
                            <small class="text-muted extra-small d-block text-center mt-2">
                                Genera una nueva contraseña temporal y te permite enviársela por WhatsApp o imprimir su recibo oficial.
                            </small>
                        <?php else: ?>
                            <div class="alert alert-warning border-0 rounded-3 small mb-3">
                                <i class="bi bi-info-circle me-1"></i> Esta ficha comercial aún no cuenta con usuario de acceso para gestionar sus fotos y datos.
                            </div>
                            <button type="button" 
                                    class="btn btn-success rounded-pill w-100 py-2 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modalAprovisionarCuentaDirecta">
                                <i class="bi bi-person-plus-fill fs-5"></i>
                                <span>Aprovisionar Cuenta y Plan</span>
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Tarjeta 6: Barra de Acciones y Guardado -->
                <div class="card border-0 shadow-sm rounded-4 bg-white p-4 sticky-top" style="top: 20px; z-index: 10;">
                    <span class="text-muted extra-small fw-bold text-uppercase d-block mb-3">Acciones de Guardado</span>

                    <button type="submit" class="btn btn-success rounded-pill w-100 py-2 fw-bold shadow-sm mb-2 d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-check2-circle fs-5"></i>
                        <span>Guardar Cambios</span>
                    </button>

                    <button type="submit" name="guardar_y_continuar" value="1" class="btn btn-outline-success rounded-pill w-100 py-2 fw-semibold mb-3">
                        <i class="bi bi-save me-1"></i> Guardar y Seguir Editando
                    </button>

                    <div class="border-top pt-3 text-center">
                        <a href="<?= htmlspecialchars($baseUrl) ?>/admin/lugares" class="btn btn-light rounded-pill px-4 btn-sm text-secondary">
                            Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Modal para Confirmar Eliminación de Foto -->
<div class="modal fade" id="modalConfirmarEliminarFoto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-body p-4 text-center">
                <div class="rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center p-3 mb-3" style="width: 60px; height: 60px;">
                    <i class="bi bi-trash3-fill fs-3"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2">¿Eliminar fotografía?</h5>
                <p class="text-muted small mb-4">Esta imagen se borrará permanentemente del servidor y ya no se mostrará en el catálogo.</p>
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger rounded-pill px-4 fw-semibold" id="btnConfirmarBorradoFoto">
                        Sí, eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Aprovisionar Cuenta y Plan Directo (Nivel 4) -->
<div class="modal fade" id="modalAprovisionarCuentaDirecta" tabindex="-1" aria-labelledby="tituloModalAprovDirecta" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content rounded-4 border-0 shadow" id="formAprovisionarDirecta">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <input type="hidden" name="id_lugar" value="<?= (int)$lugar['id_lugar'] ?>">
            <div class="modal-header bg-success text-white py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-person-plus-fill fs-4"></i>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="tituloModalAprovDirecta">Aprovisionar Cuenta Comercial</h5>
                        <small class="text-white-50">Crear portal de autoservicio y suscripción</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted small mb-3">Se generará el usuario, contraseña inicial y vigencia para <strong><?= htmlspecialchars($lugar['nombre']) ?></strong>.</p>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Plan o Tarifa *</label>
                    <select name="codigo_plan" class="form-select" required>
                        <?php if (!empty($tarifas)): ?>
                            <?php foreach ($tarifas as $tar): ?>
                                <option value="<?= htmlspecialchars($tar['codigo_plan']) ?>">
                                    <?= htmlspecialchars($tar['nombre']) ?> · Bs <?= number_format($tar['monto'], 2) ?> (<?= (int)$tar['meses_duracion'] ?> mes<?= (int)$tar['meses_duracion'] > 1 ? 'es' : '' ?>)
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="MENSUAL">Plan Mensual (Bs. 50.00)</option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Método de Pago *</label>
                    <select name="metodo_pago" class="form-select">
                        <option value="EFECTIVO" selected>💵 Efectivo (Cobrado en mano)</option>
                        <option value="QR">📱 QR Simple</option>
                        <option value="TRANSFERENCIA">🏦 Transferencia Bancaria</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Nº de Recibo / Talonario (opcional)</label>
                    <input type="text" name="numero_comprobante" class="form-control" placeholder="Ej. REC-00124">
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm">
                    <i class="bi bi-check2-circle me-1"></i> Aprovisionar y Generar Clave
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal de Credenciales Generadas (Compartido) -->
<?php require dirname(__DIR__) . '/parciales/modal_credenciales.php'; ?>

<script src="<?= htmlspecialchars($baseUrl) ?>/assets/js/admin/credenciales-modal.js?v=<?= filemtime(dirname(__DIR__, 4) . '/public/assets/js/admin/credenciales-modal.js') ?>" defer></script>
<script src="<?= htmlspecialchars($baseUrl) ?>/assets/js/shared/compresor-imagen.js?v=<?= filemtime(dirname(__DIR__, 4) . '/public/assets/js/shared/compresor-imagen.js') ?>"></script>

<!-- Script Interactivo para Gestión Dinámica de Fotos y Previsualizaciones -->
<script>

document.addEventListener('DOMContentLoaded', () => {
    const baseUrl = '<?= htmlspecialchars($baseUrl) ?>';
    const csrfToken = document.getElementById('csrfTokenGlobal').value;
    const idLugar = document.getElementById('idLugarGlobal').value;

    // 1. Manejo de Subida Múltiple y Previsualización Local
    const dropzone = document.getElementById('dropzoneFotos');
    const inputFotos = document.getElementById('inputFotografiasNuevas');
    const previewContainer = document.getElementById('previewFotosNuevasContainer');
    const previewGrid = document.getElementById('gridPreviewNuevasFotos');
    const conteoNuevasFotos = document.getElementById('conteoNuevasFotos');
    const btnLimpiar = document.getElementById('btnLimpiarNuevasFotos');

    if (dropzone && inputFotos) {
        dropzone.addEventListener('click', () => inputFotos.click());

        // Arrastrar y soltar
        ['dragenter', 'dragover'].forEach(event => {
            dropzone.addEventListener(event, (e) => {
                e.preventDefault();
                dropzone.classList.add('border-success', 'bg-light');
            });
        });

        ['dragleave', 'drop'].forEach(event => {
            dropzone.addEventListener(event, (e) => {
                e.preventDefault();
                dropzone.classList.remove('border-success', 'bg-light');
            });
        });

        dropzone.addEventListener('drop', async (e) => {
            if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                let files = e.dataTransfer.files;
                if (window.CompresorImagen) {
                    conteoNuevasFotos.textContent = 'Optimizando fotos en el navegador...';
                    const res = await Promise.all(Array.from(files).map(f => window.CompresorImagen.comprimir(f, { maxDimension: 1280, calidad: 0.82 })));
                    if (typeof DataTransfer !== 'undefined') {
                        const dt = new DataTransfer();
                        res.forEach(r => dt.items.add(r.file));
                        inputFotos.files = dt.files;
                    }
                } else {
                    inputFotos.files = files;
                }
                mostrarPrevisualizaciones(inputFotos.files);
            }
        });

        inputFotos.addEventListener('change', async () => {
            if (window.CompresorImagen && inputFotos.files.length > 0) {
                previewContainer.classList.remove('d-none');
                conteoNuevasFotos.innerHTML = '<span class="spinner-border spinner-border-sm me-1 text-success"></span> Optimizando fotos en el navegador...';
                const res = await Promise.all(Array.from(inputFotos.files).map(f => window.CompresorImagen.comprimir(f, { maxDimension: 1280, calidad: 0.82 })));
                if (typeof DataTransfer !== 'undefined') {
                    const dt = new DataTransfer();
                    res.forEach(r => dt.items.add(r.file));
                    inputFotos.files = dt.files;
                }
            }
            mostrarPrevisualizaciones(inputFotos.files);
        });

        btnLimpiar?.addEventListener('click', () => {
            inputFotos.value = '';
            previewGrid.innerHTML = '';
            previewContainer.classList.add('d-none');
        });
    }

    function mostrarPrevisualizaciones(files) {
        if (!files || files.length === 0) {
            previewContainer.classList.add('d-none');
            previewGrid.innerHTML = '';
            return;
        }

        previewGrid.innerHTML = '';
        conteoNuevasFotos.textContent = `${files.length} fotografía(s) seleccionada(s) para añadir`;
        previewContainer.classList.remove('d-none');

        Array.from(files).forEach((file, index) => {
            if (!file.type.startsWith('image/')) return;

            const col = document.createElement('div');
            col.className = 'col-4 col-sm-3 col-md-2';
            
            const card = document.createElement('div');
            card.className = 'card h-100 border rounded-3 overflow-hidden shadow-sm position-relative';
            
            const img = document.createElement('img');
            img.className = 'w-100 object-fit-cover';
            img.style.height = '85px';
            
            const reader = new FileReader();
            reader.onload = (e) => { img.src = e.target.result; };
            reader.readAsDataURL(file);

            const badge = document.createElement('span');
            badge.className = 'badge bg-dark bg-opacity-75 text-white position-absolute bottom-0 start-0 m-1 extra-small';
            badge.textContent = Math.round(file.size / 1024) + ' KB';

            card.appendChild(img);
            card.appendChild(badge);
            col.appendChild(card);
            previewGrid.appendChild(col);
        });
    }

    // 2. Eliminación de Fotografías Existentes (AJAX con Modal)
    let fotoIdAEliminar = null;
    const modalEl = document.getElementById('modalConfirmarEliminarFoto');
    const modalConfirmacion = modalEl && window.bootstrap ? new window.bootstrap.Modal(modalEl) : null;
    const btnConfirmarBorrado = document.getElementById('btnConfirmarBorradoFoto');

    document.querySelectorAll('.btn-eliminar-foto-ajax').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            fotoIdAEliminar = btn.getAttribute('data-id-foto');
            if (modalConfirmacion) {
                modalConfirmacion.show();
            } else if (confirm('¿Estás seguro de eliminar esta fotografía de la ficha?')) {
                ejecutarEliminacion(fotoIdAEliminar);
            }
        });
    });

    btnConfirmarBorrado?.addEventListener('click', () => {
        if (fotoIdAEliminar) {
            ejecutarEliminacion(fotoIdAEliminar);
            modalConfirmacion?.hide();
        }
    });

    function ejecutarEliminacion(idFoto) {
        const cardFoto = document.getElementById(`card-foto-${idFoto}`);
        if (cardFoto) {
            cardFoto.style.opacity = '0.5';
        }

        const formData = new FormData();
        formData.append('csrf_token', csrfToken);
        formData.append('id_fotografia', idFoto);
        formData.append('id_lugar', idLugar);

        fetch(`${baseUrl}/admin/lugares/eliminar-foto`, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (cardFoto) {
                    cardFoto.style.transition = 'all 0.3s ease';
                    cardFoto.style.transform = 'scale(0.8)';
                    cardFoto.style.opacity = '0';
                    setTimeout(() => {
                        cardFoto.remove();
                        actualizarConteoFotos();
                    }, 300);
                }
            } else {
                alert(data.message || 'Error al eliminar la fotografía.');
                if (cardFoto) cardFoto.style.opacity = '1';
            }
        })
        .catch(err => {
            console.error('Error:', err);
            // Fallback: Si falla la petición AJAX, enviar formulario tradicional
            enviarFormularioDirecto(`${baseUrl}/admin/lugares/eliminar-foto`, {
                csrf_token: csrfToken,
                id_fotografia: idFoto,
                id_lugar: idLugar
            });
        });
    }

    // 3. Establecer Fotografía como Portada Principal (AJAX)
    document.querySelectorAll('.btn-establecer-portada-ajax').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const idFoto = btn.getAttribute('data-id-foto');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Guardando...';

            const formData = new FormData();
            formData.append('csrf_token', csrfToken);
            formData.append('id_fotografia', idFoto);
            formData.append('id_lugar', idLugar);

            fetch(`${baseUrl}/admin/lugares/establecer-portada`, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Recargar suavemente para reflejar la nueva portada en todos los badges
                    window.location.reload();
                } else {
                    alert(data.message || 'Error al cambiar portada.');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-star me-1 text-warning"></i> Portada';
                }
            })
            .catch(err => {
                console.error('Error:', err);
                enviarFormularioDirecto(`${baseUrl}/admin/lugares/establecer-portada`, {
                    csrf_token: csrfToken,
                    id_fotografia: idFoto,
                    id_lugar: idLugar
                });
            });
        });
    });

    function actualizarConteoFotos() {
        const totalRestantes = document.querySelectorAll('.item-foto-galeria').length;
        const badgeConteo = document.getElementById('conteoFotosGaleria');
        if (badgeConteo) {
            badgeConteo.textContent = `${totalRestantes} foto(s) registrada(s)`;
        }
        if (totalRestantes === 0) {
            const grid = document.getElementById('gridFotografiasActuales');
            if (grid) {
                grid.innerHTML = `
                    <div class="col-12" id="estadoVacioGaleria">
                        <div class="text-center p-4 bg-light rounded-4 border border-dashed">
                            <div class="rounded-circle bg-white shadow-sm d-inline-flex align-items-center justify-content-center p-3 text-secondary mb-2" style="width: 60px; height: 60px;">
                                <i class="bi bi-image fs-3"></i>
                            </div>
                            <h6 class="fw-bold text-dark mb-1">Sin Fotografías Registradas</h6>
                            <p class="text-muted small mb-0">Esta ficha aún no cuenta con imágenes. Puedes seleccionar y subir varias a continuación.</p>
                        </div>
                    </div>
                `;
            }
        }
    }

    function enviarFormularioDirecto(url, params) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = url;
        for (const key in params) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = params[key];
            form.appendChild(input);
        }
        document.body.appendChild(form);
        form.submit();
    }

    // 4. Captura de Coordenadas GPS del Administrador
    const btnGps = document.getElementById('btnCapturarGpsAdmin');
    const inputGps = document.getElementById('coordenadas_gps');

    btnGps?.addEventListener('click', () => {
        if (!navigator.geolocation) {
            alert('Tu navegador no soporta geolocalización GPS.');
            return;
        }

        btnGps.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Obteniendo GPS...';
        btnGps.disabled = true;

        navigator.geolocation.getCurrentPosition(
            (pos) => {
                const lat = pos.coords.latitude.toFixed(6);
                const lng = pos.coords.longitude.toFixed(6);
                inputGps.value = `${lat}, ${lng}`;
                btnGps.innerHTML = '<i class="bi bi-check2 text-success me-1"></i> ¡GPS capturado!';
                setTimeout(() => {
                    btnGps.innerHTML = '<i class="bi bi-crosshair me-1"></i> Mi GPS actual';
                    btnGps.disabled = false;
                }, 3000);
            },
            (err) => {
                alert('No se pudo obtener tu ubicación GPS: ' + err.message);
                btnGps.innerHTML = '<i class="bi bi-crosshair me-1"></i> Mi GPS actual';
                btnGps.disabled = false;
            },
            { enableHighAccuracy: true, timeout: 10000 }
        );
    });

    // 5. Restablecimiento de Contraseña para Comercios (Nivel 4)
    const btnRestablecer = document.getElementById('btnRestablecerClaveEditar');
    if (btnRestablecer) {
        btnRestablecer.addEventListener('click', async () => {
            if (!confirm('¿Deseas restablecer la clave de acceso de este comercio? Se creará una nueva clave temporal de inmediato.')) {
                return;
            }

            const idLugar = btnRestablecer.dataset.idLugar;
            const originalHtml = btnRestablecer.innerHTML;
            btnRestablecer.disabled = true;
            btnRestablecer.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Generando clave...';

            try {
                const formData = new FormData();
                formData.append('csrf_token', csrfToken);
                formData.append('id_lugar', idLugar);

                const res = await fetch(`${baseUrl}/admin/lugares/restablecer-clave`, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                });

                const data = await res.json();
                if (data.success && data.data) {
                    if (window.BeniTursModalCredenciales) {
                        window.BeniTursModalCredenciales.mostrar(data.data);
                    }
                } else {
                    alert(data.message || 'Error al restablecer la contraseña.');
                }
            } catch (e) {
                alert('Error en la comunicación con el servidor: ' + e.message);
            } finally {
                btnRestablecer.disabled = false;
                btnRestablecer.innerHTML = originalHtml;
            }
        });
    }

    // 6. Aprovisionamiento Directo de Cuenta y Plan Comercial (Nivel 4)
    const formAprovDirecta = document.getElementById('formAprovisionarDirecta');
    if (formAprovDirecta) {
        formAprovDirecta.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = formAprovDirecta.querySelector('button[type="submit"]');
            const originalHtml = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Aprovisionando...';

            try {
                const formData = new FormData(formAprovDirecta);
                const res = await fetch(`${baseUrl}/admin/lugares/crear-cuenta`, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                });

                const data = await res.json();
                if (data.success && data.data) {
                    const modalAprovEl = document.getElementById('modalAprovisionarCuentaDirecta');
                    if (modalAprovEl && window.bootstrap) {
                        bootstrap.Modal.getInstance(modalAprovEl)?.hide();
                    }
                    if (window.BeniTursModalCredenciales) {
                        window.BeniTursModalCredenciales.mostrar(data.data);
                    }
                    const modalCredEl = document.getElementById('modalCredencialesGeneradas');
                    modalCredEl?.addEventListener('hidden.bs.modal', () => {
                        window.location.reload();
                    }, { once: true });
                } else {
                    alert(data.message || 'Error al aprovisionar la cuenta.');
                }
            } catch (e) {
                alert('Error al comunicarse con el servidor: ' + e.message);
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalHtml;
            }
        });
    }
});
</script>