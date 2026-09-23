<?php
namespace App\Services;

use App\Core\Database;
use App\Models\Lugar;
use App\Models\Publicacion;
use App\Models\Tarifa;
use InvalidArgumentException;
use PDOException;
use RuntimeException;
use Throwable;

class RegistroNegocioService {
    public static function registrar(array $entrada, array $archivo): array {
        $datos = SolicitudEntradaService::validarDatos($entrada);
        $db = Database::getConnection();
        if (!Database::beginTransaction()) throw new RuntimeException('Ya hay una transacción en curso.');
        $nombreArchivo = null;
        try {
            $usuario = $db->prepare('SELECT id_cuenta FROM cuentas_negocio WHERE usuario = ?');
            $usuario->execute([$datos['usuario_solicitado']]);
            if ($usuario->fetchColumn()) throw new InvalidArgumentException('Ese usuario ya está registrado. Elige otro o inicia sesión.');
            $tarifa = (new Tarifa())->obtenerTarifaVigente();
            if (!$tarifa) throw new RuntimeException('No hay una tarifa vigente.');
            $nombreArchivo = ComprobanteService::subir($archivo);
            $idLugar = (new Lugar())->crear([
                'id_categoria'=>$datos['id_categoria'], 'nombre'=>$datos['nombre_establecimiento'],
                'descripcion'=>$datos['descripcion'], 'direccion'=>$datos['direccion'],
                'telefono_contacto'=>$datos['telefono_contacto'], 'whatsapp_contacto'=>$datos['telefono_contacto'],
                'email_contacto'=>$datos['email_contacto'], 'horario_atencion'=>$datos['horarios'],
                'tipo_lugar'=>'COMERCIAL'
            ]);
            (new Publicacion())->crear($idLugar, 1, 1);
            $db->prepare('INSERT INTO cuentas_negocio (id_lugar,usuario,email,password_hash) VALUES (?,?,?,?)')
                ->execute([$idLugar,$datos['usuario_solicitado'],$datos['email_contacto'],$datos['password_hash_solicitado']]);
            $idCuenta = (int)$db->lastInsertId();
            $meses = $datos['plan_solicitado'] === 'ANUAL' ? 12 : 1;
            $db->prepare("INSERT INTO pagos (id_lugar,id_tarifa,monto,meses_duracion,fecha_pago_declarada,
                numero_comprobante,comprobante_archivo,estado,es_registro_inicial)
                VALUES (?,?,?,?,?,?,?,'PENDIENTE',1)")
                ->execute([$idLugar,$tarifa['id_tarifa'],$meses === 12 ? 2500 : 250,$meses,date('Y-m-d'),
                    $datos['numero_comprobante'],$nombreArchivo]);
            $idPago = (int)$db->lastInsertId();
            $db->commit();
            return ['id_cuenta'=>$idCuenta,'id_lugar'=>$idLugar,'id_pago'=>$idPago,
                'usuario'=>$datos['usuario_solicitado'],'nombre_negocio'=>$datos['nombre_establecimiento']];
        } catch (Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            if ($nombreArchivo !== null) {
                try {
                    if (!ComprobanteService::eliminar($nombreArchivo)) error_log('No se pudo limpiar comprobante de registro fallido: '.$nombreArchivo);
                } catch (Throwable $limpieza) { error_log($limpieza->getMessage()); }
            }
            if ($e instanceof PDOException && ($e->errorInfo[1] ?? null) === 1062) {
                throw new InvalidArgumentException('El usuario o la ficha ya fueron registrados. Inicia sesión o elige otro usuario.', 0, $e);
            }
            throw $e;
        }
    }
}
