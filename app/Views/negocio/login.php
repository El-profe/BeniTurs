<div class="container py-5 my-md-4">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="p-4 text-center hero-beni text-white">
                    <div class="icon-bubble mx-auto mb-3 bg-white text-success">
                        <i class="bi bi-shop-window fs-2"></i>
                    </div>
                    <h4 class="fw-bold mb-1">Acceso para Negocios</h4>
                    <p class="small text-white-50 mb-0">Gestiona tus fotos y publica promociones</p>
                </div>

                <div class="card-body p-4 p-md-5 bg-white">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger d-flex align-items-center gap-2 small rounded-3 mb-4">
                            <i class="bi bi-exclamation-octagon-fill fs-5"></i>
                            <div><?= htmlspecialchars($error) ?></div>
                        </div>
                    <?php endif; ?>

                    <form action="<?= htmlspecialchars($baseUrl) ?>/negocio/login" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Usuario o Correo Registrado</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                                <input type="text" name="usuario" class="form-control border-start-0 ps-0" placeholder="negocio_demo" required autofocus>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold small">Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-key text-muted"></i></span>
                                <input type="password" name="password" class="form-control border-start-0 ps-0" placeholder="••••••••" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-warning w-100 py-2 rounded-pill fw-bold text-dark shadow-sm">
                            <i class="bi bi-door-open-fill me-1"></i> Entrar a Mi Negocio
                        </button>
                    </form>

                    <div class="text-center mt-4 pt-2 border-top">
                        <a href="<?= htmlspecialchars($baseUrl) ?>/solicitar-incorporacion" class="text-decoration-none text-success small fw-semibold">
                            ¿Aún no registraste tu negocio? Solicítalo aquí
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>