<?php
namespace App\Services;

use DateTime;

/**
 * Reglas de negocio para el cálculo de vigencias:
 * - Vigencia de mes calendario desde la fecha de inicio.
 * - Fecha de inicio incluida, fecha de vencimiento excluida.
 * - Manejo de meses cortos (28, 29, 30 días): si el día equivalente no existe, usa el último día.
 * - Renovación anticipada: continúa desde el vencimiento vigente sin perder días.
 * - Reactivación: comienza en la fecha acordada de pago.
 */
class VigenciaService {

    /**
     * Suma meses calendario respetando días límite de fin de mes
     */
    public static function sumarMesesCalendario(string $fechaInicioStr, int $meses = 1): string {
        $fechaInicio = new DateTime($fechaInicioStr);
        $diaOriginal = (int)$fechaInicio->format('d');

        // Mover al primer día del mes objetivo para evitar desbordes de DateTime
        $target = clone $fechaInicio;
        $target->modify("first day of +{$meses} month");
        $diasEnMesTarget = (int)$target->format('t');

        // Si el día equivalente no existe en el mes siguiente, utilizar el último día
        $diaFinal = min($diaOriginal, $diasEnMesTarget);
        $target->setDate((int)$target->format('Y'), (int)$target->format('m'), $diaFinal);

        return $target->format('Y-m-d');
    }

    /**
     * Determina las fechas de inicio y vencimiento según el estado previo del comercio
     */
    public static function calcularPeriodo(string $fechaDeclarada, ?string $ultimoVencimiento, int $meses = 1): array {
        $hoy = date('Y-m-d');

        // Caso 1: Renovación Anticipada (existe vencimiento previo y aún no ha expirado)
        if (!empty($ultimoVencimiento) && $ultimoVencimiento > $hoy) {
            $fechaInicio = $ultimoVencimiento;
            $tipoPeriodo = 'RENOVACION_ANTICIPADA';
        } else {
            // Caso 2: Nuevo periodo o Reactivación posterior al vencimiento
            $fechaInicio = !empty($fechaDeclarada) ? $fechaDeclarada : $hoy;
            $tipoPeriodo = !empty($ultimoVencimiento) ? 'REACTIVACION' : 'NUEVO';
        }

        $fechaVencimiento = self::sumarMesesCalendario($fechaInicio, $meses);

        return [
            'fecha_inicio'      => $fechaInicio,
            'fecha_vencimiento' => $fechaVencimiento,
            'tipo_periodo'      => $tipoPeriodo
        ];
    }
}