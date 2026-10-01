<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Categoria {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function listarTodasActivas(): array {
        $sql = "SELECT id_categoria, nombre, slug, descripcion, icono, tipo_defecto 
                FROM categorias 
                WHERE activo = 1 
                ORDER BY id_categoria ASC";
        return $this->db->query($sql)->fetchAll();
    }

    public function listarTodas(): array {
        $sql = "SELECT c.*, COUNT(l.id_lugar) AS total_lugares
                FROM categorias c
                LEFT JOIN lugares l ON l.id_categoria = c.id_categoria
                GROUP BY c.id_categoria
                ORDER BY c.activo DESC, c.nombre ASC";
        return $this->db->query($sql)->fetchAll();
    }

    public function buscarPorId(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM categorias WHERE id_categoria = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public function buscarPorSlug(string $slug): ?array {
        $stmt = $this->db->prepare("SELECT * FROM categorias WHERE slug = :slug LIMIT 1");
        $stmt->execute([':slug' => $slug]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public function crear(array $datos): int {
        $sql = "INSERT INTO categorias (nombre, slug, descripcion, icono, tipo_defecto, activo)
                VALUES (:nombre, :slug, :descripcion, :icono, :tipo_defecto, :activo)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nombre'       => trim($datos['nombre']),
            ':slug'         => trim($datos['slug']),
            ':descripcion'  => !empty($datos['descripcion']) ? trim($datos['descripcion']) : null,
            ':icono'        => !empty($datos['icono']) ? trim($datos['icono']) : 'bi-tag',
            ':tipo_defecto' => in_array($datos['tipo_defecto'] ?? '', ['PUBLICO', 'COMERCIAL'], true) ? $datos['tipo_defecto'] : 'COMERCIAL',
            ':activo'       => isset($datos['activo']) ? (int)$datos['activo'] : 1
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function actualizar(int $id, array $datos): bool {
        $sql = "UPDATE categorias 
                SET nombre = :nombre, 
                    slug = :slug, 
                    descripcion = :descripcion, 
                    icono = :icono, 
                    tipo_defecto = :tipo_defecto, 
                    activo = :activo
                WHERE id_categoria = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id'           => $id,
            ':nombre'       => trim($datos['nombre']),
            ':slug'         => trim($datos['slug']),
            ':descripcion'  => !empty($datos['descripcion']) ? trim($datos['descripcion']) : null,
            ':icono'        => !empty($datos['icono']) ? trim($datos['icono']) : 'bi-tag',
            ':tipo_defecto' => in_array($datos['tipo_defecto'] ?? '', ['PUBLICO', 'COMERCIAL'], true) ? $datos['tipo_defecto'] : 'COMERCIAL',
            ':activo'       => isset($datos['activo']) ? (int)$datos['activo'] : 1
        ]);
    }

    public function cambiarEstado(int $id, int $activo): bool {
        $stmt = $this->db->prepare("UPDATE categorias SET activo = :activo WHERE id_categoria = :id");
        return $stmt->execute([
            ':id'     => $id,
            ':activo' => $activo ? 1 : 0
        ]);
    }
}