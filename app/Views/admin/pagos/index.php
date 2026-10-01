<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold text-dark mb-1">Control de Pagos y Vigencias</h3>
            <p class="text-muted small mb-0">Auditoría manual de transferencias bancarias y cobertura de mensualidades</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <!-- Filtros de Estado -->
            <div class="btn-group rounded-pill shadow-sm" role="group">
                <a href="<?= htmlspecialchars($baseUrl) ?>/admin/pagos" class="btn btn-sm <?= empty($filtroActual) ? 'btn-success fw-bold' : 'btn-light border' ?>">Todos</a>
                <a href="<?= htmlspecialchars($baseUrl) ?>/admin/pagos?estado=PENDIENTE" class="btn btn-sm <?= $filtroActual === 'PENDIENTE' ? 'btn-warning text-dark fw-bold' : 'btn-light border' ?>">Pendientes</a>
                <a href="<?= htmlspecialchars($baseUrl) ?>/admin/pagos?estado=CONFIRMADO" class="btn btn-sm <?= $filtroActual === 'CONFIRMADO' ? 'btn-success fw-bold' : 'btn-light border' ?>">Confirmados</a>
            </div>

            <a href="<?= htmlspecialchars($baseUrl) ?>/admin/pagos/crear" class="btn btn-warning rounded-pill px-4 fw-bold text-dark shadow-sm">
                <i class="bi bi-plus-lg me-1"></i> Registrar Pago
            </a>
        </div>
    </div>

    <!-- Alertas de Publicaciones Próximas a Vencer (RF-29) -->
    <?php if (!empty($proximosVencimientos)): ?>
        <div class="alert alert-warning rounded-4 shadow-sm border-0 mb-4 p-3">
            <h6 class="fw-bold mb-2"><i class="bi bi-exclamation-triangle-fill me-2"></i>Publicaciones Próximas a Vencer (Siguientes 5 días):</h6>
            <ul class="mb-0 small ps-3">
                <?php foreach ($proximosVencimientos as $pv): ?>
                    <li>
                        <strong><?= htmlspecialchars($pv['nombre']) ?></strong> vence el 
                        <strong><?= date('d/m/Y', strtotime($pv['fecha_vencimiento'])) ?></strong> 
                        (Quedan <?= $pv['dias_restantes'] ?> días).
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-4 small mb-4 border-0 shadow-sm">
            <i class="bi bi-check-circle-fill me-2 fs-5"></i> <?= htmlspecialchars($mensaje) ?>
            <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-4 small mb-4 border-0 shadow-sm">
            <i class="bi bi-x-octagon-fill me-2 fs-5"></i> <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Resumen financiero y comercial -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="payment-kpi-card payment-kpi-total p-3 h-100">
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <div>
                        <span class="small text-uppercase fw-bold opacity-75">Ingresos confirmados</span>
                        <h4 class="fw-bold mt-2 mb-1">Bs <?= number_format($estadisticas['totalConfirmado'], 0) ?></h4>
                        <small class="opacity-75">Histórico acumulado</small>
                    </div>
                    <i class="bi bi-wallet2 fs-3 opacity-75"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="payment-kpi-card payment-kpi-month p-3 h-100">
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <div>
                        <span class="small text-uppercase fw-bold opacity-75">Generado este mes</span>
                        <h4 class="fw-bold mt-2 mb-1">Bs <?= number_format($estadisticas['recaudadoMes'], 0) ?></h4>
                        <small class="opacity-75">
                            <?php if ($estadisticas['variacionMensual'] !== null): ?>
                                <?= $estadisticas['variacionMensual'] >= 0 ? '+' : '' ?><?= number_format($estadisticas['variacionMensual'], 1) ?>% vs. mes anterior
                            <?php else: ?>
                                Primer mes con comparación
                            <?php endif; ?>
                        </small>
                    </div>
                    <i class="bi bi-graph-up-arrow fs-3 opacity-75"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="payment-kpi-card payment-kpi-pending p-3 h-100">
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <div>
                        <span class="small text-uppercase fw-bold opacity-75">Por validar</span>
                        <h4 class="fw-bold mt-2 mb-1">Bs <?= number_format($estadisticas['pendienteMonto'], 0) ?></h4>
                        <small class="opacity-75"><?= $estadisticas['pendienteCantidad'] ?> comprobante(s) pendiente(s)</small>
                    </div>
                    <i class="bi bi-hourglass-split fs-3 opacity-75"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="payment-kpi-card payment-kpi-active p-3 h-100">
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <div>
                        <span class="small text-uppercase fw-bold opacity-75">Comercios vigentes</span>
                        <h4 class="fw-bold mt-2 mb-1"><?= $estadisticas['comerciosVigentes'] ?></h4>
                        <small class="opacity-75">Con cobertura activa hoy</small>
                    </div>
                    <i class="bi bi-patch-check-fill fs-3 opacity-75"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="admin-card p-3 p-md-4 h-100">
                <div class="d-flex justify-content-between align-items-center gap-2 mb-3 flex-wrap">
                    <div>
                        <h5 class="fw-bold text-dark mb-1"><i class="bi bi-bar-chart-line-fill text-success me-2"></i>Ingresos confirmados por mes</h5>
                        <p class="small text-muted mb-0">Evolución financiera de los últimos seis meses.</p>
                    </div>
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2">Solo pagos confirmados</span>
                </div>
                <div style="height: 275px; position: relative;"><canvas id="chartIngresosPagos"></canvas></div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="payment-insight-card h-100 p-4">
                <span class="small text-uppercase fw-bold text-success">Prioridad operativa</span>
                <h5 class="fw-bold text-dark mt-2">Revisa los comprobantes pendientes</h5>
                <p class="text-muted small mb-3">Cada pago confirmado activa o extiende la cobertura comercial correspondiente.</p>
                <div class="d-flex align-items-center gap-3 pt-2 border-top">
                    <span class="payment-insight-number"><?= $estadisticas['pendienteCantidad'] ?></span>
                    <span class="small text-muted">pagos esperan verificación administrativa</span>
                </div>
                <a href="<?= htmlspecialchars($baseUrl) ?>/admin/pagos?estado=PENDIENTE" class="btn btn-success rounded-pill w-100 mt-4">Ver pagos pendientes <i class="bi bi-arrow-right ms-1"></i></a>
            </div>
        </div>
    </div>

    <!-- Tabla Principal de Pagos -->
    <div class="admin-card overflow-hidden">
        <div class="table-responsive">
            <table class="table admin-table align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 70px;">Pago #</th>
                        <th>Comercio</th>
                        <th>Importe</th>
                        <th>Fecha Declarada</th>
                        <th>Método / N° Transacción</th>
                        <th>Estado Pago</th>
                        <th>Vigencia Cubierta</th>
                        <th class="text-end" style="width: 150px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($pagos)): ?>
                        <?php foreach ($pagos as $p): ?>
                            <tr>
                                <td class="text-muted fw-bold">#<?= $p['id_pago'] ?></td>
                                <td>
                                    <strong class="text-dark d-block"><?= htmlspecialchars($p['nombre_establecimiento']) ?></strong>
                                    <?php if (!empty($p['observaciones'])): ?>
                                        <small class="text-muted extra-small d-block text-truncate" style="max-width: 220px;"><?= htmlspecialchars($p['observaciones']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold text-dark fs-6">
                                    Bs <?= number_format($p['monto'], 2) ?><small class="d-block text-muted"><?= (int)$p['meses_duracion'] ?> mes(es)</small>
                                </td>
                                <td class="small text-muted">
                                    <?= date('d/m/Y', strtotime($p['fecha_pago_declarada'])) ?>
                                </td>
                                <td>
                                    <span class="small d-block text-dark fw-medium"><?= htmlspecialchars($p['metodo_pago']) ?></span>
                                    <code class="extra-small text-muted"><?= htmlspecialchars($p['numero_comprobante'] ?: 'Sin comprobante') ?></code>
                                    <?php if (!empty($p['comprobante_archivo'])): ?>
                                        <button type="button" class="btn btn-outline-primary btn-sm d-block mt-2" data-bs-toggle="modal" data-bs-target="#comprobantePago<?= (int)$p['id_pago'] ?>">Ver comprobante</button>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($p['estado'] === 'PENDIENTE'): ?>
                                        <span class="badge bg-warning text-dark rounded-pill px-3 py-1">Pendiente</span>
                                    <?php elseif ($p['estado'] === 'CONFIRMADO'): ?>
                                        <span class="badge bg-success rounded-pill px-3 py-1">Confirmado</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger rounded-pill px-3 py-1">Anulado</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($p['fecha_inicio'])): ?>
                                        <div class="small fw-semibold text-success">
                                            <?= date('d/m/Y', strtotime($p['fecha_inicio'])) ?> &rarr; <?= date('d/m/Y', strtotime($p['fecha_vencimiento'])) ?>
                                        </div>
                                        <span class="badge bg-light text-muted border extra-small" style="font-size: 0.65rem;">
                                            <?= htmlspecialchars($p['tipo_periodo']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted extra-small">Sin vigencia asignada</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if ($p['estado'] === 'PENDIENTE'): ?>
                                        <div class="d-inline-flex gap-1">
                                            <!-- Formulario Confirmar Pago (Transaccional) -->
                                            <form action="<?= htmlspecialchars($baseUrl) ?>/admin/pagos/confirmar" method="POST" class="d-inline m-0">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                                <input type="hidden" name="id_pago" value="<?= $p['id_pago'] ?>">
                                                <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 fw-bold" title="Confirmar abono y activar ficha">
                                                    <i class="bi bi-check2 me-1"></i> Confirmar Abono
                                                </button>
                                            </form>

                                            <!-- Botón Anular -->
                                            <?php if (!empty($p['es_registro_inicial'])): ?>
                                                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rechazarRegistro<?= (int)$p['id_pago'] ?>">Rechazar / Eliminar</button>
                                            <?php else: ?>
                                            <button type="button" class="btn btn-sm btn-outline-danger rounded-circle" data-bs-toggle="modal" data-bs-target="#modalAnular<?= $p['id_pago'] ?>" title="Anular comprobante" style="width: 32px; height: 32px; padding: 0;">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Modal Anulación -->
                                        <div class="modal fade" id="modalAnular<?= $p['id_pago'] ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-sm">
                                                <div class="modal-content rounded-4 border-0 shadow">
                                                    <form action="<?= htmlspecialchars($baseUrl) ?>/admin/pagos/anular" method="POST">
                                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                                        <input type="hidden" name="id_pago" value="<?= $p['id_pago'] ?>">
                                                        <div class="modal-body p-4 text-center">
                                                            <i class="bi bi-exclamation-circle text-danger display-5 mb-2 d-block"></i>
                                                            <h6 class="fw-bold mb-2">¿Anular Pago #<?= $p['id_pago'] ?>?</h6>
                                                            <textarea name="motivo_anulacion" rows="2" class="form-control form-control-sm mb-3" placeholder="Motivo (ej. Fondos no acreditados)" required></textarea>
                                                            <button type="submit" class="btn btn-danger btn-sm rounded-pill w-100 fw-bold">Confirmar Anulación</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <small class="text-muted extra-small">Procesado</small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-cash-coin display-5 d-block mb-2"></i>
                                No hay registros de pagos con el filtro seleccionado.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php foreach ($pagos as $p): ?>
    <?php if (!empty($p['comprobante_archivo'])): ?>
    <div class="modal fade" id="comprobantePago<?= (int)$p['id_pago'] ?>" tabindex="-1" aria-labelledby="tituloPago<?= (int)$p['id_pago'] ?>" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content">
            <div class="modal-header"><h5 id="tituloPago<?= (int)$p['id_pago'] ?>" class="modal-title">Comprobante del pago #<?= (int)$p['id_pago'] ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
            <div class="modal-body text-center"><img class="img-fluid" loading="lazy" alt="Comprobante bancario" src="<?= htmlspecialchars($baseUrl) ?>/admin/pagos/comprobante?id=<?= (int)$p['id_pago'] ?>"></div>
        </div></div>
    </div>
    <?php endif; ?>
    <?php if ($p['estado'] === 'PENDIENTE' && !empty($p['es_registro_inicial'])): ?>
    <div class="modal fade" id="rechazarRegistro<?= (int)$p['id_pago'] ?>" tabindex="-1" aria-labelledby="tituloRechazar<?= (int)$p['id_pago'] ?>" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
            <div class="modal-header"><h5 id="tituloRechazar<?= (int)$p['id_pago'] ?>" class="modal-title">Eliminar registro falso</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
            <div class="modal-body">
                <p>Se eliminarán permanentemente <strong><?= htmlspecialchars($p['nombre_establecimiento']) ?></strong>, su cuenta, fotos, promociones y pagos pendientes. El propietario perderá el acceso.</p>
                <form action="<?= htmlspecialchars($baseUrl) ?>/admin/pagos/rechazar-eliminar" method="post">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <input type="hidden" name="id_pago" value="<?= (int)$p['id_pago'] ?>">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Eliminar registro definitivamente</button>
                </form>
            </div>
        </div></div>
    </div>
    <?php endif; ?>
<?php endforeach; ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const contexto = document.getElementById('chartIngresosPagos')?.getContext('2d');
    if (!contexto || !window.Chart) return;

    const etiquetas = <?= json_encode($graficoIngresos['labels'], JSON_UNESCAPED_UNICODE) ?>;
    const ingresos = <?= json_encode($graficoIngresos['valores']) ?>;
    const pagosMes = <?= json_encode($graficoIngresos['pagos']) ?>;
    const gradiente = contexto.createLinearGradient(0, 0, 0, 260);
    gradiente.addColorStop(0, 'rgba(25, 135, 84, 0.48)');
    gradiente.addColorStop(1, 'rgba(25, 135, 84, 0.03)');

    new Chart(contexto, {
        type: 'bar',
        data: {
            labels: etiquetas,
            datasets: [{
                label: 'Ingresos confirmados',
                data: ingresos,
                backgroundColor: gradiente,
                borderColor: '#198754',
                borderWidth: 2,
                borderRadius: 8,
                maxBarThickness: 54
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#092611',
                    padding: 11,
                    callbacks: {
                        label: (context) => ' Bs ' + Number(context.parsed.y).toLocaleString('es-BO', { minimumFractionDigits: 2 }),
                        afterLabel: (context) => ' ' + pagosMes[context.dataIndex] + ' pago(s) confirmado(s)'
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0, 0, 0, 0.06)' },
                    ticks: { callback: (valor) => 'Bs ' + Number(valor).toLocaleString('es-BO') }
                },
                x: { grid: { display: false } }
            }
        }
    });
});
</script>
