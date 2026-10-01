<!-- Modal de Credenciales Generadas (Compartido para Solicitudes, Crear Lugar y Editar Lugar) -->
<div class="modal fade" id="modalCredencialesGeneradas" tabindex="-1" aria-labelledby="tituloModalCredenciales" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header bg-success text-white py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill fs-3"></i>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="tituloModalCredenciales">¡Comercio Activado Exitosamente!</h5>
                        <small class="text-white-50">Credenciales listas para entregar al propietario</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4 p-md-5">
                <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded-3 mb-4 flex-wrap gap-2 border">
                    <div>
                        <span class="text-muted small text-uppercase fw-bold d-block">Establecimiento</span>
                        <h5 class="fw-bold mb-0 text-dark" id="modalCredComercio">-</h5>
                    </div>
                    <div class="text-md-end">
                        <span class="text-muted small text-uppercase fw-bold d-block">Monto y Plan</span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fw-bold fs-6" id="modalCredPlan">-</span>
                    </div>
                </div>

                <!-- Credenciales destacadas -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="p-3 border rounded-3 bg-white h-100 shadow-sm">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small text-muted fw-bold text-uppercase"><i class="bi bi-person-fill text-success me-1"></i> Usuario</span>
                                <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 text-success fw-semibold btn-copiar-dato" data-target="modalCredUsuario">
                                    <i class="bi bi-clipboard me-1"></i> Copiar
                                </button>
                            </div>
                            <div class="fs-5 fw-bold font-monospace text-dark text-break" id="modalCredUsuario">-</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 border rounded-3 bg-white h-100 shadow-sm">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small text-muted fw-bold text-uppercase"><i class="bi bi-key-fill text-warning me-1"></i> Contraseña</span>
                                <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 text-success fw-semibold btn-copiar-dato" data-target="modalCredClave">
                                    <i class="bi bi-clipboard me-1"></i> Copiar
                                </button>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="fs-5 fw-bold font-monospace text-dark text-break" id="modalCredClave">-</div>
                                <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" id="btnToggleClave" title="Ver / Ocultar">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vista previa del mensaje completo -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label fw-bold mb-0 small text-uppercase text-muted">Mensaje Listo para WhatsApp o Mensajería</label>
                        <span id="avisoCopiado" class="text-success small fw-bold d-none"><i class="bi bi-check2-all me-1"></i>¡Mensaje copiado al portapapeles!</span>
                    </div>
                    <textarea id="modalCredMensajeCompleto" class="form-control font-monospace small bg-light" rows="4" readonly></textarea>
                </div>

                <!-- Botones de Acción Inmediata -->
                <div class="row g-2">
                    <div class="col-md-6">
                        <button type="button" class="btn btn-primary w-100 py-2 fw-semibold d-flex align-items-center justify-content-center gap-2 shadow-sm" id="btnCopiarMensajeCompleto">
                            <i class="bi bi-clipboard-check fs-5"></i>
                            <span>Copiar Mensaje Completo</span>
                        </button>
                    </div>
                    <div class="col-md-6">
                        <a href="#" target="_blank" rel="noopener noreferrer" class="btn btn-success w-100 py-2 fw-semibold d-flex align-items-center justify-content-center gap-2 shadow-sm" id="btnAbrirWhatsApp">
                            <i class="bi bi-whatsapp fs-5"></i>
                            <span>Enviar por WhatsApp</span>
                        </a>
                    </div>
                    <div class="col-12 mt-2">
                        <button type="button" class="btn btn-outline-secondary w-100 py-2 d-flex align-items-center justify-content-center gap-2" id="btnImprimirRecibo">
                            <i class="bi bi-printer"></i>
                            <span>Imprimir Recibo Oficial / Tarjeta de Bienvenida</span>
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2 px-4">
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4" data-bs-dismiss="modal">Listo, cerrar</button>
            </div>
        </div>
    </div>
</div>
