document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('formSolicitudComercial');
    if (!form) return;
    const alertBox = document.getElementById('solicitudAlertContainer');
    const btnSubmit = document.getElementById('btnEnviarSolicitud');
    const inputCategoria = document.getElementById('id_categoria_seleccionada');
    const errorCatHelp = document.getElementById('categoriaErrorHelp');

    function escapeText(value) {
        const element = document.createElement('span');
        element.textContent = String(value);
        return element.innerHTML;
    }

    function showAlert() {
        alertBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
        alertBox.focus({ preventScroll: true });
    }

    // 1. Manejo visual de selección de Planes
    const planLabels = document.querySelectorAll('.plan-card');
    planLabels.forEach(label => {
        const radio = label.querySelector('input[type="radio"]');
        if (radio.checked) label.classList.add('active-plan');

        label.addEventListener('click', () => {
            planLabels.forEach(l => l.classList.remove('active-plan'));
            label.classList.add('active-plan');
            radio.checked = true;
        });
    });

    // 2. Manejo visual de selección de Categoría por Iconos (Un clic)
    const categoryButtons = document.querySelectorAll('.btn-categoria-tile');
    categoryButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            categoryButtons.forEach(b => b.classList.remove('active-category'));
            btn.classList.add('active-category');
            inputCategoria.value = btn.getAttribute('data-id');
            if (errorCatHelp) errorCatHelp.classList.add('d-none');
        });
    });

    // 3. Atajos de Horarios Rápidos
    const btnHorarios = document.querySelectorAll('.btn-horario-quick');
    const inputHorarios = document.getElementById('inputHorarios');
    btnHorarios.forEach(b => {
        b.addEventListener('click', () => {
            if (inputHorarios) {
                inputHorarios.value = b.getAttribute('data-horario');
                inputHorarios.focus();
            }
        });
    });

    // 4. Envío Asíncrono con Fetch
    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        // Validar selección de categoría
        if (!inputCategoria.value) {
            if (errorCatHelp) errorCatHelp.classList.remove('d-none');
            document.getElementById('gridCategoriasSelector').scrollIntoView({ behavior: 'smooth', block: 'center' });
            categoryButtons[0]?.focus({ preventScroll: true });
            return;
        }

        if (btnSubmit.disabled) return;
        const originalButtonHtml = btnSubmit.innerHTML;
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Procesando...';
        alertBox.innerHTML = '';

        const formData = new FormData(form);

        try {
            const data = await http.post(form.action, formData);
            if (!data.success) throw new Error(data.error || 'No se pudo guardar la solicitud. Intenta nuevamente.');

            alertBox.innerHTML = `
                <div class="alert alert-success alert-dismissible fade show rounded-4 p-4 shadow-sm border-0 mb-4" role="alert">
                    <div class="d-flex align-items-center gap-3">
                        <i class="bi bi-check-circle-fill text-success display-6"></i>
                        <div>
                            <h5 class="fw-bold mb-1">¡Solicitud Recibida con Éxito!</h5>
                            <p class="mb-0 small">${escapeText(data.mensaje)}</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;

            form.reset();
            categoryButtons.forEach(b => b.classList.remove('active-category'));
            inputCategoria.value = '';
            planLabels.forEach(label => label.classList.toggle('active-plan', label.querySelector('input[type="radio"]').checked));
            showAlert();

        } catch (error) {
            alertBox.innerHTML = `
                <div class="alert alert-danger alert-dismissible fade show rounded-4 p-3 shadow-sm border-0 mb-4" role="alert">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-triangle-fill text-danger fs-4"></i>
                        <div class="small fw-semibold">${escapeText(error.message)}</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            showAlert();
        } finally {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = originalButtonHtml;
        }
    });
});
