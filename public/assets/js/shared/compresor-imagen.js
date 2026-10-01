/**
 * compresor-imagen.js - Compresor de imágenes en el cliente (Browser-side)
 * Utiliza HTML5 Canvas y la API DataTransfer para reducir fotos pesadas a ~150-250 KB
 * antes de transmitirlas por la red hacia el servidor.
 * 
 * BeniTurs / Trinidad Turismo
 */
const CompresorImagen = (() => {
    'use strict';

    const CONFIG_DEFECTO = {
        maxDimension: 1280, // Ancho o alto máximo
        calidad: 0.82,      // Calidad de compresión JPEG (82%)
        tipoMime: 'image/jpeg',
        mostrarProgreso: true
    };

    /**
     * Formatea tamaños en bytes a unidades legibles (B, KB, MB)
     */
    function formatearTamano(bytes) {
        if (!bytes || bytes <= 0) return '0 B';
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
    }

    /**
     * Comprime un archivo File individual usando HTML5 Canvas
     * @param {File} archivo
     * @param {Object} config
     * @returns {Promise<{file: File, origSize: number, newSize: number, ahorrado: number}>}
     */
    function comprimir(archivo, config = {}) {
        const opciones = Object.assign({}, CONFIG_DEFECTO, config);

        if (!archivo || !archivo.type || !archivo.type.startsWith('image/')) {
            return Promise.resolve({
                file: archivo,
                origSize: archivo ? archivo.size : 0,
                newSize: archivo ? archivo.size : 0,
                ahorrado: 0
            });
        }

        const origSize = archivo.size;

        return new Promise((resolve) => {
            const reader = new FileReader();

            reader.onload = (evento) => {
                const img = new Image();

                img.onload = () => {
                    let { width, height } = img;
                    const maxDim = opciones.maxDimension;

                    // Calcular escala proporcional
                    if (width > maxDim || height > maxDim) {
                        if (width >= height) {
                            height = Math.round((height * maxDim) / width);
                            width = maxDim;
                        } else {
                            width = Math.round((width * maxDim) / height);
                            height = maxDim;
                        }
                    }

                    const canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');

                    if (!ctx) {
                        resolve({ file: archivo, origSize, newSize: origSize, ahorrado: 0 });
                        return;
                    }

                    ctx.imageSmoothingEnabled = true;
                    ctx.imageSmoothingQuality = 'high';

                    // Si se convierte a JPEG, rellenar fondo blanco (evita fondo negro si había transparencia)
                    if (opciones.tipoMime === 'image/jpeg') {
                        ctx.fillStyle = '#FFFFFF';
                        ctx.fillRect(0, 0, width, height);
                    }

                    ctx.drawImage(img, 0, 0, width, height);

                    canvas.toBlob((blob) => {
                        if (!blob || blob.size >= origSize) {
                            // Si por algún motivo el blob es mayor, conservar el archivo original
                            resolve({ file: archivo, origSize, newSize: origSize, ahorrado: 0 });
                            return;
                        }

                        let nuevoNombre = archivo.name;
                        if (opciones.tipoMime === 'image/jpeg' && !nuevoNombre.match(/\.(jpe?g)$/i)) {
                            nuevoNombre = nuevoNombre.replace(/\.[^.]+$/, '') + '.jpg';
                        }

                        const archivoComprimido = new File([blob], nuevoNombre, {
                            type: opciones.tipoMime,
                            lastModified: Date.now()
                        });

                        resolve({
                            file: archivoComprimido,
                            origSize,
                            newSize: archivoComprimido.size,
                            ahorrado: origSize - archivoComprimido.size
                        });
                    }, opciones.tipoMime, opciones.calidad);
                };

                img.onerror = () => {
                    resolve({ file: archivo, origSize, newSize: origSize, ahorrado: 0 });
                };

                img.src = evento.target.result;
            };

            reader.onerror = () => {
                resolve({ file: archivo, origSize, newSize: origSize, ahorrado: 0 });
            };

            reader.readAsDataURL(archivo);
        });
    }

    /**
     * Vincula un <input type="file"> para comprimir automáticamente las fotos al seleccionarlas
     * @param {HTMLInputElement} input
     * @param {Object} config
     * @param {Function} onCompletado
     */
    function vincular(input, config = {}, onCompletado = null) {
        if (!input) return;

        // Buscar o crear contenedor de feedback
        let feedback = input.parentElement ? input.parentElement.querySelector('.compresor-feedback') : null;
        if (!feedback && input.parentElement) {
            feedback = document.createElement('div');
            feedback.className = 'compresor-feedback mt-1 small';
            input.insertAdjacentElement('afterend', feedback);
        }

        input.addEventListener('change', async () => {
            const archivos = Array.from(input.files || []);
            if (!archivos.length) {
                if (feedback) feedback.innerHTML = '';
                return;
            }

            if (feedback) {
                feedback.innerHTML = '<span class="text-primary d-inline-flex align-items-center gap-1"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Optimizando imagen para subida rápida...</span>';
            }

            try {
                const resultados = await Promise.all(archivos.map(a => comprimir(a, config)));

                // Reemplazar input.files con DataTransfer si el navegador lo soporta
                if (typeof DataTransfer !== 'undefined') {
                    const dt = new DataTransfer();
                    resultados.forEach(r => dt.items.add(r.file));
                    input.files = dt.files;
                }

                const totalOrig = resultados.reduce((acc, r) => acc + r.origSize, 0);
                const totalNew = resultados.reduce((acc, r) => acc + r.newSize, 0);
                const pct = totalOrig > 0 ? Math.round(((totalOrig - totalNew) / totalOrig) * 100) : 0;

                if (feedback) {
                    if (pct > 5) {
                        feedback.innerHTML = `<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 d-inline-flex align-items-center gap-1">
                            <i class="bi bi-lightning-charge-fill"></i> Optimizada: ${formatearTamano(totalOrig)} → ${formatearTamano(totalNew)} (-${pct}%)
                        </span>`;
                    } else {
                        feedback.innerHTML = `<span class="text-muted extra-small d-inline-flex align-items-center gap-1"><i class="bi bi-check2-circle text-success"></i> Tamaño óptimo (${formatearTamano(totalNew)})</span>`;
                    }
                }

                if (typeof onCompletado === 'function') {
                    onCompletado(resultados, input.files);
                }
            } catch (err) {
                console.warn('CompresorImagen: No se pudo optimizar:', err);
                if (feedback) feedback.innerHTML = '';
            }
        });
    }

    return {
        comprimir,
        vincular,
        formatearTamano
    };
})();

if (typeof window !== 'undefined') {
    window.CompresorImagen = CompresorImagen;
}
