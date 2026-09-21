<?php
// Ejecutar: php tests/solicitudes.php. Los registros de prueba se revierten.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
$sessionDirectory = sys_get_temp_dir() . '/trinidad-solicitudes-test-' . bin2hex(random_bytes(8));
if (!mkdir($sessionDirectory, 0700)) throw new RuntimeException('No se pudo crear la sesión de prueba.');
session_save_path($sessionDirectory);
register_shutdown_function(static function () use ($sessionDirectory): void {
    if (session_status() === PHP_SESSION_ACTIVE) session_destroy();
    rmdir($sessionDirectory);
});
require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Database;
use App\Middleware\CsrfMiddleware;
use App\Controllers\Publico\SolicitudController;

class SolicitudTestResponse extends Error {
    public function __construct(public array $data, public int $status) {
        parent::__construct('Respuesta capturada para la prueba');
    }
}

class SolicitudTestController extends SolicitudController {
    protected function json(mixed $data, int $statusCode = 200): void {
        throw new SolicitudTestResponse($data, $statusCode);
    }
}

function checkSolicitud(bool $condition, string $message): void {
    if (!$condition) throw new RuntimeException($message);
}

$db = Database::getConnection();
$engine = $db->query("SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'solicitudes'")->fetchColumn();
checkSolicitud($engine === 'InnoDB', 'La prueba requiere transacciones InnoDB.');
$category = $db->query('SELECT id_categoria FROM categorias WHERE activo = 1 LIMIT 1')->fetchColumn();
checkSolicitud($category !== false, 'Se necesita una categoría activa.');
$token = CsrfMiddleware::obtenerToken();
$payload = [
    'csrf_token' => $token,
    'nombre_establecimiento' => 'PRUEBA AUTOMATICA - REVERTIR',
    'id_categoria' => $category,
    'plan_solicitado' => 'MENSUAL',
    'nombre_solicitante' => 'Prueba automática',
    'telefono_contacto' => '00000000',
    'email_contacto' => '',
    'direccion' => 'Dirección de prueba',
    'descripcion' => 'Validación del envío; no publicar.',
    'horarios' => '08:00 a 18:00',
];
$controller = new SolicitudTestController();
$_SERVER['REQUEST_METHOD'] = 'POST';
$cases = [
    ['plan mensual', [], 200],
    ['plan anual', ['plan_solicitado' => 'ANUAL'], 200],
    ['categoría sin seleccionar', ['id_categoria' => ''], 422],
    ['correo inválido', ['email_contacto' => 'invalido'], 422],
    ['token inválido', ['csrf_token' => 'invalido'], 403],
];
foreach ($cases as [$name, $changes, $expectedStatus]) {
    $db->beginTransaction();
    try {
        $_POST = array_replace($payload, $changes);
        $before = (int)$db->query('SELECT COUNT(*) FROM solicitudes')->fetchColumn();
        try {
            $controller->apiEnviar();
            throw new RuntimeException('El controlador no devolvió una respuesta.');
        } catch (SolicitudTestResponse $response) {
            checkSolicitud($response->status === $expectedStatus, $name . ': estado inesperado.');
            if ($expectedStatus === 200) {
                checkSolicitud($response->data['success'] === true, 'No se confirmó el guardado.');
                $query = $db->prepare('SELECT * FROM solicitudes WHERE id_solicitud = ?');
                $query->execute([$response->data['id_solicitud']]);
                $saved = $query->fetch();
                checkSolicitud($saved && $saved['estado'] === 'PENDIENTE', 'No se guardó como pendiente.');
                foreach (['plan_solicitado', 'nombre_establecimiento', 'telefono_contacto', 'direccion', 'descripcion', 'horarios'] as $field) {
                    checkSolicitud($saved[$field] === $_POST[$field], 'Dato incorrecto: ' . $field);
                }
            } else {
                checkSolicitud((int)$db->query('SELECT COUNT(*) FROM solicitudes')->fetchColumn() === $before, 'Se guardó una solicitud inválida.');
            }
        }
    } finally {
        $db->rollBack();
    }
    echo "OK: {$name} (sin registros de prueba persistentes)\n";
}
