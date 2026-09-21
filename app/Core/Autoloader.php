<?php
namespace App\Core;

/**
 * Autocargador de clases compatible con convención PSR-4.
 * Convierte el namespace 'App\...' al directorio 'app/...'.
 */
class Autoloader {
    public static function register(): void {
        spl_autoload_register([__CLASS__, 'loadClass']);
    }

    public static function loadClass(string $className): void {
        $prefix = 'App\\';
        $baseDir = dirname(__DIR__) . DIRECTORY_SEPARATOR; // Ruta absoluta a /app/

        $len = strlen($prefix);
        if (strncmp($prefix, $className, $len) !== 0) {
            return;
        }

        // Obtener nombre relativo de clase y reemplazar separadores de namespace por directorios
        $relativeClass = substr($className, $len);
        $file = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';

        if (file_exists($file)) {
            require_once $file;
        }
    }
}