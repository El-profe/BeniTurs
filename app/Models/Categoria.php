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

    public function buscarPorId(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM categorias WHERE id_categoria = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }
}