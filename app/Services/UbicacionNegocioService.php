<?php
namespace App\Services;

use App\Core\Database;
use InvalidArgumentException;
use RuntimeException;

class UbicacionNegocioService {
    public static function guardar(int $idCuenta, int $idLugar, array $datos): void {
        foreach (['direccion', 'referencia_ubicacion', 'coordenadas_gps'] as $campo) {
            if (isset($datos[$campo]) && !is_string($datos[$campo])) {
                throw new InvalidArgumentException('Revisa los datos de ubicación.');
            }
        }
        $direccion = trim($datos['direccion'] ?? '');
        $referencia = trim($datos['referencia_ubicacion'] ?? '');
        $mapa = MapaService::desdeCoordenadas($datos['coordenadas_gps'] ?? '');
        if ($direccion === '' || mb_strlen($direccion) > 255) {
            throw new InvalidArgumentException('Escribe una dirección de hasta 255 caracteres.');
        }
        if (mb_strlen($referencia) > 255) {
            throw new InvalidArgumentException('La referencia debe tener hasta 255 caracteres.');
        }
        if (!$mapa) {
            throw new InvalidArgumentException('Marca tu local en el mapa o escribe coordenadas válidas: latitud, longitud.');
        }
        $db = Database::getConnection();
        $cuenta = $db->prepare('SELECT id_cuenta FROM cuentas_negocio WHERE id_cuenta = ? AND id_lugar = ? AND activo = 1');
        $cuenta->execute([$idCuenta, $idLugar]);
        if (!$cuenta->fetchColumn()) throw new RuntimeException('No tienes acceso a este negocio.');

        $stmt = $db->prepare('UPDATE lugares l INNER JOIN cuentas_negocio c ON c.id_lugar = l.id_lugar
            SET l.direccion = ?, l.referencia_ubicacion = ?, l.coordenadas_gps = ?
            WHERE l.id_lugar = ? AND c.id_cuenta = ? AND c.activo = 1');
        $stmt->execute([$direccion, $referencia ?: null, $mapa['coordenadas'], $idLugar, $idCuenta]);
    }
}
