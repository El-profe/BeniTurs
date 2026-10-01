<?php
/**
 * Configuración de la base de datos MariaDB / MySQL.
 * Lee desde variables de entorno (.env) con respaldo local por defecto para Laragon.
 */

return [
    'host'     => (string)env('DB_HOST', '127.0.0.1'),
    'port'     => (int)env('DB_PORT', 3306),
    'database' => (string)env('DB_DATABASE', 'benitours'),
    'username' => (string)env('DB_USERNAME', 'root'),
    'password' => (string)env('DB_PASSWORD', ''), 
    'charset'  => (string)env('DB_CHARSET', 'utf8mb4')
];