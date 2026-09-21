/**
 * Cliente HTTP compartido con Fetch API
 * Soporta peticiones asíncronas GET y POST (FormData y JSON)
 */
const http = {
    /**
     * Realiza peticiones GET esperando respuesta JSON
     */
    async get(endpoint) {
        const url = this.buildUrl(endpoint);
        try {
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            return await this.handleResponse(response);
        } catch (error) {
            console.error(`Error en GET ${url}:`, error);
            throw error;
        }
    },

    /**
     * Realiza peticiones POST enviando FormData o JSON
     */
    async post(endpoint, bodyData, asJson = false) {
        const url = this.buildUrl(endpoint);
        const headers = {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        };

        let body = bodyData;
        if (asJson) {
            headers['Content-Type'] = 'application/json';
            body = JSON.stringify(bodyData);
        }

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: headers,
                body: body
            });
            return await this.handleResponse(response);
        } catch (error) {
            console.error(`Error en POST ${url}:`, error);
            throw error;
        }
    },

    /**
     * Resuelve la URL absoluta combinando con la URL base del sistema
     */
    buildUrl(endpoint) {
        if (endpoint.startsWith('http://') || endpoint.startsWith('https://')) {
            return endpoint;
        }
        const base = window.APP_CONFIG?.baseUrl || '';
        const cleanEndpoint = endpoint.startsWith('/') ? endpoint : '/' + endpoint;
        return `${base}${cleanEndpoint}`;
    },

    /**
     * Parsea la respuesta del servidor y maneja errores HTTP y de formato
     */
    async handleResponse(response) {
        const contentType = response.headers.get('content-type') || '';
        if (!contentType.includes('application/json')) {
            throw new Error(`El servidor devolvió un formato no esperado (${response.status})`);
        }

        const data = await response.json();
        if (!response.ok) {
            throw new Error(data.error || data.mensaje || `Error del servidor: HTTP ${response.status}`);
        }
        return data;
    }
};