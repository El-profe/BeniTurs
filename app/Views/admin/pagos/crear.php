<div class="container py-3">
    <div class="row justify-content-center">
        <div class="col-lg-9 col-xl-8">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                <!-- Encabezado -->
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                    <div>
                        <h4 class="fw-bold text-dark mb-1">
                            <i class="bi bi-cash-stack text-success me-2"></i>Registrar Pago Comercial
                        </h4>
                        <small class="text-muted">El pago se guardará en estado PENDIENTE para su verificación administrativa</small>
                    </div>
                    <a href="<?= htmlspecialchars($baseUrl) ?>/admin/pagos" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                        <i class="bi bi-arrow-left me-1"></i> Volver a Pagos
                    </a>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger d-flex align-items-center gap-2 mb-4" role="alert">
                        <i class="bi bi-exclamation-octagon-fill fs-5"></i>
                        <div><?= htmlspecialchars($error) ?></div>
                    </div>
                <?php endif; ?>

                <?php if (!$tarifa): ?>
                    <div class="alert alert-warning mb-4">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> No se encontró una tarifa vigente en el sistema. Se utilizará la tarifa base de referencia (Bs 250.00/mes).
                    </div>
                <?php endif; ?>

                <form action="<?= htmlspecialchars($baseUrl) ?>/admin/pagos/guardar" method="POST" id="formPagoComercial">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <input type="hidden" name="id_tarifa" id="id_tarifa" value="<?= (int)($tarifa['id_tarifa'] ?? 1) ?>">
                    <input type="hidden" name="meses_duracion" id="meses_duracion" value="1">
                    <input type="hidden" name="tipo_plan_modalidad" id="tipo_plan_modalidad" value="ESTANDAR">

                    <div class="row g-3">
                        <!-- 1. Selección del Comercio -->
                        <div class="col-12">
                            <label class="form-label fw-bold small text-dark">
                                <i class="bi bi-shop me-1 text-primary"></i>Establecimiento Comercial *
                            </label>
                            <select name="id_lugar" id="pago-lugar" class="form-select form-select-lg fs-6" required>
                                <option value="">Seleccione el comercio afiliado en Trinidad...</option>
                                <?php foreach ($comercios as $c): ?>
                                    <?php 
                                        $esSeleccionado = ((int)$c['id_lugar'] === (int)$idLugarSeleccionado);
                                        $vencimiento = !empty($c['ultimo_vencimiento']) ? date('d/m/Y', strtotime($c['ultimo_vencimiento'])) : '';
                                        $vencimientoRaw = $c['ultimo_vencimiento'] ?? '';
                                    ?>
                                    <option value="<?= $c['id_lugar'] ?>" 
                                            data-plan="<?= htmlspecialchars($c['plan_solicitado'] ?? '') ?>"
                                            data-vencimiento="<?= htmlspecialchars($vencimientoRaw) ?>"
                                            data-vencimiento-fmt="<?= htmlspecialchars($vencimiento) ?>"
                                            <?= $esSeleccionado ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($c['nombre']) ?> <?= $vencimiento ? " (Vence: {$vencimiento})" : " (Sin vigencia previa)" ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            
                            <!-- Banner contextual de vigencia actual del comercio -->
                            <div id="info-vigencia-comercio" class="mt-2" style="display: none;"></div>
                        </div>

                        <!-- 2. Modalidad de Contratación: Planes Estándar vs Plan Personalizado -->
                        <div class="col-12 mt-4">
                            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                                <label class="form-label fw-bold small text-dark mb-0">
                                    <i class="bi bi-calendar-check me-1 text-success"></i>Modalidad de Cobertura y Plan *
                                </label>
                                <span class="badge bg-light text-muted border">Tarifa Base: Bs <?= number_format((float)($tarifa['monto_mensual'] ?? 250.00), 2) ?> / mes</span>
                            </div>

                            <!-- Botonera de Selección de Modo -->
                            <div class="btn-group w-100 p-1 bg-light rounded-4 border mb-3" role="group">
                                <button type="button" class="btn btn-sm rounded-pill fw-semibold btn-modo-plan active" id="btnModoEstandar" onclick="seleccionarModoPlan('ESTANDAR')">
                                    <i class="bi bi-grid-fill me-1"></i> Planes Predefinidos
                                </button>
                                <button type="button" class="btn btn-sm rounded-pill fw-semibold btn-modo-plan" id="btnModoPersonalizado" onclick="seleccionarModoPlan('PERSONALIZADO')">
                                    <i class="bi bi-star-fill text-warning me-1"></i> Plan Personalizado (A Medida)
                                </button>
                            </div>

                            <!-- Sección A: Planes Rápidos Predefinidos -->
                            <div id="seccion-planes-estandar">
                                <div class="row g-2">
                                    <!-- Plan 1 Mes -->
                                    <div class="col-6 col-md-3">
                                        <div class="card card-plan h-100 p-3 text-center border-2 cursor-pointer active" id="cardPlan1" onclick="elegirPlanEstandar(1, 250.00, 1, 0)">
                                            <span class="badge bg-light text-muted extra-small mb-1">Básico</span>
                                            <h6 class="fw-bold text-dark mb-1">1 Mes</h6>
                                            <span class="text-success fw-bold fs-6">Bs 250</span>
                                            <small class="text-muted extra-small d-block mt-1">Precio regular</small>
                                        </div>
                                    </div>
                                    <!-- Plan 3 Meses -->
                                    <div class="col-6 col-md-3">
                                        <div class="card card-plan h-100 p-3 text-center border-2 cursor-pointer" id="cardPlan3" onclick="elegirPlanEstandar(3, 712.50, 1, 37.50)">
                                            <span class="badge bg-info-subtle text-info-emphasis extra-small mb-1">Trimestral</span>
                                            <h6 class="fw-bold text-dark mb-1">3 Meses</h6>
                                            <span class="text-success fw-bold fs-6">Bs 712.50</span>
                                            <small class="text-success fw-semibold extra-small d-block mt-1"><i class="bi bi-tag-fill me-1"></i>Ahorra Bs 37.50 (5%)</small>
                                        </div>
                                    </div>
                                    <!-- Plan 6 Meses -->
                                    <div class="col-6 col-md-3">
                                        <div class="card card-plan h-100 p-3 text-center border-2 cursor-pointer" id="cardPlan6" onclick="elegirPlanEstandar(6, 1350.00, 1, 150.00)">
                                            <span class="badge bg-primary-subtle text-primary extra-small mb-1">Semestral</span>
                                            <h6 class="fw-bold text-dark mb-1">6 Meses</h6>
                                            <span class="text-success fw-bold fs-6">Bs 1,350</span>
                                            <small class="text-success fw-semibold extra-small d-block mt-1"><i class="bi bi-tag-fill me-1"></i>Ahorra Bs 150 (10%)</small>
                                        </div>
                                    </div>
                                    <!-- Plan 12 Meses (Anual) -->
                                    <div class="col-6 col-md-3">
                                        <div class="card card-plan h-100 p-3 text-center border-2 cursor-pointer position-relative" id="cardPlan12" onclick="elegirPlanEstandar(12, 2500.00, 2, 500.00)">
                                            <span class="badge bg-warning text-dark fw-bold extra-small mb-1">★ Más Popular</span>
                                            <h6 class="fw-bold text-dark mb-1">12 Meses</h6>
                                            <span class="text-success fw-bold fs-6">Bs 2,500</span>
                                            <small class="text-success fw-bold extra-small d-block mt-1"><i class="bi bi-gift-fill me-1"></i>Ahorra Bs 500 (2m gratis)</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Sección B: Apartado de Plan Personalizado (Dinámico) -->
                            <div id="seccion-plan-personalizado" class="p-3 p-md-4 rounded-4 border border-2 border-warning-subtle bg-warning-subtle bg-opacity-10" style="display: none;">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <span class="badge bg-warning text-dark px-2 py-1 rounded-pill fw-bold">
                                        <i class="bi bi-sliders me-1"></i> Configuración a Medida
                                    </span>
                                    <span class="small text-muted">Ajuste los meses pactados con el cliente y elija la política de descuento</span>
                                </div>

                                <div class="row g-3 align-items-center">
                                    <!-- Selector de Cantidad de Meses -->
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-dark mb-1">
                                            Cantidad de Meses contratados:
                                        </label>
                                        <div class="input-group input-group-lg">
                                            <button type="button" class="btn btn-outline-secondary px-3" onclick="cambiarMesesRelativo(-1)" title="Restar 1 mes">
                                                <i class="bi bi-dash-lg"></i>
                                            </button>
                                            <input type="number" id="inputMesesPersonalizado" min="1" max="120" value="5" class="form-control text-center fw-bold fs-4 text-primary" oninput="recalcularPlanPersonalizado()">
                                            <button type="button" class="btn btn-outline-secondary px-3" onclick="cambiarMesesRelativo(1)" title="Sumar 1 mes">
                                                <i class="bi bi-plus-lg"></i>
                                            </button>
                                        </div>
                                        
                                        <!-- Accesos directos a duraciones comunes -->
                                        <div class="d-flex gap-1 flex-wrap mt-2">
                                            <span class="extra-small text-muted align-self-center me-1">Rápidos:</span>
                                            <button type="button" class="btn btn-light btn-sm border py-0 px-2 extra-small" onclick="fijarMeses(2)">2m</button>
                                            <button type="button" class="btn btn-light btn-sm border py-0 px-2 extra-small" onclick="fijarMeses(4)">4m</button>
                                            <button type="button" class="btn btn-warning btn-sm border border-warning py-0 px-2 extra-small fw-bold" onclick="fijarMeses(5)">5 meses</button>
                                            <button type="button" class="btn btn-light btn-sm border py-0 px-2 extra-small" onclick="fijarMeses(7)">7m</button>
                                            <button type="button" class="btn btn-light btn-sm border py-0 px-2 extra-small" onclick="fijarMeses(8)">8m</button>
                                            <button type="button" class="btn btn-light btn-sm border py-0 px-2 extra-small" onclick="fijarMeses(10)">10m</button>
                                        </div>
                                    </div>

                                    <!-- Selector de Descuento / Ahorro Aplicado -->
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-dark mb-1">
                                            Descuento o Beneficio de Ahorro:
                                        </label>
                                        <select id="selectTipoDescuento" class="form-select form-select-lg fs-6" onchange="cambiarReglaDescuento()">
                                            <option value="0">Sin descuento (Tarifa regular 100%)</option>
                                            <option value="5">5% de Descuento comercial</option>
                                            <option value="10" selected>10% de Descuento preferencial (Sugerido)</option>
                                            <option value="15">15% de Descuento fidelidad</option>
                                            <option value="20">20% de Descuento especial</option>
                                            <option value="mes_gratis">1 Mes de regalo (Descuenta 1 mensualidad)</option>
                                            <option value="manual">Fijar importe acordado manualmente en Bs</option>
                                        </select>
                                        <small class="text-muted extra-small d-block mt-1">El ahorro se calcula comparando contra la tarifa regular mensual.</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Panel Dinámico de Liquidación, Desglose de Ahorro y Proyección de Vigencia -->
                        <div class="col-12 mt-3">
                            <div class="card rounded-4 border p-3 p-md-4 bg-light shadow-xs">
                                <div class="row g-3 align-items-center">
                                    <!-- A la izquierda: Desglose numérico y tarjeta de ahorro -->
                                    <div class="col-lg-7">
                                        <div class="d-flex justify-content-between align-items-center mb-1 text-muted small">
                                            <span>Precio regular sin descuento (<span id="txt-calc-meses">5</span> meses × Bs <?= number_format((float)($tarifa['monto_mensual'] ?? 250.00), 2) ?>):</span>
                                            <span class="fw-semibold text-decoration-line-through" id="txt-precio-regular">Bs 1,250.00</span>
                                        </div>

                                        <!-- Tarjeta Destacada de Ahorro para el Cliente -->
                                        <div id="caja-ahorro-destacada" class="p-3 my-2 rounded-3 border d-flex align-items-center gap-3 bg-white">
                                            <div class="rounded-circle bg-success text-white p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                                                <i class="bi bi-piggy-bank-fill fs-4"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-success fs-5">
                                                    ¡El cliente se está ahorrando <span id="txt-ahorro-monto">Bs 125.00</span>!
                                                </div>
                                                <div class="extra-small text-muted mt-0">
                                                    Representa un <strong class="text-dark"><span id="txt-ahorro-porcentaje">10.0%</span> de ahorro</strong> sobre el costo mensual regular. 
                                                    Equivale a pagar solo <strong class="text-success"><span id="txt-costo-mensual">Bs 225.00</span></strong> por mes.
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Proyección de Vigencia -->
                                        <div class="d-flex align-items-center gap-2 mt-2 extra-small text-muted">
                                            <i class="bi bi-calendar2-range text-primary"></i>
                                            <span id="txt-proyeccion-vigencia">Proyectando fechas de vigencia...</span>
                                        </div>
                                    </div>

                                    <!-- A la derecha: Importe Final a Cobrar (editable) -->
                                    <div class="col-lg-5">
                                        <div class="p-3 bg-white rounded-3 border text-center">
                                            <label class="form-label fw-bold extra-small text-muted text-uppercase mb-1">
                                                Importe Final a Cobrar (Bs) *
                                            </label>
                                            <div class="input-group input-group-lg justify-content-center">
                                                <span class="input-group-text bg-light text-success fw-bold border-end-0">Bs</span>
                                                <input type="number" step="0.01" name="monto" id="inputMontoFinal" 
                                                       class="form-control text-center fw-bold fs-3 text-success border-start-0" 
                                                       value="1125.00" required oninput="alCambiarMontoManualmente()">
                                            </div>
                                            <small class="text-muted extra-small d-block mt-1">Monto acordado para el recibo</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 4. Fecha Declarada y Método de Pago -->
                        <div class="col-md-6 mt-3">
                            <label class="form-label fw-semibold small text-dark">
                                <i class="bi bi-calendar-event me-1 text-muted"></i>Fecha Declarada del Abono *
                            </label>
                            <input type="date" name="fecha_pago_declarada" id="inputFechaDeclarada" class="form-control" value="<?= date('Y-m-d') ?>" required onchange="actualizarProyeccionVigencia()">
                        </div>

                        <div class="col-md-6 mt-3">
                            <label class="form-label fw-semibold small text-dark">
                                <i class="bi bi-credit-card-2-front me-1 text-muted"></i>Método de Pago
                            </label>
                            <select name="metodo_pago" class="form-select">
                                <option value="Transferencia bancaria / QR">Transferencia bancaria / QR</option>
                                <option value="Depósito en ventanilla">Depósito en ventanilla</option>
                                <option value="Efectivo en oficina">Efectivo en oficina</option>
                            </select>
                        </div>

                        <!-- 5. Comprobante y Notas -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-dark">
                                <i class="bi bi-receipt me-1 text-muted"></i>N° de Comprobante o Transacción
                            </label>
                            <input type="text" name="numero_comprobante" class="form-control" placeholder="Ej. TRF-9823412 o Recibo #042">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-dark">
                                <i class="bi bi-chat-left-text me-1 text-muted"></i>Observaciones o Notas Internas
                            </label>
                            <input type="text" name="observaciones" id="inputObservaciones" class="form-control" placeholder="Banco emisor, acuerdo comercial o notas...">
                        </div>
                    </div>

                    <!-- Botón de Envío -->
                    <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <span class="text-muted small">
                            <i class="bi bi-info-circle me-1"></i>Al guardar, el pago quedará <strong>PENDIENTE</strong> para revisión y posterior confirmación.
                        </span>
                        <button <?= !$tarifa ? 'disabled' : '' ?> type="submit" class="btn btn-success btn-lg rounded-pill px-5 fw-bold shadow-sm">
                            <i class="bi bi-check-circle me-2"></i>Guardar Pago Pendiente
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.cursor-pointer { cursor: pointer; }
.card-plan {
    transition: all 0.2s ease-in-out;
    background: #fff;
    border-color: #dee2e6;
}
.card-plan:hover {
    border-color: #198754;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}
.card-plan.active {
    border-color: #198754 !important;
    background-color: rgba(25, 135, 84, 0.04);
}
.btn-modo-plan.active {
    background-color: #ffffff !important;
    color: #198754 !important;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
}
</style>

<script>
const TARIFA_BASE_MENSUAL = <?= (float)($tarifa['monto_mensual'] ?? 250.00) ?>;
let modoActual = 'ESTANDAR'; // 'ESTANDAR' o 'PERSONALIZADO'
let mesesSeleccionados = 1;
let reglaDescuentoManual = false;

document.addEventListener('DOMContentLoaded', () => {
    // Escuchar cambio de comercio para mostrar estado de vigencia y recalcular
    const selectLugar = document.getElementById('pago-lugar');
    if (selectLugar) {
        selectLugar.addEventListener('change', alCambiarComercio);
        if (selectLugar.value) {
            alCambiarComercio();
        }
    }

    // Si viene preseleccionado o solicitud previa anual
    const optSeleccionada = selectLugar?.selectedOptions[0];
    if (optSeleccionada && optSeleccionada.dataset.plan === 'ANUAL') {
        elegirPlanEstandar(12, 2500.00, 2, 500.00);
    } else {
        elegirPlanEstandar(1, TARIFA_BASE_MENSUAL, 1, 0);
    }
});

function alCambiarComercio() {
    const select = document.getElementById('pago-lugar');
    const opt = select.selectedOptions[0];
    const contenedorInfo = document.getElementById('info-vigencia-comercio');

    if (!opt || !opt.value) {
        contenedorInfo.style.display = 'none';
        contenedorInfo.innerHTML = '';
        actualizarProyeccionVigencia();
        return;
    }

    const vencimientoRaw = opt.dataset.vencimiento;
    const vencimientoFmt = opt.dataset.vencimientoFmt;

    if (vencimientoRaw) {
        const fechaVenc = new Date(vencimientoRaw + 'T00:00:00');
        const hoy = new Date();
        hoy.setHours(0,0,0,0);

        if (fechaVenc >= hoy) {
            contenedorInfo.innerHTML = `
                <div class="alert alert-success py-2 px-3 mb-0 small rounded-3 d-flex align-items-center gap-2">
                    <i class="bi bi-shield-check fs-5 text-success"></i>
                    <div>
                        <strong>Membresía activa al día:</strong> Vence el <strong>${vencimientoFmt}</strong>. 
                        Este pago sumará los meses contratados directamente a partir de esa fecha sin perder días.
                    </div>
                </div>
            `;
        } else {
            contenedorInfo.innerHTML = `
                <div class="alert alert-warning py-2 px-3 mb-0 small rounded-3 d-flex align-items-center gap-2">
                    <i class="bi bi-clock-history fs-5 text-warning-emphasis"></i>
                    <div>
                        <strong>Membresía vencida el ${vencimientoFmt}:</strong> 
                        Este pago reactivará la publicación a partir de la fecha declarada del abono.
                    </div>
                </div>
            `;
        }
    } else {
        contenedorInfo.innerHTML = `
            <div class="alert alert-info py-2 px-3 mb-0 small rounded-3 d-flex align-items-center gap-2">
                <i class="bi bi-star fs-5 text-info"></i>
                <div>
                    <strong>Primer periodo de membresía:</strong> La cobertura iniciará a partir de la fecha de pago declarada.
                </div>
            </div>
        `;
    }
    contenedorInfo.style.display = 'block';
    actualizarProyeccionVigencia();
}

/**
 * Alterna entre Planes Predefinidos y Plan Personalizado
 */
function seleccionarModoPlan(modo) {
    modoActual = modo;
    document.getElementById('tipo_plan_modalidad').value = modo;

    const btnEst = document.getElementById('btnModoEstandar');
    const btnPer = document.getElementById('btnModoPersonalizado');
    const secEst = document.getElementById('seccion-planes-estandar');
    const secPer = document.getElementById('seccion-plan-personalizado');

    if (modo === 'ESTANDAR') {
        btnEst.classList.add('active');
        btnPer.classList.remove('active');
        secEst.style.display = 'block';
        secPer.style.display = 'none';
        elegirPlanEstandar(1, TARIFA_BASE_MENSUAL, 1, 0);
    } else {
        btnPer.classList.add('active');
        btnEst.classList.remove('active');
        secEst.style.display = 'none';
        secPer.style.display = 'block';
        document.querySelectorAll('.card-plan').forEach(c => c.classList.remove('active'));
        
        // Cargar por defecto 5 meses como en la solicitud del usuario
        fijarMeses(5);
    }
}

/**
 * Selecciona uno de los planes estándar rápidos
 */
function elegirPlanEstandar(meses, monto, idTarifa, ahorro) {
    mesesSeleccionados = meses;
    reglaDescuentoManual = false;

    // Resaltar tarjeta seleccionada
    document.querySelectorAll('.card-plan').forEach(c => c.classList.remove('active'));
    document.getElementById('cardPlan' + meses)?.classList.add('active');

    // Asignar campos del formulario
    document.getElementById('meses_duracion').value = meses;
    document.getElementById('id_tarifa').value = idTarifa;
    document.getElementById('inputMontoFinal').value = Number(monto).toFixed(2);

    actualizarDesgloseAhorro(meses, monto, ahorro);
    actualizarProyeccionVigencia();
}

/**
 * Modifica la cantidad de meses en el plan personalizado
 */
function cambiarMesesRelativo(delta) {
    const input = document.getElementById('inputMesesPersonalizado');
    let val = parseInt(input.value || 1, 10) + delta;
    if (val < 1) val = 1;
    if (val > 120) val = 120;
    input.value = val;
    recalcularPlanPersonalizado();
}

function fijarMeses(meses) {
    const input = document.getElementById('inputMesesPersonalizado');
    input.value = meses;
    recalcularPlanPersonalizado();
}

function cambiarReglaDescuento() {
    reglaDescuentoManual = false;
    recalcularPlanPersonalizado();
}

/**
 * Calcula dinámicamente el precio regular, descuento, ahorro y nuevo importe en el Plan Personalizado
 */
function recalcularPlanPersonalizado() {
    const inputMeses = document.getElementById('inputMesesPersonalizado');
    let meses = parseInt(inputMeses.value || 1, 10);
    if (isNaN(meses) || meses < 1) meses = 1;
    if (meses > 120) meses = 120;
    mesesSeleccionados = meses;

    document.getElementById('meses_duracion').value = meses;

    const regla = document.getElementById('selectTipoDescuento').value;
    const precioRegular = meses * TARIFA_BASE_MENSUAL;
    let montoFinal = precioRegular;
    let ahorro = 0;

    if (regla === 'manual') {
        // En modo manual respetamos lo que el usuario tipee en inputMontoFinal
        const inputMonto = document.getElementById('inputMontoFinal');
        montoFinal = parseFloat(inputMonto.value || 0);
        ahorro = Math.max(0, precioRegular - montoFinal);
    } else if (regla === 'mes_gratis') {
        // Descuenta exactamente 1 mes regular
        ahorro = TARIFA_BASE_MENSUAL;
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

    document.getElementById('inputMontoFinal').value = Number(montoFinal).toFixed(2);
    actualizarDesgloseAhorro(meses, montoFinal, ahorro);
    actualizarProyeccionVigencia();
}

/**
 * Cuando el administrador edita manualmente el campo de Bs en modo personalizado
 */
function alCambiarMontoManualmente() {
    if (modoActual === 'PERSONALIZADO') {
        document.getElementById('selectTipoDescuento').value = 'manual';
    }
    const meses = mesesSeleccionados;
    const precioRegular = meses * TARIFA_BASE_MENSUAL;
    const montoFinal = parseFloat(document.getElementById('inputMontoFinal').value || 0);
    const ahorro = Math.max(0, precioRegular - montoFinal);

    actualizarDesgloseAhorro(meses, montoFinal, ahorro);
}

/**
 * Actualiza los elementos visuales de la tarjeta de Ahorro y Liquidación
 */
function actualizarDesgloseAhorro(meses, monto, ahorro) {
    const precioRegular = meses * TARIFA_BASE_MENSUAL;
    const ahorroReal = Math.max(0, ahorro);
    const porcentaje = precioRegular > 0 ? ((ahorroReal / precioRegular) * 100) : 0;
    const costoMensual = meses > 0 ? (monto / meses) : monto;

    document.getElementById('txt-calc-meses').textContent = meses;
    document.getElementById('txt-precio-regular').textContent = 'Bs ' + precioRegular.toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    document.getElementById('txt-ahorro-monto').textContent = 'Bs ' + ahorroReal.toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    document.getElementById('txt-ahorro-porcentaje').textContent = porcentaje.toFixed(1) + '%';
    document.getElementById('txt-costo-mensual').textContent = 'Bs ' + costoMensual.toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    const caja = document.getElementById('caja-ahorro-destacada');
    if (ahorroReal > 0) {
        caja.className = 'p-3 my-2 rounded-3 border d-flex align-items-center gap-3 bg-white border-success shadow-xs';
        caja.innerHTML = `
            <div class="rounded-circle bg-success text-white p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                <i class="bi bi-piggy-bank-fill fs-4"></i>
            </div>
            <div>
                <div class="fw-bold text-success fs-5">
                    ¡El cliente se está ahorrando Bs ${ahorroReal.toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}!
                </div>
                <div class="extra-small text-muted mt-0">
                    Ahorro del <strong class="text-dark">${porcentaje.toFixed(1)}%</strong> sobre la tarifa mensual regular. 
                    Equivale a pagar solo <strong class="text-success">Bs ${costoMensual.toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</strong> por mes.
                </div>
            </div>
        `;
    } else {
        caja.className = 'p-3 my-2 rounded-3 border d-flex align-items-center gap-3 bg-white border-secondary-subtle';
        caja.innerHTML = `
            <div class="rounded-circle bg-light text-muted p-2 d-flex align-items-center justify-content-center flex-shrink-0 border" style="width: 44px; height: 44px;">
                <i class="bi bi-receipt fs-4 text-muted"></i>
            </div>
            <div>
                <div class="fw-bold text-dark fs-6">
                    Tarifa regular estándar (Bs 250.00 / mes)
                </div>
                <div class="extra-small text-muted">
                    No se ha aplicado ningún descuento promocional en este plan.
                </div>
            </div>
        `;
    }
}

/**
 * Calcula y proyecta las fechas de inicio y vencimiento en tiempo real
 */
function actualizarProyeccionVigencia() {
    const select = document.getElementById('pago-lugar');
    const opt = select?.selectedOptions[0];
    const fechaDeclaradaStr = document.getElementById('inputFechaDeclarada')?.value || new Date().toISOString().slice(0, 10);
    const txtProyeccion = document.getElementById('txt-proyeccion-vigencia');

    const vencimientoRaw = opt?.dataset.vencimiento;
    const hoyStr = new Date().toISOString().slice(0, 10);

    let fechaInicio = fechaDeclaradaStr;
    let esRenovacion = false;

    if (vencimientoRaw && vencimientoRaw > hoyStr) {
        fechaInicio = vencimientoRaw;
        esRenovacion = true;
    }

    const dInicio = new Date(fechaInicio + 'T00:00:00');
    if (isNaN(dInicio.getTime())) {
        txtProyeccion.textContent = 'Fecha no válida';
        return;
    }

    // Sumar meses
    const dFin = new Date(dInicio);
    dFin.setMonth(dFin.getMonth() + mesesSeleccionados);

    const fmtInicio = dInicio.toLocaleDateString('es-BO');
    const fmtFin = dFin.toLocaleDateString('es-BO');

    if (esRenovacion) {
        txtProyeccion.innerHTML = `Renovación anticipada: <strong>${fmtInicio}</strong> &rarr; <strong>${fmtFin}</strong> (${mesesSeleccionados} mes(es) adicionados)`;
    } else {
        txtProyeccion.innerHTML = `Vigencia proyectada: <strong>${fmtInicio}</strong> &rarr; <strong>${fmtFin}</strong> (${mesesSeleccionados} mes(es) de cobertura)`;
    }
}
</script>