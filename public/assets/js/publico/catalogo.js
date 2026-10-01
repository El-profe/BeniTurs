document.addEventListener('DOMContentLoaded', () => {
    // 1. Manejo del menú colapsable en móvil
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

    // 2. Elementos del DOM del catálogo
    const inputBuscar = document.getElementById('inputBuscarHero');
    const inputBuscarLugar = document.getElementById('inputBuscarLugar');
    const chips = Array.from(document.querySelectorAll('#explorar button[data-categoria]:not(#btnCercaDeMi)'));
    const contenedor = document.getElementById('gridLugaresCatalogo') || document.getElementById('contenedorLugares');
    const contador = document.getElementById('contadorResultados');
    const btnLimpiar = document.getElementById('btnLimpiarFiltros');
    const selectMunicipio = document.getElementById('selectMunicipio');

    if (!contenedor) return;

    const btnCerca = document.getElementById('btnCercaDeMi');
    const gridLugares = contenedor;
    const alertaGeo = document.getElementById('alertaGeolocalizacion');
    const alertaGeoTexto = document.getElementById('alertaGeoTexto');

    // Elementos de la Fase 3: Selector de Vistas y Mapa
    const btnVistaGrid = document.getElementById('btnVistaGrid');
    const btnVistaMapa = document.getElementById('btnVistaMapa');
    const mapaWrapper = document.getElementById('mapaCatalogoWrapper');
    const btnCentrarTrinidad = document.getElementById('btnMapaCentrarTrinidad');
    const btnMapaMiUbi = document.getElementById('btnMapaMiUbicacion');
    const mapaConteoEl = document.getElementById('mapaConteoLugares');
    const mapaBadgeRadio = document.getElementById('mapaBadgeRadio');

    // Variables de estado
    let geolocalizacionActiva = false;
    let ubicacionUsuario = null;
    let precisionUsuario = 0;
    let solicitudUbicacion = 0;
    let ordenOriginalTarjetas = Array.from(contenedor.querySelectorAll('.tarjeta-lugar-col'));
    let modoVistaActual = 'grid'; // 'grid' | 'mapa'
    const RADIO_BUSQUEDA_KM = 1.0; // Radio de búsqueda: 1 km

    // Variables de Leaflet
    let mapa = null;
    let marcadoresLayer = null;
    let marcadorUsuario = null;
    let circuloPrecisionUsuario = null;
    let circuloRadio1Km = null;
    const COORDENADAS_TRINIDAD = [-14.8333, -64.9000];

    // Cargar datos iniciales del catálogo desde el script JSON embebido
    let lugaresActuales = [];
    const scriptLugaresJson = document.getElementById('lugaresInicialesJson');
    if (scriptLugaresJson) {
        try {
            lugaresActuales = JSON.parse(scriptLugaresJson.textContent || '[]');
        } catch (e) {
            console.error('Error parseando lugares iniciales:', e);
        }
    }

    // 3. Funciones de Geolocalización y Haversine
    function actualizarBotonCerca(cargando = false) {
        if (!btnCerca) return;
        btnCerca.disabled = cargando;
        btnCerca.classList.toggle('active', geolocalizacionActiva);
        btnCerca.setAttribute('aria-pressed', String(geolocalizacionActiva));
        btnCerca.setAttribute('aria-busy', String(cargando));
        const etiqueta = cargando ? 'Buscando en radio de 1 km…'
            : geolocalizacionActiva ? 'Quitar filtro de 1 km (Ver todos)' : 'Lugares a menos de 1 km';
        btnCerca.title = etiqueta;
        btnCerca.setAttribute('aria-label', etiqueta);
        btnCerca.innerHTML = cargando
            ? '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span>'
            : '<i class="bi bi-geo-alt-fill" aria-hidden="true"></i>';
    }

    actualizarBotonCerca();

    // Fórmula de Haversine
    function calcularDistanciaHaversine(lat1, lon1, lat2, lon2) {
        const R = 6371; // Radio de la Tierra en kilómetros
        const dLat = (lat2 - lat1) * (Math.PI / 180);
        const dLon = (lon2 - lon1) * (Math.PI / 180);

        const valor = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                      Math.cos(lat1 * (Math.PI / 180)) * Math.cos(lat2 * (Math.PI / 180)) *
                      Math.sin(dLon / 2) * Math.sin(dLon / 2);

        const a = Math.min(1, Math.max(0, valor));
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    }

    function formatearDistancia(km) {
        if (km < 1) {
            const metros = Math.round(km * 1000);
            return `A ${metros} m`;
        }
        return `A ${km.toFixed(1)} km`;
    }

    // Parseo robusto de coordenadas con corrección para Trinidad / Beni
    function parsearCoordenadas(coordsStr) {
        if (!coordsStr || typeof coordsStr !== 'string') return null;
        const partes = coordsStr.split(',');
        if (partes.length !== 2) return null;
        let lat = parseFloat(partes[0].trim());
        let lng = parseFloat(partes[1].trim());
        if (isNaN(lat) || isNaN(lng)) return null;

        // Autocorrección si la latitud se registró positiva en Trinidad/Bolivia (10° a 25° S)
        if (lat > 10 && lat < 25 && lng < -55 && lng > -75) {
            lat = -lat;
        }

        if (Math.abs(lat) > 90 || Math.abs(lng) > 180) return null;
        return { lat, lng };
    }

    function mostrarAlerta(mensaje, tipo = 'info') {
        if (!alertaGeo || !alertaGeoTexto) return;
        alertaGeo.className = `alert alert-${tipo} alert-dismissible fade show rounded-4 small mb-4 border-0 shadow-sm mx-auto text-center`;
        alertaGeoTexto.textContent = mensaje;
        alertaGeo.classList.remove('d-none');
    }

    function ordenarYFiltrarLugaresPorRadio(userLat, userLng) {
        const tarjetas = Array.from(contenedor.querySelectorAll('.tarjeta-lugar-col'));
        let conCoordenadas = 0;
        let lugaresEnRadio = 0;

        tarjetas.forEach(tarjeta => {
            const coordsRaw = tarjeta.getAttribute('data-coordenadas') || '';
            const contenedorBadge = tarjeta.querySelector('.badge-distancia-container');
            const coords = parsearCoordenadas(coordsRaw);

            if (coords) {
                conCoordenadas++;
                const distanciaKm = calcularDistanciaHaversine(userLat, userLng, coords.lat, coords.lng);
                tarjeta.dataset.distancia = distanciaKm;

                if (distanciaKm <= RADIO_BUSQUEDA_KM) {
                    lugaresEnRadio++;
                    tarjeta.classList.remove('d-none');
                    if (contenedorBadge) {
                        contenedorBadge.innerHTML = `
                            <span class="badge bg-danger text-white shadow-sm rounded-pill px-2 py-1 fw-bold" style="font-size: 0.72rem;">
                                <i class="bi bi-cursor-fill me-1"></i>${formatearDistancia(distanciaKm)}
                            </span>
                        `;
                    }
                } else {
                    tarjeta.classList.add('d-none');
                    if (contenedorBadge) contenedorBadge.innerHTML = '';
                }
                return;
            }

            tarjeta.dataset.distancia = 999999;
            tarjeta.classList.add('d-none');
            if (contenedorBadge) contenedorBadge.innerHTML = '';
        });

        // Ordenar tarjetas visibles de menor a mayor distancia
        tarjetas.sort((a, b) => {
            return parseFloat(a.dataset.distancia || 999999) - parseFloat(b.dataset.distancia || 999999);
        });

        tarjetas.forEach(t => gridLugares.appendChild(t));

        // Aviso en caso de no haber lugares dentro del radio de 1 km
        let avisoVacio = document.getElementById('avisoSinLugaresRadio');
        if (lugaresEnRadio === 0) {
            if (!avisoVacio) {
                avisoVacio = document.createElement('div');
                avisoVacio.id = 'avisoSinLugaresRadio';
                avisoVacio.className = 'col-12 text-center py-5';
                avisoVacio.innerHTML = `
                    <div class="card border-0 shadow-sm rounded-4 p-4 mx-auto" style="max-width: 520px; background-color: #fff9f0; border: 1px solid #ffe8cc !important;">
                        <i class="bi bi-broadcast-pin display-4 text-warning mb-2 d-block"></i>
                        <h5 class="fw-bold text-dark mb-1">Sin lugares a menos de 1 km</h5>
                        <p class="text-secondary small mb-3">No encontramos lugares turísticos ni comerciales registrados en un radio de 1 kilómetro de tu ubicación actual.</p>
                        <button type="button" id="btnRestaurarDesdeAviso" class="btn btn-outline-success rounded-pill btn-sm px-4 fw-semibold">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Ver todos los lugares
                        </button>
                    </div>
                `;
                gridLugares.appendChild(avisoVacio);
                document.getElementById('btnRestaurarDesdeAviso')?.addEventListener('click', () => {
                    restaurarOrdenOriginal();
                });
            } else {
                avisoVacio.classList.remove('d-none');
            }
        } else if (avisoVacio) {
            avisoVacio.classList.add('d-none');
        }

        if (contador) {
            contador.textContent = `${lugaresEnRadio} lugares encontrados (en radio de 1 km)`;
        }

        if (mapaBadgeRadio) {
            mapaBadgeRadio.classList.remove('d-none');
        }

        mostrarAlerta(lugaresEnRadio > 0
            ? `Se encontraron ${lugaresEnRadio} lugares en un radio de 1 km a tu alrededor. Pulsa de nuevo el icono GPS para ver todos.`
            : 'No se encontraron lugares en un radio de 1 km desde tu ubicación. Pulsa el botón GPS para ver todos los lugares.',
            lugaresEnRadio > 0 ? 'success' : 'warning');
    }

    function restaurarOrdenOriginal() {
        solicitudUbicacion++;
        ordenOriginalTarjetas.forEach(tarjeta => {
            tarjeta.classList.remove('d-none');
            const contenedorBadge = tarjeta.querySelector('.badge-distancia-container');
            if (contenedorBadge) contenedorBadge.innerHTML = '';
            delete tarjeta.dataset.distancia;
            gridLugares.appendChild(tarjeta);
        });

        const avisoVacio = document.getElementById('avisoSinLugaresRadio');
        if (avisoVacio) avisoVacio.classList.add('d-none');

        if (alertaGeo) alertaGeo.classList.add('d-none');
        geolocalizacionActiva = false;
        ubicacionUsuario = null;
        precisionUsuario = 0;
        actualizarBotonCerca();

        if (mapaBadgeRadio) {
            mapaBadgeRadio.classList.add('d-none');
        }

        if (contador) {
            contador.textContent = `${ordenOriginalTarjetas.length} lugares encontrados`;
        }

        // Limpiar marcador GPS del usuario y círculo de radio en el mapa
        if (mapa) {
            if (marcadorUsuario) {
                mapa.removeLayer(marcadorUsuario);
                marcadorUsuario = null;
            }
            if (circuloPrecisionUsuario) {
                mapa.removeLayer(circuloPrecisionUsuario);
                circuloPrecisionUsuario = null;
            }
            if (circuloRadio1Km) {
                mapa.removeLayer(circuloRadio1Km);
                circuloRadio1Km = null;
            }
        }
        // Refrescar popups del mapa para retirar la distancia y mostrar todos los lugares
        actualizarMarcadoresMapa(lugaresActuales);
    }

    // Manejador del botón GPS
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

        const solicitud = ++solicitudUbicacion;
        actualizarBotonCerca(true);

        navigator.geolocation.getCurrentPosition(
            (posicion) => {
                if (solicitud !== solicitudUbicacion) return;
                const userLat = posicion.coords.latitude;
                const userLng = posicion.coords.longitude;
                precisionUsuario = posicion.coords.accuracy || 0;

                ubicacionUsuario = { lat: userLat, lng: userLng };
                geolocalizacionActiva = true;
                actualizarBotonCerca();

                ordenarYFiltrarLugaresPorRadio(userLat, userLng);

                // Actualizar posición en el mapa interactivo con círculo de 1 km
                actualizarMarcadorGpsUsuario(userLat, userLng, precisionUsuario);
                actualizarMarcadoresMapa(lugaresActuales);

                if (modoVistaActual === 'mapa' && mapa) {
                    if (circuloRadio1Km) {
                        mapa.fitBounds(circuloRadio1Km.getBounds(), { padding: [35, 35] });
                    } else {
                        mapa.flyTo([userLat, userLng], 15, { animate: true, duration: 0.8 });
                    }
                }
            },
            (error) => {
                if (solicitud !== solicitudUbicacion) return;
                actualizarBotonCerca();

                let mensajeError = 'No se pudo obtener tu ubicación.';
                if (error.code === error.PERMISSION_DENIED) {
                    mensajeError = 'Permiso de ubicación denegado. Permite el acceso a la ubicación en tu navegador para ver distancias y centrar el mapa.';
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

    alertaGeo?.addEventListener('close.bs.alert', event => {
        event.preventDefault();
        alertaGeo.classList.add('d-none');
        alertaGeo.classList.remove('show');
    });

    // 4. MAPA INTERACTIVO DE TRINIDAD (Leaflet)
    function obtenerClasePinPorCategoria(nombreCat, icono) {
        const cat = (nombreCat || '').toLowerCase();
        const ico = (icono || '').toLowerCase();

        // 1. Museos y Patrimonio Cultural (Dorado/Ámbar) - Comprobar antes de building
        if (cat.includes('museo') || cat.includes('patrimon') || cat.includes('cultur') || ico.includes('bank')) {
            return 'pin-museum';
        }
        // 2. Bares y Vida Nocturna (Violeta/Morado) - Comprobar antes de gastro para evitar que 'straw' o 'cup' colisionen
        if (cat.includes('bar') || cat.includes('nocturn') || cat.includes('pub') || cat.includes('karaoke') || ico.includes('straw') || ico.includes('moon')) {
            return 'pin-night';
        }
        // 3. Gastronomía y Sabores (Naranja Ámbar)
        if (cat.includes('restauran') || cat.includes('gastro') || cat.includes('comida') || cat.includes('sabor') || ico.includes('egg') || ico.includes('cup-hot')) {
            return 'pin-gastro';
        }
        // 4. Hoteles y Hospedajes (Azul Cielo)
        if (cat.includes('hotel') || cat.includes('hospedaj') || cat.includes('alojam') || ico.includes('building') || ico.includes('houses')) {
            return 'pin-hotel';
        }
        // 5. Balnearios y Recreación Acuática (Turquesa / Cian)
        if (cat.includes('balneari') || cat.includes('recreac') || cat.includes('piscina') || cat.includes('laguna') || ico.includes('water')) {
            return 'pin-water';
        }
        // 6. Atractivos y Naturaleza (Verde Esmeralda)
        if (cat.includes('atractiv') || cat.includes('naturaleza') || cat.includes('turist') || ico.includes('compass') || ico.includes('tree')) {
            return 'pin-nature';
        }
        // 7. Comercio / Tiendas (Rosa Coral)
        if (cat.includes('artesan') || cat.includes('comercio') || cat.includes('tienda') || ico.includes('shop') || ico.includes('bag')) {
            return 'pin-shop';
        }
        return 'pin-default';
    }

    function inicializarMapa() {
        if (mapa || !window.L) return;
        const mapDiv = document.getElementById('mapaCatalogo');
        if (!mapDiv) return;

        mapa = L.map('mapaCatalogo', {
            center: COORDENADAS_TRINIDAD,
            zoom: 14,
            zoomControl: true,
            scrollWheelZoom: true
        });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a> | BeniTurs Trinidad'
        }).addTo(mapa);

        marcadoresLayer = L.featureGroup().addTo(mapa);

        // Si el usuario ya tenía ubicación activa previa
        if (ubicacionUsuario) {
            actualizarMarcadorGpsUsuario(ubicacionUsuario.lat, ubicacionUsuario.lng, precisionUsuario);
        }

        actualizarMarcadoresMapa(lugaresActuales);
    }

    function actualizarMarcadorGpsUsuario(lat, lng, accuracy = 0) {
        if (!mapa || !window.L) return;

        if (!marcadorUsuario) {
            const userIcon = L.divIcon({
                className: 'user-gps-marker',
                html: '<div class="user-gps-dot" title="Tu posición GPS"></div>',
                iconSize: [20, 20],
                iconAnchor: [10, 10]
            });
            marcadorUsuario = L.marker([lat, lng], { icon: userIcon, zIndexOffset: 2000 }).addTo(mapa);
            marcadorUsuario.bindPopup('<strong class="small text-success"><i class="bi bi-geo-fill me-1"></i>¡Estás aquí!</strong><br><span class="extra-small text-muted"><i class="bi bi-broadcast-pin me-1"></i>Buscando en radio de 1 km</span>');
        } else {
            marcadorUsuario.setLatLng([lat, lng]);
        }

        // Círculo visual de 1 km (1000 m) de radio de búsqueda
        if (!circuloRadio1Km) {
            circuloRadio1Km = L.circle([lat, lng], {
                radius: RADIO_BUSQUEDA_KM * 1000,
                color: '#198754',
                weight: 2,
                dashArray: '6, 8',
                fillColor: '#10b981',
                fillOpacity: 0.10
            }).addTo(mapa);
            circuloRadio1Km.bindTooltip('<strong>Radio de búsqueda: 1 km</strong>', { permanent: false, direction: 'top' });
        } else {
            circuloRadio1Km.setLatLng([lat, lng]);
            circuloRadio1Km.setRadius(RADIO_BUSQUEDA_KM * 1000);
        }
    }

    function actualizarMarcadoresMapa(lugares) {
        if (!window.L) return;
        if (!mapa) {
            inicializarMapa();
            if (!mapa) return;
        }

        marcadoresLayer.clearLayers();
        let lugaresConGps = 0;
        const baseUrl = window.APP_CONFIG?.baseUrl || '';

        // Si la geolocalización está activa, filtrar marcadores al radio de 1 km
        let lugaresAMostrar = lugares;
        if (geolocalizacionActiva && ubicacionUsuario) {
            lugaresAMostrar = lugares.filter(lug => {
                const coords = parsearCoordenadas(lug.coordenadas_gps);
                if (!coords) return false;
                const d = calcularDistanciaHaversine(ubicacionUsuario.lat, ubicacionUsuario.lng, coords.lat, coords.lng);
                return d <= RADIO_BUSQUEDA_KM;
            });
        }

        lugaresAMostrar.forEach(lug => {
            const coords = parsearCoordenadas(lug.coordenadas_gps);
            if (!coords) return;
            lugaresConGps++;

            let pinClass = obtenerClasePinPorCategoria(lug.categoria, lug.categoria_icono);
            let icono = escapeHtml(lug.categoria_icono || 'bi-geo-alt');

            // Asegurar icono de museo correcto (bi-bank) para museos y patrimonio
            if (pinClass === 'pin-museum' && (icono === 'bi-building' || icono === 'bi-geo-alt')) {
                icono = 'bi-bank';
            }

            const customIcon = L.divIcon({
                className: 'beniturs-map-marker',
                html: `<div class="beniturs-marker-pin ${pinClass}"><i class="bi ${icono}"></i></div>`,
                iconSize: [36, 36],
                iconAnchor: [18, 36],
                popupAnchor: [0, -34]
            });

            // Distancia si la ubicación del turista está activa
            let distHtml = '';
            if (ubicacionUsuario) {
                const distKm = calcularDistanciaHaversine(ubicacionUsuario.lat, ubicacionUsuario.lng, coords.lat, coords.lng);
                distHtml = `<span class="badge bg-danger text-white rounded-pill px-2 py-1 extra-small"><i class="bi bi-cursor-fill me-1"></i>${formatearDistancia(distKm)}</span>`;
            }

            const imgHtml = lug.imagen
                ? `<img src="${baseUrl}/imagen?f=${encodeURIComponent(lug.imagen)}" alt="${escapeHtml(lug.nombre)}" class="beniturs-popup-img" loading="lazy" decoding="async">`
                : `<div class="p-4 text-white text-center" style="background: linear-gradient(135deg, #092611 0%, #1b7a42 100%);"><i class="bi ${icono} fs-1 text-warning"></i></div>`;

            const direccionHtml = lug.direccion
                ? `<p class="extra-small text-secondary mb-1 text-truncate" title="${escapeHtml(lug.direccion)}"><i class="bi bi-geo-alt me-1 text-danger"></i>${escapeHtml(lug.direccion)}</p>`
                : '';

            const horarioHtml = lug.horario_atencion
                ? `<p class="extra-small text-muted mb-2 text-truncate" title="${escapeHtml(lug.horario_atencion)}"><i class="bi bi-clock me-1 text-warning"></i>${escapeHtml(lug.horario_atencion)}</p>`
                : '';

            let whatsappRaw = (lug.whatsapp_contacto || '').replace(/\D/g, '');
            if (whatsappRaw.length === 8) whatsappRaw = '591' + whatsappRaw;
            const msgWa = encodeURIComponent(`Hola, vi la información de ${lug.nombre} en BeniTurs y quisiera hacer una consulta.`);
            const waBtnHtml = whatsappRaw
                ? `<a href="https://wa.me/${whatsappRaw}?text=${msgWa}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-success rounded-pill px-2 py-1 extra-small" title="Contactar por WhatsApp"><i class="bi bi-whatsapp"></i></a>`
                : '';

            const googleMapsDir = `https://www.google.com/maps/dir/?api=1&destination=${coords.lat},${coords.lng}`;

            const popupContent = `
                <div class="beniturs-popup-card">
                    ${imgHtml}
                    <div class="p-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="badge bg-light text-success border border-success-subtle rounded-pill extra-small px-2 py-1">
                                <i class="bi ${icono} me-1"></i>${escapeHtml(lug.categoria || '')}
                            </span>
                            ${distHtml}
                        </div>
                        <h6 class="fw-bold text-dark mb-1 text-truncate" title="${escapeHtml(lug.nombre)}">${escapeHtml(lug.nombre)}</h6>
                        ${direccionHtml}
                        ${horarioHtml}
                        <div class="d-flex gap-1 mt-2">
                            <a href="${baseUrl}/catalogo/detalle?slug=${encodeURIComponent(lug.slug)}" 
                               class="btn btn-sm btn-success text-white rounded-pill px-3 py-1 flex-grow-1 fw-semibold extra-small"
                               style="color: #ffffff !important; text-decoration: none;">
                                Ver Ficha <i class="bi bi-arrow-right ms-1 text-white"></i>
                            </a>
                            <a href="${googleMapsDir}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1 extra-small" title="Cómo llegar (Google Maps)">
                                <i class="bi bi-map-fill"></i>
                            </a>
                            ${waBtnHtml}
                        </div>
                    </div>
                </div>
            `;

            const marker = L.marker([coords.lat, coords.lng], { icon: customIcon });
            marker.bindPopup(popupContent, { maxWidth: 300 });
            marcadoresLayer.addLayer(marker);
        });

        if (mapaConteoEl) {
            mapaConteoEl.textContent = (geolocalizacionActiva && ubicacionUsuario)
                ? `${lugaresConGps} en radio 1 km`
                : `${lugaresConGps} en mapa`;
        }

        // Si la vista mapa está visible y hay marcadores, encuadrar
        if (modoVistaActual === 'mapa') {
            if (geolocalizacionActiva && circuloRadio1Km) {
                try {
                    mapa.fitBounds(circuloRadio1Km.getBounds(), { padding: [35, 35] });
                } catch (e) {}
            } else if (lugaresConGps > 0) {
                try {
                    mapa.fitBounds(marcadoresLayer.getBounds(), { padding: [50, 50], maxZoom: 16 });
                } catch (e) {}
            }
        }
    }

    // 5. Selector de Vistas: Cuadrícula vs Mapa
    function cambiarModoVista(nuevoModo) {
        modoVistaActual = nuevoModo;
        if (nuevoModo === 'mapa') {
            gridLugares.classList.add('d-none');
            mapaWrapper?.classList.remove('d-none');
            btnVistaGrid?.classList.remove('active');
            btnVistaGrid?.classList.add('text-secondary');
            btnVistaMapa?.classList.add('active');
            btnVistaMapa?.classList.remove('text-secondary');

            inicializarMapa();
            setTimeout(() => {
                if (mapa) {
                    mapa.invalidateSize();
                    if (marcadoresLayer && marcadoresLayer.getLayers().length > 0) {
                        mapa.fitBounds(marcadoresLayer.getBounds(), { padding: [50, 50], maxZoom: 16 });
                    }
                }
            }, 120);
        } else {
            mapaWrapper?.classList.add('d-none');
            gridLugares.classList.remove('d-none');
            btnVistaMapa?.classList.remove('active');
            btnVistaMapa?.classList.add('text-secondary');
            btnVistaGrid?.classList.add('active');
            btnVistaGrid?.classList.remove('text-secondary');
        }
    }

    btnVistaGrid?.addEventListener('click', () => cambiarModoVista('grid'));
    btnVistaMapa?.addEventListener('click', () => cambiarModoVista('mapa'));

    btnCentrarTrinidad?.addEventListener('click', () => {
        if (mapa) {
            mapa.flyTo(COORDENADAS_TRINIDAD, 14, { animate: true, duration: 0.8 });
        }
    });

    btnMapaMiUbi?.addEventListener('click', () => {
        if (ubicacionUsuario && mapa) {
            mapa.flyTo([ubicacionUsuario.lat, ubicacionUsuario.lng], 15, { animate: true, duration: 0.8 });
            marcadorUsuario?.openPopup();
        } else {
            btnCerca?.click();
        }
    });

    // 6. Filtros y Búsqueda en Vivo
    let categoriaSeleccionada = chips.find(chip => chip.classList.contains('active'))?.getAttribute('data-categoria') || '';
    let debounceTimer = null;
    let ultimaConsulta = 0;

    const filtrarLugares = async () => {
        clearTimeout(debounceTimer);
        const consulta = ++ultimaConsulta;
        const query = encodeURIComponent((inputBuscar?.value || inputBuscarLugar?.value || '').trim());
        const cat = encodeURIComponent(categoriaSeleccionada);
        const mun = selectMunicipio?.value ? encodeURIComponent(selectMunicipio.value) : '';
        const baseUrl = window.APP_CONFIG?.baseUrl || '';
        const endpoint = `${baseUrl}/api/lugares/buscar?q=${query}&categoria=${cat}&municipio=${mun}`;

        try {
            const data = await http.get(endpoint);
            if (consulta !== ultimaConsulta) return;

            lugaresActuales = data.resultados || [];
            renderizarCuadricula(lugaresActuales);
            ordenOriginalTarjetas = Array.from(contenedor.querySelectorAll('.tarjeta-lugar-col'));
            if (geolocalizacionActiva && ubicacionUsuario) {
                ordenarYFiltrarLugaresPorRadio(ubicacionUsuario.lat, ubicacionUsuario.lng);
            } else if (contador) {
                contador.textContent = `${data.total} lugares encontrados`;
            }
            actualizarMarcadoresMapa(lugaresActuales);
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

    selectMunicipio?.addEventListener('change', () => {
        restaurarOrdenOriginal();
        filtrarLugares();
        const opt = selectMunicipio.selectedOptions[0];
        const lat = parseFloat(opt?.getAttribute('data-lat'));
        const lng = parseFloat(opt?.getAttribute('data-lng'));
        if (!isNaN(lat) && !isNaN(lng) && mapa) {
            mapa.flyTo([lat, lng], 13, { animate: true, duration: 0.8 });
        }
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
            if (selectMunicipio) selectMunicipio.value = '';
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

    // 7. Renderizado de la cuadrícula de tarjetas
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
            let catIco = lug.categoria_icono || 'bi-geo-alt';
            if ((lug.categoria || '').toLowerCase().includes('museo') && (catIco === 'bi-building' || catIco === 'bi-geo-alt')) {
                catIco = 'bi-bank';
            }
            lug.categoria_icono = escapeHtml(catIco);

            let desc = lug.descripcion || '';
            if (desc.length > 140) {
                desc = desc.substring(0, 140) + '...';
            }

            const horarioHtml = lug.horario_atencion ? `
                <div class="text-muted extra-small text-truncate">
                    <i class="bi bi-clock me-1 text-warning"></i> ${escapeHtml(lug.horario_atencion)}
                </div>
            ` : '';

            const munBadge = lug.municipio ? ` · <i class="bi bi-geo-alt text-warning"></i> ${escapeHtml(lug.municipio)}` : '';
            const munHeader = lug.municipio ? ` · ${escapeHtml(lug.municipio)}` : '';

            let cabeceraHtml = '';
            if (lug.imagen) {
                cabeceraHtml = `
                    <div class="position-relative" style="height: 200px; background-color: #092611;">
                        <img src="${baseUrl}/imagen?f=${encodeURIComponent(lug.imagen)}" 
                             alt="${escapeHtml(lug.nombre)}" 
                             class="w-100 h-100 object-fit-cover"
                             loading="lazy"
                             decoding="async">
                        <span class="badge bg-dark bg-opacity-75 text-white position-absolute top-0 start-0 m-3 rounded-pill fw-bold shadow-sm">
                            <i class="bi ${lug.categoria_icono} me-1"></i> ${escapeHtml(lug.categoria)}${munBadge}
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
                            ${escapeHtml(lug.categoria)}${munHeader}
                        </span>
                    </div>
                `;
            }

            const cardHtml = `
                <div class="col-md-6 col-lg-4 item-lugar tarjeta-lugar-col"
                     data-id="${escapeHtml(lug.id_lugar)}"
                     data-nombre="${escapeHtml((lug.nombre || '').toLowerCase())}"
                     data-categoria="${escapeHtml(lug.id_categoria)}"
                     data-municipio="${escapeHtml(lug.id_municipio || 1)}"
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

    // =========================================================================
    // Protección de Imágenes contra Descarga (Anti-Save)
    // =========================================================================
    document.addEventListener('contextmenu', (e) => {
        if (e.target.closest('img, video, .place-card, .hero-video, .gallery-item, .beniturs-popup-img')) {
            e.preventDefault();
            return false;
        }
    }, { passive: false });

    document.addEventListener('dragstart', (e) => {
        if (e.target.closest('img, video, .place-card, .hero-video, .gallery-item, .beniturs-popup-img')) {
            e.preventDefault();
            return false;
        }
    }, { passive: false });
});
