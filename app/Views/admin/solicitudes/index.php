<?php $escape = static fn($valor) => htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); ?>
<div class="container-fluid p-0">
    <h3>Bandeja de solicitudes comerciales</h3>
    <p class="text-muted">Revisa el comprobante antes de activar el negocio, su cuenta y la vigencia.</p>
    <div class="btn-group mb-3" role="group" aria-label="Filtrar solicitudes">
        <?php foreach ([''=>'Todas','PENDIENTE'=>'Pendientes','ACEPTADA'=>'Aceptadas','RECHAZADA'=>'Rechazadas'] as $estado=>$etiqueta): ?>
            <a class="btn btn-sm <?= $filtroActual === $estado ? 'btn-success' : 'btn-outline-secondary' ?>" href="<?= $escape($baseUrl) ?>/admin/solicitudes?estado=<?= $estado ?>"><?= $etiqueta ?></a>
        <?php endforeach; ?>
    </div>
    <?php if ($mensaje): ?><div class="alert alert-success" role="status"><?= $escape($mensaje) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger" role="alert"><?= $escape($error) ?></div><?php endif; ?>
    <div class="table-responsive admin-card">
        <table class="table align-middle">
            <thead><tr><th>ID</th><th>Establecimiento / Plan</th><th>Rubro</th><th>Contacto</th><th>Comprobante</th><th>Estado</th><th>Acciones</th></tr></thead>
            <tbody>
            <?php foreach ($solicitudes as $s): ?>
                <tr>
                    <td>#<?= (int)$s['id_solicitud'] ?></td>
                    <td><strong><?= $escape($s['nombre_establecimiento']) ?></strong><br>
                        <small><?= $s['plan_solicitado'] === 'ANUAL' ? 'Anual · Bs 2.500' : 'Mensual · Bs 250' ?></small><br>
                        <small class="text-muted"><?= $escape($s['created_at']) ?></small></td>
                    <td><?= $escape($s['categoria']) ?></td>
                    <td><?= $escape($s['nombre_solicitante']) ?><br><?= $escape($s['telefono_contacto']) ?></td>
                    <td>
                        <?php if ($s['comprobante_archivo']): ?>
                            <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#comprobante<?= (int)$s['id_solicitud'] ?>">Ver comprobante</button>
                            <small class="d-block"><?= $escape($s['numero_comprobante']) ?></small>
                        <?php else: ?><span class="text-muted">Sin comprobante</span><?php endif; ?>
                    </td>
                    <td><?= $escape($s['estado']) ?>
                        <?php if ($s['id_lugar_creado']): ?><small class="d-block">Ficha #<?= (int)$s['id_lugar_creado'] ?></small><?php endif; ?>
                    </td>
                    <td>
                        <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#detalle<?= (int)$s['id_solicitud'] ?>">Detalles</button>
                        <?php if ($s['estado'] === 'PENDIENTE' && $s['comprobante_archivo'] && $s['usuario_solicitado'] && $s['password_hash_solicitado']): ?>
                            <form action="<?= $escape($baseUrl) ?>/admin/solicitudes/aprovisionar" method="post" class="mt-2 form-aprovisionar">
                                <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>">
                                <input type="hidden" name="id_solicitud" value="<?= (int)$s['id_solicitud'] ?>">
                                <button class="btn btn-success btn-sm" type="submit">Verificar y Aprovisionar Todo</button>
                            </form>
                        <?php elseif ($s['estado'] === 'PENDIENTE'): ?>
                            <small class="d-block text-muted">Solicitud incompleta: requiere comprobante y credenciales.</small>
                        <?php endif; ?>
                        <?php if ($s['estado'] === 'ACEPTADA' && $s['id_cuenta_creada'] && $s['usuario_solicitado']): ?>
                            <?php $whatsapp = \App\Services\SolicitudService::enlaceBienvenida($s); ?>
                            <?php if ($whatsapp): ?><a href="<?= $escape($whatsapp) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-success btn-sm mt-2">Enviar bienvenida por WhatsApp</a><?php endif; ?>
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
                <p><strong>Dirección:</strong> <?= $escape($s['direccion']) ?><br>
                    <strong>Horarios:</strong> <?= $escape($s['horarios']) ?><br>
                    <strong>Correo:</strong> <?= $escape($s['email_contacto']) ?><br>
                    <strong>Usuario solicitado:</strong> <?= $escape($s['usuario_solicitado']) ?></p>
                <?php if ($s['estado'] === 'PENDIENTE'): ?>
                <form action="<?= $escape($baseUrl) ?>/admin/solicitudes/resolver" method="post">
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
<script>
document.querySelectorAll('.form-aprovisionar').forEach(form => {
    form.addEventListener('submit', () => {
        const button = form.querySelector('button[type="submit"]');
        button.disabled = true;
        button.textContent = 'Aprovisionando…';
    });
});
</script>
