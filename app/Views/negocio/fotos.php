<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold text-dark mb-0">Galería de Fotografías</h4>
            <p class="text-muted small mb-0">Sube imágenes de tus platos, ambientación o instalaciones</p>
        </div>
    </div>

    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-success rounded-4 small mb-4"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger rounded-4 small mb-4"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Subida de Foto -->
    <div class="admin-card p-4 mb-4">
        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-cloud-arrow-up-fill text-success me-2"></i>Añadir Nueva Fotografía</h6>
        <form action="<?= htmlspecialchars($baseUrl) ?>/negocio/fotos/subir" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
            
            <div class="row g-3 align-items-center">
                <div class="col-md-7">
                    <input type="file" name="fotografia" class="form-control" accept="image/jpeg,image/png,image/webp" required>
                    <small class="text-muted extra-small">Admite JPG, PNG, WEBP hasta 5 MB.</small>
                </div>
                <div class="col-md-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="es_principal" value="1" id="checkPrincipal">
                        <label class="form-check-label small fw-semibold" for="checkPrincipal">Definir como foto de portada</label>
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-success w-100 rounded-pill fw-bold">
                        <i class="bi bi-upload me-1"></i> Subir
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Mosaico de Fotografías -->
    <div class="row g-3">
        <?php if (!empty($fotos)): ?>
            <?php foreach ($fotos as $f): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="admin-card overflow-hidden position-relative">
                        <img src="<?= htmlspecialchars($baseUrl) ?>/imagen?f=<?= urlencode($f['nombre_archivo']) ?>" 
                             class="w-100 object-fit-cover" style="height: 180px;">
                        
                        <?php if ($f['es_principal']): ?>
                            <span class="badge bg-warning text-dark position-absolute top-0 start-0 m-2 rounded-pill fw-bold" style="font-size: 0.65rem;">
                                <i class="bi bi-star-fill me-1"></i> Portada
                            </span>
                        <?php endif; ?>

                        <div class="p-2 text-end bg-white border-top">
                            <form action="<?= htmlspecialchars($baseUrl) ?>/negocio/fotos/eliminar" method="POST" onsubmit="return confirm('¿Eliminar esta fotografía?');">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                <input type="hidden" name="id_fotografia" value="<?= $f['id_fotografia'] ?>">
                                <button type="submit" class="btn btn-outline-danger btn-sm rounded-circle" title="Eliminar foto" style="width: 32px; height: 32px; padding: 0;">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5 text-muted">
                <i class="bi bi-images display-5 d-block mb-2"></i>
                Tu negocio aún no tiene fotos en su galería. Sube la primera arriba.
            </div>
        <?php endif; ?>
    </div>
</div>