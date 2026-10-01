<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Pago {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Registra un pago comercial con estado inicial PENDIENTE (RF-22)
     */
    public function registrar(array $datos): int {
        $meses = filter_var($datos['meses_duracion'] ?? null, FILTER_VALIDATE_INT);
        if ($meses === false || $meses < 1 || $meses > 120) {
            throw new \InvalidArgumentException('La duración del plan debe ser entre 1 y 120 meses.');
        }
        $sql = "INSERT INTO pagos (
                    id_lugar, id_tarifa, monto, meses_duracion, fecha_pago_declarada,
                    numero_comprobante, comprobante_archivo, metodo_pago, estado, observaciones, es_registro_inicial
                ) VALUES (
                    :id_lugar, :id_tarifa, :monto, :meses, :fecha_declarada,
                    :comprobante, :archivo, :metodo, 'PENDIENTE', :obs, :es_inicial
                )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_lugar'         => $datos['id_lugar'],
            ':id_tarifa'        => $datos['id_tarifa'],
            ':monto'            => $datos['monto'],
            ':meses'            => $meses,
            ':fecha_declarada'  => $datos['fecha_pago_declarada'],
            ':comprobante'      => $datos['numero_comprobante'] ?? null,
            ':archivo'          => $datos['comprobante_archivo'] ?? null,
            ':metodo'           => $datos['metodo_pago'] ?? 'Transferencia bancaria / QR',
            ':es_inicial'       => !empty($datos['es_registro_inicial']) ? 1 : 0,
            ':obs'              => $datos['observaciones'] ?? null
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Listado administrativo con estados, establecimiento y vigencias asociadas
     */
    public function listar(?string $filtroEstado = null): array {
        $sql = "SELECT p.*, l.nombre AS nombre_establecimiento, l.slug AS slug_lugar,
                       v.fecha_inicio, v.fecha_vencimiento, v.tipo_periodo,
                       a.nombre AS nombre_admin
                FROM pagos p
                INNER JOIN lugares l ON p.id_lugar = l.id_lugar
                LEFT JOIN vigencias v ON v.id_pago = p.id_pago
                LEFT JOIN administradores a ON p.id_administrador_confirmacion = a.id_administrador";

        $params = [];
        if (!empty($filtroEstado) && in_array($filtroEstado, ['PENDIENTE', 'CONFIRMADO', 'ANULADO'])) {
            $sql .= " WHERE p.estado = :estado";
            $params[':estado'] = $filtroEstado;
        }

        $sql .= " ORDER BY p.id_pago DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id): ?array {
        $sql = "SELECT p.*, l.nombre AS nombre_establecimiento 
                FROM pagos p 
                INNER JOIN lugares l ON p.id_lugar = l.id_lugar 
                WHERE p.id_pago = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public function anular(int $idPago, string $motivo): bool {
        $sql = "UPDATE pagos SET estado = 'ANULADO', observaciones = CONCAT(COALESCE(observaciones, ''), ' [ANULADO: ', :motivo, ']') WHERE id_pago = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':motivo' => $motivo, ':id' => $idPago]);
    }
}
