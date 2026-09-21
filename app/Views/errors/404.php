<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Recurso no encontrado | <?= htmlspecialchars($appName ?? 'Trinidad Turismo') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($baseUrl ?? '') ?>/assets/css/app.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center min-vh-100">
    <div class="container text-center py-5">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5">
                    <div class="display-1 fw-bold text-success mb-2">404</div>
                    <i class="bi bi-geo-alt-fill fs-1 text-warning mb-3"></i>
                    <h3 class="fw-bold text-dark mb-2">Ruta no encontrada</h3>
                    <p class="text-muted small mb-4">
                        El lugar, página o recurso solicitado no existe en la guía de Trinidad o fue reubicado.
                    </p>
                    <a href="<?= htmlspecialchars($baseUrl ?? '/') ?>/" class="btn btn-success rounded-pill px-4 py-2 fw-semibold">
                        <i class="bi bi-arrow-left me-1"></i> Volver a la página principal
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>