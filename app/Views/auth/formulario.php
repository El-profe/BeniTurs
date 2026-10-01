<?php
$esAdmin = $tipoAcceso === 'admin';
$escape = static fn($valor) => htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
?>
<link rel="stylesheet" href="<?= $escape($baseUrl) ?>/assets/css/login.css?v=<?= filemtime(dirname(__DIR__, 3) . '/public/assets/css/login.css') ?>">
<section class="container py-5" aria-labelledby="tituloAcceso">
    <div class="login-shell mx-auto">
        <aside class="login-brand p-4 p-lg-5">
            <div class="login-logo-wrap mb-4">
                <a href="<?= $escape($baseUrl) ?>/" title="Volver al Catálogo Público">
                    <img src="<?= $escape($baseUrl) ?>/assets/img/logo.png" 
                         alt="BeniTurs" 
                         class="login-logo img-fluid">
                </a>
            </div>
            <span class="badge rounded-pill bg-warning text-dark mb-4 align-self-start">
                <i class="bi <?= $esAdmin ? 'bi-shield-lock-fill' : 'bi-shop' ?> me-1" aria-hidden="true"></i>
                <?= $esAdmin ? 'Panel Administrativo' : 'Portal de Comercios' ?> · Trinidad
            </span>
            <h2 class="fw-bold mb-3"><?= $esAdmin ? 'Todo listo para administrar.' : 'Tu negocio, más cerca de todos.' ?></h2>
            <p class="mb-0"><?= $esAdmin ? 'Revisa solicitudes y gestiona los lugares de nuestra guía.' : 'Actualiza tu información, comparte fotografías y gestiona tus promociones.' ?></p>
        </aside>
        <div class="bg-white p-4 p-lg-5">
            <nav class="login-tabs mb-4" aria-label="Tipo de acceso">
                <a href="<?= $escape($baseUrl) ?>/negocio/login" <?= !$esAdmin ? 'aria-current="page"' : '' ?>>Mi negocio</a>
                <a href="<?= $escape($baseUrl) ?>/admin/login" <?= $esAdmin ? 'aria-current="page"' : '' ?>>Administración</a>
            </nav>
            <h1 class="h3 fw-bold mb-2" id="tituloAcceso"><?= $esAdmin ? 'Acceso administrativo' : 'Bienvenido a tu negocio' ?></h1>
            <p class="text-secondary small mb-4"><?= $esAdmin ? 'Ingresa con tu cuenta de administrador.' : 'Usa el usuario y la contraseña que recibiste al aprobarse tu solicitud.' ?></p>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger small" role="alert"><i class="bi bi-exclamation-circle me-2" aria-hidden="true"></i><?= $escape($error) ?></div>
            <?php endif; ?>
            <form action="<?= $escape($baseUrl) ?>/<?= $tipoAcceso ?>/login" method="post" data-login-form>
                <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>">
                <div class="mb-3">
                    <label for="loginUsuario" class="form-label fw-semibold">Usuario o correo electrónico</label>
                    <input id="loginUsuario" name="usuario" type="text" class="form-control form-control-lg" value="<?= $escape($usuarioAnterior ?? '') ?>" autocomplete="username" autocapitalize="none" spellcheck="false" maxlength="120" required>
                </div>
                <div class="mb-4">
                    <label for="loginPassword" class="form-label fw-semibold">Contraseña</label>
                    <div class="input-group input-group-lg">
                        <input id="loginPassword" name="password" type="password" class="form-control" autocomplete="current-password" required aria-describedby="avisoMayusculas">
                        <button type="button" class="btn btn-outline-secondary" data-password-toggle aria-controls="loginPassword" aria-label="Mostrar contraseña" aria-pressed="false" title="Mostrar contraseña"><i class="bi bi-eye" aria-hidden="true"></i></button>
                    </div>
                    <small id="avisoMayusculas" class="text-warning-emphasis d-block mt-2" aria-live="polite"></small>
                </div>
                <button type="submit" class="btn btn-success btn-lg w-100 fw-semibold">Entrar <?= $esAdmin ? 'al panel' : 'a mi negocio' ?> <i class="bi bi-arrow-right ms-2" aria-hidden="true"></i></button>
            </form>
            <?php if (!$esAdmin): ?>
                <p class="small text-center mt-4 mb-0">¿Todavía no tienes cuenta? <a class="text-success fw-semibold" href="<?= $escape($baseUrl) ?>/solicitar-incorporacion">Publica tu negocio</a></p>
            <?php endif; ?>
            <a class="d-block text-center text-secondary small mt-4" href="<?= $escape($baseUrl) ?>/"><i class="bi bi-arrow-left me-1" aria-hidden="true"></i> Volver al catálogo</a>
        </div>
    </div>
</section>
<script src="<?= $escape($baseUrl) ?>/assets/js/shared/login.js?v=<?= filemtime(dirname(__DIR__, 3) . '/public/assets/js/shared/login.js') ?>" defer></script>
