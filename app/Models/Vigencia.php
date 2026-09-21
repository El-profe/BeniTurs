<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Vigencia {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function registrar(int $idLugar, int $idPago, string $inicio, string $vencimiento, string $tipo): int {
        $sql = "INSERT INTO vigencias (id_lugar, id_pago, fecha_inicio, fecha_vencimiento, tipo_periodo) 
                VALUES (:id_lugar, :id_pago, :inicio, :vencimiento, :tipo)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_lugar'     => $idLugar,
            ':id_pago'      => $idPago,
            ':inicio'       => $inicio,
            ':vencimiento'  => $vencimiento,
            ':tipo'         => $tipo
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function obtenerUltimoVencimiento(int $idLugar): ?string {
        $sql = "SELECT MAX(v.fecha_vencimiento) FROM vigencias v
                INNER JOIN pagos p ON p.id_pago = v.id_pago
                WHERE v.id_lugar = :id AND p.estado = 'CONFIRMADO'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idLugar]);
        return $stmt->fetchColumn() ?: null;
    }
}
