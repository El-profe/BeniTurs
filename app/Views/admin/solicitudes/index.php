<?php $escape = static fn($valor) => htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); ?>
<?php
$conteosSolicitud = ['PENDIENTE' => 0, 'ACEPTADA' => 0, 'RECHAZADA' => 0];
foreach ($solicitudes as $solicitud) {
    if (isset($conteosSolicitud[$solicitud['estado']])) $conteosSolicitud[$solicitud['estado']]++;
}
?>
<div class="container-fluid p-0">
    <div class="admin-page-hero mb-4 d-flex justify-content-between align-items-center gap-3 flex-wrap">
        <div class="d-flex align-items-center gap-3">
            <span class="admin-page-hero-icon"><i class="bi bi-inboxes-fill"></i></span>
            <div>
                <h1 class="h4 fw-bold mb-1">Bandeja de solicitudes</h1>
                <p class="text-muted small mb-0">Revisa comprobantes y datos antes de activar un nuevo comercio.</p>
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <div class="admin-request-stat"><strong><?= $conteosSolicitud['PENDIENTE'] ?></strong><small>Pendientes</small></div>
            <div class="admin-request-stat"><strong><?= $conteosSolicitud['ACEPTADA'] ?></strong><small>Aceptadas</small></div>
            <div class="admin-request-stat"><strong><?= $conteosSolicitud['RECHAZADA'] ?></strong><small>Rechazadas</small></div>
        </div>
    </div>
    <div class="admin-card p-3 p-md-4 mb-3">
        <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-3">
            <div>
                <h2 class="h6 fw-bold mb-1">Filtrar solicitudes</h2>
                <p class="small text-muted mb-0">Selecciona un estado para priorizar la revisión.</p>
            </div>
            <div class="btn-group" role="group" aria-label="Filtrar solicitudes">
        <?php foreach ([''=>'Todas','PENDIENTE'=>'Pendientes','ACEPTADA'=>'Aceptadas','RECHAZADA'=>'Rechazadas'] as $estado=>$etiqueta): ?>
            <a class="btn btn-sm <?= $filtroActual === $estado ? 'btn-success' : 'btn-outline-secondary' ?>" href="<?= $escape($baseUrl) ?>/admin/solicitudes?estado=<?= $estado ?>"><?= $etiqueta ?></a>
        <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php if ($mensaje): ?><div class="alert alert-success" role="status"><?= $escape($mensaje) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger" role="alert"><?= $escape($error) ?></div><?php endif; ?>
    <div id="solicitudAdminMensaje" role="status" aria-live="polite" tabindex="-1"></div>
    <div class="table-responsive admin-card overflow-hidden">
        <table class="table solicitudes-admin-table align-middle mb-0">
            <thead><tr><th>ID</th><th>Establecimiento / Plan</th><th>Rubro</th><th>Contacto</th><th>Comprobante</th><th>Estado</th><th>Acciones</th></tr></thead>
            <tbody>
            <?php foreach ($solicitudes as $s): ?>
                <tr data-solicitud="<?= (int)$s['id_solicitud'] ?>">
                    <td>#<?= (int)$s['id_solicitud'] ?></td>
                    <td><strong><?= $escape($s['nombre_establecimiento']) ?></strong><br>
                        <span class="badge bg-secondary-subtle text-secondary py-0 px-2 small me-1"><i class="bi bi-geo-alt me-1"></i><?= $escape($s['municipio'] ?? 'Trinidad') ?></span>
                        <small><?= $escape($s['tarifa_nombre'] ?? ($s['plan_solicitado'] === 'ANUAL' ? 'Anual · Bs 2.500' : 'Mensual · Bs 250')) ?></small><br>
                        <small class="text-muted"><?= $escape($s['created_at']) ?></small></td>
                    <td><?= $escape($s['categoria']) ?></td>
                    <td><?= $escape($s['nombre_solicitante']) ?><br><?= $escape($s['telefono_contacto']) ?></td>
                    <td>
                        <?php if ($s['comprobante_archivo']): ?>
                            <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#comprobante<?= (int)$s['id_solicitud'] ?>">Ver comprobante</button>
                            <small class="d-block"><?= $escape($s['numero_comprobante']) ?></small>
                        <?php elseif (($s['metodo_pago'] ?? '') === 'EFECTIVO'): ?>
                            <span class="badge bg-warning-subtle text-dark border border-warning-subtle py-1 px-2">
                                <i class="bi bi-cash-coin me-1 text-success"></i> Efectivo
                            </span>
                            <small class="d-block text-muted">Cobro en persona</small>
                        <?php else: ?>
                            <span class="text-muted small">Sin comprobante</span>
                        <?php endif; ?>
                    </td>
                    <td data-estado>
                        <span class="solicitud-status solicitud-status-<?= strtolower($escape($s['estado'])) ?>">
                            <i class="bi <?= $s['estado'] === 'PENDIENTE' ? 'bi-hourglass-split' : ($s['estado'] === 'ACEPTADA' ? 'bi-check-circle-fill' : 'bi-x-circle-fill') ?>"></i>
                            <?= $escape($s['estado']) ?>
                        </span>
                        <?php if ($s['id_lugar_creado']): ?><small class="d-block">Ficha #<?= (int)$s['id_lugar_creado'] ?></small><?php endif; ?>
                    </td>
                    <td data-acciones>
                        <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#detalle<?= (int)$s['id_solicitud'] ?>">Detalles</button>
                        <?php if ($s['estado'] === 'PENDIENTE' && $s['comprobante_archivo']): ?>
                            <form action="<?= $escape($baseUrl) ?>/admin/solicitudes/aprovisionar" method="post" class="mt-2 form-aprovisionar" data-solicitud-accion="aprovisionar">
                                <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>">
                                <input type="hidden" name="id_solicitud" value="<?= (int)$s['id_solicitud'] ?>">
                                <button class="btn btn-success btn-sm w-100" type="submit"><i class="bi bi-check2-circle me-1"></i>Aprobar y Activar</button>
                            </form>
                        <?php elseif ($s['estado'] === 'PENDIENTE'): ?>
                            <button class="btn btn-success btn-sm mt-2 w-100 btn-cobro-efectivo" 
                                    type="button" 
                                    data-id="<?= (int)$s['id_solicitud'] ?>" 
                                    data-nombre="<?= $escape($s['nombre_establecimiento']) ?>" 
                                    data-monto="<?= $escape($s['monto_declarado']) ?>" 
                                    data-plan="<?= $escape($s['plan_solicitado']) ?>">
                                <i class="bi bi-cash-stack me-1"></i> Cobrar y Activar
                            </button>
                        <?php endif; ?>
                        <?php if ($s['estado'] === 'PENDIENTE'): ?>
                            <form action="<?= $escape($baseUrl) ?>/admin/solicitudes/resolver" method="post" class="mt-2" data-solicitud-accion="rechazar">
                                <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>">
                                <input type="hidden" name="id_solicitud" value="<?= (int)$s['id_solicitud'] ?>">
                                <input type="hidden" name="estado" value="RECHAZADA">
                                <button class="btn btn-outline-danger btn-sm w-100" type="submit">Rechazar</button>
                            </form>
                        <?php endif; ?>
                        <?php if ($s['estado'] === 'ACEPTADA' && $s['id_cuenta_creada']): ?>
                            <?php try { $whatsapp = \App\Services\SolicitudService::enlaceBienvenida($s); } catch (\Throwable $e) { $whatsapp = null; } ?>
                            <?php if ($whatsapp): ?><a href="<?= $escape($whatsapp) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-success btn-sm mt-2 d-inline-flex align-items-center gap-1 w-100 justify-content-center"><i class="bi bi-whatsapp"></i> <span>WhatsApp</span></a><?php endif; ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$solicitudes): ?><tr><td colspan="7" class="text-center py-4">No hay solicitudes con este filtro.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php foreach ($solicitudes as $s): ?>
    <?php if ($s['comprobante_archivo']): ?>
    <div class="modal fade" id="comprobante<?= (int)$s['id_solicitud'] ?>" tabindex="-1" aria-labelledby="tituloComprobante<?= (int)$s['id_solicitud'] ?>" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content">
            <div class="modal-header"><h5 id="tituloComprobante<?= (int)$s['id_solicitud'] ?>" class="modal-title">Comprobante #<?= (int)$s['id_solicitud'] ?></h5><button class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
            <div class="modal-body text-center"><img loading="lazy" class="img-fluid" alt="Comprobante bancario de la solicitud" src="<?= $escape($baseUrl) ?>/admin/solicitudes/comprobante?id=<?= (int)$s['id_solicitud'] ?>"></div>
        </div></div>
    </div>
    <?php endif; ?>
    <div class="modal fade" id="detalle<?= (int)$s['id_solicitud'] ?>" tabindex="-1" aria-labelledby="tituloDetalle<?= (int)$s['id_solicitud'] ?>" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
            <div class="modal-header"><h5 id="tituloDetalle<?= (int)$s['id_solicitud'] ?>" class="modal-title"><?= $escape($s['nombre_establecimiento']) ?></h5><button class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
            <div class="modal-body">
                <p style="white-space: pre-line"><?= $escape($s['descripcion']) ?></p>
                <p><strong>Municipio:</strong> <?= $escape($s['municipio'] ?? 'Trinidad') ?><br>
                    <strong>Dirección:</strong> <?= $escape($s['direccion']) ?><br>
                    <strong>Horarios:</strong> <?= $escape($s['horarios']) ?><br>
                    <strong>Correo:</strong> <?= $escape($s['email_contacto']) ?><br>
                    <strong>Usuario asignado:</strong> <?= $escape($s['usuario_asignado'] ?: 'Se genera al aprobar') ?></p>
                <?php if ($s['estado'] === 'PENDIENTE'): ?>
                <form action="<?= $escape($baseUrl) ?>/admin/solicitudes/resolver" method="post" data-solicitud-accion="rechazar">
                    <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>">
                    <input type="hidden" name="id_solicitud" value="<?= (int)$s['id_solicitud'] ?>">
                    <input type="hidden" name="estado" value="RECHAZADA">
                    <label class="form-label" for="motivo<?= (int)$s['id_solicitud'] ?>">Observaciones</label>
                    <textarea id="motivo<?= (int)$s['id_solicitud'] ?>" name="observaciones_admin" class="form-control mb-2" maxlength="2000"></textarea>
                    <button class="btn btn-outline-danger" type="submit">Rechazar solicitud</button>
                </form>
                <?php else: ?><p><?= $escape($s['observaciones_admin']) ?></p><?php endif; ?>
            </div>
        </div></div>
    </div>
<?php endforeach; ?>

<!-- Modal de Credenciales Generadas (Nivel 1) -->
<?php require dirname(__DIR__) . '/parciales/modal_credenciales.php'; ?>


<!-- Modal para Confirmar Cobro en Efectivo -->
<div class="modal fade" id="modalConfirmarCobroEfectivo" tabindex="-1" aria-labelledby="tituloModalCobro" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content rounded-4 border-0 shadow" id="formCobroEfectivoModal">
            <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>">
            <input type="hidden" name="id_solicitud" id="cobroEfectivoIdSolicitud" value="">
            <input type="hidden" name="metodo_pago" value="EFECTIVO">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" id="tituloModalCobro"><i class="bi bi-cash-stack text-success me-2"></i>Registrar Cobro en Efectivo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted small mb-3">Vas a confirmar que recibiste el pago en efectivo de este comerciante y se activará su cuenta de inmediato.</p>
                <div class="p-3 bg-light rounded-3 mb-3 border">
                    <strong class="d-block text-dark fs-6" id="cobroEfectivoNombre">-</strong>
                    <small class="text-muted" id="cobroEfectivoPlan">-</small>
                </div>
                <div class="mb-3">
                    <label for="cobroEfectivoMonto" class="form-label fw-bold small">Monto Recibido en Mano (Bs) *</label>
                    <input type="text" class="form-control form-control-lg fw-bold text-success" id="cobroEfectivoMonto" readonly>
                </div>
                <div class="mb-3">
                    <label for="cobroEfectivoRecibo" class="form-label fw-bold small">N° de Recibo o Talonario Físico (opcional)</label>
                    <input type="text" name="numero_recibo" class="form-control" id="cobroEfectivoRecibo" placeholder="Ej. REC-00124" maxlength="50">
                    <small class="text-muted">Si le entregaste un recibo en papel, anota aquí el número correlativo.</small>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm" id="btnConfirmarCobroEfectivo">
                    <i class="bi bi-check2-circle me-1"></i> Cobrar y Generar Credenciales
                </button>
            </div>
        </form>
    </div>
</div>

<script src="<?= $escape($baseUrl) ?>/assets/js/admin/credenciales-modal.js?v=<?= filemtime(dirname(__DIR__, 4) . '/public/assets/js/admin/credenciales-modal.js') ?>" defer></script>
<script src="<?= $escape($baseUrl) ?>/assets/js/admin/solicitudes.js?v=<?= filemtime(dirname(__DIR__, 4) . '/public/assets/js/admin/solicitudes.js') ?>" defer></script>
