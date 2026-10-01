document.querySelectorAll('[data-login-form]').forEach(form => {
    const password = form.querySelector('[name="password"]');
    const toggle = form.querySelector('[data-password-toggle]');
    const warning = form.querySelector('#avisoMayusculas');
    const setVisible = visible => {
        password.type = visible ? 'text' : 'password';
        const label = visible ? 'Ocultar contraseña' : 'Mostrar contraseña';
        toggle.setAttribute('aria-label', label);
        toggle.setAttribute('aria-pressed', String(visible));
        toggle.title = label;
        toggle.querySelector('i').className = visible ? 'bi bi-eye-slash' : 'bi bi-eye';
    };
    toggle.addEventListener('click', () => setVisible(password.type === 'password'));
    ['keydown', 'keyup'].forEach(event => password.addEventListener(event, e => {
        warning.textContent = e.getModifierState?.('CapsLock') ? 'Bloq Mayús está activado.' : '';
    }));
    password.addEventListener('blur', () => { warning.textContent = ''; });
    form.addEventListener('submit', () => setVisible(false));
    window.addEventListener('pageshow', () => setVisible(false));
});
