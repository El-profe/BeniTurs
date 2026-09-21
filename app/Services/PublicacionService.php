<?php
namespace App\Services;

use App\Core\Database;
use PDO;

/**
 * Centraliza las reglas de visibilidad pública de las publicaciones.
 * 
 * Regla de negocio:
 * 1. Lugar PUBLICO: requiere aprobado = 1 y habilitado = 1 (no requiere pago).
 * 2. Lugar COMERCIAL: requiere aprobado = 1, habilitado = 1 Y tener una vigencia de pago
 *    confirmado activa (CURDATE() >= fecha_inicio Y CURDATE() < fecha_vencimiento).
 */
class PublicacionService {

    /**
     * Retorna la expresión SQL para determinar la visibilidad pública en consultas.
     */
    public static function getSqlCondicionVisibilidad(string $aliasLugar = 'l', string $aliasPub = 'pub'): string {
        return "(
            ({$aliasLugar}.tipo_lugar = 'PUBLICO' AND {$aliasPub}.aprobado = 1 AND {$aliasPub}.habilitado = 1)
            OR
            ({$aliasLugar}.tipo_lugar = 'COMERCIAL' AND {$aliasPub}.aprobado = 1 AND {$aliasPub}.habilitado = 1 AND EXISTS (
                SELECT 1 FROM vigencias v
                INNER JOIN pagos p ON v.id_pago = p.id_pago
                WHERE v.id_lugar = {$aliasLugar}.id_lugar
                  AND p.estado = 'CONFIRMADO'
                  AND CURDATE() >= v.fecha_inicio
                  AND CURDATE() < v.fecha_vencimiento
            ))
        )";
    }

    /**
     * Comprueba si una ficha específica es visible al público general.
     */
    public static function esFichaVisible(int $idLugar): bool {
        $db = Database::getConnection();
        $condicion = self::getSqlCondicionVisibilidad('l', 'pub');
        
        $sql = "SELECT COUNT(*) FROM lugares l
                INNER JOIN publicaciones pub ON l.id_lugar = pub.id_lugar
                WHERE l.id_lugar = :id AND {$condicion}";

        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $idLugar]);
        return ((int)$stmt->fetchColumn()) > 0;
    }
}