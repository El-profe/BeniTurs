<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class Solicitud {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function registrar(array $datos): int {
        $idTarifa = (int)($datos['id_tarifa'] ?? 0);
        if ($idTarifa <= 0) {
            $plan = strtoupper(trim($datos['plan_solicitado'] ?? 'MENSUAL'));
            $tarifa = (new Tarifa())->obtenerTarifaVigente($plan);
            if (!$tarifa) throw new \InvalidArgumentException('El plan seleccionado no está disponible.');
            $idTarifa = (int)$tarifa['id_tarifa'];
        }

        $sql = "INSERT INTO solicitudes (
                    id_municipio, id_categoria, id_tarifa, nombre_establecimiento, 
                    plan_solicitado, metodo_pago, nombre_solicitante, telefono_contacto, email_contacto, 
                    direccion, descripcion, horarios, comprobante_archivo, 
                    numero_comprobante, monto_declarado, estado
                ) VALUES (
                    :id_municipio, :id_categoria, :id_tarifa, :nombre_establecimiento,
                    :plan_solicitado, :metodo_pago, :nombre_solicitante, :telefono_contacto, :email_contacto,
                    :direccion, :descripcion, :horarios, :archivo,
                    :comprobante, :monto, 'PENDIENTE'
                )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_municipio'          => (int)($datos['id_municipio'] ?? 1),
            ':id_categoria'          => (int)$datos['id_categoria'],
            ':id_tarifa'             => $idTarifa,
            ':nombre_establecimiento' => $datos['nombre_establecimiento'],
            ':plan_solicitado'       => $datos['plan_solicitado'] ?? 'MENSUAL',
            ':metodo_pago'           => $datos['metodo_pago'] ?? 'QR / Transferencia',
            ':nombre_solicitante'    => $datos['nombre_solicitante'],
            ':telefono_contacto'     => $datos['telefono_contacto'],
            ':email_contacto'        => $datos['email_contacto'] ?? null,
            ':direccion'             => $datos['direccion'],
            ':descripcion'           => $datos['descripcion'],
            ':horarios'              => $datos['horarios'] ?? null,
            ':archivo'               => $datos['comprobante_archivo'] ?? null,
            ':comprobante'           => $datos['numero_comprobante'] ?? null,
            ':monto'                 => $datos['monto_declarado']
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function listar(?string $estado = null): array {
        $sql = "SELECT s.*, c.nombre AS categoria, c.icono AS categoria_icono,
                       m.nombre AS municipio, m.provincia AS municipio_provincia,
                       l.id_lugar AS id_lugar_creado, l.slug AS slug_lugar_creado,
                       cn.id_cuenta AS id_cuenta_creada, cn.usuario AS usuario_asignado
                FROM solicitudes s
                INNER JOIN categorias c ON s.id_categoria = c.id_categoria
                LEFT JOIN municipios m ON s.id_municipio = m.id_municipio
                LEFT JOIN lugares l ON l.id_solicitud_origen = s.id_solicitud
                LEFT JOIN cuentas_negocio cn ON cn.id_lugar = l.id_lugar";

        $params = [];
        if (!empty($estado) && in_array($estado, ['PENDIENTE', 'ACEPTADA', 'RECHAZADA'])) {
            $sql .= " WHERE s.estado = :estado";
            $params[':estado'] = $estado;
        }

        $sql .= " ORDER BY s.id_solicitud DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id): ?array {
        $sql = "SELECT s.*, c.nombre AS categoria, 
                       m.nombre AS municipio, m.provincia AS municipio_provincia,
                       l.id_lugar AS id_lugar_creado
                FROM solicitudes s
                INNER JOIN categorias c ON s.id_categoria = c.id_categoria
                LEFT JOIN municipios m ON s.id_municipio = m.id_municipio
                LEFT JOIN lugares l ON l.id_solicitud_origen = s.id_solicitud
                WHERE s.id_solicitud = :id LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public function cambiarEstado(int $idSolicitud, string $nuevoEstado, ?int $idAdmin, ?string $observaciones = null): bool {
        if (!in_array($nuevoEstado, ['PENDIENTE', 'ACEPTADA', 'RECHAZADA'])) {
            return false;
        }

        $sql = "UPDATE solicitudes SET
                    estado = :estado,
                    id_administrador_revision = :id_admin,
                    observaciones_admin = :observaciones,
                    fecha_revision = NOW()
                WHERE id_solicitud = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':estado'        => $nuevoEstado,
            ':id_admin'      => $idAdmin,
            ':observaciones' => $observaciones,
            ':id'            => $idSolicitud
        ]);
    }

    public function yaFueConvertida(int $idSolicitud): bool {
        $sql = "SELECT COUNT(*) FROM lugares WHERE id_solicitud_origen = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idSolicitud]);
        return ((int)$stmt->fetchColumn()) > 0;
    }
}
