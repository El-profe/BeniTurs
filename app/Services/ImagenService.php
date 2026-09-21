<?php
namespace App\Services;

/**
 * Valida formatos, pesos y almacena las imágenes en storage/uploads/lugares/
 */
class ImagenService {

    public static function getDirectorioStorage(): string {
        $dir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'lugares' . DIRECTORY_SEPARATOR;
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return $dir;
    }

    /**
     * Procesa la subida de un archivo físico proveniente de $_FILES
     */
    public static function subir(array $file): ?array {
        if (empty($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        // 1. Validar tamaño (máximo 5 MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            throw new \RuntimeException('La imagen excede el tamaño máximo permitido de 5 MB.');
        }

        // 2. Validar tipo MIME real del archivo
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $mimesPermitidos = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp'
        ];

        if (!array_key_exists($mime, $mimesPermitidos)) {
            throw new \RuntimeException('Formato de imagen no admitido. Use JPG, PNG o WEBP.');
        }

        $extension = $mimesPermitidos[$mime];
        $nombreServidor = 'lugar_' . bin2hex(random_bytes(8)) . '_' . time() . '.' . $extension;
        $destino = self::getDirectorioStorage() . $nombreServidor;

        if (!move_uploaded_file($file['tmp_name'], $destino)) {
            throw new \RuntimeException('No se pudo guardar la fotografía en el servidor.');
        }

        return [
            'nombre_archivo'  => $nombreServidor,
            'nombre_original' => basename($file['name']),
            'mime_type'       => $mime,
            'tamano_bytes'    => (int)$file['size']
        ];
    }
}