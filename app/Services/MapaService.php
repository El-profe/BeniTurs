<?php
namespace App\Services;

class MapaService {
    /** Coordenadas decimales guardadas como "latitud, longitud". */
    public static function desdeCoordenadas(?string $valor): ?array {
        if (!preg_match('/^\s*([+-]?\d+(?:\.\d+)?)\s*,\s*([+-]?\d+(?:\.\d+)?)\s*$/D', $valor ?? '', $partes)) {
            return null;
        }

        $latitud = (float)$partes[1];
        $longitud = (float)$partes[2];
        if (abs($latitud) > 90 || abs($longitud) > 180) {
            return null;
        }

        $coordenadas = $latitud . ',' . $longitud;
        $limites = implode(',', [
            max(-180, $longitud - 0.005),
            max(-90, $latitud - 0.005),
            min(180, $longitud + 0.005),
            min(90, $latitud + 0.005),
        ]);

        return [
            'coordenadas' => $coordenadas,
            'url' => 'https://www.openstreetmap.org/export/embed.html?' . http_build_query([
                'bbox' => $limites,
                'layer' => 'mapnik',
                'marker' => $coordenadas,
            ], '', '&', PHP_QUERY_RFC3986),
        ];
    }
}
