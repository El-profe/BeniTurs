<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
<div class="container py-2"><div class="row justify-content-center"><div class="col-xl-10">
    <div class="admin-page-hero mb-4 d-flex justify-content-between align-items-center gap-3 flex-wrap">
        <div class="d-flex align-items-center gap-3"><span class="admin-page-hero-icon"><i class="bi bi-plus-circle-fill"></i></span><div><h1 class="h4 fw-bold mb-1">Crear una nueva ficha</h1><p class="text-muted small mb-0">Prepara una ficha clara, atractiva y fácil de encontrar para tus visitantes.</p></div></div>
        <a href="<?= htmlspecialchars($baseUrl) ?>/admin/lugares" class="btn btn-light btn-sm rounded-pill px-3 fw-semibold"><i class="bi bi-arrow-left me-1"></i> Volver al directorio</a>
    </div>
    <form action="<?= htmlspecialchars($baseUrl) ?>/admin/lugares/guardar" method="POST" enctype="multipart/form-data" class="card admin-form-card rounded-4 p-4 p-md-5 bg-white">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>"><input type="hidden" name="foto_portada" id="fotoPortada" value="0">
        <?php if (!empty($error)): ?><div class="alert alert-danger rounded-3 small mb-4"><i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <div class="d-flex justify-content-between align-items-center mb-4 gap-3 flex-wrap"><div><h2 class="h5 fw-bold text-dark mb-1">Información de la ficha</h2><p class="text-muted small mb-0">Los campos con asterisco son obligatorios.</p></div><span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2"><i class="bi bi-shield-check me-1"></i>Publicación segura</span></div>
        <div class="row g-3">
            <div class="col-12"><div class="admin-form-section-title"><i class="bi bi-card-text me-1"></i> Identidad y clasificación</div></div>
            <div class="col-md-8"><label class="form-label fw-semibold small">Nombre del lugar o establecimiento *</label><input type="text" name="nombre" class="form-control" placeholder="Ej. Museo Ictícola del Beni" required></div>
            <div class="col-md-4"><label class="form-label fw-semibold small">Municipio del Beni *</label><select name="id_municipio" class="form-select" required><?php foreach ($municipios as $m): ?><option value="<?= $m['id_municipio'] ?>"><?= htmlspecialchars($m['nombre']) ?> (<?= htmlspecialchars($m['provincia']) ?>)</option><?php endforeach; ?></select></div>
            <div class="col-12"><label class="form-label fw-semibold small d-block">Tipo de ficha *</label><div class="choice-grid choice-grid-two"><label class="choice-card"><input type="radio" name="tipo_lugar" value="PUBLICO" checked><span class="choice-card-icon"><i class="bi bi-tree-fill"></i></span><span><strong>Público</strong><small>Atractivo gratuito</small></span></label><label class="choice-card"><input type="radio" name="tipo_lugar" value="COMERCIAL"><span class="choice-card-icon"><i class="bi bi-shop"></i></span><span><strong>Comercial</strong><small>Negocio o servicio</small></span></label></div></div>
            <div class="col-12 d-none" id="seccionAprovisionamientoComercial">
                <div class="card border border-success-subtle bg-success-subtle bg-opacity-10 rounded-4 p-3 p-md-4 shadow-sm">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" role="switch" name="crear_cuenta" id="switchCrearCuenta" value="1" checked>
                        <label class="form-check-label fw-bold text-dark fs-6" for="switchCrearCuenta">
                            <i class="bi bi-person-check-fill text-success me-1"></i> Aprovisionar cuenta comercial y registrar cobro inicial de inmediato
                        </label>
                    </div>
                    <p class="small text-muted mb-3">Ideal si el comerciante está pagando en efectivo o por QR directo en tu oficina. Se generará su usuario, clave y vigencia en un solo paso.</p>
                    <div class="row g-3" id="camposAprovisionamiento">
                        <!-- Campos ocultos de plan y monto -->
                        <input type="hidden" name="tipo_plan_modalidad" id="lugar_tipo_plan_modalidad" value="ESTANDAR">
                        <input type="hidden" name="codigo_plan" id="lugar_codigo_plan" value="MENSUAL">
                        <input type="hidden" name="meses_duracion" id="lugar_meses_duracion" value="1">
                        <input type="hidden" name="id_tarifa" id="lugar_id_tarifa" value="<?= (int)($tarifaVigente['id_tarifa'] ?? 1) ?>">

                        <!-- Botonera de Selección de Modo de Plan -->
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                                <label class="form-label fw-bold small text-dark mb-0">
                                    <i class="bi bi-calendar-check text-success me-1"></i>Plan de Membresía & Periodo Inicial *
                                </label>
                                <span class="badge bg-light text-muted border">Tarifa Base: Bs <?= number_format((float)($tarifaVigente['monto_mensual'] ?? 250.00), 2) ?>/mes</span>
                            </div>

                            <div class="btn-group w-100 p-1 bg-white rounded-4 border mb-3" role="group">
                                <button type="button" class="btn btn-sm rounded-pill fw-semibold btn-modo-lugar active" id="btnModoLugarEstandar" onclick="seleccionarModoPlanLugar('ESTANDAR')">
                                    <i class="bi bi-grid-fill me-1"></i> Planes Predefinidos
                                </button>
                                <button type="button" class="btn btn-sm rounded-pill fw-semibold btn-modo-lugar" id="btnModoLugarPersonalizado" onclick="seleccionarModoPlanLugar('PERSONALIZADO')">
                                    <i class="bi bi-star-fill text-warning me-1"></i> Plan Personalizado (A Medida)
                                </button>
                            </div>

                            <!-- Sección A: Planes Rápidos Predefinidos -->
                            <div id="seccion-lugar-planes-estandar">
                                <div class="row g-2">
                                    <div class="col-6 col-md-3">
                                        <div class="card card-plan-lugar h-100 p-2 text-center border-2 cursor-pointer active" id="cardPlanLugar1" onclick="elegirPlanEstandarLugar(1, 250.00, 1, 0, 'MENSUAL')">
                                            <span class="badge bg-light text-muted extra-small mb-1">Básico</span>
                                            <div class="fw-bold text-dark small">1 Mes</div>
                                            <span class="text-success fw-bold small">Bs 250</span>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="card card-plan-lugar h-100 p-2 text-center border-2 cursor-pointer" id="cardPlanLugar3" onclick="elegirPlanEstandarLugar(3, 712.50, 1, 37.50, 'MENSUAL')">
                                            <span class="badge bg-info-subtle text-info-emphasis extra-small mb-1">Trimestral</span>
                                            <div class="fw-bold text-dark small">3 Meses</div>
                                            <span class="text-success fw-bold small">Bs 712.50</span>
                                            <small class="text-success fw-semibold extra-small d-block">Ahorra 5%</small>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="card card-plan-lugar h-100 p-2 text-center border-2 cursor-pointer" id="cardPlanLugar6" onclick="elegirPlanEstandarLugar(6, 1350.00, 1, 150.00, 'MENSUAL')">
                                            <span class="badge bg-primary-subtle text-primary extra-small mb-1">Semestral</span>
                                            <div class="fw-bold text-dark small">6 Meses</div>
                                            <span class="text-success fw-bold small">Bs 1,350</span>
                                            <small class="text-success fw-semibold extra-small d-block">Ahorra 10%</small>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="card card-plan-lugar h-100 p-2 text-center border-2 cursor-pointer" id="cardPlanLugar12" onclick="elegirPlanEstandarLugar(12, 2500.00, 2, 500.00, 'ANUAL')">
                                            <span class="badge bg-warning text-dark fw-bold extra-small mb-1">★ Anual</span>
                                            <div class="fw-bold text-dark small">12 Meses</div>
                                            <span class="text-success fw-bold small">Bs 2,500</span>
                                            <small class="text-success fw-bold extra-small d-block">Ahorra Bs 500</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Sección B: Plan Personalizado (Meses libres + Descuento) -->
                            <div id="seccion-lugar-plan-personalizado" class="p-3 rounded-4 border border-2 border-warning-subtle bg-white shadow-xs" style="display: none;">
                                <div class="row g-2 align-items-center">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold extra-small text-dark mb-1">Cantidad de Meses contratados:</label>
                                        <div class="input-group">
                                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="cambiarMesesRelativoLugar(-1)"><i class="bi bi-dash-lg"></i></button>
                                            <input type="number" id="inputMesesLugar" min="1" max="120" value="5" class="form-control form-control-sm text-center fw-bold text-primary fs-5" oninput="recalcularPlanPersonalizadoLugar()">
                                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="cambiarMesesRelativoLugar(1)"><i class="bi bi-plus-lg"></i></button>
                                        </div>
                                        <div class="d-flex gap-1 flex-wrap mt-1">
                                            <button type="button" class="btn btn-light btn-sm border py-0 px-2 extra-small" onclick="fijarMesesLugar(2)">2m</button>
                                            <button type="button" class="btn btn-light btn-sm border py-0 px-2 extra-small" onclick="fijarMesesLugar(4)">4m</button>
                                            <button type="button" class="btn btn-warning btn-sm border border-warning py-0 px-2 extra-small fw-bold" onclick="fijarMesesLugar(5)">5 meses</button>
                                            <button type="button" class="btn btn-light btn-sm border py-0 px-2 extra-small" onclick="fijarMesesLugar(7)">7m</button>
                                            <button type="button" class="btn btn-light btn-sm border py-0 px-2 extra-small" onclick="fijarMesesLugar(8)">8m</button>
                                            <button type="button" class="btn btn-light btn-sm border py-0 px-2 extra-small" onclick="fijarMesesLugar(10)">10m</button>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold extra-small text-dark mb-1">Descuento o Beneficio de Ahorro:</label>
                                        <select id="selectReglaLugar" class="form-select form-select-sm" onchange="recalcularPlanPersonalizadoLugar()">
                                            <option value="0">Sin descuento (Tarifa regular 100%)</option>
                                            <option value="5">5% de Descuento comercial</option>
                                            <option value="10" selected>10% de Descuento preferencial (Sugerido)</option>
                                            <option value="15">15% de Descuento fidelidad</option>
                                            <option value="20">20% de Descuento especial</option>
                                            <option value="mes_gratis">1 Mes de regalo (Descuenta 1 mes)</option>
                                            <option value="manual">Fijar importe pactado manualmente en Bs</option>
                                        </select>
                                        <small class="text-muted extra-small">Calcula el ahorro exacto en tiempo real.</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tarjeta Dinámica de Liquidación & Ahorro -->
                        <div class="col-12">
                            <div class="p-3 bg-white rounded-3 border">
                                <div class="row g-2 align-items-center">
                                    <div class="col-md-7">
                                        <div class="d-flex justify-content-between align-items-center mb-1 text-muted extra-small">
                                            <span>Precio regular (<span id="txt-lugar-calc-meses">5</span> meses × Bs <?= number_format((float)($tarifaVigente['monto_mensual'] ?? 250.00), 2) ?>):</span>
                                            <span class="text-decoration-line-through fw-bold" id="txt-lugar-precio-regular">Bs 1,250.00</span>
                                        </div>

                                        <!-- Badge destacado de ahorro -->
                                        <div id="caja-ahorro-lugar" class="alert alert-success py-2 px-3 mb-1 d-flex align-items-center gap-2 rounded-3 border border-success-subtle">
                                            <i class="bi bi-piggy-bank-fill fs-4 text-success flex-shrink-0"></i>
                                            <div>
                                                <div class="fw-bold text-success small" id="txt-lugar-ahorro-banner">
                                                    ¡El cliente se ahorra Bs 125.00 (10.0%)!
                                                </div>
                                                <div class="extra-small text-muted">
                                                    Equivale a pagar <strong class="text-success" id="txt-lugar-costo-mensual">Bs 225.00</strong> por mes.
                                                </div>
                                            </div>
                                        </div>

                                        <div class="extra-small text-muted mt-1">
                                            <i class="bi bi-calendar-range text-primary me-1"></i>
                                            <span id="txt-lugar-proyeccion">Vigencia inicial proyectada...</span>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label fw-bold extra-small text-dark mb-1">Importe Inicial a Cobrar (Bs) *</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light text-success fw-bold border-end-0">Bs</span>
                                            <input type="number" step="0.01" name="monto" id="inputLugarMonto" class="form-control fw-bold fs-5 text-success border-start-0 text-center" value="1125.00" required oninput="alCambiarMontoLugarManual()">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Método de Cobro y Nº Comprobante -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-dark">Método de Cobro *</label>
                            <select name="metodo_pago" class="form-select form-select-sm">
                                <option value="EFECTIVO" selected>💵 Efectivo (Cobrado en mano)</option>
                                <option value="QR">📱 QR Simple</option>
                                <option value="TRANSFERENCIA">🏦 Transferencia Bancaria</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-dark">Nº Recibo / Talonario</label>
                            <input type="text" name="numero_comprobante" class="form-control form-control-sm" placeholder="Ej. REC-001 (Opcional)">
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12"><label class="form-label fw-semibold small d-block">Categoría *</label><div class="choice-grid choice-grid-categories"><?php foreach ($categorias as $indice => $c): ?><label class="choice-card choice-card-category"><input type="radio" name="id_categoria" value="<?= (int)$c['id_categoria'] ?>" <?= $indice === 0 ? 'required' : '' ?>><span class="choice-card-icon"><i class="bi <?= htmlspecialchars($c['icono']) ?>"></i></span><span><strong><?= htmlspecialchars($c['nombre']) ?></strong><small><?= htmlspecialchars($c['tipo_defecto']) ?></small></span></label><?php endforeach; ?></div></div>
            <div class="col-md-6"><label class="form-label fw-semibold small">Horario de atención</label><input type="text" name="horario_atencion" class="form-control" placeholder="Ej. Lunes a viernes, 08:00 a 18:00"></div>
            <div class="col-md-6"><label class="form-label fw-semibold small">Descripción completa *</label><textarea name="descripcion" rows="2" class="form-control" placeholder="Qué ofrece, historia o servicios destacados..." required></textarea></div>
            <div class="col-12"><div class="admin-form-section-title mt-2"><i class="bi bi-images me-1"></i> Galería y portada</div><p class="small text-muted mb-2">Puedes subir hasta seis imágenes. Haz clic en una imagen para elegir la portada.</p><label for="fotografias" class="image-upload-zone"><i class="bi bi-cloud-arrow-up fs-3"></i><strong>Seleccionar imágenes</strong><small>JPG, PNG o WEBP · máximo 5 MB cada una</small></label><input id="fotografias" type="file" name="fotografias[]" class="d-none" accept="image/jpeg,image/png,image/webp" multiple><div id="previsualizacionFotos" class="image-preview-grid mt-3" aria-live="polite"></div></div>
            <div class="col-12"><div class="admin-form-section-title mt-2"><i class="bi bi-geo-alt me-1"></i> Ubicación y contacto</div></div>
            <div class="col-md-6"><label class="form-label fw-semibold small">Dirección exacta *</label><input type="text" name="direccion" class="form-control" placeholder="Ej. Av. Dr. Antonio Vaca Díez" required></div>
            <div class="col-md-6"><label class="form-label fw-semibold small">Referencia de ubicación</label><input type="text" name="referencia_ubicacion" class="form-control" placeholder="Ej. Frente a la plaza principal"></div>
            <div class="col-12"><label class="form-label fw-semibold small d-block">Coordenadas GPS</label><div class="location-mode-tabs" role="tablist"><button type="button" class="active" data-location-mode="manual"><i class="bi bi-keyboard me-1"></i>Escribir coordenadas</button><button type="button" data-location-mode="mapa"><i class="bi bi-map me-1"></i>Buscar en el mapa</button></div><div id="ubicacionManual" class="mt-3"><input type="text" id="coordenadasGps" name="coordenadas_gps" class="form-control" placeholder="Ej. -14.8333, -64.9000"><small class="text-muted">Formato: latitud, longitud.</small></div><div id="ubicacionMapa" class="d-none mt-3"><div id="mapaNuevaFicha" class="location-picker-map"></div><small id="estadoMapaNuevaFicha" class="text-muted d-block mt-2">Haz clic en el mapa para marcar la ubicación exacta.</small></div></div>
            <div class="col-md-4"><label class="form-label fw-semibold small">WhatsApp</label><input type="text" name="whatsapp_contacto" class="form-control" placeholder="Ej. 59170000000"></div>
            <div class="col-md-4"><label class="form-label fw-semibold small">Teléfono</label><input type="text" name="telefono_contacto" class="form-control" placeholder="Ej. 34620000"></div>
            <div class="col-md-4"><label class="form-label fw-semibold small">Email de contacto</label><input type="email" name="email_contacto" class="form-control" placeholder="contacto@lugar.bo"></div>
        </div>
        <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center gap-3 flex-wrap"><small class="text-muted"><i class="bi bi-info-circle me-1"></i>La ficha se crea aprobada y habilitada.</small><button type="submit" class="btn btn-success rounded-pill px-5 fw-bold shadow-sm"><i class="bi bi-save me-1"></i>Guardar ficha</button></div>
    </form>
</div></div></div>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script src="<?= htmlspecialchars($baseUrl) ?>/assets/js/shared/compresor-imagen.js?v=<?= filemtime(dirname(__DIR__, 4) . '/public/assets/js/shared/compresor-imagen.js') ?>"></script>
<script src="<?= htmlspecialchars($baseUrl) ?>/assets/js/admin/crear-lugar.js?v=<?= filemtime(dirname(__DIR__, 4) . '/public/assets/js/admin/crear-lugar.js') ?>" defer></script>

<style>
.card-plan-lugar {
    transition: all 0.2s ease-in-out;
    background: #fff;
    border-color: #dee2e6;
}
.card-plan-lugar:hover {
    border-color: #198754;
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0,0,0,0.05);
}
.card-plan-lugar.active {
    border-color: #198754 !important;
    background-color: rgba(25, 135, 84, 0.05);
}
.btn-modo-lugar.active {
    background-color: #ffffff !important;
    color: #198754 !important;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
}
</style>

<script>
const TARIFA_BASE_LUGAR = <?= (float)($tarifaVigente['monto_mensual'] ?? 250.00) ?>;
let modoPlanLugar = 'ESTANDAR';
let mesesPlanLugar = 1;

function seleccionarModoPlanLugar(modo) {
    modoPlanLugar = modo;
    document.getElementById('lugar_tipo_plan_modalidad').value = modo;

    const btnEst = document.getElementById('btnModoLugarEstandar');
    const btnPer = document.getElementById('btnModoLugarPersonalizado');
    const secEst = document.getElementById('seccion-lugar-planes-estandar');
    const secPer = document.getElementById('seccion-lugar-plan-personalizado');

    if (modo === 'ESTANDAR') {
        btnEst.classList.add('active');
        btnPer.classList.remove('active');
        secEst.style.display = 'block';
        secPer.style.display = 'none';
        elegirPlanEstandarLugar(1, TARIFA_BASE_LUGAR, 1, 0, 'MENSUAL');
    } else {
        btnPer.classList.add('active');
        btnEst.classList.remove('active');
        secEst.style.display = 'none';
        secPer.style.display = 'block';
        document.querySelectorAll('.card-plan-lugar').forEach(c => c.classList.remove('active'));
        document.getElementById('lugar_codigo_plan').value = 'PERSONALIZADO';
        fijarMesesLugar(5);
    }
}

function elegirPlanEstandarLugar(meses, monto, idTarifa, ahorro, codigoPlan) {
    mesesPlanLugar = meses;
    document.querySelectorAll('.card-plan-lugar').forEach(c => c.classList.remove('active'));
    document.getElementById('cardPlanLugar' + meses)?.classList.add('active');

    document.getElementById('lugar_meses_duracion').value = meses;
    document.getElementById('lugar_id_tarifa').value = idTarifa;
    document.getElementById('lugar_codigo_plan').value = codigoPlan;
    document.getElementById('inputLugarMonto').value = Number(monto).toFixed(2);

    actualizarDesgloseAhorroLugar(meses, monto, ahorro);
}

function cambiarMesesRelativoLugar(delta) {
    const input = document.getElementById('inputMesesLugar');
    let val = parseInt(input.value || 1, 10) + delta;
    if (val < 1) val = 1;
    if (val > 120) val = 120;
    input.value = val;
    recalcularPlanPersonalizadoLugar();
}

function fijarMesesLugar(meses) {
    const input = document.getElementById('inputMesesLugar');
    input.value = meses;
    recalcularPlanPersonalizadoLugar();
}

function recalcularPlanPersonalizadoLugar() {
    const inputMeses = document.getElementById('inputMesesLugar');
    let meses = parseInt(inputMeses.value || 1, 10);
    if (isNaN(meses) || meses < 1) meses = 1;
    if (meses > 120) meses = 120;
    mesesPlanLugar = meses;

    document.getElementById('lugar_meses_duracion').value = meses;
    document.getElementById('lugar_codigo_plan').value = 'PERSONALIZADO';

    const regla = document.getElementById('selectReglaLugar').value;
    const precioRegular = meses * TARIFA_BASE_LUGAR;
    let montoFinal = precioRegular;
    let ahorro = 0;

    if (regla === 'manual') {
        montoFinal = parseFloat(document.getElementById('inputLugarMonto').value || 0);
        ahorro = Math.max(0, precioRegular - montoFinal);
    } else if (regla === 'mes_gratis') {
        ahorro = TARIFA_BASE_LUGAR;
        montoFinal = Math.max(0, precioRegular - ahorro);
    } else {
        const pct = parseFloat(regla);
        if (pct > 0) {
            ahorro = precioRegular * (pct / 100);
            montoFinal = precioRegular - ahorro;
        } else {
            ahorro = 0;
            montoFinal = precioRegular;
        }
    }

    document.getElementById('inputLugarMonto').value = Number(montoFinal).toFixed(2);
    actualizarDesgloseAhorroLugar(meses, montoFinal, ahorro);
}

function alCambiarMontoLugarManual() {
    if (modoPlanLugar === 'PERSONALIZADO') {
        document.getElementById('selectReglaLugar').value = 'manual';
    }
    const meses = mesesPlanLugar;
    const precioRegular = meses * TARIFA_BASE_LUGAR;
    const montoFinal = parseFloat(document.getElementById('inputLugarMonto').value || 0);
    const ahorro = Math.max(0, precioRegular - montoFinal);
    actualizarDesgloseAhorroLugar(meses, montoFinal, ahorro);
}

function actualizarDesgloseAhorroLugar(meses, monto, ahorro) {
    const precioRegular = meses * TARIFA_BASE_LUGAR;
    const ahorroReal = Math.max(0, ahorro);
    const porcentaje = precioRegular > 0 ? ((ahorroReal / precioRegular) * 100) : 0;
    const costoMensual = meses > 0 ? (monto / meses) : monto;

    document.getElementById('txt-lugar-calc-meses').textContent = meses;
    document.getElementById('txt-lugar-precio-regular').textContent = 'Bs ' + precioRegular.toLocaleString('es-BO', { minimumFractionDigits: 2 });
    document.getElementById('txt-lugar-costo-mensual').textContent = 'Bs ' + costoMensual.toLocaleString('es-BO', { minimumFractionDigits: 2 });

    const caja = document.getElementById('caja-ahorro-lugar');
    const banner = document.getElementById('txt-lugar-ahorro-banner');
    if (ahorroReal > 0) {
        caja.className = 'alert alert-success py-2 px-3 mb-1 d-flex align-items-center gap-2 rounded-3 border border-success-subtle';
        banner.innerHTML = `¡El cliente se ahorra Bs ${ahorroReal.toLocaleString('es-BO', { minimumFractionDigits: 2 })} (${porcentaje.toFixed(1)}%)!`;
    } else {
        caja.className = 'alert alert-light py-2 px-3 mb-1 d-flex align-items-center gap-2 rounded-3 border';
        banner.innerHTML = `Tarifa regular estándar (Bs ${TARIFA_BASE_LUGAR.toFixed(2)}/mes)`;
    }

    // Proyección de vigencia desde hoy
    const hoy = new Date();
    const fin = new Date(hoy);
    fin.setMonth(fin.getMonth() + meses);
    document.getElementById('txt-lugar-proyeccion').innerHTML = `Vigencia inicial: <strong>${hoy.toLocaleDateString('es-BO')}</strong> &rarr; <strong>${fin.toLocaleDateString('es-BO')}</strong> (${meses} mes(es) de cobertura)`;
}

document.addEventListener('DOMContentLoaded', () => {
    actualizarDesgloseAhorroLugar(1, TARIFA_BASE_LUGAR, 0);
});
</script>
