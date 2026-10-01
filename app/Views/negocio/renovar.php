<?php
$qr = $cobro['qr_url'] ?? '';
$qrValido = is_string($qr) && preg_match('~\A(?:https?://|/(?!/))~i', $qr);
$qrSrc = $qrValido ? (preg_match('~\Ahttps?://~i', $qr) ? $qr : $baseUrl . $qr) : null;
?>
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Renovación de Membresía</h1>
            <p class="text-muted small mb-0">Mantén activo y visible tu negocio en la guía turística y comercial BeniTurs.</p>
        </div>
        <a href="<?= htmlspecialchars($baseUrl) ?>/negocio/dashboard" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> Volver al Panel
        </a>
    </div>

    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($mensaje) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <?php if ($pagoPendiente): ?>
        <div class="alert alert-warning rounded-4 p-4 mb-4 shadow-sm" role="status">
            <div class="d-flex gap-3 align-items-center">
                <i class="bi bi-hourglass-split display-6 text-warning flex-shrink-0"></i>
                <div>
                    <h5 class="fw-bold mb-1">Comprobante de Renovación en Verificación</h5>
                    <p class="mb-0 small text-dark">Ya has enviado un comprobante de pago que está siendo revisado por la administración. Una vez confirmado, tu vigencia se extenderá automáticamente sin perder ningún día.</p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Estado Actual de Cobertura -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Estado de tu Ficha</span>
                <div class="d-flex align-items-center gap-2 mb-3">
                    <?php if ($esVisible): ?>
                        <span class="badge bg-success rounded-pill px-3 py-2 fs-6">
                            <i class="bi bi-check-circle me-1"></i> Visible y Activo en la Guía
                        </span>
                    <?php else: ?>
                        <span class="badge bg-danger rounded-pill px-3 py-2 fs-6">
                            <i class="bi bi-pause-circle me-1"></i> Suscripción Vencida o No Visible
                        </span>
                    <?php endif; ?>
                </div>

                <div class="border-top pt-3">
                    <?php if ($fechaVencimiento): ?>
                        <p class="mb-1 text-secondary small">Vencimiento registrado: <strong class="text-dark"><?= htmlspecialchars(date('d/m/Y', strtotime($fechaVencimiento))) ?></strong></p>
                        <?php
                            $diasRestantes = (int)(ceil((strtotime($fechaVencimiento) - strtotime(date('Y-m-d'))) / 86400));
                        ?>
                        <?php if ($diasRestantes > 0): ?>
                            <div class="alert alert-info rounded-3 p-2 small mb-0 mt-2">
                                <i class="bi bi-info-circle me-1"></i> Te quedan <strong><?= $diasRestantes ?></strong> días de cobertura. Si renuevas hoy, tus nuevos meses se sumarán a partir del <?= htmlspecialchars(date('d/m/Y', strtotime($fechaVencimiento))) ?> sin perder ningún día.
                            </div>
                        <?php else: ?>
                            <div class="alert alert-danger rounded-3 p-2 small mb-0 mt-2">
                                <i class="bi bi-exclamation-circle me-1"></i> Tu vigencia expiró hace <?= abs($diasRestantes) ?> días. Al confirmar tu pago, tu ficha se reactivará de inmediato.
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="mb-0 text-muted small">Sin vigencia activa registrada.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Datos de Pago Institucional -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                <span class="text-muted small fw-semibold text-uppercase d-block mb-2">Cuentas Oficiales de Recaudación</span>
                <div class="d-flex align-items-center gap-3">
                    <?php if ($qrSrc !== null): ?>
                        <div class="bg-white p-2 border rounded-3 shadow-sm text-center flex-shrink-0">
                            <img src="<?= htmlspecialchars($qrSrc) ?>" alt="QR Recaudación" class="img-fluid rounded" style="max-height: 120px;">
                        </div>
                    <?php endif; ?>
                    <div class="small">
                        <?php if (!empty($cobro['banco']) && !empty($cobro['titular']) && !empty($cobro['cuenta'])): ?>
                            <p class="mb-1"><strong>Banco:</strong> <?= htmlspecialchars($cobro['banco']) ?></p>
                            <p class="mb-1"><strong>Titular:</strong> <?= htmlspecialchars($cobro['titular']) ?></p>
                            <p class="mb-1"><strong>Nº Cuenta:</strong> <code><?= htmlspecialchars($cobro['cuenta']) ?></code></p>
                            <p class="mb-0 text-muted extra-small">Escanea el QR desde tu banca móvil o haz una transferencia bancaria.</p>
                        <?php else: ?>
                            <p class="mb-0 text-muted">Los datos de recaudación todavía no están configurados.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (!$pagoPendiente): ?>
    <!-- Formulario de Envío de Comprobante -->
    <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
        <h4 class="fw-bold text-dark mb-4"><i class="bi bi-cash-stack text-success me-2"></i>Reportar Pago de Renovación</h4>

        <form action="<?= htmlspecialchars($baseUrl) ?>/negocio/renovar/guardar" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

            <!-- Selector de Plan -->
            <label class="form-label small fw-semibold mb-2">Selecciona el Plan de Renovación *</label>
            <div class="row g-3 mb-4">
                <?php foreach ($planes as $indice => $plan): ?>
                <div class="col-md-6">
                    <label class="card h-100 border p-3 rounded-4 plan-radio-card" style="cursor: pointer;">
                        <div class="d-flex align-items-center gap-3">
                            <input type="radio" name="plan" value="<?= htmlspecialchars($plan['codigo_plan']) ?>" <?= $indice === 0 ? 'checked' : '' ?> required class="form-check-input flex-shrink-0 mt-0">
                            <div>
                                <strong class="d-block text-dark"><?= htmlspecialchars($plan['nombre']) ?> (<?= (int)$plan['meses_duracion'] ?> mes(es))</strong>
                                <span class="fs-5 fw-bold text-success">Bs <?= number_format((float)$plan['monto'], 2) ?></span>
                                <small class="d-block text-muted"><?= htmlspecialchars($plan['descripcion']) ?></small>
                            </div>
                        </div>
                    </label>
                </div>
                <?php endforeach; ?>
                <?php if (!$planes): ?><p class="text-muted">No hay planes disponibles en este momento.</p><?php endif; ?>
            </div>
            <!-- Datos del Comprobante -->
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="numero_comprobante" class="form-label small fw-semibold">Número de Operación / Comprobante Bancario</label>
                    <input type="text" id="numero_comprobante" name="numero_comprobante" class="form-control" 
                           placeholder="Ej. OP-98765432 o Nº de transacción" maxlength="100">
                    <small class="text-muted extra-small">Número de referencia emitido por tu banco.</small>
                </div>

                <div class="col-md-6">
                    <label for="comprobante" class="form-label small fw-semibold">Comprobante de Pago (Foto o Captura) *</label>
                    <input type="file" id="comprobante" name="comprobante" class="form-control" accept="image/jpeg,image/png,image/webp" required>
                    <small class="text-muted extra-small">Formatos permitidos: JPG, PNG, WEBP. Tamaño máximo: 5 MB.</small>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                <a href="<?= htmlspecialchars($baseUrl) ?>/negocio/dashboard" class="btn btn-light rounded-pill px-4">Cancelar</a>
                <button type="submit" class="btn btn-success rounded-pill px-5 fw-semibold shadow-sm">
                    <i class="bi bi-cloud-arrow-up-fill me-1"></i> Enviar Comprobante
                </button>
            </div>
        </form>
    </div>
    <?php endif; ?>
</div>

<script src="<?= htmlspecialchars($baseUrl) ?>/assets/js/shared/compresor-imagen.js?v=<?= filemtime(dirname(__DIR__, 3) . '/public/assets/js/shared/compresor-imagen.js') ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('comprobante');
    if (input && window.CompresorImagen) {
        window.CompresorImagen.vincular(input, { maxDimension: 1280, calidad: 0.82 });
    }
});
</script>
