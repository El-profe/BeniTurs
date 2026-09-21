<?php
namespace App\Services;

use App\Core\Database;
use App\Models\Lugar;
use App\Models\Publicacion;
use App\Models\Solicitud;
use RuntimeException;
use Throwable;

class SolicitudService {
    public static function convertirAFicha(int $idSolicitud, int $idAdmin): int {
        $db = Database::getConnection();
        $db->beginTransaction();
        try {
            $stmt = $db->prepare('SELECT * FROM solicitudes WHERE id_solicitud = ? FOR UPDATE');
            $stmt->execute([$idSolicitud]);
            $solicitud = $stmt->fetch();
            if (!$solicitud || $solicitud['estado'] !== 'ACEPTADA') {
                throw new RuntimeException('La solicitud debe existir y estar aceptada.');
            }
            if ((new Solicitud())->yaFueConvertida($idSolicitud)) {
                throw new RuntimeException('Esta solicitud ya fue convertida en una ficha.');
            }
            $idLugar = (new Lugar())->crear([
                'id_categoria' => (int)$solicitud['id_categoria'],
                'nombre' => $solicitud['nombre_establecimiento'],
                'descripcion' => $solicitud['descripcion'],
                'direccion' => $solicitud['direccion'],
                'referencia_ubicacion' => 'Ingresado mediante solicitud web #' . $idSolicitud,
                'telefono_contacto' => $solicitud['telefono_contacto'],
                'whatsapp_contacto' => $solicitud['telefono_contacto'],
                'email_contacto' => $solicitud['email_contacto'],
                'horario_atencion' => $solicitud['horarios'],
                'tipo_lugar' => 'COMERCIAL'
            ]);
            $db->prepare('UPDATE lugares SET id_solicitud_origen = ? WHERE id_lugar = ?')
                ->execute([$idSolicitud, $idLugar]);
            (new Publicacion())->crear($idLugar, 1, 1, $idAdmin);
            $db->commit();
            return $idLugar;
        } catch (Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            throw $e;
        }
    }
}
