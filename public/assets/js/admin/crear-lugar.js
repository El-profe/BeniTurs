document.addEventListener('DOMContentLoaded', () => {
    const inputFotos = document.getElementById('fotografias');
    const previews = document.getElementById('previsualizacionFotos');
    const inputPortada = document.getElementById('fotoPortada');
    const inputCoordenadas = document.getElementById('coordenadasGps');
    const panelManual = document.getElementById('ubicacionManual');
    const panelMapa = document.getElementById('ubicacionMapa');
    const estadoMapa = document.getElementById('estadoMapaNuevaFicha');
    const botonesModo = document.querySelectorAll('[data-location-mode]');
    let mapa;
    let marcador;

    const elegirPortada = (indice) => {
        inputPortada.value = String(indice);
        previews.querySelectorAll('[data-foto-indice]').forEach((elemento) => {
            elemento.classList.toggle('is-cover', Number(elemento.dataset.fotoIndice) === indice);
        });
    };

    inputFotos?.addEventListener('change', async () => {
        let archivos = Array.from(inputFotos.files || []).slice(0, 6);
        if (!archivos.length) {
            previews.replaceChildren();
            return;
        }

        // Si CompresorImagen está disponible, optimizar en el cliente antes de la subida
        if (window.CompresorImagen) {
            previews.innerHTML = '<div class="col-12 py-3 text-center text-muted small"><span class="spinner-border spinner-border-sm me-2 text-success"></span> Optimizando fotos en el navegador para subida rápida...</div>';
            const resultados = await Promise.all(archivos.map(a => window.CompresorImagen.comprimir(a, { maxDimension: 1280, calidad: 0.82 })));
            if (typeof DataTransfer !== 'undefined') {
                const dt = new DataTransfer();
                resultados.forEach(r => dt.items.add(r.file));
                inputFotos.files = dt.files;
            }
            archivos = Array.from(inputFotos.files || []);
        }

        previews.replaceChildren();
        archivos.forEach((archivo, indice) => {
            const tarjeta = document.createElement('button');
            tarjeta.type = 'button';
            tarjeta.className = 'image-preview-card';
            tarjeta.dataset.fotoIndice = String(indice);
            tarjeta.setAttribute('aria-label', `Elegir ${archivo.name} como portada`);
            const imagen = document.createElement('img');
            imagen.alt = `Previsualización de ${archivo.name}`;
            imagen.src = URL.createObjectURL(archivo);
            imagen.addEventListener('load', () => URL.revokeObjectURL(imagen.src), { once: true });
            const etiqueta = document.createElement('span');
            etiqueta.innerHTML = '<i class="bi bi-star-fill"></i> Portada';
            tarjeta.append(imagen, etiqueta);
            tarjeta.addEventListener('click', () => elegirPortada(indice));
            previews.append(tarjeta);
        });
        elegirPortada(0);
    });

    const colocarMarcador = (latitud, longitud, actualizarCampo = true) => {
        if (!mapa || !window.L) return;
        if (marcador) marcador.setLatLng([latitud, longitud]);
        else marcador = L.marker([latitud, longitud], { draggable: true }).addTo(mapa);
        marcador.on('dragend', () => {
            const punto = marcador.getLatLng();
            colocarMarcador(punto.lat, punto.lng);
        });
        if (actualizarCampo) inputCoordenadas.value = `${Number(latitud).toFixed(6)}, ${Number(longitud).toFixed(6)}`;
        estadoMapa.textContent = `Ubicación seleccionada: ${Number(latitud).toFixed(6)}, ${Number(longitud).toFixed(6)}.`;
    };

    const iniciarMapa = () => {
        if (mapa || !window.L) return;
        mapa = L.map('mapaNuevaFicha').setView([-14.8333, -64.9000], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(mapa);
        mapa.on('click', (evento) => colocarMarcador(evento.latlng.lat, evento.latlng.lng));
        const coincidencia = (inputCoordenadas.value || '').match(/^\s*(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)\s*$/);
        if (coincidencia) colocarMarcador(Number(coincidencia[1]), Number(coincidencia[2]), false);
    };

    botonesModo.forEach((boton) => boton.addEventListener('click', () => {
        const mapaActivo = boton.dataset.locationMode === 'mapa';
        botonesModo.forEach((item) => item.classList.toggle('active', item === boton));
        panelManual.classList.toggle('d-none', mapaActivo);
        panelMapa.classList.toggle('d-none', !mapaActivo);
        if (mapaActivo) {
            iniciarMapa();
            setTimeout(() => mapa?.invalidateSize(), 0);
        }
    }));

    inputCoordenadas?.addEventListener('change', () => {
        const coincidencia = inputCoordenadas.value.match(/^\s*(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)\s*$/);
        if (coincidencia && mapa) colocarMarcador(Number(coincidencia[1]), Number(coincidencia[2]), false);
    });

    // Control de Aprovisionamiento Comercial
    const radiosTipo = document.querySelectorAll('input[name="tipo_lugar"]');
    const seccionComercial = document.getElementById('seccionAprovisionamientoComercial');
    const switchCrear = document.getElementById('switchCrearCuenta');
    const camposAprov = document.getElementById('camposAprovisionamiento');

    function actualizarVisibilidadComercial() {
        const esComercial = document.querySelector('input[name="tipo_lugar"]:checked')?.value === 'COMERCIAL';
        if (seccionComercial) {
            seccionComercial.classList.toggle('d-none', !esComercial);
        }
    }

    radiosTipo.forEach(radio => {
        radio.addEventListener('change', actualizarVisibilidadComercial);
    });
    actualizarVisibilidadComercial();

    switchCrear?.addEventListener('change', () => {
        if (camposAprov) {
            camposAprov.classList.toggle('d-none', !switchCrear.checked);
        }
    });
});

