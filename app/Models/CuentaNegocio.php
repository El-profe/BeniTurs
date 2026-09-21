<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class CuentaNegocio {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function buscarPorCredencial(string $identificador): ?array {
        $sql = "SELECT cn.*, l.nombre AS nombre_negocio, l.tipo_lugar, l.slug
                FROM cuentas_negocio cn
                INNER JOIN lugares l ON cn.id_lugar = l.id_lugar
                WHERE (cn.usuario = :id_user OR cn.email = :id_email) AND cn.activo = 1
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_user'  => $identificador,
            ':id_email' => $identificador
        ]);
        $cuenta = $stmt->fetch();
        return $cuenta ?: null;
    }

    public function actualizarUltimoAcceso(int $idCuenta): void {
        $sql = "UPDATE cuentas_negocio SET ultimo_acceso = NOW() WHERE id_cuenta = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idCuenta]);
    }

    public function registrarCuenta(int $idLugar, string $usuario, string $email, string $passwordPlano): int {
        $sql = "INSERT INTO cuentas_negocio (id_lugar, usuario, email, password_hash) 
                VALUES (:id_lugar, :usuario, :email, :hash)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_lugar' => $idLugar,
            ':usuario'  => $usuario,
            ':email'    => $email,
            ':hash'     => password_hash($passwordPlano, PASSWORD_BCRYPT)
        ]);
        return (int)$this->db->lastInsertId();
    }
}