<div class="container-fluid p-0">
    <div class="admin-welcome-banner p-4 text-white mb-4 shadow-sm">
        <span class="badge bg-warning text-dark fw-bold px-3 py-1 rounded-pill mb-2">Panel del Comercio</span>
        <h3 class="fw-bold mb-1"><?= htmlspecialchars($lugar['nombre']) ?></h3>
        <p class="text-white-50 mb-0 small">Controla la presencia de tu marca, mantén tus fotos al día y lanza ofertas para atraer visitantes.</p>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-4">
            <div class="admin-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small fw-semibold">Fotos en Galería</span>
                        <h2 class="fw-bold text-dark mt-1 mb-0"><?= $totalFotos ?></h2>
                        <a href="<?= htmlspecialchars($baseUrl) ?>/negocio/fotos" class="text-success small fw-semibold text-decoration-none">Subir fotos &rarr;</a>
                    </div>
                    <div class="widget-icon bg-success-subtle text-success">
                        <i class="bi bi-images"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4">
            <div class="admin-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small fw-semibold">Promociones Creadas</span>
                        <h2 class="fw-bold text-warning mt-1 mb-0"><?= count($promociones) ?></h2>
                        <a href="<?= htmlspecialchars($baseUrl) ?>/negocio/promociones" class="text-warning text-decoration-none small fw-semibold">Crear promoción &rarr;</a>
                    </div>
                    <div class="widget-icon bg-warning-subtle text-warning">
                        <i class="bi bi-tag-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-12 col-xl-4">
            <div class="admin-card p-4 h-100">
                <span class="text-muted small fw-semibold d-block">Estado de Publicación</span>
                <div class="d-flex align-items-center gap-2 mt-2">
                    <span class="badge bg-success rounded-pill px-3 py-2"><i class="bi bi-check-circle me-1"></i> Ficha Habilitada</span>
                </div>
                <small class="text-muted d-block mt-2">Visible para todos los turistas en Trinidad.</small>
            </div>
        </div>
    </div>
</div>