document.addEventListener('DOMContentLoaded', () => {
    const inputBuscar = document.getElementById('inputBuscarHero');
    const chips = document.querySelectorAll('.chip-filter');
    const contenedor = document.getElementById('contenedorLugares');
    const contador = document.getElementById('contadorResultados');
    const btnLimpiar = document.getElementById('btnLimpiarFiltros');

    if (!contenedor) return;

    let categoriaSeleccionada = '';
    let debounceTimer = null;

    const filtrarLugares = async () => {
        const query = encodeURIComponent((inputBuscar?.value || '').trim());
        const cat = encodeURIComponent(categoriaSeleccionada);
        const endpoint = `/api/lugares/buscar?q=${query}&categoria=${cat}`;

        try {
            const data = await http.get(endpoint);
            renderizarCuadricula(data.resultados || []);
            if (contador) {
                contador.textContent = `${data.total} lugares encontrados`;
            }
        } catch (error) {
            console.error('Error al consultar lugares:', error);
        }
    };

    if (inputBuscar) {
        inputBuscar.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(filtrarLugares, 300);
        });
    }

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
            categoriaSeleccionada = '';
            chips.forEach(c => c.classList.remove('active'));
            const primerChip = document.querySelector('.chip-filter[data-categoria=""]');
            if (primerChip) primerChip.classList.add('active');
            filtrarLugares();
        });
    }

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
                        <span class="badge ${badgeClass} position-absolute top-0 end-0 m-3 rounded-pill fw-bold shadow-sm">
                            ${badgeTexto}
                        </span>
                    </div>
                `;
            } else {
                cabeceraHtml = `
                    <div class="card-header-place p-4 text-white text-center position-relative">
                        <span class="badge ${badgeClass} position-absolute top-0 end-0 m-3 rounded-pill fw-bold">
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
                <div class="col-md-6 col-lg-4 item-lugar">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden place-card">
                        ${cabeceraHtml}
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
        const d = document.createElement('div');
        d.innerText = str || '';
        return d.innerHTML;
    }
});
