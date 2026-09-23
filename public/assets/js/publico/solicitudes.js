document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('formSolicitudComercial');
    if (!form) return;
    const pasos = [...form.querySelectorAll('[data-wizard-step]')];
    const indicadores = document.querySelectorAll('[data-step-indicator]');
    const alerta = document.getElementById('solicitudAlertContainer');
    const enviar = document.getElementById('btnSubmitSolicitud');
    const comprobante = form.elements.comprobante;
    let paso = 0;
    let enviando = false;

    function mostrarPaso(indice, enfocar = true) {
        paso = indice;
        pasos.forEach((panel, i) => { panel.hidden = i !== indice; });
        indicadores.forEach((item, i) => {
            item.classList.toggle('text-success', i === indice);
            item.classList.toggle('fw-bold', i === indice);
            if (i === indice) item.setAttribute('aria-current', 'step');
            else item.removeAttribute('aria-current');
        });
        if (enfocar) pasos[indice].querySelector('h2').focus();
    }
    function validarPaso(indice) {
        for (const campo of pasos[indice].querySelectorAll('input, select, textarea')) {
            if (!campo.checkValidity()) {
                mostrarPaso(indice);
                campo.reportValidity();
                return false;
            }
        }
        return true;
    }
    function actualizarPlan() {
        const anual = form.elements.plan_solicitado.value === 'ANUAL';
        document.getElementById('montoPlan').textContent = anual ? 'Bs 2,500' : 'Bs 250';
        document.getElementById('nombrePlan').textContent = anual ? 'Plan Anual' : 'Plan Mensual';
        form.elements.monto_declarado.value = anual ? '2500.00' : '250.00';
        form.querySelectorAll('.plan-card').forEach(label => label.classList.toggle('active-plan', label.querySelector('input').checked));
    }
    function validarArchivo() {
        const file = comprobante.files[0];
        comprobante.setCustomValidity(file && (file.size === 0 || file.size > 5 * 1024 * 1024 ||
            !['image/jpeg', 'image/png', 'image/webp'].includes(file.type))
            ? 'Adjunta una imagen JPG, PNG o WEBP de hasta 5 MB.' : '');
    }
    function mensaje(texto, tipo) {
        alerta.replaceChildren();
        const div = document.createElement('div');
        div.className = `alert alert-${tipo} rounded-4`;
        div.textContent = texto;
        alerta.append(div);
        alerta.focus();
    }
    form.querySelectorAll('[data-next]').forEach(btn => btn.addEventListener('click', () => {
        if (!enviando && validarPaso(paso)) mostrarPaso(Math.min(paso + 1, 2));
    }));
    form.querySelectorAll('[data-back]').forEach(btn => btn.addEventListener('click', () => {
        if (!enviando) mostrarPaso(Math.max(paso - 1, 0));
    }));
    form.querySelectorAll('[name="plan_solicitado"]').forEach(radio => radio.addEventListener('change', actualizarPlan));
    comprobante.addEventListener('change', validarArchivo);
    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (enviando) return;
        if (paso < 2) {
            if (validarPaso(paso)) mostrarPaso(paso + 1);
            return;
        }
        validarArchivo();
        for (let i = 0; i < pasos.length; i++) if (!validarPaso(i)) return;
        const datos = new FormData(form);
        enviando = true;
        enviar.disabled = true;
        enviar.textContent = 'Enviando solicitud…';
        try {
            const respuesta = await http.post('/solicitudes/enviar', datos);
            if (!respuesta.success) throw new Error(respuesta.error || 'No se pudo guardar la solicitud.');
            form.hidden = true;
            mensaje(respuesta.message, 'success');
        } catch (error) {
            mensaje(error.message || 'No se pudo enviar la solicitud. Inténtalo nuevamente.', 'danger');
        } finally {
            enviando = false;
            enviar.disabled = false;
            enviar.textContent = 'Enviar Solicitud y Comprobante';
        }
    });
    mostrarPaso(0, false);
    actualizarPlan();
});