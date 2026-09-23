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
    const chips = document.querySelectorAll('.chip-filter');
    const contenedor = document.getElementById('gridLugaresCatalogo') || document.getElementById('contenedorLugares');
    const contador = document.getElementById('contadorResultados');
    const btnLimpiar = document.getElementById('btnLimpiarFiltros');

    if (!contenedor) return;

    const btnCerca = document.getElementById('btnCercaDeMi');
    const btnCercaTexto = document.getElementById('btnCercaDeMiTexto');
    const gridLugares = contenedor;
    const alertaGeo = document.getElementById('alertaGeolocalizacion');
    const alertaGeoTexto = document.getElementById('alertaGeoTexto');


    let geolocalizacionActiva = false;
    let ubicacionUsuario = null;
    let ordenOriginalTarjetas = Array.from(contenedor.querySelectorAll('.tarjeta-lugar-col'));

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
        alertaGeo.className = `alert alert-${tipo} alert-dismissible fade show rounded-4 small mb-4 border-0 shadow-sm`;
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
                            <span class="badge bg-success text-white shadow-sm rounded-pill px-2 py-1 fw-bold" style="font-size: 0.72rem;">
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
            ? `Se ordenaron ${conCoordenadas} lugares por distancia en línea recta. Los lugares sin coordenadas válidas aparecen al final.`
            : 'No hay lugares con coordenadas válidas en estos resultados.', conCoordenadas ? 'success' : 'info');
    }

    // Restaurar orden predeterminado
    function restaurarOrdenOriginal() {
        ordenOriginalTarjetas.forEach(tarjeta => {
            const contenedorBadge = tarjeta.querySelector('.badge-distancia-container');
            if (contenedorBadge) contenedorBadge.innerHTML = '';
            delete tarjeta.dataset.distancia;
            gridLugares.appendChild(tarjeta);
        });

        btnCerca.classList.remove('btn-success', 'text-white');
        btnCerca.classList.add('btn-outline-success');
        btnCercaTexto.innerText = 'Lugares cerca de mí';
        if (alertaGeo) alertaGeo.classList.add('d-none');
        geolocalizacionActiva = false;
        ubicacionUsuario = null;
    }

    // Manejador del botón "Cerca de mí"
    btnCerca?.addEventListener('click', () => {
        if (geolocalizacionActiva) {
            restaurarOrdenOriginal();
            return;
        }

        if (!navigator.geolocation) {
            mostrarAlerta('Tu navegador no admite la función de geolocalización.', 'warning');
            return;
        }

        // Estado visual de carga
        btnCerca.disabled = true;
        btnCercaTexto.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Ubicando...';

        navigator.geolocation.getCurrentPosition(
            (posicion) => {
                const userLat = posicion.coords.latitude;
                const userLng = posicion.coords.longitude;

                ubicacionUsuario = { lat: userLat, lng: userLng };
                ordenarLugaresPorDistancia(userLat, userLng);

                btnCerca.disabled = false;
                btnCerca.classList.remove('btn-outline-success');
                btnCerca.classList.add('btn-success', 'text-white');
                btnCercaTexto.innerHTML = '<i class="bi bi-x-circle me-1"></i> Quitar filtro de distancia';
                geolocalizacionActiva = true;
            },
            (error) => {
                btnCerca.disabled = false;
                btnCercaTexto.innerText = 'Lugares cerca de mí';

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

    let categoriaSeleccionada = document.querySelector('.chip-filter.active')?.getAttribute('data-categoria') || '';
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
            chips.forEach(c => c.classList.remove('active'));
            chip.classList.add('active');
            categoriaSeleccionada = chip.getAttribute('data-categoria') || '';
            filtrarLugares();
        });
    });

    if (btnLimpiar) {
        btnLimpiar.addEventListener('click', () => {
            if (inputBuscar) inputBuscar.value = '';
            if (inputBuscarLugar) inputBuscarLugar.value = '';
            categoriaSeleccionada = '';
            chips.forEach(c => c.classList.remove('active'));
            const primerChip = document.querySelector('.chip-filter[data-categoria=""]');
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
            const esPublico = lug.tipo_lugar === 'PUBLICO';
            const badgeClass = esPublico ? 'badge-publico' : 'badge-comercio';
            const badgeTexto = esPublico ? 'Gratuito' : 'Comercio';

            let desc = lug.descripcion || '';
            if (desc.length > 140) {
                desc = desc.substring(0, 140) + '...';
            }

            const botonWhatsapp = lug.whatsapp_contacto ? `
                <a href="https://wa.me/${lug.whatsapp_contacto.replace(/\D/g, '')}" target="_blank" rel="noopener noreferrer" 
                   class="btn btn-outline-success rounded-pill btn-sm px-3" title="Contactar por WhatsApp">
                    <i class="bi bi-whatsapp"></i>
                </a>
            ` : '';

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
                        <span class="badge ${badgeClass} position-absolute top-0 start-0 m-3 rounded-pill fw-bold shadow-sm">
                            ${badgeTexto}
                        </span>
                    </div>
                `;
            } else {
                cabeceraHtml = `
                    <div class="card-header-place p-4 text-white text-center position-relative">
                        <span class="badge ${badgeClass} position-absolute top-0 start-0 m-3 rounded-pill fw-bold">
                            ${badgeTexto}
                        </span>
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
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-light text-success border border-success-subtle rounded-pill extra-small">
                                        <i class="bi ${lug.categoria_icono || 'bi-tag'} me-1"></i> ${escapeHtml(lug.categoria)}
                                    </span>
                                </div>
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
                                ${botonWhatsapp}
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
