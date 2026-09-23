<?php
/**
 * Configuración general del proyecto Trinidad Turismo.
 * Accesible únicamente desde el backend.
 */

return [
    'app_name'     => 'Trinidad Turismo & Directorio Comercial',
    'app_env'      => 'local', // 'local' o 'production'
    'base_url'     => getenv('BENITURS_BASE_URL') ?: 'http://localhost/trinidad_turismo/public',
    'timezone'     => 'America/La_Paz', // Hora oficial de Bolivia (Trinidad, Beni)
    'currency'     => 'Bs',
    'tarifa_base'  => 250.00, // Tarifa comercial mensual de referencia
    'version'      => '1.0.0',
    'session_name' => 'TRINI_TURISMO_SESSID'
];
