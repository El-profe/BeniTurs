<div class="business-heading">
    <div><div class="business-eyebrow">Ayuda a tus clientes a encontrarte</div><h1 class="mb-0">Ubicación de mi local</h1><p>Escribe la dirección y marca la entrada de tu negocio en el mapa.</p></div>
</div>
<?php if (!empty($mensaje)): ?><div class="alert alert-success" role="status"><?= htmlspecialchars($mensaje) ?></div><?php endif; ?>
<?php if (!empty($error)): ?><div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<form action="<?= htmlspecialchars($baseUrl) ?>/negocio/ubicacion/guardar" method="post" id="locationForm">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
    <div class="row g-4">
        <div class="col-lg-5">
            <section class="admin-card p-4 h-100">
                <h2 class="mb-4"><span class="location-step">1</span> Dirección del negocio</h2>
                <label for="businessAddress" class="form-label">Calle, avenida o barrio</label>
                <input id="businessAddress" name="direccion" class="form-control mb-3" maxlength="255" required placeholder="Ej. Av. 6 de Agosto, esquina..." value="<?= htmlspecialchars($datosUbicacion['direccion'] ?? '') ?>">
                <label for="businessReference" class="form-label">Una referencia para llegar <span class="text-muted">(opcional)</span></label>
                <input id="businessReference" name="referencia_ubicacion" class="form-control" maxlength="255" placeholder="Ej. Frente a la plaza, puerta verde" value="<?= htmlspecialchars($datosUbicacion['referencia_ubicacion'] ?? '') ?>">
                <hr class="my-4">
                <h2 class="mb-3">¿Estás en tu local?</h2>
                <button type="button" id="locateBusiness" class="btn btn-outline-success w-100"><i class="bi bi-crosshair" aria-hidden="true"></i> Usar mi ubicación actual</button>
                <p class="form-text mt-2">Usa esta opción solo si estás en el negocio. Revisa el marcador antes de guardar.</p>
                <label for="businessCoordinates" class="form-label mt-3">Coordenadas del punto</label>
                <input id="businessCoordinates" name="coordenadas_gps" class="form-control" maxlength="100" required placeholder="-14.8333, -64.9000" aria-describedby="coordinatesHelp" value="<?= htmlspecialchars($datosUbicacion['coordenadas_gps'] ?? '') ?>">
                <p id="coordinatesHelp" class="form-text">Se completan al tocar el mapa. También puedes pegarlas desde Google Maps, en orden latitud, longitud.</p>
            </section>
        </div>
        <div class="col-lg-7">
            <section class="admin-card p-4">
                <h2><span class="location-step">2</span> Marca la entrada del local</h2>
                <p class="text-muted small">Toca el mapa para colocar el punto. Puedes mover el marcador para ajustarlo.</p>
                <div id="businessMap" class="location-map" aria-label="Mapa para elegir la ubicación del local"></div>
                <p id="locationStatus" class="location-status mt-3 mb-0" role="status">Selecciona un punto y guarda tu ubicación.</p>
                <noscript><p class="text-muted small mt-3">Puedes guardar tu ubicación escribiendo las coordenadas en el campo correspondiente.</p></noscript>
            </section>
        </div>
    </div>
    <div class="d-flex flex-wrap align-items-center gap-3 mt-4">
        <button type="submit" class="btn btn-success px-4"><i class="bi bi-check2" aria-hidden="true"></i> Guardar ubicación</button>
        <span class="text-muted small">El mapa de tu ficha usará este punto.</span>
    </div>
</form>
