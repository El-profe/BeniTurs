<div class="container-fluid p-0">
    <!-- Encabezado de Sección -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold text-dark mb-1">Directorio de Lugares y Negocios</h3>
            <p class="text-muted small mb-0">Control integral de atractivos turísticos y comercios de Trinidad</p>
        </div>
        <a href="<?= htmlspecialchars($baseUrl) ?>/admin/lugares/crear" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> Nueva Ficha
        </a>
    </div>

    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-4 small mb-4 border-0 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2 fs-5"></i> <?= htmlspecialchars($mensaje) ?>
            <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Tabla Principal de Fichas -->
    <div class="admin-card overflow-hidden">
        <div class="table-responsive">
            <table class="table admin-table align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 70px;">ID</th>
                        <th>Establecimiento / Lugar</th>
                        <th>Categoría</th>
                        <th>Condición</th>
                        <th>Estado Publicación</th>
                        <th>Visibilidad Web</th>
                        <th class="text-end" style="width: 140px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($lugares)): ?>
                        <?php foreach ($lugares as $l): ?>
                            <tr>
                                <td class="text-muted fw-bold small">#<?= $l['id_lugar'] ?></td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($l['nombre']) ?></div>
                                    <small class="text-muted d-block text-truncate" style="max-width: 280px; font-size: 0.75rem;">
                                        Slug: <code><?= htmlspecialchars($l['slug']) ?></code>
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border rounded-pill px-3 py-1">
                                        <?= htmlspecialchars($l['categoria']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?= $l['tipo_lugar'] === 'PUBLICO' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-primary-subtle text-primary border border-primary-subtle' ?> rounded-pill px-3 py-1">
                                        <?= $l['tipo_lugar'] === 'PUBLICO' ? 'Público Gratuito' : 'Comercial' ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($l['habilitado']): ?>
                                        <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1">
                                            <i class="bi bi-check-circle me-1"></i> Habilitado
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger rounded-pill px-2 py-1">
                                            <i class="bi bi-slash-circle me-1"></i> Deshabilitado
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($l['es_visible']): ?>
                                        <span class="badge bg-success rounded-pill px-3 py-1">
                                            <i class="bi bi-eye-fill me-1"></i> Visible
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary rounded-pill px-3 py-1">
                                            <i class="bi bi-eye-slash-fill me-1"></i> Oculto
                                        </span>
                                        <?php if ($l['tipo_lugar'] === 'COMERCIAL'): ?>
                                            <small class="d-block text-muted mt-1" style="font-size: 0.68rem;">(Pendiente de pago)</small>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <!-- Enlace a vista pública si está visible -->
                                        <?php if ($l['es_visible']): ?>
                                            <a href="<?= htmlspecialchars($baseUrl) ?>/catalogo/detalle?slug=<?= urlencode($l['slug']) ?>" 
                                               target="_blank" class="btn btn-light btn-sm border rounded-circle" title="Ver en el sitio público" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                                <i class="bi bi-arrow-up-right"></i>
                                            </a>
                                        <?php endif; ?>

                                        <!-- Botón Editar -->
                                        <a href="<?= htmlspecialchars($baseUrl) ?>/admin/lugares/editar?id=<?= $l['id_lugar'] ?>" 
                                           class="btn btn-light btn-sm border rounded-circle" title="Editar Ficha" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        <!-- Formulario Toggle Habilitación -->
                                        <form action="<?= htmlspecialchars($baseUrl) ?>/admin/lugares/cambiar-habilitacion" method="POST" class="d-inline m-0">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                            <input type="hidden" name="id_lugar" value="<?= $l['id_lugar'] ?>">
                                            <input type="hidden" name="habilitado" value="<?= $l['habilitado'] ? '0' : '1' ?>">
                                            <button type="submit" class="btn btn-sm rounded-circle <?= $l['habilitado'] ? 'btn-outline-danger' : 'btn-outline-success' ?>" 
                                                    title="<?= $l['habilitado'] ? 'Suspender/Deshabilitar' : 'Habilitar' ?>"
                                                    style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                                <i class="bi <?= $l['habilitado'] ? 'bi-slash-circle' : 'bi-check-circle' ?>"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-6 d-block mb-2"></i>
                                No existen lugares registrados en este momento.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>