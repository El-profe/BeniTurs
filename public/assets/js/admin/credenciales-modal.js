/**
 * Manejo centralizado del Modal de Credenciales Comerciales y Recibo Oficial
 */
(function() {
    let modalInstance = null;
    let datosActuales = null;

    function initModal() {
        const modalEl = document.getElementById('modalCredencialesGeneradas');
        if (!modalEl || typeof bootstrap === 'undefined') return;
        if (!modalInstance) {
            modalInstance = new bootstrap.Modal(modalEl);
        }
    }

    function mostrarModalCredenciales(data) {
        initModal();
        if (!modalInstance) return;
        datosActuales = data;

        const elComercio = document.getElementById('modalCredComercio');
        if (elComercio) elComercio.textContent = data.nombre_establecimiento || 'Comercio';

        const elPlan = document.getElementById('modalCredPlan');
        if (elPlan) {
            const montoStr = data.monto !== undefined ? `Bs ${Number(data.monto || 0).toLocaleString('es-BO')}` : '';
            const planStr = data.plan || 'Comercial';
            elPlan.textContent = montoStr ? `${montoStr} · ${planStr}` : planStr;
        }

        const elUsuario = document.getElementById('modalCredUsuario');
        if (elUsuario) elUsuario.textContent = data.usuario || '-';
        
        const elClave = document.getElementById('modalCredClave');
        if (elClave) {
            elClave.textContent = data.clave || 'Generada';
            elClave.dataset.claveReal = data.clave || '';
        }

        const textareaMensaje = document.getElementById('modalCredMensajeCompleto');
        if (textareaMensaje) {
            const loginUrl = data.login_url || (window.location.origin + '/negocio/login');
            const mensajeCompleto = data.mensaje_texto || `¡Hola! Tu negocio "${data.nombre_establecimiento}" ya está activo en BeniTurs.\nAcceso: ${loginUrl}\nUsuario: ${data.usuario}\nClave: ${data.clave}`;
            textareaMensaje.value = mensajeCompleto;
        }

        const btnWA = document.getElementById('btnAbrirWhatsApp');
        if (btnWA) {
            if (data.whatsapp_url) {
                btnWA.href = data.whatsapp_url;
                btnWA.classList.remove('disabled');
            } else {
                btnWA.removeAttribute('href');
                btnWA.classList.add('disabled');
            }
        }

        // Configurar botón de impresión
        const btnPrint = document.getElementById('btnImprimirRecibo');
        if (btnPrint) {
            btnPrint.onclick = () => {
                imprimirRecibo(datosActuales || data);
            };
        }

        modalInstance.show();
    }

    function imprimirRecibo(data) {
        if (!data) return;
        const ventana = window.open('', '_blank', 'width=750,height=850');
        if (!ventana) {
            alert('Por favor habilita las ventanas emergentes en tu navegador para ver el recibo.');
            return;
        }

        const fechaHoy = new Date().toLocaleDateString('es-BO', {day:'2-digit',month:'2-digit',year:'numeric'});
        const loginUrl = data.login_url || (window.location.origin + '/negocio/login');
        const contenido = `<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobante de Activación - ${data.nombre_establecimiento || 'Comercio'}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; padding: 40px; color: #1c2b21; max-width: 650px; margin: auto; }
        .header { text-align: center; border-bottom: 2px solid #14633e; padding-bottom: 20px; margin-bottom: 25px; }
        .brand { font-size: 28px; font-weight: 800; color: #14633e; letter-spacing: -0.5px; }
        .brand span { color: #f59e0b; }
        .title { font-size: 16px; color: #555; text-transform: uppercase; margin-top: 5px; letter-spacing: 1px; }
        .box { background: #f4f8f5; border: 1px solid #d1e5d7; border-radius: 12px; padding: 20px; margin-bottom: 25px; }
        .row { display: flex; justify-content: space-between; margin-bottom: 12px; }
        .row:last-child { margin-bottom: 0; }
        .label { color: #666; font-size: 14px; }
        .value { font-weight: 600; font-size: 15px; }
        .cred-box { border: 2px dashed #14633e; border-radius: 12px; padding: 20px; margin-bottom: 25px; background: #fff; }
        .cred-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; font-family: monospace; font-size: 16px; }
        .cred-row strong { font-family: sans-serif; font-size: 14px; color: #333; }
        .footer { text-align: center; font-size: 12px; color: #888; border-top: 1px solid #eee; padding-top: 20px; margin-top: 40px; }
        @media print {
            body { padding: 0; }
            .btn-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="brand">Beni<span>Turs</span></div>
        <div class="title">Comprobante Oficial de Membresía Comercial</div>
        <div style="font-size: 13px; color: #888; margin-top: 4px;">Trinidad, Departamento del Beni · Bolivia</div>
    </div>

    <div class="box">
        <div class="row"><span class="label">Establecimiento:</span><span class="value">${data.nombre_establecimiento || ''}</span></div>
        <div class="row"><span class="label">Propietario / Contacto:</span><span class="value">${data.nombre_solicitante || data.telefono || 'Propietario'}</span></div>
        <div class="row"><span class="label">Fecha de Activación:</span><span class="value">${fechaHoy}</span></div>
        <div class="row"><span class="label">Método de Pago:</span><span class="value">${data.metodo_pago || 'Efectivo'}</span></div>
        <div class="row"><span class="label">Monto Cancelado:</span><span class="value" style="color: #14633e; font-size: 17px;">Bs ${Number(data.monto || 0).toFixed(2)}</span></div>
        <div class="row"><span class="label">Periodo de Cobertura:</span><span class="value">${data.fecha_inicio || fechaHoy} al ${data.fecha_vencimiento || '-'}</span></div>
    </div>

    <div class="cred-box">
        <div style="font-weight: bold; color: #14633e; margin-bottom: 15px; font-size: 15px; text-transform: uppercase;">Credenciales de Autoservicio</div>
        <div class="cred-row"><strong>Portal de Acceso:</strong> <span>${loginUrl}</span></div>
        <div class="cred-row"><strong>Usuario:</strong> <span style="font-weight: bold; color: #14633e;">${data.usuario || '-'}</span></div>
        <div class="cred-row"><strong>Contraseña:</strong> <span style="font-weight: bold;">${data.clave || '-'}</span></div>
    </div>

    <p style="font-size: 13px; color: #666; line-height: 1.5; text-align: center;">
        Inicia sesión en tu portal para actualizar tus fotografías, gestionar horarios, fijar tu ubicación GPS y publicar promociones para los turistas.
    </p>

    <div class="footer">
        BeniTurs · Guía Turística y Directorio Departamental del Beni · Soporte: Trinidad, Beni
    </div>

    <div style="text-align: center; margin-top: 30px;" class="btn-print">
        <button onclick="window.print()" style="background: #14633e; color: #fff; border: none; padding: 12px 28px; border-radius: 50px; font-size: 15px; font-weight: bold; cursor: pointer;">
            🖨️ Imprimir Comprobante
        </button>
    </div>
</body>
</html>`;

        ventana.document.write(contenido);
        ventana.document.close();
    }

    document.addEventListener('DOMContentLoaded', () => {
        initModal();

        // Botón copiar mensaje completo
        const btnCopiar = document.getElementById('btnCopiarMensajeCompleto');
        if (btnCopiar) {
            btnCopiar.addEventListener('click', async () => {
                const textarea = document.getElementById('modalCredMensajeCompleto');
                if (!textarea) return;
                try {
                    await navigator.clipboard.writeText(textarea.value);
                    const aviso = document.getElementById('avisoCopiado');
                    aviso?.classList.remove('d-none');
                    setTimeout(() => aviso?.classList.add('d-none'), 3500);
                } catch (e) {
                    textarea.select();
                    document.execCommand('copy');
                    alert('¡Copiado al portapapeles!');
                }
            });
        }

        // Copiar campos individuales
        document.querySelectorAll('.btn-copiar-dato').forEach(btn => {
            btn.addEventListener('click', async () => {
                const targetId = btn.dataset.target;
                const targetEl = document.getElementById(targetId);
                if (!targetEl) return;
                const texto = targetEl.dataset.claveReal || targetEl.textContent.trim();
                try {
                    await navigator.clipboard.writeText(texto);
                    const original = btn.innerHTML;
                    btn.innerHTML = '<i class="bi bi-check2 text-success"></i> Copiado';
                    setTimeout(() => { btn.innerHTML = original; }, 2000);
                } catch (e) {
                    alert('Copiado: ' + texto);
                }
            });
        });

        // Ver / Ocultar Contraseña
        const btnToggleClave = document.getElementById('btnToggleClave');
        if (btnToggleClave) {
            let oculta = false;
            btnToggleClave.addEventListener('click', () => {
                const elClave = document.getElementById('modalCredClave');
                if (!elClave) return;
                oculta = !oculta;
                if (oculta) {
                    elClave.textContent = '••••••••••••';
                    btnToggleClave.innerHTML = '<i class="bi bi-eye-slash"></i>';
                } else {
                    elClave.textContent = elClave.dataset.claveReal || '';
                    btnToggleClave.innerHTML = '<i class="bi bi-eye"></i>';
                }
            });
        }
    });

    // Exponer API global
    window.BeniTursModalCredenciales = {
        mostrar: mostrarModalCredenciales,
        imprimir: imprimirRecibo
    };
})();
