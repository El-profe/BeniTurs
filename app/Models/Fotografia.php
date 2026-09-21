<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Fotografia {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function registrar(int $idLugar, array $datosFoto, int $esPrincipal = 1): int {
        if ($esPrincipal) {
            // Desmarcar principal anterior si existe
            $stmtReset = $this->db->prepare("UPDATE fotografias SET es_principal = 0 WHERE id_lugar = :id");
            $stmtReset->execute([':id' => $idLugar]);
        }

        $sql = "INSERT INTO fotografias (id_lugar, nombre_archivo, nombre_original, mime_type, tamano_bytes, es_principal) 
                VALUES (:id_lugar, :nombre_archivo, :nombre_original, :mime_type, :tamano_bytes, :es_principal)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_lugar'         => $idLugar,
            ':nombre_archivo'   => $datosFoto['nombre_archivo'],
            ':nombre_original'  => $datosFoto['nombre_original'],
            ':mime_type'        => $datosFoto['mime_type'],
            ':tamano_bytes'     => $datosFoto['tamano_bytes'],
            ':es_principal'     => $esPrincipal
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function obtenerPrincipalPorLugar(int $idLugar): ?array {
        $stmt = $this->db->prepare("SELECT * FROM fotografias WHERE id_lugar = :id AND es_principal = 1 LIMIT 1");
        $stmt->execute([':id' => $idLugar]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public function obtenerPorNombreArchivo(string $archivo): ?array {
        $stmt = $this->db->prepare("SELECT * FROM fotografias WHERE nombre_archivo = :arch LIMIT 1");
        $stmt->execute([':arch' => $archivo]);
        $res = $stmt->fetch();
        return $res ?: null;
    }
}