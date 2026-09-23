document.addEventListener('DOMContentLoaded', () => {
    const menu = document.getElementById('navbarMain');
    menu?.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', () => {
            if (!menu.classList.contains('show') || !window.bootstrap?.Collapse) return;
            const destination = new URL(link.href);
            if (destination.pathname === location.pathname && destination.search === location.search && destination.hash) {
                menu.addEventListener('hidden.bs.collapse', () => {
                    document.getElementById(destination.hash.slice(1))?.scrollIntoView({ block: 'start' });
                }, { once: true });
            }
            window.bootstrap.Collapse.getOrCreateInstance(menu).hide();
        });
    });
    const inputBuscar = document.getElementById('inputBuscarHero');
    const inputBuscarLugar = document.getElementById('inputBuscarLugar');
    // Identificar los filtros por su función, independientemente de su diseño.
    const chips = Array.from(document.querySelectorAll('#explorar button[data-categoria]:not(#btnCercaDeMi)'));
    const contenedor = document.getElementById('gridLugaresCatalogo') || document.getElementById('contenedorLugares');
    const contador = document.getElementById('contadorResultados');
    const btnLimpiar = document.getElementById('btnLimpiarFiltros');

    if (!contenedor) return;

    const btnCerca = document.getElementById('btnCercaDeMi');
    const gridLugares = contenedor;
    const alertaGeo = document.getElementById('alertaGeolocalizacion');
    const alertaGeoTexto = document.getElementById('alertaGeoTexto');


    let geolocalizacionActiva = false;
    let ubicacionUsuario = null;
    let solicitudUbicacion = 0;
    let ordenOriginalTarjetas = Array.from(contenedor.querySelectorAll('.tarjeta-lugar-col'));

    function actualizarBotonCerca(cargando = false) {
        if (!btnCerca) return;
        btnCerca.disabled = cargando;
        btnCerca.classList.toggle('active', geolocalizacionActiva);
        btnCerca.setAttribute('aria-pressed', String(geolocalizacionActiva));
        btnCerca.setAttribute('aria-busy', String(cargando));
        const etiqueta = cargando ? 'Obteniendo tu ubicación…'
            : geolocalizacionActiva ? 'Quitar orden por cercanía' : 'Lugares cerca de mí';
        btnCerca.title = etiqueta;
        btnCerca.setAttribute('aria-label', etiqueta);
        btnCerca.innerHTML = cargando
            ? '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span>'
            : '<i class="bi bi-geo-alt-fill" aria-hidden="true"></i>';
    }

    actualizarBotonCerca();

    // Fórmula Matemática de Haversine (Distancia en línea recta sobre esfera)
    function calcularDistanciaHaversine(lat1, lon1, lat2, lon2) {
        const R = 6371; // Radio de la Tierra en kilómetros
        const dLat = (lat2 - lat1) * (Math.PI / 180);
        const dLon = (lon2 - lon1) * (Math.PI / 180);

        const valor = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                  Math.cos(lat1 * (Math.PI / 180)) * Math.cos(lat2 * (Math.PI / 180)) *
                  Math.sin(dLon / 2) * Math.sin(dLon / 2);

        const a = Math.min(1, Math.max(0, valor));
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c; // Devuelve distancia en kilómetros
    }

    // Formatear distancia amigable (metros o kilómetros)
    function formatearDistancia(km) {
        if (km < 1) {
            const metros = Math.round(km * 1000);
            return `A ${metros} m`;
        }
        return `A ${km.toFixed(1)} km`;
    }

    // Mostrar mensaje de feedback
    function mostrarAlerta(mensaje, tipo = 'info') {
        if (!alertaGeo || !alertaGeoTexto) return;
        alertaGeo.className = `alert alert-${tipo} alert-dismissible fade show rounded-4 small mb-4 border-0 shadow-sm mx-auto text-center`;
        alertaGeoTexto.textContent = mensaje;
        alertaGeo.classList.remove('d-none');
    }

    // Procesar ordenamiento de tarjetas según ubicación del usuario
    function ordenarLugaresPorDistancia(userLat, userLng) {
        const tarjetas = Array.from(contenedor.querySelectorAll('.tarjeta-lugar-col'));
        let conCoordenadas = 0;

        tarjetas.forEach(tarjeta => {
            const coordsRaw = tarjeta.getAttribute('data-coordenadas') || '';
            const contenedorBadge = tarjeta.querySelector('.badge-distancia-container');

            if (coordsRaw.split(',').length === 2) {
                const [latStr, lngStr] = coordsRaw.split(',');
                const latLugar = latStr.trim() === '' ? NaN : Number(latStr.trim());
                const lngLugar = lngStr.trim() === '' ? NaN : Number(lngStr.trim());

                if (Number.isFinite(latLugar) && Number.isFinite(lngLugar) && Math.abs(latLugar) <= 90 && Math.abs(lngLugar) <= 180) {
                    const distanciaKm = calcularDistanciaHaversine(userLat, userLng, latLugar, lngLugar);
                    tarjeta.dataset.distancia = distanciaKm;

                    if (contenedorBadge) {
                        contenedorBadge.innerHTML = `
                            <span class="badge bg-danger text-white shadow-sm rounded-pill px-2 py-1 fw-bold" style="font-size: 0.72rem;">
                                <i class="bi bi-cursor-fill me-1"></i>${formatearDistancia(distanciaKm)}
                            </span>
                        `;
                    }
                    conCoordenadas++;
                    return;
                }
            }

            // Si no tiene GPS válido
            tarjeta.dataset.distancia = 999999;
            if (contenedorBadge) contenedorBadge.innerHTML = '';
        });

        // Ordenar tarjetas de menor a mayor distancia
        tarjetas.sort((a, b) => {
            return parseFloat(a.dataset.distancia || 999999) - parseFloat(b.dataset.distancia || 999999);
        });

        // Reinsertar tarjetas en el DOM en su nuevo orden
        tarjetas.forEach(t => gridLugares.appendChild(t));

        mostrarAlerta(conCoordenadas
            ? `Se ordenaron ${conCoordenadas} lugares por distancia en línea recta. Los lugares sin coordenadas válidas aparecen al final. Pulsa de nuevo el icono GPS para restaurar el orden.`
            : 'No hay lugares con coordenadas válidas en estos resultados.', conCoordenadas ? 'success' : 'info');
    }

    // Restaurar orden predeterminado
    function restaurarOrdenOriginal() {
        solicitudUbicacion++;
        ordenOriginalTarjetas.forEach(tarjeta => {
            const contenedorBadge = tarjeta.querySelector('.badge-distancia-container');
            if (contenedorBadge) contenedorBadge.innerHTML = '';
            delete tarjeta.dataset.distancia;
            gridLugares.appendChild(tarjeta);
        });

        if (alertaGeo) alertaGeo.classList.add('d-none');
        geolocalizacionActiva = false;
        ubicacionUsuario = null;
        actualizarBotonCerca();
    }

    // Manejador del botón "Cerca de mí"
    btnCerca?.addEventListener('click', () => {
        if (btnCerca.disabled) return;
        if (geolocalizacionActiva) {
            restaurarOrdenOriginal();
            return;
        }

        if (!navigator.geolocation) {
            mostrarAlerta('Tu navegador no admite la función de geolocalización.', 'warning');
            return;
        }

        // Estado visual de carga
        const solicitud = ++solicitudUbicacion;
        actualizarBotonCerca(true);

        navigator.geolocation.getCurrentPosition(
            (posicion) => {
                if (solicitud !== solicitudUbicacion) return;
                const userLat = posicion.coords.latitude;
                const userLng = posicion.coords.longitude;

                ubicacionUsuario = { lat: userLat, lng: userLng };
                ordenarLugaresPorDistancia(userLat, userLng);

                geolocalizacionActiva = true;
                actualizarBotonCerca();
            },
            (error) => {
                if (solicitud !== solicitudUbicacion) return;
                actualizarBotonCerca();

                let mensajeError = 'No se pudo obtener tu ubicación.';
                if (error.code === error.PERMISSION_DENIED) {
                    mensajeError = 'Permiso de ubicación denegado. Permite el acceso a la ubicación en tu navegador para ordenar por cercanía.';
                } else if (error.code === error.POSITION_UNAVAILABLE) {
                    mensajeError = 'La señal GPS o de red no está disponible en este momento.';
                } else if (error.code === error.TIMEOUT) {
                    mensajeError = 'Se agotó el tiempo para obtener tu ubicación. Inténtalo de nuevo.';
                }

                mostrarAlerta(mensajeError, 'warning');
            },
            {
                enableHighAccuracy: true,
                timeout: 8000,
                maximumAge: 0
            }
        );
    });

    // Bootstrap elimina las alertas al cerrarlas; conservar esta para reutilizarla.
    alertaGeo?.addEventListener('close.bs.alert', event => {
        event.preventDefault();
        alertaGeo.classList.add('d-none');
        alertaGeo.classList.remove('show');
    });

    let categoriaSeleccionada = chips.find(chip => chip.classList.contains('active'))?.getAttribute('data-categoria') || '';
    let debounceTimer = null;
    let ultimaConsulta = 0;

    const filtrarLugares = async () => {
        clearTimeout(debounceTimer);
        const consulta = ++ultimaConsulta;
        const query = encodeURIComponent((inputBuscar?.value || inputBuscarLugar?.value || '').trim());
        const cat = encodeURIComponent(categoriaSeleccionada);
        const endpoint = `/api/lugares/buscar?q=${query}&categoria=${cat}`;

        try {
            const data = await http.get(endpoint);
            if (consulta !== ultimaConsulta) return;
            renderizarCuadricula(data.resultados || []);
            ordenOriginalTarjetas = Array.from(contenedor.querySelectorAll('.tarjeta-lugar-col'));
            if (geolocalizacionActiva && ubicacionUsuario) {
                ordenarLugaresPorDistancia(ubicacionUsuario.lat, ubicacionUsuario.lng);
            }
            if (contador) {
                contador.textContent = `${data.total} lugares encontrados`;
            }
        } catch (error) {
            console.error('Error al consultar lugares:', error);
        }
    };

    [inputBuscar, inputBuscarLugar].filter(Boolean).forEach(input => {
        input.addEventListener('input', () => {
            if (inputBuscar) inputBuscar.value = input.value;
            if (inputBuscarLugar) inputBuscarLugar.value = input.value;
            ultimaConsulta++;
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(filtrarLugares, 300);
        });
    });

    chips.forEach(chip => {
        chip.addEventListener('click', () => {
            restaurarOrdenOriginal();
            chips.forEach(c => c.classList.remove('active'));
            chip.classList.add('active');
            categoriaSeleccionada = chip.getAttribute('data-categoria') || '';
            filtrarLugares();
        });
    });

    if (btnLimpiar) {
        btnLimpiar.addEventListener('click', () => {
            restaurarOrdenOriginal();
            if (inputBuscar) inputBuscar.value = '';
            if (inputBuscarLugar) inputBuscarLugar.value = '';
            categoriaSeleccionada = '';
            chips.forEach(c => c.classList.remove('active'));
            const primerChip = chips.find(chip => chip.getAttribute('data-categoria') === '');
            if (primerChip) primerChip.classList.add('active');
            filtrarLugares();
        });
    }

    document.querySelector('[data-catalogo-todos]')?.addEventListener('click', () => {
        if (!location.search) btnLimpiar?.click();
    });

    function renderizarCuadricula(lugares) {
        contenedor.innerHTML = '';

        if (lugares.length === 0) {
            contenedor.innerHTML = `
                <div class="col-12 text-center py-5">
                    <i class="bi bi-search display-4 text-muted mb-3 d-block"></i>
                    <h5 class="text-muted fw-bold">No se encontraron resultados</h5>
                    <p class="text-secondary small">Intenta buscar con otra palabra clave o selecciona otra categoría.</p>
                </div>
            `;
            return;
        }

        const baseUrl = window.APP_CONFIG?.baseUrl || '';

        lugares.forEach(lug => {
            lug.categoria_icono = escapeHtml(lug.categoria_icono || 'bi-geo-alt');

            let desc = lug.descripcion || '';
            if (desc.length > 140) {
                desc = desc.substring(0, 140) + '...';
            }

            const horarioHtml = lug.horario_atencion ? `
                <div class="text-muted extra-small text-truncate">
                    <i class="bi bi-clock me-1 text-warning"></i> ${escapeHtml(lug.horario_atencion)}
                </div>
            ` : '';

            let cabeceraHtml = '';
            if (lug.imagen) {
                cabeceraHtml = `
                    <div class="position-relative" style="height: 200px; background-color: #092611;">
                        <img src="${baseUrl}/imagen?f=${encodeURIComponent(lug.imagen)}" 
                             alt="${escapeHtml(lug.nombre)}" 
                             class="w-100 h-100 object-fit-cover">
                        <span class="badge bg-dark bg-opacity-75 text-white position-absolute top-0 start-0 m-3 rounded-pill fw-bold shadow-sm">
                            <i class="bi ${lug.categoria_icono} me-1"></i> ${escapeHtml(lug.categoria)}
                        </span>
                    </div>
                `;
            } else {
                cabeceraHtml = `
                    <div class="card-header-place p-4 text-white text-center position-relative">
                        <div class="place-icon-bubble mx-auto mb-2 shadow-sm">
                            <i class="bi ${lug.categoria_icono || 'bi-geo-alt'} fs-3 text-success"></i>
                        </div>
                        <h5 class="fw-bold mb-1 text-white text-truncate">${escapeHtml(lug.nombre)}</h5>
                        <span class="extra-small text-white-50 text-uppercase fw-semibold tracking-wide">
                            ${escapeHtml(lug.categoria)}
                        </span>
                    </div>
                `;
            }

            const cardHtml = `
                <div class="col-md-6 col-lg-4 item-lugar tarjeta-lugar-col"
                     data-id="${escapeHtml(lug.id_lugar)}"
                     data-nombre="${escapeHtml((lug.nombre || '').toLowerCase())}"
                     data-categoria="${escapeHtml(lug.id_categoria)}"
                     data-coordenadas="${escapeHtml(lug.coordenadas_gps || '')}">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden position-relative place-card">
                        ${cabeceraHtml}
                        <div class="badge-distancia-container position-absolute top-0 end-0 m-3"></div>
                        <div class="card-body p-4 d-flex flex-column">
                            ${lug.imagen ? `
                                <h5 class="fw-bold text-dark text-truncate mb-2">${escapeHtml(lug.nombre)}</h5>
                            ` : ''}
                            <p class="card-text text-secondary small flex-grow-1 line-clamp-3 mb-3">
                                ${escapeHtml(desc)}
                            </p>
                            ${horarioHtml ? `<div class="place-meta border-top pt-3 mb-3 small">
                                ${horarioHtml}
                            </div>` : ''}
                            <div class="d-flex gap-2">
                                <a href="${baseUrl}/catalogo/detalle?slug=${encodeURIComponent(lug.slug)}" 
                                   class="btn btn-success rounded-pill w-100 fw-semibold btn-sm py-2">
                                    Ver Ficha <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            contenedor.insertAdjacentHTML('beforeend', cardHtml);
        });
    }

    function escapeHtml(str) {
        return String(str ?? '').replace(/[&<>"']/g, char => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
        }[char]));
    }
});
