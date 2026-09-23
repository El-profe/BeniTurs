<?php
namespace App\Services;

/** Compatibilidad con llamadas antiguas: el alta siempre permanece en cuarentena. */
class RegistroNegocioService {
    public static function registrar(array $entrada, array $archivo): array {
        return ['id_solicitud' => SolicitudEntradaService::recibir($entrada, $archivo)];
    }
}