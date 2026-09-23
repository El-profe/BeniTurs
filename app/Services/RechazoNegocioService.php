<?php
namespace App\Services;

use App\Core\Database;
use RuntimeException;
use Throwable;

class RechazoNegocioService {
    public static function eliminarRegistro(int $idPago, int $idAdmin): array {
        $db = Database::getConnection();
        $buscar = $db->prepare('SELECT id_lugar FROM pagos WHERE id_pago = ?');
        $buscar->execute([$idPago]);
        $idLugar = (int)$buscar->fetchColumn();
        if (!$idLugar) throw new RuntimeException('El registro ya no existe.');
        if (!Database::beginTransaction()) throw new RuntimeException('Ya hay una transacción en curso.');
        try {
            $admin = $db->prepare('SELECT id_administrador FROM administradores WHERE id_administrador = ? AND activo = 1');
            $admin->execute([$idAdmin]);
            if (!$admin->fetchColumn()) throw new RuntimeException('Administrador no autorizado.');
            // Mismo orden de bloqueos que la confirmación: lugar, después pagos.
            $lock = $db->prepare('SELECT tipo_lugar FROM lugares WHERE id_lugar = ? FOR UPDATE');
            $lock->execute([$idLugar]);
            if ($lock->fetchColumn() !== 'COMERCIAL') throw new RuntimeException('No es un registro comercial.');
            $stmt = $db->prepare('SELECT * FROM pagos WHERE id_lugar = ? FOR UPDATE');
            $stmt->execute([$idLugar]);
            $pagos = $stmt->fetchAll();
            $objetivo = null;
            foreach ($pagos as $pago) {
                if ($pago['estado'] === 'CONFIRMADO') throw new RuntimeException('No se puede eliminar un negocio con pagos confirmados.');
                if ((int)$pago['id_pago'] === $idPago) $objetivo = $pago;
            }
            if (!$objetivo || $objetivo['estado'] !== 'PENDIENTE' || !(int)$objetivo['es_registro_inicial']) {
                throw new RuntimeException('Solo se pueden eliminar altas de autoservicio pendientes de verificación.');
            }
            $vigencias = $db->prepare('SELECT id_vigencia FROM vigencias WHERE id_lugar = ? LIMIT 1');
            $vigencias->execute([$idLugar]);
            if ($vigencias->fetchColumn()) throw new RuntimeException('El negocio tiene historial de vigencias; no se puede eliminar como registro falso.');
            $stmt = $db->prepare('SELECT nombre_archivo FROM fotografias WHERE id_lugar = ?');
            $stmt->execute([$idLugar]);
            $fotos = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            // FK ON DELETE CASCADE retira cuenta, fotos, promociones, publicación y pagos.
            $db->prepare('DELETE FROM lugares WHERE id_lugar = ?')->execute([$idLugar]);
            $db->commit();
        } catch (Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            throw $e;
        }
        $fallos = [];
        foreach (array_unique($fotos) as $nombre) {
            try {
                $ref = $db->prepare('SELECT COUNT(*) FROM fotografias WHERE nombre_archivo = ?');
                $ref->execute([$nombre]);
                if (!(int)$ref->fetchColumn() && !ImagenService::eliminarArchivo($nombre)) $fallos[] = $nombre;
            } catch (Throwable $e) { $fallos[] = $nombre; }
        }
        foreach (array_unique(array_filter(array_column($pagos,'comprobante_archivo'))) as $nombre) {
            try {
                $ref = $db->prepare('SELECT (SELECT COUNT(*) FROM pagos WHERE comprobante_archivo = ?) +
                    (SELECT COUNT(*) FROM solicitudes WHERE comprobante_archivo = ?)');
                $ref->execute([$nombre,$nombre]);
                if (!(int)$ref->fetchColumn() && !ComprobanteService::eliminar($nombre)) $fallos[] = $nombre;
            } catch (Throwable $e) { $fallos[] = $nombre; }
        }
        if ($fallos) error_log('Archivos pendientes de limpieza del lugar '.$idLugar.': '.json_encode($fallos));
        error_log("Registro comercial eliminado: lugar=$idLugar, pago=$idPago, administrador=$idAdmin");
        return ['id_lugar'=>$idLugar,'fallos_archivos'=>count($fallos)];
    }
}
