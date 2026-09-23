document.addEventListener('DOMContentLoaded', () => {
    const mensaje = document.getElementById('solicitudAdminMensaje');
    const pendientes = new Set();
    document.querySelectorAll('[data-solicitud-accion]').forEach(form => {
        form.addEventListener('submit', async event => {
            event.preventDefault();
            const id = form.elements.id_solicitud.value;
            if (pendientes.has(id)) return;
            const aceptar = form.dataset.solicitudAccion === 'aprovisionar';
            if (!window.confirm(aceptar ? '¿Verificaste el abono bancario y deseas activar este negocio?' : '¿Rechazar esta solicitud?')) return;
            pendientes.add(id);
            const relacionados = [...document.querySelectorAll('[data-solicitud-accion]')].filter(f => f.elements.id_solicitud.value === id);
            relacionados.forEach(f => { f.querySelector('button[type="submit"]').disabled = true; });
            mensaje.className = 'alert alert-info';
            mensaje.textContent = aceptar ? 'Aprovisionando negocio…' : 'Guardando resolución…';
            try {
                const response = await fetch(form.action, {method:'POST',body:new FormData(form),headers:{Accept:'application/json','X-Requested-With':'XMLHttpRequest'}});
                const data = await response.json();
                if (!response.ok || !data.success) throw new Error(data.error || 'No se pudo completar la operación.');
                const fila = document.querySelector(`[data-solicitud="${id}"]`);
                fila.querySelector('[data-estado]').textContent = data.estado;
                relacionados.forEach(f => f.remove());
                if (data.whatsapp_url) {
                    const url = new URL(data.whatsapp_url);
                    if (url.protocol === 'https:' && url.hostname === 'wa.me') {
                        const enlace = document.createElement('a');
                        enlace.href = url.href;
                        enlace.target = '_blank'; enlace.rel = 'noopener noreferrer';
                        enlace.className = 'btn btn-success mt-2';
                        enlace.textContent = '📲 Enviar Credenciales por WhatsApp';
                        fila.querySelector('[data-acciones]').append(enlace);
                    }
                }
                mensaje.className = 'alert alert-success';
                mensaje.textContent = data.message;
                const modal = form.closest('.modal');
                if (modal) window.bootstrap?.Modal.getInstance(modal)?.hide();
            } catch (error) {
                mensaje.className = 'alert alert-danger';
                mensaje.textContent = error.message || 'No se pudo completar la operación. Revisa el estado antes de reintentar.';
            } finally {
                pendientes.delete(id);
                relacionados.forEach(f => { const b = f.querySelector('button[type="submit"]'); if (b) b.disabled = false; });
                mensaje.focus();
            }
        });
    });
});
