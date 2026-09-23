<?php
namespace App\Services;

use App\Core\Database;
use App\Models\Pago;
use App\Models\Vigencia;
use RuntimeException;

/**
 * Coordina la confirmación manual de pagos y la creación de vigencias dentro de una transacción PDO atómica.
 */
class PagoService {

    /**
     * Confirma un pago pendiente y genera el periodo de vigencia correspondiente (RF-23 al RF-25)
     */
    public static function confirmarPago(int $idPago, int $idAdmin): array {
        $db = Database::getConnection();
        $pagoModel = new Pago();
        $vigenciaModel = new Vigencia();

        $pago = $pagoModel->buscarPorId($idPago);
        if (!$pago) {
            throw new RuntimeException("El pago #{$idPago} no existe.");
        }

        // Regla: No confirmar el mismo pago dos veces
        if ($pago['estado'] === 'CONFIRMADO') {
            throw new RuntimeException("Este pago ya fue confirmado previamente.");
        }

        if ($pago['estado'] === 'ANULADO') {
            throw new RuntimeException("No es posible confirmar un pago anulado.");
        }

        $mesesDuracion = (int)$pago['meses_duracion'];
        if (!in_array($mesesDuracion, [1, 12], true)) {
            throw new RuntimeException('Seleccione la duración del pago antes de confirmarlo.');
        }

        if (!Database::beginTransaction()) throw new RuntimeException('Ya hay una transacción en curso.');
        try {

            $lock = $db->prepare('SELECT id_lugar FROM lugares WHERE id_lugar = ? FOR UPDATE');
            $lock->execute([$pago['id_lugar']]);
            if (!$lock->fetchColumn()) throw new RuntimeException('El negocio ya no existe.');
            $admin = $db->prepare('SELECT id_administrador FROM administradores WHERE id_administrador = ? AND activo = 1');
            $admin->execute([$idAdmin]);
            if (!$admin->fetchColumn()) throw new RuntimeException('Administrador no autorizado.');
            $actual = $db->prepare('SELECT * FROM pagos WHERE id_pago = ? FOR UPDATE');
            $actual->execute([$idPago]);
            $pago = $actual->fetch();
            if (!$pago || $pago['estado'] !== 'PENDIENTE') throw new RuntimeException('El pago ya fue procesado.');
            if (!empty($pago['es_registro_inicial'])) {
                ComprobanteService::validarImagen(ComprobanteService::ruta($pago['comprobante_archivo'] ?? ''));
            }

            // 1. Actualizar estado del pago a CONFIRMADO
            $sqlUpdatePago = "UPDATE pagos SET 
                                estado = 'CONFIRMADO',
                                id_administrador_confirmacion = :id_admin,
                                fecha_confirmacion = NOW()
                              WHERE id_pago = :id AND estado = 'PENDIENTE'";
            
            $stmtUpdate = $db->prepare($sqlUpdatePago);
            $stmtUpdate->execute([
                ':id_admin' => $idAdmin,
                ':id'       => $idPago
            ]);

            if ($stmtUpdate->rowCount() === 0) {
                throw new RuntimeException("Fallo de concurrencia: el pago ya no se encuentra pendiente.");
            }

            // 2. Calcular fechas de cobertura de la mensualidad
            $ultimoVencimiento = $vigenciaModel->obtenerUltimoVencimiento((int)$pago['id_lugar']);
            $periodo = VigenciaService::calcularPeriodo(
                !empty($pago['es_registro_inicial']) ? date('Y-m-d') : $pago['fecha_pago_declarada'],
                $ultimoVencimiento,
                $mesesDuracion
            );

            // 3. Registrar la vigencia vinculada
            $idVigencia = $vigenciaModel->registrar(
                (int)$pago['id_lugar'],
                $idPago,
                $periodo['fecha_inicio'],
                $periodo['fecha_vencimiento'],
                $periodo['tipo_periodo']
            );

            $db->commit();

            return [
                'id_pago'           => $idPago,
                'id_vigencia'       => $idVigencia,
                'fecha_inicio'      => $periodo['fecha_inicio'],
                'fecha_vencimiento' => $periodo['fecha_vencimiento'],
                'tipo_periodo'      => $periodo['tipo_periodo']
            ];

        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }
}
