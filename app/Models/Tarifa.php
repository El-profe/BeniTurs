<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Tarifa {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function obtenerTarifaVigente(): ?array {
        $sql = "SELECT * FROM tarifas WHERE activo = 1 AND vigente_desde <= CURDATE() ORDER BY vigente_desde DESC, id_tarifa DESC LIMIT 1";
        $stmt = $this->db->query($sql);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public function buscarPorId(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM tarifas WHERE id_tarifa = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }
}
