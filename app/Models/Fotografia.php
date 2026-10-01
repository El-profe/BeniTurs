<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Fotografia {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Registra una fotografía asociada a un lugar
     */
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

    /**
     * Lista todas las fotografías de un lugar con metadatos completos
     */
    public function listarPorLugar(int $idLugar): array {
        $stmt = $this->db->prepare("SELECT id_fotografia, id_lugar, nombre_archivo, nombre_original, mime_type, tamano_bytes, es_principal 
                                     FROM fotografias 
                                     WHERE id_lugar = :id 
                                     ORDER BY es_principal DESC, id_fotografia ASC");
        $stmt->execute([':id' => $idLugar]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca una fotografía por su ID
     */
    public function buscarPorId(int $idFoto): ?array {
        $stmt = $this->db->prepare("SELECT * FROM fotografias WHERE id_fotografia = :id LIMIT 1");
        $stmt->execute([':id' => $idFoto]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    /**
     * Elimina una fotografía de la base de datos y garantiza que quede una principal si quedan fotos
     */
    public function eliminar(int $idFoto, ?int $idLugar = null): bool {
        $sql = "DELETE FROM fotografias WHERE id_fotografia = :id";
        $params = [':id' => $idFoto];
        if ($idLugar !== null) {
            $sql .= " AND id_lugar = :id_lugar";
            $params[':id_lugar'] = $idLugar;
        }
        $stmt = $this->db->prepare($sql);
        $ok = $stmt->execute($params);
        if ($ok && $idLugar !== null) {
            $this->asegurarPrincipal($idLugar);
        }
        return $ok;
    }

    /**
     * Establece una fotografía específica como la principal/portada del lugar
     */
    public function establecerPrincipal(int $idFoto, int $idLugar): bool {
        $this->db->prepare("UPDATE fotografias SET es_principal = 0 WHERE id_lugar = :id_lugar")->execute([':id_lugar' => $idLugar]);
        $stmt = $this->db->prepare("UPDATE fotografias SET es_principal = 1 WHERE id_fotografia = :id AND id_lugar = :id_lugar");
        return $stmt->execute([':id' => $idFoto, ':id_lugar' => $idLugar]);
    }

    /**
     * Asegura que exista al menos una fotografía principal si hay fotos en la galería
     */
    public function asegurarPrincipal(int $idLugar): void {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM fotografias WHERE id_lugar = :id AND es_principal = 1");
        $stmt->execute([':id' => $idLugar]);
        if ((int)$stmt->fetchColumn() === 0) {
            $stmtUpdate = $this->db->prepare("UPDATE fotografias SET es_principal = 1 WHERE id_lugar = :id ORDER BY id_fotografia ASC LIMIT 1");
            $stmtUpdate->execute([':id' => $idLugar]);
        }
    }

    public function obtenerPrincipalPorLugar(int $idLugar): ?array {
        $stmt = $this->db->prepare("SELECT * FROM fotografias WHERE id_lugar = :id AND es_principal = 1 LIMIT 1");
        $stmt->execute([':id' => $idLugar]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    public function obtenerPorNombreArchivo(string $archivo): ?array {
        $stmt = $this->db->prepare("SELECT * FROM fotografias WHERE nombre_archivo = :arch LIMIT 1");
        $stmt->execute([':arch' => $archivo]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }
}
