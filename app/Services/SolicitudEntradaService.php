<?php
namespace App\Services;

use App\Models\Categoria;
use App\Models\Solicitud;
use InvalidArgumentException;
use Throwable;

class SolicitudEntradaService {
    public static function validarDatos(array $entrada): array {
        $datos = [];
        $limites = ['nombre_establecimiento'=>150, 'nombre_solicitante'=>120,
            'telefono_contacto'=>30, 'email_contacto'=>120, 'direccion'=>255,
            'descripcion'=>10000, 'horarios'=>150, 'usuario_solicitado'=>60,
            'numero_comprobante'=>100, 'plan_solicitado'=>7];
        foreach ($limites as $campo => $maximo) {
            if (isset($entrada[$campo]) && !is_string($entrada[$campo])) {
                throw new InvalidArgumentException('Formato de campo inválido: ' . $campo);
            }
            $valor = trim($entrada[$campo] ?? '');
            if (!mb_check_encoding($valor, 'UTF-8') || mb_strlen($valor, 'UTF-8') > $maximo || str_contains($valor, "\0")) {
                throw new InvalidArgumentException('Longitud o contenido inválido: ' . $campo);
            }
            $datos[$campo] = $valor;
        }
        foreach (['nombre_establecimiento','nombre_solicitante','telefono_contacto','direccion','descripcion','numero_comprobante'] as $campo) {
            if ($datos[$campo] === '') throw new InvalidArgumentException('Complete todos los campos obligatorios.');
        }
        if (!in_array($datos['plan_solicitado'], ['MENSUAL','ANUAL'], true)) {
            throw new InvalidArgumentException('Seleccione un plan mensual o anual.');
        }
        if (!preg_match('/\A[A-Za-z0-9_.-]{3,60}\z/D', $datos['usuario_solicitado'])) {
            throw new InvalidArgumentException('El usuario debe tener de 3 a 60 letras, números, puntos, guiones o guiones bajos.');
        }
        $password = $entrada['password'] ?? null;
        if (!is_string($password) || strlen($password) < 8 || strlen($password) > 72 || str_contains($password, "\0")) {
            throw new InvalidArgumentException('La contraseña debe tener entre 8 y 72 bytes.');
        }
        if ($datos['email_contacto'] !== '' && !filter_var($datos['email_contacto'], FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('El correo electrónico no es válido.');
        }
        $telefono = preg_replace('/[\s()+-]/', '', $datos['telefono_contacto']);
        if (preg_match('/\A591\d{8}\z/D', $telefono)) $telefono = substr($telefono, 3);
        if (!preg_match('/\A\d{8}\z/D', $telefono)) {
            throw new InvalidArgumentException('Ingrese un teléfono boliviano de 8 dígitos, con prefijo +591 opcional.');
        }
        $datos['telefono_contacto'] = $telefono;
        $id = filter_var($entrada['id_categoria'] ?? null, FILTER_VALIDATE_INT);
        $categoria = $id && $id > 0 ? (new Categoria())->buscarPorId($id) : null;
        if (!$categoria || !(int)$categoria['activo'] || $categoria['tipo_defecto'] !== 'COMERCIAL') {
            throw new InvalidArgumentException('Seleccione una categoría comercial activa.');
        }
        $datos['id_categoria'] = $id;
        $datos['password_hash_solicitado'] = password_hash($password, PASSWORD_BCRYPT);
        return $datos;
    }

    public static function recibir(array $entrada, array $archivo): int {
        $datos = self::validarDatos($entrada);
        $nombre = ComprobanteService::subir($archivo);
        try {
            $datos['comprobante_archivo'] = $nombre;
            return (new Solicitud())->registrar($datos);
        } catch (Throwable $e) {
            if (!ComprobanteService::eliminar($nombre)) error_log('No se pudo retirar un comprobante sin solicitud.');
            throw $e;
        }
    }
}
