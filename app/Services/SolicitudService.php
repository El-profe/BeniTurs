<?php
namespace App\Services;

use App\Core\Database;
use App\Models\Lugar;
use App\Models\Publicacion;
use App\Models\Solicitud;
use RuntimeException;
use Throwable;

class SolicitudService {
    public static function aprovisionarNegocioCompleto(int $idSolicitud, int $idAdmin): array {
        $db = Database::getConnection();
        if (!Database::beginTransaction()) throw new RuntimeException('Ya hay una transacción en curso.');
        try {
            $admin = $db->prepare('SELECT id_administrador FROM administradores WHERE id_administrador = ? AND activo = 1');
            $admin->execute([$idAdmin]);
            if (!$admin->fetchColumn()) throw new RuntimeException('Administrador no autorizado.');
            $stmt = $db->prepare('SELECT * FROM solicitudes WHERE id_solicitud = ? FOR UPDATE');
            $stmt->execute([$idSolicitud]);
            $s = $stmt->fetch();
            if (!$s || $s['estado'] !== 'PENDIENTE' || (new Solicitud())->yaFueConvertida($idSolicitud)) {
                throw new RuntimeException('La solicitud ya fue procesada o no está pendiente.');
            }
            if (!preg_match('/\A[A-Za-z0-9_.-]{3,60}\z/D', $s['usuario_solicitado']) ||
                (password_get_info($s['password_hash_solicitado'])['algoName'] ?? '') !== 'bcrypt' ||
                empty($s['comprobante_archivo']) || empty($s['numero_comprobante'])) {
                throw new RuntimeException('La solicitud no tiene credenciales y comprobante completos.');
            }
            ComprobanteService::validarImagen(ComprobanteService::ruta($s['comprobante_archivo']));
            $categoria = (new \App\Models\Categoria())->buscarPorId((int)$s['id_categoria']);
            if (!$categoria || !(int)$categoria['activo'] || $categoria['tipo_defecto'] !== 'COMERCIAL') {
                throw new RuntimeException('La categoría comercial ya no está disponible.');
            }
            $ocupado = $db->prepare('SELECT id_cuenta FROM cuentas_negocio WHERE usuario = ?');
            $ocupado->execute([$s['usuario_solicitado']]);
            if ($ocupado->fetchColumn()) throw new RuntimeException('El usuario solicitado ya está ocupado. No se creó ningún registro.');
            $tarifa = (new \App\Models\Tarifa())->obtenerTarifaVigente();
            if (!$tarifa) throw new RuntimeException('No existe una tarifa vigente para registrar el pago.');
            if (!in_array($s['plan_solicitado'], ['MENSUAL','ANUAL'], true)) throw new RuntimeException('Plan inválido.');
            $meses = $s['plan_solicitado'] === 'ANUAL' ? 12 : 1;
            $monto = $meses === 12 ? 2500.00 : 250.00;
            (new Solicitud())->cambiarEstado($idSolicitud, 'ACEPTADA', $idAdmin, 'Comprobante verificado; aprovisionamiento completo.');
            $idLugar = (new Lugar())->crear([
                'id_categoria'=>$s['id_categoria'], 'nombre'=>$s['nombre_establecimiento'],
                'descripcion'=>$s['descripcion'], 'direccion'=>$s['direccion'],
                'telefono_contacto'=>$s['telefono_contacto'], 'whatsapp_contacto'=>$s['telefono_contacto'],
                'email_contacto'=>$s['email_contacto'], 'horario_atencion'=>$s['horarios'],
                'tipo_lugar'=>'COMERCIAL'
            ]);
            $db->prepare('UPDATE lugares SET id_solicitud_origen = ? WHERE id_lugar = ?')->execute([$idSolicitud, $idLugar]);
            (new Publicacion())->crear($idLugar, 1, 1, $idAdmin);
            // Copiar el hash, nunca volver a hashearlo ni almacenar la contraseña original.
            $db->prepare('INSERT INTO cuentas_negocio (id_lugar,usuario,email,password_hash) VALUES (?,?,?,?)')
                ->execute([$idLugar, $s['usuario_solicitado'], $s['email_contacto'] ?? '', $s['password_hash_solicitado']]);
            $idCuenta = (int)$db->lastInsertId();
            $hoy = date('Y-m-d');
            $db->prepare("INSERT INTO pagos (id_lugar,id_tarifa,monto,meses_duracion,fecha_pago_declarada,
                numero_comprobante,comprobante_archivo,estado,id_administrador_confirmacion,fecha_confirmacion)
                VALUES (?,?,?,?,?,?,?,'CONFIRMADO',?,NOW())")
                ->execute([$idLugar,$tarifa['id_tarifa'],$monto,$meses,$hoy,$s['numero_comprobante'],$s['comprobante_archivo'],$idAdmin]);
            $idPago = (int)$db->lastInsertId();
            $periodo = VigenciaService::calcularPeriodo($hoy, null, $meses);
            $idVigencia = (new \App\Models\Vigencia())->registrar($idLugar,$idPago,
                $periodo['fecha_inicio'],$periodo['fecha_vencimiento'],$periodo['tipo_periodo']);
            $db->commit();
            return ['id_lugar'=>$idLugar,'id_cuenta'=>$idCuenta,'id_pago'=>$idPago,'id_vigencia'=>$idVigencia] + $periodo;
        } catch (Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            throw $e;
        }
    }

    public static function enlaceBienvenida(array $solicitud): ?string {
        $telefono = preg_replace('/\D/', '', $solicitud['telefono_contacto']);
        if (preg_match('/\A\d{8}\z/D', $telefono)) $telefono = '591' . $telefono;
        if (!preg_match('/\A591\d{8}\z/D', $telefono)) return null;
        $config = require dirname(__DIR__, 2) . '/config/app.php';
        $mensaje = '¡Bienvenido a BeniTurs! Tu negocio ' . $solicitud['nombre_establecimiento'] .
            ' ha sido activado con el plan ' . $solicitud['plan_solicitado'] .
            '. Ingresa con tu usuario ' . $solicitud['usuario_solicitado'] .
            ' y la contraseña que elegiste: ' . rtrim($config['base_url'], '/') . '/negocio/login';
        return 'https://wa.me/' . $telefono . '?text=' . rawurlencode($mensaje);
    }

    public static function purgarSolicitudesAbandonadas(): array {
        $db = Database::getConnection();
        if (!Database::beginTransaction()) throw new RuntimeException('Ya hay una transacción en curso.');
        try {
            // El bloqueo evita competir con el clic de aprovisionamiento.
            $filas = $db->query("SELECT id_solicitud, comprobante_archivo FROM solicitudes s
                WHERE estado = 'PENDIENTE' AND created_at < DATE_SUB(NOW(), INTERVAL 15 DAY)
                AND NOT EXISTS (SELECT 1 FROM lugares l WHERE l.id_solicitud_origen = s.id_solicitud)
                FOR UPDATE")->fetchAll();
            $eliminar = $db->prepare("DELETE FROM solicitudes WHERE id_solicitud = ? AND estado = 'PENDIENTE'");
            foreach ($filas as $fila) $eliminar->execute([$fila['id_solicitud']]);
            $db->commit();
        } catch (Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            throw $e;
        }
        // Los archivos se retiran tras el commit. Un fallo se reintenta en la siguiente purga.
        $candidatos = array_filter(array_column($filas, 'comprobante_archivo'));
        foreach (glob(ComprobanteService::directorio() . 'comprobante_*') ?: [] as $ruta) {
            if (is_file($ruta) && filemtime($ruta) < time() - 15 * 86400) $candidatos[] = basename($ruta);
        }
        $referencias = $db->prepare('SELECT (SELECT COUNT(*) FROM solicitudes WHERE comprobante_archivo = ?) +
            (SELECT COUNT(*) FROM pagos WHERE comprobante_archivo = ?)');
        $archivos = 0;
        $fallos = 0;
        foreach (array_unique($candidatos) as $nombre) {
            try {
                $referencias->execute([$nombre,$nombre]);
                if ((int)$referencias->fetchColumn() !== 0) continue;
                if (!ComprobanteService::eliminar($nombre)) throw new RuntimeException('No se pudo eliminar el archivo.');
                $archivos++;
            } catch (Throwable $e) {
                $fallos++;
                error_log('Purga de comprobantes: ' . $e->getMessage());
            }
        }
        return ['solicitudes'=>count($filas), 'archivos'=>$archivos, 'fallos_archivos'=>$fallos];
    }

    public static function convertirAFicha(int $idSolicitud, int $idAdmin): int {
        $db = Database::getConnection();
        $db->beginTransaction();
        try {
            $stmt = $db->prepare('SELECT * FROM solicitudes WHERE id_solicitud = ? FOR UPDATE');
            $stmt->execute([$idSolicitud]);
            $solicitud = $stmt->fetch();
            if ($solicitud && !empty($solicitud['usuario_solicitado'])) {
                throw new RuntimeException('Esta solicitud requiere el aprovisionamiento completo.');
            }
            if (!$solicitud || $solicitud['estado'] !== 'ACEPTADA') {
                throw new RuntimeException('La solicitud debe existir y estar aceptada.');
            }
            if ((new Solicitud())->yaFueConvertida($idSolicitud)) {
                throw new RuntimeException('Esta solicitud ya fue convertida en una ficha.');
            }
            $idLugar = (new Lugar())->crear([
                'id_categoria' => (int)$solicitud['id_categoria'],
                'nombre' => $solicitud['nombre_establecimiento'],
                'descripcion' => $solicitud['descripcion'],
                'direccion' => $solicitud['direccion'],
                'referencia_ubicacion' => 'Ingresado mediante solicitud web #' . $idSolicitud,
                'telefono_contacto' => $solicitud['telefono_contacto'],
                'whatsapp_contacto' => $solicitud['telefono_contacto'],
                'email_contacto' => $solicitud['email_contacto'],
                'horario_atencion' => $solicitud['horarios'],
                'tipo_lugar' => 'COMERCIAL'
            ]);
            $db->prepare('UPDATE lugares SET id_solicitud_origen = ? WHERE id_lugar = ?')
                ->execute([$idSolicitud, $idLugar]);
            (new Publicacion())->crear($idLugar, 1, 1, $idAdmin);
            $db->commit();
            return $idLugar;
        } catch (Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            throw $e;
        }
    }
}
