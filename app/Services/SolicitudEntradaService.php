<?php
namespace App\Services;

use App\Models\Categoria;
use App\Models\Solicitud;
use App\Core\Database;
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
        foreach (['nombre_establecimiento','nombre_solicitante','telefono_contacto','direccion','descripcion'] as $campo) {
            if ($datos[$campo] === '') throw new InvalidArgumentException('Complete todos los campos obligatorios.');
        }
        if (!in_array($datos['plan_solicitado'], ['MENSUAL','ANUAL'], true)) {
            throw new InvalidArgumentException('Seleccione un plan mensual o anual.');
        }
        // El precio y las credenciales nunca se aceptan del navegador.
        $datos['monto_declarado'] = $datos['plan_solicitado'] === 'ANUAL' ? 2500.00 : 250.00;
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
        return $datos;
    }

    public static function recibir(array $entrada, array $archivo): int {
        $datos = self::validarDatos($entrada);
        $db = Database::getConnection();
        if ($db->inTransaction()) throw new \RuntimeException('Ya hay una transacción en curso.');
        $nombre = ComprobanteService::subir($archivo);
        try {
            if (!Database::beginTransaction()) throw new \RuntimeException('Ya hay una transacción en curso.');
            $datos['comprobante_archivo'] = $nombre;
            $id = (new Solicitud())->registrar($datos);
            TelegramService::encolar($id, 'NUEVA');
            $db->commit();
            return $id;
        } catch (Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            if (!ComprobanteService::eliminar($nombre)) error_log('No se pudo retirar un comprobante sin solicitud.');
            throw $e;
        }
    }
}
