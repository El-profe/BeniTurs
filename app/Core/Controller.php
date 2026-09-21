<?php
namespace App\Core;

/**
 * Controlador Base.
 * Provee utilidades comunes para renderizado de vistas HTML y emisión de respuestas JSON.
 */
abstract class Controller {
    protected array $config;

    public function __construct() {
        $this->config = require dirname(__DIR__, 2) . '/config/app.php';
    }

    /**
     * Renderiza una vista dentro de una plantilla (layout).
     */
    protected function render(string $viewPath, array $data = [], string $layout = 'publico'): void {
        // Extraer variables para que estén disponibles en la vista
        extract($data, EXTR_SKIP);

        // Compartir variables globales útiles en vistas
        $appName = $this->config['app_name'];
        $baseUrl = rtrim($this->config['base_url'], '/');

        // Capturar contenido de la vista interna
        $viewFile = dirname(__DIR__) . "/Views/{$viewPath}.php";
        if (!file_exists($viewFile)) {
            http_response_code(500);
            die("Error interno: La vista [{$viewPath}] no existe en el sistema.");
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        // Cargar el layout que envolverá la vista
        $layoutFile = dirname(__DIR__) . "/Views/layouts/{$layout}.php";
        if (!file_exists($layoutFile)) {
            http_response_code(500);
            die("Error interno: El layout [{$layout}] no fue encontrado.");
        }

        require $layoutFile;
    }

    /**
     * Emite una respuesta estructurada en formato JSON para peticiones Fetch.
     */
    protected function json(mixed $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit();
    }
}