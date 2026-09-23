document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('businessCoordinates');
    const status = document.getElementById('locationStatus');
    const locate = document.getElementById('locateBusiness');
    let map, marker;
    function coordinates(value) {
        const match = value.trim().match(/^([+-]?\d+(?:\.\d+)?)\s*,\s*([+-]?\d+(?:\.\d+)?)$/);
        if (!match) return null;
        const point = [Number(match[1]), Number(match[2])];
        return Math.abs(point[0]) <= 90 && Math.abs(point[1]) <= 180 ? point : null;
    }
    function select(point, write = true) {
        if (write) input.value = point.map(n => n.toFixed(6)).join(', ');
        input.setCustomValidity('');
        if (map) {
            if (marker) marker.setLatLng(point);
            else {
                marker = L.marker(point, { draggable: true, autoPan: true, title: 'Ubicación del local' }).addTo(map);
                marker.on('dragend', () => {
                    const p = marker.getLatLng().wrap();
                    select([p.lat, p.lng]);
                });
            }
            map.setView(point, Math.max(map.getZoom(), 16));
        }
        status.textContent = 'Punto seleccionado. Revisa la ubicación y pulsa Guardar ubicación.';
    }
    const saved = coordinates(input.value);
    if (window.L) {
        map = L.map('businessMap', { scrollWheelZoom: false }).setView(saved || [-14.8333, -64.9], saved ? 16 : 13);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map).on('tileerror', () => {
            status.textContent = 'No se pudo cargar parte del mapa. Revisa tu conexión o escribe las coordenadas.';
        });
        map.on('click', event => { const p = event.latlng.wrap(); select([p.lat, p.lng]); });
        if (saved) {
            select(saved, false);
            status.textContent = 'Este es el punto indicado. Puedes moverlo y guardar el cambio.';
        }
    } else {
        status.textContent = 'No se pudo cargar el mapa. Puedes usar tu ubicación actual o escribir las coordenadas.';
    }
    input.addEventListener('input', () => input.setCustomValidity(''));
    input.addEventListener('change', () => {
        const point = coordinates(input.value);
        if (point) select(point, false);
        else {
            if (marker) { marker.remove(); marker = null; }
            status.textContent = 'Escribe coordenadas válidas en orden latitud, longitud.';
        }
    });
    document.getElementById('locationForm').addEventListener('submit', event => {
        if (!coordinates(input.value)) {
            event.preventDefault();
            input.setCustomValidity('Marca un punto o escribe latitud, longitud válidas.');
            input.reportValidity();
        }
    });
    locate.addEventListener('click', () => {
        if (!navigator.geolocation) { status.textContent = 'Tu navegador no permite obtener la ubicación. Selecciona el local en el mapa.'; return; }
        locate.disabled = true;
        status.textContent = 'Buscando tu ubicación…';
        navigator.geolocation.getCurrentPosition(position => {
            select([position.coords.latitude, position.coords.longitude]);
            status.textContent = 'Ubicación aproximada encontrada. Ajusta el punto a la entrada del local y guarda.';
            locate.disabled = false;
        }, () => {
            status.textContent = 'No pudimos obtener tu ubicación. Permite el acceso o selecciona el punto en el mapa.';
            locate.disabled = false;
        }, { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 });
    });
});
