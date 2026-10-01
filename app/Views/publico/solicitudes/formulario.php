<?php
$escape = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$qr = $cobro['qr_url'] ?? '';
$qrValido = is_string($qr) && preg_match('~\A(?:https?://|/(?!/))~i', $qr);
$qrSrc = $qrValido ? (preg_match('~\Ahttps?://~i', $qr) ? $qr : $baseUrl . $qr) : null;
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
            <input type="hidden" name="monto_declarado" value="<?= $escape($planes[0]['monto'] ?? '') ?>">
            <section data-wizard-step="0" class="card border-0 shadow-sm rounded-4 p-4 p-md-5">
                <h2 class="h4 mb-4" tabindex="-1">Datos del negocio y del propietario</h2>
                <div class="row g-3">
                    <div class="col-md-7">
                        <label for="solNombre" class="form-label">Nombre del establecimiento *</label>
                        <input id="solNombre" name="nombre_establecimiento" class="form-control" maxlength="150" required>
                    </div>
                    <div class="col-md-5">
                        <label for="solCategoria" class="form-label">Categoría *</label>
                        <select id="solCategoria" name="id_categoria" class="form-select" required>
                            <option value="">Selecciona una categoría</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?= (int)$cat['id_categoria'] ?>"><?= $escape($cat['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="solMunicipio" class="form-label">Municipio del Beni *</label>
                        <select id="solMunicipio" name="id_municipio" class="form-select" required>
                            <?php foreach ($municipios as $m): ?>
                                <option value="<?= (int)$m['id_municipio'] ?>" <?= (int)$m['id_municipio'] === 1 ? 'selected' : '' ?>>
                                    <?= $escape($m['nombre']) ?> (<?= $escape($m['provincia']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="solDireccion" class="form-label">Dirección física *</label>
                        <input id="solDireccion" name="direccion" class="form-control" maxlength="255" autocomplete="street-address" required placeholder="Calle, avenida o zona">
                    </div>
                    <div class="col-md-6">
                        <label for="solTelefono" class="form-label">Teléfono / WhatsApp *</label>
                        <input id="solTelefono" name="telefono_contacto" type="tel" class="form-control" maxlength="30" autocomplete="tel" placeholder="Ej. 70000000 o +591 70000000" required>
                    </div>
                    <div class="col-md-6">
                        <label for="solDueno" class="form-label">Nombre completo del dueño *</label>
                        <input id="solDueno" name="nombre_solicitante" class="form-control" maxlength="120" autocomplete="name" required>
                    </div>
                    <div class="col-12">
                        <label for="solDescripcion" class="form-label">Breve descripción *</label>
                        <textarea id="solDescripcion" name="descripcion" rows="3" class="form-control" maxlength="10000" required></textarea>
                    </div>
                    <div class="col-md-6">
                        <label for="solHorario" class="form-label">Horarios de atención</label>
                        <input id="solHorario" name="horarios" class="form-control" maxlength="150" placeholder="Ej. Lunes a Sábado de 08:00 a 22:00">
                    </div>
                    <div class="col-md-6">
                        <label for="solEmail" class="form-label">Correo electrónico (opcional)</label>
                        <input id="solEmail" name="email_contacto" type="email" class="form-control" maxlength="120" autocomplete="email">
                    </div>
                </div>
                <button type="button" data-next class="btn btn-success rounded-pill mt-4 align-self-end">Siguiente: Elegir Plan <i class="bi bi-arrow-right ms-1"></i></button>
            </section>
            <section data-wizard-step="1" class="card border-0 shadow-sm rounded-4 p-4 p-md-5" hidden>
                <h2 class="h4 mb-4" tabindex="-1">Elige tu plan</h2>
                <div class="row g-3">
                    <?php foreach ($planes as $indice => $plan): ?>
                    <div class="col-md-6">
                        <label class="plan-card d-block h-100 border rounded-4 p-4 <?= $indice === 0 ? 'active-plan' : '' ?>">
                            <input type="radio" name="plan_solicitado" value="<?= $escape($plan['codigo_plan']) ?>" data-monto="<?= $escape($plan['monto']) ?>" data-nombre="<?= $escape($plan['nombre']) ?>" class="form-check-input me-2" <?= $indice === 0 ? 'checked' : '' ?> required>
                            <span class="fw-bold"><?= $escape($plan['nombre']) ?></span>
                            <span class="d-block h2 my-3">Bs <?= number_format((float)$plan['monto'], 2) ?> <small class="fs-6 text-muted">/ <?= (int)$plan['meses_duracion'] ?> mes(es)</small></span>
                            <span class="text-muted"><?= $escape($plan['descripcion']) ?></span>
                        </label>
                    </div>
                    <?php endforeach; ?>
                    <?php if (!$planes): ?><p class="text-muted">No hay planes disponibles en este momento.</p><?php endif; ?>
                </div>
                <div class="d-flex justify-content-between gap-2 mt-4">
                    <button type="button" data-back class="btn btn-outline-secondary rounded-pill">Atrás</button>
                    <button type="button" data-next class="btn btn-success rounded-pill">Siguiente: Realizar Pago</button>
                </div>
            </section>
            <section data-wizard-step="2" class="card border-0 shadow-sm rounded-4 p-4 p-md-5" hidden>
                <h2 class="h4 mb-3" tabindex="-1">Forma de pago y confirmación</h2>
                <p class="fs-5 mb-4">Monto a pagar: <strong id="montoPlan" class="text-success" aria-live="polite">Bs <?= number_format((float)($planes[0]['monto'] ?? 0), 2) ?></strong> · <span id="nombrePlan"><?= $escape($planes[0]['nombre'] ?? '') ?></span></p>

                <div class="mb-4">
                    <label class="form-label fw-bold d-block mb-2">Selecciona cómo deseas realizar el pago *</label>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="p-3 border rounded-4 d-flex align-items-center gap-3 cursor-pointer w-100 shadow-sm h-100 bg-white" id="labelMetodoQr">
                                <input type="radio" name="metodo_pago" value="QR / Transferencia" class="form-check-input flex-shrink-0" checked id="radioPagoQr">
                                <div>
                                    <strong class="d-block text-dark"><i class="bi bi-qr-code-scan text-success me-1"></i> QR / Transferencia Bancaria</strong>
                                    <small class="text-muted extra-small">Escanea el QR o transfiere y adjunta tu comprobante</small>
                                </div>
                            </label>
                        </div>
                        <div class="col-md-6">
                            <label class="p-3 border rounded-4 d-flex align-items-center gap-3 cursor-pointer w-100 shadow-sm h-100 bg-white" id="labelMetodoEfectivo">
                                <input type="radio" name="metodo_pago" value="EFECTIVO" class="form-check-input flex-shrink-0" id="radioPagoEfectivo">
                                <div>
                                    <strong class="d-block text-dark"><i class="bi bi-cash-stack text-success me-1"></i> Pago en Efectivo</strong>
                                    <small class="text-muted extra-small">Paga en nuestra oficina o a un asesor en tu local</small>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Bloque 1: QR y Transferencia Digital -->
                <div id="seccionPagoDigital">
                    <div class="row g-4 align-items-center mb-4">
                        <div class="col-md-6 text-center">
                            <?php if ($qrSrc !== null): ?>
                                <div class="bg-white p-2 d-inline-block rounded-4 border shadow-sm">
                                    <img src="<?= $escape($qrSrc) ?>" class="img-fluid rounded-3" style="max-height: 240px;" alt="QR institucional de recaudación BeniTurs">
                                </div>
                                <small class="d-block text-muted mt-2">Escanea desde la app de tu banco</small>
                            <?php else: ?>
                                <p class="alert alert-warning mb-0">El QR de pago todavía no está disponible.</p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <h3 class="h6 fw-bold">Cuenta de recaudación · Trinidad</h3>
                            <?php if ($cobro['banco'] && $cobro['titular'] && $cobro['cuenta']): ?>
                                <dl class="mb-0 small">
                                    <dt>Banco</dt><dd class="text-dark fw-semibold"><?= $escape($cobro['banco']) ?></dd>
                                    <dt>Titular</dt><dd class="text-dark fw-semibold"><?= $escape($cobro['titular']) ?></dd>
                                    <dt>Número de cuenta</dt><dd class="text-dark fw-semibold"><?= $escape($cobro['cuenta']) ?></dd>
                                </dl>
                            <?php else: ?>
                                <p class="text-muted small">Los datos bancarios todavía no están disponibles.</p>
                            <?php endif; ?>
                            <p class="small text-muted mt-3 mb-0">Verifica el titular e ingresa el monto de tu plan antes de confirmar la transferencia.</p>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="numeroComprobante" class="form-label">Número de operación (opcional)</label>
                        <input id="numeroComprobante" name="numero_comprobante" class="form-control" maxlength="100" placeholder="Ej. 18274921">
                    </div>
                    <div class="mb-3">
                        <label for="comprobante" class="form-label">Foto o captura del comprobante *</label>
                        <input id="comprobante" name="comprobante" type="file" accept="image/jpeg,image/png,image/webp" class="form-control" required aria-describedby="comprobanteAyuda">
                        <small id="comprobanteAyuda" class="text-muted">JPG, PNG o WEBP. Máximo 5 MB.</small>
                    </div>
                </div>

                <!-- Bloque 2: Pago en Efectivo -->
                <div id="seccionPagoEfectivo" class="d-none">
                    <div class="alert alert-success-subtle border border-success-subtle rounded-4 p-4 mb-4">
                        <div class="d-flex align-items-start gap-3">
                            <i class="bi bi-cash-coin fs-1 text-success flex-shrink-0"></i>
                            <div>
                                <h3 class="h5 fw-bold text-success-emphasis mb-2">Pago Presencial en Efectivo</h3>
                                <p class="small text-muted mb-3">Tu solicitud quedará registrada en el sistema. Puedes cancelar en nuestras oficinas de BeniTurs en Trinidad o coordinar para que un promotor pase por tu negocio.</p>
                                <div class="bg-white p-3 rounded-3 border border-success-subtle">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                        <div>
                                            <span class="text-muted small d-block">Monto a cancelar en efectivo:</span>
                                            <strong class="h4 text-success mb-0" id="montoEfectivoTexto">Bs <?= number_format((float)($planes[0]['monto'] ?? 0), 2) ?></strong>
                                        </div>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fw-semibold">
                                            <i class="bi bi-clock-history me-1"></i> Cobro presencial
                                        </span>
                                    </div>
                                </div>
                                <small class="text-muted d-block mt-2"><i class="bi bi-info-circle me-1"></i>Al recibir tu pago en efectivo, el administrador te entregará tu recibo y tus credenciales de acceso de forma inmediata.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap justify-content-between gap-2 mt-4">
                    <button type="button" data-back class="btn btn-outline-secondary rounded-pill px-4">Atrás</button>
                    <button id="btnSubmitSolicitud" type="submit" class="btn btn-success rounded-pill px-5 fw-bold shadow-sm">Enviar Solicitud y Comprobante</button>
                </div>
            </section>
        </form>
        <noscript><p class="alert alert-warning mt-3">Activa JavaScript para completar los tres pasos.</p></noscript>
    </div></div>
</div>
