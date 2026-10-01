<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Tarifa {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Retorna la tarifa vigente para un código de plan (MENSUAL o ANUAL).
     * Proporciona monto_mensual como alias de monto para compatibilidad retroactiva.
     */
    public function obtenerTarifaVigente(?string $codigoPlan = null): ?array {
        if ($codigoPlan !== null) {
            $sql = "SELECT *, monto AS monto_mensual FROM tarifas 
                    WHERE activo = 1 AND codigo_plan = :plan AND vigente_desde <= CURDATE() 
                    ORDER BY vigente_desde DESC, id_tarifa DESC LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':plan' => strtoupper(trim($codigoPlan))]);
        } else {
            $sql = "SELECT *, monto AS monto_mensual FROM tarifas 
                    WHERE activo = 1 AND vigente_desde <= CURDATE() 
                    ORDER BY (codigo_plan = 'MENSUAL') DESC, vigente_desde DESC, id_tarifa DESC LIMIT 1";
            $stmt = $this->db->query($sql);
        }
        $res = $stmt->fetch();
        return $res ?: null;
    }

    /**
     * Busca una tarifa activa por su código único de plan (ej. 'MENSUAL', 'ANUAL').
     */
    public function buscarPorCodigoPlan(string $codigoPlan): ?array {
        $sql = "SELECT *, monto AS monto_mensual FROM tarifas 
                WHERE codigo_plan = :codigo AND activo = 1 LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':codigo' => strtoupper(trim($codigoPlan))]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    /**
     * Retorna todos los planes comerciales activos disponibles.
     */
    public function listarPlanesActivos(): array {
        $sql = "SELECT *, monto AS monto_mensual FROM tarifas 
                WHERE activo = 1 AND vigente_desde <= CURDATE() 
                ORDER BY meses_duracion ASC, id_tarifa ASC";
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Retorna todos los planes comerciales (activos e inactivos) con métricas de uso.
     */
    public function listarTodas(): array {
        $sql = "SELECT t.*, t.monto AS monto_mensual,
                       (SELECT COUNT(*) FROM pagos p WHERE p.id_tarifa = t.id_tarifa) AS total_pagos,
                       (SELECT COUNT(*) FROM solicitudes s WHERE s.id_tarifa = t.id_tarifa) AS total_solicitudes
                FROM tarifas t
                ORDER BY t.activo DESC, t.meses_duracion ASC, t.id_tarifa ASC";
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Busca una tarifa por su ID.
     */
    public function buscarPorId(int $id): ?array {
        $stmt = $this->db->prepare("SELECT *, monto AS monto_mensual FROM tarifas WHERE id_tarifa = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    /**
     * Registra un nuevo plan o tarifa en el sistema.
     */
    public function crear(array $datos): int {
        $sql = "INSERT INTO tarifas (codigo_plan, nombre, monto, meses_duracion, descripcion, vigente_desde, activo)
                VALUES (:codigo_plan, :nombre, :monto, :meses, :descripcion, :vigente_desde, :activo)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':codigo_plan'   => strtoupper(trim($datos['codigo_plan'])),
            ':nombre'        => trim($datos['nombre']),
            ':monto'         => (float)$datos['monto'],
            ':meses'         => max(1, (int)$datos['meses_duracion']),
            ':descripcion'   => trim($datos['descripcion'] ?? ''),
            ':vigente_desde' => !empty($datos['vigente_desde']) ? $datos['vigente_desde'] : date('Y-m-d'),
            ':activo'        => isset($datos['activo']) ? (int)$datos['activo'] : 1
        ]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Actualiza una tarifa existente.
     */
    public function actualizar(int $id, array $datos): bool {
        $sql = "UPDATE tarifas
                SET codigo_plan = :codigo_plan,
                    nombre = :nombre,
                    monto = :monto,
                    meses_duracion = :meses,
                    descripcion = :descripcion,
                    vigente_desde = :vigente_desde,
                    activo = :activo
                WHERE id_tarifa = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id'            => $id,
            ':codigo_plan'   => strtoupper(trim($datos['codigo_plan'])),
            ':nombre'        => trim($datos['nombre']),
            ':monto'         => (float)$datos['monto'],
            ':meses'         => max(1, (int)$datos['meses_duracion']),
            ':descripcion'   => trim($datos['descripcion'] ?? ''),
            ':vigente_desde' => !empty($datos['vigente_desde']) ? $datos['vigente_desde'] : date('Y-m-d'),
            ':activo'        => isset($datos['activo']) ? (int)$datos['activo'] : 1
        ]);
    }

    /**
     * Cambia el estado activo/inactivo de una tarifa.
     */
    public function cambiarEstado(int $id, int $activo): bool {
        $stmt = $this->db->prepare("UPDATE tarifas SET activo = :activo WHERE id_tarifa = :id");
        return $stmt->execute([
            ':id'     => $id,
            ':activo' => $activo ? 1 : 0
        ]);
    }
}
