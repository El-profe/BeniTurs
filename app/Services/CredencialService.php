<?php
namespace App\Services;

use RuntimeException;

class CredencialService {
    public static function generarClave(): string {
        $letras = 'abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
        $alfabeto = $letras . '23456789';
        do {
            $clave = '';
            for ($i = 0; $i < 8; $i++) $clave .= $alfabeto[random_int(0, strlen($alfabeto) - 1)];
        } while (!preg_match('/[a-zA-Z]/', $clave) || !preg_match('/[0-9]/', $clave));
        return $clave;
    }

    private static function llave(): string {
        $config = require dirname(__DIR__, 2) . '/config/comercial.php';
        if ($config['credential_key'] !== '') {
            $key = base64_decode($config['credential_key'], true);
            if ($key === false || strlen($key) !== 32) throw new RuntimeException('Clave de cifrado inválida.');
            return $key;
        }
        $dir = dirname(__DIR__, 2) . '/storage/private';
        if (!is_dir($dir) && !mkdir($dir, 0700, true) && !is_dir($dir)) throw new RuntimeException('No se pudo preparar el cifrado.');
        $file = fopen($dir . '/credenciales.key', 'c+b');
        if (!$file || !flock($file, LOCK_EX)) throw new RuntimeException('No se pudo abrir la clave de cifrado.');
        try {
            $key = stream_get_contents($file);
            if ($key === '') {
                $key = random_bytes(32);
                if (fwrite($file, $key) !== 32 || !fflush($file)) throw new RuntimeException('No se pudo guardar la clave de cifrado.');
                @chmod($dir . '/credenciales.key', 0600);
            }
            if (strlen($key) !== 32) throw new RuntimeException('Clave de cifrado dañada.');
            return $key;
        } finally { flock($file, LOCK_UN); fclose($file); }
    }

    public static function cifrar(string $texto): string {
        $iv = random_bytes(12);
        $cifrado = openssl_encrypt($texto, 'aes-256-gcm', self::llave(), OPENSSL_RAW_DATA, $iv, $tag);
        if ($cifrado === false) throw new RuntimeException('No se pudieron proteger las credenciales.');
        return base64_encode($iv . $tag . $cifrado);
    }

    public static function descifrar(string $texto): string {
        $data = base64_decode($texto, true);
        if ($data === false || strlen($data) < 29) throw new RuntimeException('Credenciales no disponibles.');
        $plano = openssl_decrypt(substr($data, 28), 'aes-256-gcm', self::llave(), OPENSSL_RAW_DATA, substr($data, 0, 12), substr($data, 12, 16));
        if ($plano === false) throw new RuntimeException('No se pudieron recuperar las credenciales.');
        return $plano;
    }
}
