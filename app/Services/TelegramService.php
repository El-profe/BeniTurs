<?php
namespace App\Services;

use App\Core\Database;
use RuntimeException;
use Throwable;

class TelegramService {
    public static function configuracion(): array {
        return require dirname(__DIR__, 2) . '/config/comercial.php';
    }

    public static function configurado(): bool {
        $c = self::configuracion();
        return preg_match('/\A\d+:[A-Za-z0-9_-]+\z/D', $c['telegram_token']) === 1
            && preg_match('/\A[1-9]\d*\z/D', (string)$c['admin_chat_id']) === 1;
    }

    private static function api(string $metodo, array $datos): array {
        if (!self::configurado()) throw new RuntimeException('Telegram no está configurado.');
        $config = self::configuracion();
        $curl = curl_init('https://api.telegram.org/bot' . $config['telegram_token'] . '/' . $metodo);
        curl_setopt_array($curl, [CURLOPT_POST=>true, CURLOPT_POSTFIELDS=>$datos,
            CURLOPT_RETURNTRANSFER=>true, CURLOPT_CONNECTTIMEOUT=>5, CURLOPT_TIMEOUT=>20,
            CURLOPT_PROTOCOLS=>CURLPROTO_HTTPS, CURLOPT_FOLLOWLOCATION=>false]);
        $respuesta = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        $json = is_string($respuesta) ? json_decode($respuesta, true) : null;
        if ($status !== 200 || !is_array($json) || empty($json['ok'])) {
            // Nunca incluir la URL (contiene el token), el cuerpo o credenciales en logs.
            if ($metodo === 'editMessageCaption' && ($json['description'] ?? '') === 'Bad Request: message is not modified') return [];
            throw new RuntimeException('Telegram no confirmó ' . $metodo . ' (HTTP ' . $status . ').');
        }
        return $json;
    }

    private static function html(string $texto): string {
        return htmlspecialchars($texto, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function notificarNuevaSolicitud(int $idSolicitud, array $datos, string $rutaComprobante): int {
        $config = self::configuracion();
        $mime = ComprobanteService::validarImagen($rutaComprobante);
        $caption = "🔔 <b>NUEVA SOLICITUD COMERCIAL - BENITURS</b>\n" .
            '🏪 <b>Negocio:</b> ' . self::html($datos['nombre_establecimiento']) . "\n" .
            '📌 <b>Categoría:</b> ' . self::html($datos['categoria']) . "\n" .
            '👤 <b>Dueño:</b> ' . self::html($datos['nombre_solicitante']) . "\n" .
            '📞 <b>WhatsApp:</b> ' . self::html($datos['telefono_contacto']) . "\n" .
            '💳 <b>Plan:</b> ' . self::html($datos['plan_solicitado']) . ' (Bs ' . number_format((float)$datos['monto_declarado'], 2) . ")\n" .
            '🕒 <b>Fecha:</b> ' . date('d/m/Y H:i', strtotime($datos['created_at'])) . "\nSolicitud #" . $idSolicitud;
        $teclado = ['inline_keyboard'=>[
            [['text'=>'✅ Aprobar y Activar','callback_data'=>'aprobar_solicitud_' . $idSolicitud]],
            [['text'=>'❌ Rechazar','callback_data'=>'rechazar_solicitud_' . $idSolicitud]]
        ]];
        $respuesta = self::api('sendPhoto', ['chat_id'=>$config['admin_chat_id'],
            'photo'=>new \CURLFile($rutaComprobante, $mime, basename($rutaComprobante)),
            'caption'=>$caption, 'parse_mode'=>'HTML', 'protect_content'=>'true',
            'reply_markup'=>json_encode($teclado, JSON_UNESCAPED_UNICODE)]);
        return (int)$respuesta['result']['message_id'];
    }

    public static function actualizarMensaje(int $chatId, int $messageId, string $nuevoTexto): void {
        self::api('editMessageCaption', ['chat_id'=>$chatId, 'message_id'=>$messageId,
            'caption'=>$nuevoTexto, 'reply_markup'=>json_encode(['inline_keyboard'=>[]])]);
    }

    public static function responderCallback(string $callbackId, string $texto, bool $showAlert = false): void {
        self::api('answerCallbackQuery', ['callback_query_id'=>$callbackId,'text'=>$texto,'show_alert'=>$showAlert ? 'true' : 'false']);
    }

    public static function registrarWebhook(string $url): void {
        $secret = self::configuracion()['webhook_secret'];
        if (!filter_var($url, FILTER_VALIDATE_URL) || parse_url($url, PHP_URL_SCHEME) !== 'https' ||
            !preg_match('/\A[A-Za-z0-9_-]{16,256}\z/D', $secret)) {
            throw new RuntimeException('Se requiere una URL HTTPS pública y un secreto de 16 a 256 caracteres (letras, números, _ o -).');
        }
        self::api('setWebhook', ['url'=>$url,'secret_token'=>$secret,'allowed_updates'=>'["callback_query"]']);
    }

    public static function encolar(int $idSolicitud, string $tipo): void {
        if (!in_array($tipo, ['NUEVA','RESOLUCION'], true)) throw new RuntimeException('Tipo de notificación inválido.');
        Database::getConnection()->prepare('INSERT INTO telegram_notificaciones (id_solicitud,tipo) VALUES (?,?) ON DUPLICATE KEY UPDATE id_solicitud=VALUES(id_solicitud)')
            ->execute([$idSolicitud,$tipo]);
    }

    // Reserva corta en BD, HTTP fuera de la transacción. Un trabajador caído se reintenta.
    public static function procesarPendientes(int $limite = 10): int {
        if (!self::configurado()) return 0;
        $db = Database::getConnection();
        $procesados = 0;
        for ($i = 0; $i < $limite; $i++) {
            if (!Database::beginTransaction()) throw new RuntimeException('Trabajador dentro de una transacción.');
            try {
                $job = $db->query("SELECT * FROM telegram_notificaciones WHERE estado='PENDIENTE'
                    AND disponible_desde <= NOW() ORDER BY id_notificacion LIMIT 1 FOR UPDATE")->fetch();
                if (!$job) { $db->commit(); break; }
                $db->prepare('UPDATE telegram_notificaciones SET intentos=intentos+1, disponible_desde=DATE_ADD(NOW(), INTERVAL 2 MINUTE) WHERE id_notificacion=?')
                    ->execute([$job['id_notificacion']]);
                $db->commit();
            } catch (Throwable $e) {
                if ($db->inTransaction()) $db->rollBack();
                throw $e;
            }
            try {
                self::procesar($job);
                $db->prepare("UPDATE telegram_notificaciones SET estado='ENVIADA', ultimo_error=NULL WHERE id_notificacion=?")->execute([$job['id_notificacion']]);
                $procesados++;
            } catch (Throwable $e) {
                $db->prepare('UPDATE telegram_notificaciones SET ultimo_error=?, disponible_desde=DATE_ADD(NOW(), INTERVAL 5 MINUTE) WHERE id_notificacion=?')
                    ->execute(['No se confirmó la entrega; se reintentará.', $job['id_notificacion']]);
                error_log('Telegram: entrega pendiente #' . $job['id_notificacion']);
            }
        }
        return $procesados;
    }

    private static function procesar(array $job): void {
        $db = Database::getConnection();
        $s = (new \App\Models\Solicitud())->buscarPorId((int)$job['id_solicitud']);
        if (!$s) return;
        if ($job['tipo'] === 'NUEVA') {
            if ($s['estado'] !== 'PENDIENTE' || $s['telegram_message_id']) return;
            $messageId = self::notificarNuevaSolicitud((int)$s['id_solicitud'], $s, ComprobanteService::ruta($s['comprobante_archivo']));
            $db->prepare('UPDATE solicitudes SET telegram_message_id=? WHERE id_solicitud=?')->execute([$messageId,$s['id_solicitud']]);
            // Si la resolución coincidió con sendPhoto, reactivar el trabajo de resolución.
            $db->prepare("UPDATE telegram_notificaciones SET estado='PENDIENTE', disponible_desde=NOW() WHERE id_solicitud=? AND tipo='RESOLUCION'")->execute([$s['id_solicitud']]);
            return;
        }
        if ($s['estado'] === 'PENDIENTE') return;
        $chatId = (int)self::configuracion()['admin_chat_id'];
        $texto = ($s['estado'] === 'ACEPTADA' ? '🟢 SOLICITUD APROBADA Y NEGOCIO ACTIVADO' : '🔴 SOLICITUD RECHAZADA') .
            "\n#{$s['id_solicitud']} · {$s['nombre_establecimiento']}\n" . date('d/m/Y H:i');
        if ($s['telegram_message_id']) self::actualizarMensaje($chatId, (int)$s['telegram_message_id'], $texto);
        if ($s['estado'] === 'ACEPTADA') {
            $link = SolicitudService::enlaceBienvenida($s);
            $mensaje = self::html($texto);
            if ($link) $mensaje .= "\n" . '<a href="' . self::html($link) . '">📲 Enviar credenciales por WhatsApp</a>';
            self::api('sendMessage', ['chat_id'=>$chatId,'text'=>$mensaje,
                'parse_mode'=>'HTML','protect_content'=>'true','link_preview_options'=>'{"is_disabled":true}']);
        }
    }

    public static function validarWebhook(array $update, string $secreto): array {
        $config = self::configuracion();
        if (!self::configurado() || $config['webhook_secret'] === '' || !hash_equals($config['webhook_secret'], $secreto)) {
            throw new \InvalidArgumentException('Webhook no autorizado.');
        }
        $callback = $update['callback_query'] ?? null;
        if (!is_array($callback)) return [];
        if ((string)($callback['from']['id'] ?? '') !== (string)$config['admin_chat_id'] ||
            (string)($callback['message']['chat']['id'] ?? '') !== (string)$config['admin_chat_id'] ||
            ($callback['message']['chat']['type'] ?? '') !== 'private') {
            throw new \InvalidArgumentException('Administrador de Telegram no autorizado.');
        }
        if (!is_string($callback['id'] ?? null) || !is_string($callback['data'] ?? null) ||
            !preg_match('/\A(aprobar|rechazar)_solicitud_([1-9][0-9]{0,9})\z/D', $callback['data'], $m) ||
            !is_int($callback['message']['message_id'] ?? null)) throw new \InvalidArgumentException('Callback inválido.');
        return ['accion'=>$m[1], 'id_solicitud'=>(int)$m[2], 'callback_id'=>$callback['id'],
            'chat_id'=>(int)$config['admin_chat_id'], 'message_id'=>$callback['message']['message_id'],
            'admin_id'=>$config['telegram_admin_id'] ?: null];
    }
}
