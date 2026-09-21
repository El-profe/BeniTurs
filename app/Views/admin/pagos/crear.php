<div class="container py-2">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="fw-bold text-dark mb-1">Registrar Pago Comercial</h4>
                        <small class="text-muted">El pago se guardará en estado PENDIENTE para su verificación</small>
                    </div>
                    <a href="<?= htmlspecialchars($baseUrl) ?>/admin/pagos" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                        <i class="bi bi-arrow-left me-1"></i> Volver
                    </a>
                </div>

                <?php if (!empty($error)): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
                <?php if (!$tarifa): ?><div class="alert alert-warning">No hay una tarifa vigente. Configure una antes de registrar pagos.</div><?php endif; ?>
                <form action="<?= htmlspecialchars($baseUrl) ?>/admin/pagos/guardar" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <input type="hidden" name="id_tarifa" value="<?= (int)($tarifa['id_tarifa'] ?? 1) ?>">

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Establecimiento Comercial *</label>
                            <select name="id_lugar" id="pago-lugar" class="form-select" required>
                                <option value="">Seleccione el comercio de Trinidad...</option>
                                <?php foreach ($comercios as $c): ?>
                                    <option data-meses="<?= ($c['plan_solicitado'] ?? '') === 'ANUAL' ? 12 : 1 ?>" value="<?= $c['id_lugar'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="pago-meses" class="form-label fw-semibold small">Plan contratado *</label>
                            <select id="pago-meses" name="meses_duracion" class="form-select" required>
                                <option value="1">Mensual (1 mes)</option>
                                <option value="12">Anual (12 meses)</option>
                            </select>
                            <small class="text-muted">Se propone el plan de la solicitud; confirme el plan y el importe acordados.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Importe en Bolivianos (Bs) *</label>
                            <input type="number" step="0.01" name="monto" class="form-control fw-bold fs-5 text-success" 
                                   value="<?= number_format((float)($tarifa['monto_mensual'] ?? 250.00), 2, '.', '') ?>" required>
                            <small class="text-muted extra-small">Tarifa base de referencia: Bs 250 / Plan Anual: Bs 2,500</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Fecha Declarada del Abono *</label>
                            <input type="date" name="fecha_pago_declarada" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Método de Pago</label>
                            <select name="metodo_pago" class="form-select">
                                <option value="Transferencia bancaria / QR">Transferencia bancaria / QR</option>
                                <option value="Depósito en ventanilla">Depósito en ventanilla</option>
                                <option value="Efectivo en oficina">Efectivo en oficina</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">N° de Comprobante o Transacción</label>
                            <input type="text" name="numero_comprobante" class="form-control" placeholder="Ej. TRF-9823412">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small">Observaciones o Notas Internas</label>
                            <textarea name="observaciones" rows="2" class="form-control" placeholder="Banco emisor, nombre del titular que transfirió o detalles adicionales..."></textarea>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top text-end">
                        <button <?= !$tarifa ? 'disabled' : '' ?> type="submit" class="btn btn-warning rounded-pill px-5 fw-bold text-dark shadow-sm">
                            <i class="bi bi-clock-history me-1"></i> Guardar Pago como PENDIENTE
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
document.getElementById('pago-lugar').addEventListener('change', function () {
    document.getElementById('pago-meses').value = this.selectedOptions[0]?.dataset.meses || '1';
});
</script>