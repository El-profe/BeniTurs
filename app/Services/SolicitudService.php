<?php
namespace App\Services;

use App\Core\Database;
use App\Models\Lugar;
use App\Models\Publicacion;
use App\Models\Solicitud;
use RuntimeException;
use Throwable;

class SolicitudService {
    private static function validarAdmin(?int $idAdmin): void {
        if ($idAdmin === null) return; // Webhook autenticado; no confundir ID de Telegram con ID local.
        $stmt = Database::getConnection()->prepare('SELECT id_administrador FROM administradores WHERE id_administrador = ? AND activo = 1');
        $stmt->execute([$idAdmin]);
        if (!$stmt->fetchColumn()) throw new RuntimeException('Administrador no autorizado.');
    }

    public static function aprovisionarNegocioCompleto(int $idSolicitud, ?int $idAdmin = null, ?string $metodoPago = null, ?string $numeroRecibo = null): array {
        $db = Database::getConnection();
        if (!Database::beginTransaction()) throw new RuntimeException('Ya hay una transacción en curso.');
        try {
            self::validarAdmin($idAdmin);
            $stmt = $db->prepare('SELECT * FROM solicitudes WHERE id_solicitud = ? FOR UPDATE');
            $stmt->execute([$idSolicitud]);
            $s = $stmt->fetch();
            if (!$s) throw new RuntimeException('Solicitud no encontrada.');
            if ($s['estado'] === 'ACEPTADA' && $s['id_cuenta_creada']) {
                $resultado = ['id_lugar'=>(int)$s['id_lugar_creado'], 'id_cuenta'=>(int)$s['id_cuenta_creada'],
                    'ya_procesada'=>true, 'whatsapp_url'=>self::enlaceBienvenida($s)];
                $db->commit();
                return $resultado;
            }
            if ($s['estado'] !== 'PENDIENTE' || (new Solicitud())->yaFueConvertida($idSolicitud)) {
                throw new RuntimeException('La solicitud ya fue procesada o no está pendiente.');
            }
            $metodoFinal = strtoupper(trim($metodoPago ?: ($s['metodo_pago'] ?? '')));
            $esEfectivo = ($metodoFinal === 'EFECTIVO');

            if (!$esEfectivo && empty($s['comprobante_archivo'])) {
                throw new RuntimeException('La solicitud no tiene comprobante de transferencia.');
            }
            if (!empty($s['comprobante_archivo'])) {
                ComprobanteService::validarImagen(ComprobanteService::ruta($s['comprobante_archivo']));
            }

            $categoria = (new \App\Models\Categoria())->buscarPorId((int)$s['id_categoria']);
            if (!$categoria || !(int)$categoria['activo'] || $categoria['tipo_defecto'] !== 'COMERCIAL') {
                throw new RuntimeException('La categoría comercial ya no está disponible.');
            }
            $tarifa = (new \App\Models\Tarifa())->buscarPorId((int)$s['id_tarifa']);
            if ($tarifa && (!(int)$tarifa['activo'] || $tarifa['vigente_desde'] > date('Y-m-d')
                || $tarifa['codigo_plan'] !== $s['plan_solicitado'])) $tarifa = null;
            if (!$tarifa) throw new RuntimeException('No existe una tarifa vigente para registrar el pago.');
            if (!in_array($s['plan_solicitado'], ['MENSUAL','ANUAL'], true)) throw new RuntimeException('Plan inválido.');
            $meses = (int)($tarifa['meses_duracion'] ?? ($s['plan_solicitado'] === 'ANUAL' ? 12 : 1));
            $monto = (float)($tarifa['monto'] ?? ($meses === 12 ? 2500.00 : 250.00));
            if ((float)$s['monto_declarado'] !== $monto) throw new RuntimeException('El monto declarado no corresponde al plan.');
            (new Solicitud())->cambiarEstado($idSolicitud, 'ACEPTADA', $idAdmin, $esEfectivo ? 'Pago en efectivo cobrado; aprovisionamiento completo.' : 'Comprobante verificado; aprovisionamiento completo.');
            $idLugar = (new Lugar())->crear([
                'id_municipio'      => (int)($s['id_municipio'] ?? 1),
                'id_categoria'      => $s['id_categoria'],
                'nombre'            => $s['nombre_establecimiento'],
                'descripcion'       => $s['descripcion'],
                'direccion'         => $s['direccion'],
                'telefono_contacto' => $s['telefono_contacto'],
                'whatsapp_contacto' => $s['telefono_contacto'],
                'email_contacto'    => $s['email_contacto'],
                'horario_atencion'  => $s['horarios'],
                'tipo_lugar'        => 'COMERCIAL'
            ]);
            $db->prepare('UPDATE lugares SET id_solicitud_origen = ? WHERE id_lugar = ?')->execute([$idSolicitud, $idLugar]);
            // Los indicadores de aprobación y habilitación pertenecen a publicaciones en este MVC.
            (new Publicacion())->crear($idLugar, 1, 1, $idAdmin);
            $slug = $db->prepare('SELECT slug FROM lugares WHERE id_lugar = ?');
            $slug->execute([$idLugar]);
            $baseUsuario = substr(str_replace('-', '_', $slug->fetchColumn()), 0, 42) ?: 'negocio';
            $usuario = $baseUsuario . '_' . $idSolicitud . '_' . bin2hex(random_bytes(3));
            $clave = CredencialService::generarClave();
            $db->prepare('INSERT INTO cuentas_negocio (id_lugar,usuario,email,password_hash) VALUES (?,?,?,?)')
                ->execute([$idLugar, $usuario, $s['email_contacto'] ?? '', password_hash($clave, PASSWORD_BCRYPT)]);
            $idCuenta = (int)$db->lastInsertId();
            $hoy = date('Y-m-d');
            $metodoRegistro = $esEfectivo ? 'Efectivo' : 'Transferencia bancaria / QR';
            $numeroComp = $numeroRecibo ?: ($s['numero_comprobante'] ?: ($esEfectivo ? 'RECIBO-EFECTIVO-' . $idSolicitud : 'SIN-NUMERO'));
            $db->prepare("INSERT INTO pagos (id_lugar,id_tarifa,monto,meses_duracion,fecha_pago_declarada,
                numero_comprobante,comprobante_archivo,metodo_pago,estado,id_administrador_confirmacion,fecha_confirmacion,es_registro_inicial)
                VALUES (?,?,?,?,?,?,?,?,'CONFIRMADO',?,NOW(),1)")
                ->execute([$idLugar,$tarifa['id_tarifa'],$monto,$meses,$hoy,$numeroComp,$s['comprobante_archivo'],$metodoRegistro,$idAdmin]);
            $idPago = (int)$db->lastInsertId();
            $periodo = VigenciaService::calcularPeriodo($hoy, null, $meses);
            $idVigencia = (new \App\Models\Vigencia())->registrar($idLugar,$idPago,
                $periodo['fecha_inicio'],$periodo['fecha_vencimiento'],$periodo['tipo_periodo']);
            $telefono = preg_replace('/\D/', '', $s['telefono_contacto']);
            if (strlen($telefono) === 8) $telefono = '591' . $telefono;
            if (!preg_match('/\A591\d{8}\z/D', $telefono)) throw new RuntimeException('Teléfono boliviano inválido.');
            $config = require dirname(__DIR__, 2) . '/config/app.php';
            $texto = "Hola {$s['nombre_solicitante']}, ¡tu negocio {$s['nombre_establecimiento']} ya está activo en BeniTurs! " .
                'Accede a tu portal para gestionar fotos y promociones: ' . rtrim($config['base_url'], '/') .
                "/negocio/login\nUsuario: {$usuario}\nClave: {$clave}";
            $whatsapp = 'https://wa.me/' . $telefono . '?text=' . rawurlencode($texto);
            $cifrado = CredencialService::cifrar($whatsapp);
            $db->prepare('UPDATE solicitudes SET id_lugar_creado=?, id_cuenta_creada=?, credenciales_cifradas=? WHERE id_solicitud=?')
                ->execute([$idLugar,$idCuenta,$cifrado,$idSolicitud]);
            TelegramService::encolar($idSolicitud, 'RESOLUCION');
            $db->commit();
            return [
                'id_lugar'               => $idLugar,
                'id_cuenta'              => $idCuenta,
                'id_pago'                => $idPago,
                'id_vigencia'            => $idVigencia,
                'usuario'                => $usuario,
                'clave'                  => $clave,
                'nombre_establecimiento' => $s['nombre_establecimiento'],
                'nombre_solicitante'     => $s['nombre_solicitante'],
                'telefono'               => $s['telefono_contacto'],
                'metodo_pago'            => $metodoRegistro,
                'numero_comprobante'     => $numeroComp,
                'monto'                  => $monto,
                'plan'                   => $s['plan_solicitado'],
                'mensaje_texto'          => $texto,
                'whatsapp_url'           => $whatsapp,
                'login_url'              => rtrim($config['base_url'], '/') . '/negocio/login',
                'ya_procesada'           => false
            ] + $periodo;
        } catch (Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            throw $e;
        }
    }

    public static function restablecerClaveComercio(int $idLugar, ?int $idAdmin = null): array {
        self::validarAdmin($idAdmin);
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT cn.*, l.nombre, l.telefono_contacto FROM cuentas_negocio cn INNER JOIN lugares l ON l.id_lugar = cn.id_lugar WHERE cn.id_lugar = ? LIMIT 1');
        $stmt->execute([$idLugar]);
        $cuenta = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$cuenta) throw new RuntimeException('El comercio no tiene cuenta de acceso creada.');

        $nuevaClave = CredencialService::generarClave();
        $db->prepare('UPDATE cuentas_negocio SET password_hash = ? WHERE id_cuenta = ?')
            ->execute([password_hash($nuevaClave, PASSWORD_BCRYPT), $cuenta['id_cuenta']]);

        $telefono = preg_replace('/\D/', '', $cuenta['telefono_contacto'] ?: '');
        if (strlen($telefono) === 8) $telefono = '591' . $telefono;

        $config = require dirname(__DIR__, 2) . '/config/app.php';
        $texto = "Hola, se ha generado tu nueva clave de acceso para tu comercio \"{$cuenta['nombre']}\" en BeniTurs:\n" .
            'Accede a tu portal: ' . rtrim($config['base_url'], '/') . "/negocio/login\n" .
            "Usuario: {$cuenta['usuario']}\n" .
            "Nueva Clave: {$nuevaClave}";

        $whatsapp = !empty($telefono) ? 'https://wa.me/' . $telefono . '?text=' . rawurlencode($texto) : null;

        return [
            'id_lugar'               => $idLugar,
            'id_cuenta'              => (int)$cuenta['id_cuenta'],
            'usuario'                => $cuenta['usuario'],
            'clave'                  => $nuevaClave,
            'nombre_establecimiento' => $cuenta['nombre'],
            'telefono'               => $cuenta['telefono_contacto'],
            'mensaje_texto'          => $texto,
            'whatsapp_url'           => $whatsapp,
            'login_url'              => rtrim($config['base_url'], '/') . '/negocio/login',
        ];
    }

    public static function crearCuentaComercialDirecta(int $idLugar, array $datosPago, ?int $idAdmin = null): array {
        self::validarAdmin($idAdmin);
        $db = Database::getConnection();
        if (!Database::beginTransaction()) throw new RuntimeException('Ya hay una transacción en curso.');
        try {
            $stmt = $db->prepare('SELECT * FROM lugares WHERE id_lugar = ?');
            $stmt->execute([$idLugar]);
            $lugar = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$lugar) throw new RuntimeException('Lugar no encontrado.');

            $slug = $lugar['slug'] ?: 'negocio_' . $idLugar;
            $baseUsuario = substr(str_replace('-', '_', $slug), 0, 42) ?: 'negocio';
            $usuario = $baseUsuario . '_' . bin2hex(random_bytes(3));
            $clave = CredencialService::generarClave();

            $db->prepare('INSERT INTO cuentas_negocio (id_lugar,usuario,email,password_hash) VALUES (?,?,?,?)')
                ->execute([$idLugar, $usuario, $lugar['email_contacto'] ?? '', password_hash($clave, PASSWORD_BCRYPT)]);
            $idCuenta = (int)$db->lastInsertId();

            $codigoPlan = strtoupper(trim($datosPago['codigo_plan'] ?? 'MENSUAL'));
            $tarifa = (new \App\Models\Tarifa())->obtenerTarifaVigente($codigoPlan);
            if (!$tarifa) {
                $tarifa = (new \App\Models\Tarifa())->obtenerTarifaVigente();
            }
            if (!$tarifa) throw new RuntimeException('Tarifa no encontrada para el plan.');

            $meses = !empty($datosPago['meses_duracion']) ? (int)$datosPago['meses_duracion'] : (int)$tarifa['meses_duracion'];
            $meses = max(1, min(120, $meses));
            $monto = isset($datosPago['monto']) && (float)$datosPago['monto'] > 0 ? (float)$datosPago['monto'] : (float)$tarifa['monto'];
            $idTarifa = !empty($datosPago['id_tarifa']) ? (int)$datosPago['id_tarifa'] : (int)$tarifa['id_tarifa'];
            $observaciones = trim($datosPago['observaciones'] ?? '');
            $metodo = trim($datosPago['metodo_pago'] ?? 'Efectivo');
            $numRecibo = trim($datosPago['numero_comprobante'] ?? ('RECIBO-DIR-' . $idLugar . '-' . date('Ymd')));
            $hoy = date('Y-m-d');

            $db->prepare("INSERT INTO pagos (id_lugar,id_tarifa,monto,meses_duracion,fecha_pago_declarada,
                numero_comprobante,metodo_pago,estado,id_administrador_confirmacion,fecha_confirmacion,observaciones,es_registro_inicial)
                VALUES (?,?,?,?,?,?,?,'CONFIRMADO',?,NOW(),?,1)")
                ->execute([$idLugar, $idTarifa, $monto, $meses, $hoy, $numRecibo, $metodo, $idAdmin, $observaciones]);
            $idPago = (int)$db->lastInsertId();

            $periodo = VigenciaService::calcularPeriodo($hoy, null, $meses);
            $idVigencia = (new \App\Models\Vigencia())->registrar($idLugar, $idPago,
                $periodo['fecha_inicio'], $periodo['fecha_vencimiento'], $periodo['tipo_periodo']);

            $telefono = preg_replace('/\D/', '', $lugar['telefono_contacto'] ?: '');
            if (strlen($telefono) === 8) $telefono = '591' . $telefono;

            $config = require dirname(__DIR__, 2) . '/config/app.php';
            $texto = "Hola, ¡tu negocio \"{$lugar['nombre']}\" ya está activo en BeniTurs!\n" .
                'Accede a tu portal comercial: ' . rtrim($config['base_url'], '/') . "/negocio/login\n" .
                "Usuario: {$usuario}\n" .
                "Clave: {$clave}\n" .
                "Vigencia: {$periodo['fecha_inicio']} al {$periodo['fecha_vencimiento']} ({$tarifa['nombre']})";

            $whatsapp = !empty($telefono) ? 'https://wa.me/' . $telefono . '?text=' . rawurlencode($texto) : null;

            $db->commit();
            return [
                'id_lugar'               => $idLugar,
                'id_cuenta'              => $idCuenta,
                'id_pago'                => $idPago,
                'id_vigencia'            => $idVigencia,
                'usuario'                => $usuario,
                'clave'                  => $clave,
                'nombre_establecimiento' => $lugar['nombre'],
                'telefono'               => $lugar['telefono_contacto'],
                'metodo_pago'            => $metodo,
                'monto'                  => $monto,
                'plan'                   => $tarifa['nombre'],
                'mensaje_texto'          => $texto,
                'whatsapp_url'           => $whatsapp,
                'login_url'              => rtrim($config['base_url'], '/') . '/negocio/login',
            ] + $periodo;
        } catch (Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            throw $e;
        }
    }

    public static function rechazar(int $idSolicitud, ?int $idAdmin = null, string $observaciones = ''): void {
        $db = Database::getConnection();
        if (!Database::beginTransaction()) throw new RuntimeException('Ya hay una transacción en curso.');
        try {
            self::validarAdmin($idAdmin);
            $stmt = $db->prepare('SELECT estado FROM solicitudes WHERE id_solicitud=? FOR UPDATE');
            $stmt->execute([$idSolicitud]);
            $estado = $stmt->fetchColumn();
            if ($estado === 'RECHAZADA') { $db->commit(); return; }
            if ($estado !== 'PENDIENTE') throw new RuntimeException('La solicitud ya fue procesada o no existe.');
            if (mb_strlen($observaciones) > 2000) throw new RuntimeException('Observaciones demasiado largas.');
            (new Solicitud())->cambiarEstado($idSolicitud, 'RECHAZADA', $idAdmin, $observaciones);
            TelegramService::encolar($idSolicitud, 'RESOLUCION');
            $db->commit();
        } catch (Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            throw $e;
        }
    }

    public static function enlaceBienvenida(array $solicitud): ?string {
        if (empty($solicitud['credenciales_cifradas'])) return null;
        return CredencialService::descifrar($solicitud['credenciales_cifradas']);
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
        throw new RuntimeException('Utilice Aprobar y Aprovisionar después de verificar el comprobante.');
    }
}
