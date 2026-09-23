<?php
namespace App\Services;

use InvalidArgumentException;
use RuntimeException;

class ComprobanteService {
    private static ?string $directorio = null;
    public const MAX_BYTES = 5 * 1024 * 1024;
    private const MIME = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

    public static function directorio(): string {
        return self::$directorio ?? dirname(__DIR__, 2) . '/storage/uploads/comprobantes/';
    }

    public static function ruta(string $nombre): string {
        if (!preg_match('/\Acomprobante_[a-f0-9]{32}\.(jpg|png|webp)\z/D', $nombre)) {
            throw new InvalidArgumentException('Nombre de comprobante inválido.');
        }
        $ruta = self::directorio() . $nombre;
        if (is_link($ruta)) throw new RuntimeException('Archivo de comprobante no permitido.');
        return $ruta;
    }

    public static function validarImagen(string $ruta): string {
        $tamano = is_file($ruta) ? filesize($ruta) : false;
        if (!$tamano || $tamano > self::MAX_BYTES) {
            throw new InvalidArgumentException('El comprobante debe pesar entre 1 byte y 5 MB.');
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($ruta);
        $imagen = @getimagesize($ruta);
        if (!isset(self::MIME[$mime]) || !$imagen || ($imagen['mime'] ?? '') !== $mime) {
            throw new InvalidArgumentException('El comprobante debe ser una imagen JPG, PNG o WEBP válida.');
        }
        return $mime;
    }

    public static function subir(array $archivo): string {
        if (($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException('Adjunte un comprobante JPG, PNG o WEBP de hasta 5 MB.');
        }
        $tmp = $archivo['tmp_name'] ?? null;
        if (!is_string($tmp) || !is_uploaded_file($tmp)) {
            throw new InvalidArgumentException('La subida del comprobante no es válida.');
        }
        $mime = self::validarImagen($tmp);
        $directorio = self::directorio();
        if (!is_dir($directorio) && !mkdir($directorio, 0750, true) && !is_dir($directorio)) {
            throw new RuntimeException('No se pudo crear el directorio de comprobantes.');
        }
        $nombre = 'comprobante_' . bin2hex(random_bytes(16)) . '.' . self::MIME[$mime];
        if (!move_uploaded_file($tmp, self::ruta($nombre))) {
            throw new RuntimeException('No se pudo guardar el comprobante.');
        }
        return $nombre;
    }

    public static function eliminar(string $nombre): bool {
        $ruta = self::ruta($nombre);
        return !is_file($ruta) || unlink($ruta);
    }
}
