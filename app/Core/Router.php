<?php
namespace App\Core;

/**
 * Enrutador central de la aplicación.
 * Registra rutas web/API y despacha la petición al controlador correspondiente.
 */
class Router {
    private array $routes = [];

    public function get(string $path, array|callable $handler): void {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, array|callable $handler): void {
        $this->addRoute('POST', $path, $handler);
    }

    private function addRoute(string $method, string $path, array|callable $handler): void {
        $normalizedPath = '/' . trim($path, '/');
        $this->routes[$method][$normalizedPath] = $handler;
    }

    /**
     * Procesa la solicitud entrante y ejecuta el controlador asociado.
     */
    public function dispatch(): void {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $this->resolveUri();

        $handler = $this->routes[$method][$uri] ?? null;

        if (!$handler) {
            $this->handleNotFound($uri);
            return;
        }

        // Manejo de función anónima (closure)
        if (is_callable($handler)) {
            call_user_func($handler);
            return;
        }

        // Manejo de [ClaseControlador, método]
        if (is_array($handler) && count($handler) === 2) {
            [$class, $action] = $handler;
            if (class_exists($class)) {
                $controllerInstance = new $class();
                if (method_exists($controllerInstance, $action)) {
                    $controllerInstance->$action();
                    return;
                }
            }
        }

        http_response_code(500);
        die("Error de configuración de ruta: El manejador no pudo ser invocado.");
    }

    /**
     * Resuelve la ruta relativa eliminando el prefijo del directorio en XAMPP.
     */
    private function resolveUri(): string {
        $requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $scriptDir  = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));

        // Si la petición contiene la ruta del script (ej. /trinidad_turismo/public), removerla
        if ($scriptDir !== '/' && !empty($scriptDir) && str_starts_with($requestUri, $scriptDir)) {
            $requestUri = substr($requestUri, strlen($scriptDir));
        } else {
            // Manejo cuando Apache redirige desde la raíz del proyecto
            $parentDir = dirname($scriptDir);
            if ($parentDir !== '/' && !empty($parentDir) && str_starts_with($requestUri, $parentDir)) {
                $requestUri = substr($requestUri, strlen($parentDir));
            }
        }

        $clean = '/' . trim($requestUri, '/');
        return ($clean === '//') ? '/' : $clean;
    }

    /**
     * Muestra la página 404 o responde con JSON según el tipo de solicitud.
     */
    private function handleNotFound(string $uri): void {
        http_response_code(404);

        if (str_starts_with($uri, '/api/')) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'error'   => 'Ruta de API no encontrada.',
                'uri'     => $uri
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $config = require dirname(__DIR__, 2) . '/config/app.php';
        $appName = $config['app_name'];
        $baseUrl = rtrim($config['base_url'], '/');
        $title = 'Página no encontrada';

        require dirname(__DIR__) . '/Views/errors/404.php';
        exit();
    }
}