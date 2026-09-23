<?php
require dirname(__DIR__) . '/app/Core/Autoloader.php';
App\Core\Autoloader::register();
date_default_timezone_set('America/La_Paz');
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { http_response_code(405); exit('{"ok":false}'); }
try {
    $raw = file_get_contents('php://input', false, null, 0, 65537);
    if ($raw === false || strlen($raw) > 65536) throw new InvalidArgumentException('Petición inválida.');
    $update = json_decode($raw, true, 32, JSON_THROW_ON_ERROR);
    if (!is_array($update)) throw new InvalidArgumentException('Petición inválida.');
    $callback = App\Services\TelegramService::validarWebhook($update, $_SERVER['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN'] ?? '');
} catch (Throwable $e) {
    http_response_code(403); exit('{"ok":false}');
}
if (!$callback) exit('{"ok":true}');
try {
    if ($callback['accion'] === 'aprobar') {
        App\Services\SolicitudService::aprovisionarNegocioCompleto($callback['id_solicitud'], $callback['admin_id']);
        $texto = '🟢 SOLICITUD APROBADA Y NEGOCIO ACTIVADO';
    } else {
        App\Services\SolicitudService::rechazar($callback['id_solicitud'], $callback['admin_id'], 'Rechazada desde Telegram.');
        $texto = '🔴 SOLICITUD RECHAZADA';
    }
} catch (PDOException $e) {
    error_log('Base de datos no disponible para callback de Telegram.');
    http_response_code(500); exit('{"ok":false}');
} catch (RuntimeException $e) {
    // Conflictos de estado son definitivos; Telegram no debe reaprovisionar.
    try { App\Services\TelegramService::responderCallback($callback['callback_id'], 'No se pudo resolver. Revisa la solicitud en el panel administrativo.', true); } catch (Throwable $ignorado) {}
    exit('{"ok":true}');
} catch (Throwable $e) {
    error_log('Fallo al resolver solicitud por Telegram #' . $callback['id_solicitud']);
    http_response_code(500); exit('{"ok":false}');
}
// Fallos de Telegram posteriores al commit no revierten la activación: queda en cola.
try {
    App\Services\TelegramService::responderCallback($callback['callback_id'], $texto);
    App\Services\TelegramService::actualizarMensaje($callback['chat_id'], $callback['message_id'], $texto . "\nSolicitud #" . $callback['id_solicitud']);
    App\Services\TelegramService::procesarPendientes(2);
} catch (Throwable $e) { error_log('Resolución guardada; notificación Telegram pendiente.'); }
echo '{"ok":true}';
