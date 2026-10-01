<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Municipio {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Retorna todos los municipios activos ordenados alfabéticamente.
     */
    public function listarActivos(): array {
        $sql = "SELECT id_municipio, nombre, provincia, slug, latitud_defecto, longitud_defecto, activo
                FROM municipios
                WHERE activo = 1
                ORDER BY nombre ASC";
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Retorna todos los municipios (activos e inactivos) con conteo de lugares.
     */
    public function listarTodosConConteo(): array {
        $sql = "SELECT m.*, COUNT(l.id_lugar) AS total_lugares
                FROM municipios m
                LEFT JOIN lugares l ON l.id_municipio = m.id_municipio
                GROUP BY m.id_municipio
                ORDER BY m.activo DESC, m.nombre ASC";
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Busca un municipio por su clave primaria.
     */
    public function buscarPorId(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM municipios WHERE id_municipio = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    /**
     * Busca un municipio por su slug.
     */
    public function buscarPorSlug(string $slug): ?array {
        $stmt = $this->db->prepare("SELECT * FROM municipios WHERE slug = :slug LIMIT 1");
        $stmt->execute([':slug' => $slug]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    /**
     * Retorna el listado de municipios con la cantidad de lugares turísticos/comerciales activos.
     */
    public function listarConConteoLugares(): array {
        $sql = "SELECT m.*, COUNT(l.id_lugar) AS total_lugares
                FROM municipios m
                LEFT JOIN lugares l ON l.id_municipio = m.id_municipio
                WHERE m.activo = 1
                GROUP BY m.id_municipio
                ORDER BY m.nombre ASC";
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Registra un nuevo municipio en el sistema.
     */
    public function crear(array $datos): int {
        $sql = "INSERT INTO municipios (nombre, provincia, slug, latitud_defecto, longitud_defecto, activo)
                VALUES (:nombre, :provincia, :slug, :latitud, :longitud, :activo)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nombre'    => trim($datos['nombre']),
            ':provincia' => trim($datos['provincia']),
            ':slug'      => trim($datos['slug']),
            ':latitud'   => !empty($datos['latitud_defecto']) ? (float)$datos['latitud_defecto'] : null,
            ':longitud'  => !empty($datos['longitud_defecto']) ? (float)$datos['longitud_defecto'] : null,
            ':activo'    => isset($datos['activo']) ? (int)$datos['activo'] : 1
        ]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Actualiza un municipio existente.
     */
    public function actualizar(int $id, array $datos): bool {
        $sql = "UPDATE municipios
                SET nombre = :nombre,
                    provincia = :provincia,
                    slug = :slug,
                    latitud_defecto = :latitud,
                    longitud_defecto = :longitud,
                    activo = :activo
                WHERE id_municipio = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id'        => $id,
            ':nombre'    => trim($datos['nombre']),
            ':provincia' => trim($datos['provincia']),
            ':slug'      => trim($datos['slug']),
            ':latitud'   => !empty($datos['latitud_defecto']) ? (float)$datos['latitud_defecto'] : null,
            ':longitud'  => !empty($datos['longitud_defecto']) ? (float)$datos['longitud_defecto'] : null,
            ':activo'    => isset($datos['activo']) ? (int)$datos['activo'] : 1
        ]);
    }

    /**
     * Cambia el estado activo/inactivo de un municipio.
     */
    public function cambiarEstado(int $id, int $activo): bool {
        $stmt = $this->db->prepare("UPDATE municipios SET activo = :activo WHERE id_municipio = :id");
        return $stmt->execute([
            ':id'     => $id,
            ':activo' => $activo ? 1 : 0
        ]);
    }
}
