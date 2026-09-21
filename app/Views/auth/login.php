<div class="container py-5 my-md-4">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="p-4 text-center hero-beni text-white">
                    <div class="icon-bubble mx-auto mb-3 bg-white text-success">
                        <i class="bi bi-person-lock fs-2"></i>
                    </div>
                    <h4 class="fw-bold mb-1">Acceso Administrativo</h4>
                    <p class="small text-white-50 mb-0">Directorio Turístico y Comercial de Trinidad</p>
                </div>

                <div class="card-body p-4 p-md-5 bg-white">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger d-flex align-items-center gap-2 small rounded-3" role="alert">
                            <i class="bi bi-exclamation-octagon-fill fs-5"></i>
                            <div><?= htmlspecialchars($error) ?></div>
                        </div>
                    <?php endif; ?>

                    <form action="<?= htmlspecialchars($baseUrl) ?>/admin/login" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Usuario o Correo Electrónico</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                                <input type="text" name="usuario" class="form-control border-start-0 ps-0" placeholder="admin" required autofocus>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold small">Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-key text-muted"></i></span>
                                <input type="password" name="password" class="form-control border-start-0 ps-0" placeholder="••••••••" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success w-100 py-2 rounded-pill fw-bold shadow-sm">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Iniciar Sesión
                        </button>
                    </form>

                    <div class="text-center mt-4 pt-2 border-top">
                        <a href="<?= htmlspecialchars($baseUrl) ?>/" class="text-decoration-none text-muted small">
                            <i class="bi bi-arrow-left me-1"></i> Volver a la guía de Trinidad
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>