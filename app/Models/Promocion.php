<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Promocion {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function listarPorLugar(int $idLugar): array {
        $sql = "SELECT * FROM promociones WHERE id_lugar = :id ORDER BY id_promocion DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idLugar]);
        return $stmt->fetchAll();
    }

    public function listarVigentesPublicas(int $idLugar): array {
        $sql = "SELECT * FROM promociones 
                WHERE id_lugar = :id AND activo = 1 
                  AND CURDATE() BETWEEN fecha_inicio AND fecha_fin
                ORDER BY id_promocion DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idLugar]);
        return $stmt->fetchAll();
    }

    public function crear(array $datos): int {
        $sql = "INSERT INTO promociones (id_lugar, titulo, descripcion, descuento_texto, fecha_inicio, fecha_fin, activo)
                VALUES (:id_lugar, :titulo, :descripcion, :descuento, :inicio, :fin, 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_lugar'    => $datos['id_lugar'],
            ':titulo'      => $datos['titulo'],
            ':descripcion' => $datos['descripcion'],
            ':descuento'   => $datos['descuento_texto'] ?? null,
            ':inicio'      => $datos['fecha_inicio'],
            ':fin'         => $datos['fecha_fin']
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function cambiarEstado(int $idPromocion, int $idLugar, int $activo): bool {
        $sql = "UPDATE promociones SET activo = :activo WHERE id_promocion = :id AND id_lugar = :id_lugar";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':activo'   => $activo,
            ':id'       => $idPromocion,
            ':id_lugar' => $idLugar
        ]);
    }

    public function eliminar(int $idPromocion, int $idLugar): bool {
        $sql = "DELETE FROM promociones WHERE id_promocion = :id AND id_lugar = :id_lugar";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $idPromocion, ':id_lugar' => $idLugar]);
    }
}