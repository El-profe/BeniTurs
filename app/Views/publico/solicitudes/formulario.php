<?php
$escape = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$qr = $cobro['qr_url'] ?? '';
$qrValido = is_string($qr) && preg_match('~\A(?:https?://|/(?!/))~i', $qr);
?>
<div class="container py-5">
    <div class="row justify-content-center"><div class="col-lg-9 col-xl-8">
        <div class="text-center mb-4">
            <span class="badge bg-warning text-dark rounded-pill mb-2">Publicación comercial</span>
            <h1 class="h2 fw-bold">Publica tu negocio en BeniTurs</h1>
            <p class="text-muted">Completa tus datos, elige un plan y envía tu comprobante. Activaremos tu negocio cuando verifiquemos el pago.</p>
        </div>
        <ol class="list-unstyled d-flex justify-content-between gap-2 small mb-4" aria-label="Pasos de la solicitud">
            <li data-step-indicator="0" class="fw-bold text-success" aria-current="step">1. Datos del negocio</li>
            <li data-step-indicator="1">2. Elegir plan</li>
            <li data-step-indicator="2">3. Pago y comprobante</li>
        </ol>
        <div id="solicitudAlertContainer" aria-live="polite" tabindex="-1"></div>
        <form id="formSolicitudComercial" action="<?= $escape($baseUrl) ?>/solicitudes/enviar" method="post" enctype="multipart/form-data" novalidate>
            <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>">
            <input type="hidden" name="monto_declarado" value="250.00">
            <section data-wizard-step="0" class="card border-0 shadow-sm rounded-4 p-4 p-md-5">
                <h2 class="h4 mb-4" tabindex="-1">Datos del negocio y del propietario</h2>
                <div class="row g-3">
                    <div class="col-md-7"><label for="solNombre" class="form-label">Nombre del establecimiento *</label><input id="solNombre" name="nombre_establecimiento" class="form-control" maxlength="150" required></div>
                    <div class="col-md-5"><label for="solCategoria" class="form-label">Categoría *</label><select id="solCategoria" name="id_categoria" class="form-select" required><option value="">Selecciona una categoría</option><?php foreach ($categorias as $cat): ?><option value="<?= (int)$cat['id_categoria'] ?>"><?= $escape($cat['nombre']) ?></option><?php endforeach; ?></select></div>
                    <div class="col-12"><label for="solDireccion" class="form-label">Dirección en Trinidad *</label><input id="solDireccion" name="direccion" class="form-control" maxlength="255" autocomplete="street-address" required></div>
                    <div class="col-md-6"><label for="solTelefono" class="form-label">Teléfono / WhatsApp *</label><input id="solTelefono" name="telefono_contacto" type="tel" class="form-control" maxlength="30" autocomplete="tel" placeholder="Ej. 70000000 o +591 70000000" required></div>
                    <div class="col-md-6"><label for="solDueno" class="form-label">Nombre completo del dueño *</label><input id="solDueno" name="nombre_solicitante" class="form-control" maxlength="120" autocomplete="name" required></div>
                    <div class="col-12"><label for="solDescripcion" class="form-label">Breve descripción *</label><textarea id="solDescripcion" name="descripcion" rows="3" class="form-control" maxlength="10000" required></textarea></div>
                    <div class="col-md-6"><label for="solHorario" class="form-label">Horarios de atención</label><input id="solHorario" name="horarios" class="form-control" maxlength="150"></div>
                    <div class="col-md-6"><label for="solEmail" class="form-label">Correo electrónico (opcional)</label><input id="solEmail" name="email_contacto" type="email" class="form-control" maxlength="120" autocomplete="email"></div>
                </div>
                <button type="button" data-next class="btn btn-success rounded-pill mt-4 align-self-end">Siguiente: Elegir Plan <i class="bi bi-arrow-right ms-1"></i></button>
            </section>
            <section data-wizard-step="1" class="card border-0 shadow-sm rounded-4 p-4 p-md-5" hidden>
                <h2 class="h4 mb-4" tabindex="-1">Elige tu plan</h2>
                <div class="row g-3">
                    <div class="col-md-6"><label class="plan-card d-block h-100 border rounded-4 p-4 active-plan"><input type="radio" name="plan_solicitado" value="MENSUAL" class="form-check-input me-2" checked required><span class="fw-bold">Plan Mensual</span><span class="d-block h2 my-3">Bs 250 <small class="fs-6 text-muted">/ mes</small></span><span class="text-muted">Un mes de publicación.</span></label></div>
                    <div class="col-md-6"><label class="plan-card d-block h-100 border rounded-4 p-4"><input type="radio" name="plan_solicitado" value="ANUAL" class="form-check-input me-2" required><span class="fw-bold">Plan Anual</span><span class="d-block h2 my-3">Bs 2,500 <small class="fs-6 text-muted">/ año</small></span><span class="badge bg-warning text-dark">Ahorro de 2 meses · Bs 500</span></label></div>
                </div>
                <div class="d-flex justify-content-between gap-2 mt-4"><button type="button" data-back class="btn btn-outline-secondary rounded-pill">Atrás</button><button type="button" data-next class="btn btn-success rounded-pill">Siguiente: Realizar Pago</button></div>
            </section>
            <section data-wizard-step="2" class="card border-0 shadow-sm rounded-4 p-4 p-md-5" hidden>
                <h2 class="h4 mb-3" tabindex="-1">Pago QR y comprobante</h2>
                <p class="fs-5">Monto a pagar: <strong id="montoPlan" class="text-success" aria-live="polite">Bs 250</strong> · <span id="nombrePlan">Plan Mensual</span></p>
                <div class="row g-4 align-items-center mb-4">
                    <div class="col-md-6 text-center">
                        <?php if ($qrValido): ?><img src="<?= $escape($qr) ?>" class="img-fluid rounded-3 border" style="max-height: 280px;" alt="QR institucional de recaudación BeniTurs">
                        <?php else: ?><p class="alert alert-warning mb-0">El QR de pago todavía no está disponible.</p><?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <h3 class="h6 fw-bold">Cuenta de recaudación · Trinidad</h3>
                        <?php if ($cobro['banco'] && $cobro['titular'] && $cobro['cuenta']): ?>
                            <dl class="mb-0"><dt>Banco</dt><dd><?= $escape($cobro['banco']) ?></dd><dt>Titular</dt><dd><?= $escape($cobro['titular']) ?></dd><dt>Número de cuenta</dt><dd><?= $escape($cobro['cuenta']) ?></dd></dl>
                        <?php else: ?><p class="text-muted">Los datos bancarios todavía no están disponibles.</p><?php endif; ?>
                        <p class="small text-muted mt-3 mb-0">Verifica el titular e ingresa el monto de tu plan antes de confirmar la transferencia.</p>
                    </div>
                </div>
                <label for="numeroComprobante" class="form-label">Número de operación (opcional)</label><input id="numeroComprobante" name="numero_comprobante" class="form-control mb-3" maxlength="100">
                <label for="comprobante" class="form-label">Foto o captura del comprobante *</label><input id="comprobante" name="comprobante" type="file" accept="image/jpeg,image/png,image/webp" class="form-control" required aria-describedby="comprobanteAyuda"><small id="comprobanteAyuda" class="text-muted">JPG, PNG o WEBP. Máximo 5 MB.</small>
                <div class="d-flex flex-wrap justify-content-between gap-2 mt-4"><button type="button" data-back class="btn btn-outline-secondary rounded-pill">Atrás</button><button id="btnSubmitSolicitud" type="submit" class="btn btn-success rounded-pill">Enviar Solicitud y Comprobante</button></div>
            </section>
        </form>
        <noscript><p class="alert alert-warning mt-3">Activa JavaScript para completar los tres pasos.</p></noscript>
    </div></div>
</div>