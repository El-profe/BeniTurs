<?php
namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Modelo de datos para la entidad administradores.
 */
class Administrador {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Busca un administrador activo por su nombre de usuario o correo electrónico.
     */
    public function buscarPorIdentificador(string $identificador): ?array {
        $sql = "SELECT id_administrador, nombre, usuario, email, password_hash, activo 
                FROM administradores 
                WHERE (usuario = :id_user OR email = :id_email) AND activo = 1 
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_user'  => $identificador,
            ':id_email' => $identificador
        ]);

        $admin = $stmt->fetch();
        return $admin ?: null;
    }

    /**
     * Registra la fecha y hora del acceso exitoso del administrador.
     */
    public function actualizarUltimoAcceso(int $idAdministrador): void {
        $sql = "UPDATE administradores SET ultimo_acceso = NOW() WHERE id_administrador = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idAdministrador]);
    }

    /**
     * Sincroniza el hash de la contraseña si se utiliza la clave inicial por defecto.
     */
    public function actualizarPasswordHash(int $idAdministrador, string $nuevoHash): void {
        $sql = "UPDATE administradores SET password_hash = :hash WHERE id_administrador = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':hash' => $nuevoHash,
            ':id'   => $idAdministrador
        ]);
    }
}