<div class="container-fluid p-0">
    <div class="business-welcome mb-4">
        <div class="business-welcome-copy">
            <span class="business-welcome-label"><i class="bi bi-shop" aria-hidden="true"></i> Panel del Comercio</span>
            <h1><?= htmlspecialchars($lugar['nombre']) ?></h1>
            <p>Controla la presencia de tu marca, mantén tus fotos al día y lanza ofertas para atraer visitantes.</p>
        </div>
        <div class="business-welcome-icon" aria-hidden="true"><i class="bi bi-shop-window"></i></div>
    </div>

    <?php if ($pagoPendiente): ?>
        <div class="alert alert-warning rounded-4 p-4" role="status">
            <h5><i class="bi bi-hourglass-split me-2"></i>Estamos verificando tu pago</h5>
            Tu comprobante de pago está siendo verificado. Mientras tanto, puedes subir las fotos de tu galería y redactar tus promociones.
        </div>
    <?php endif; ?>
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
                    <?php if ($esVisible): ?>
                        <span class="badge bg-success rounded-pill px-3 py-2 text-wrap">Ficha Activa y Visible en la Guía</span>
                    <?php else: ?>
                        <span class="badge bg-secondary rounded-pill px-3 py-2">Ficha no visible al público</span>
                    <?php endif; ?>
                </div>
                <?php if ($esVisible && $fechaVencimiento): ?>
                    <small class="text-muted d-block mt-2">Vigencia hasta <?= htmlspecialchars(date('d/m/Y', strtotime($fechaVencimiento))) ?>.</small>
                <?php elseif (!$esVisible): ?>
                    <small class="text-muted d-block mt-2">Puedes preparar tu contenido. La publicación requiere pago confirmado, vigencia activa y habilitación administrativa.</small>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
