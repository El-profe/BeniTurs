<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Models/Lugar.php';
require_once __DIR__ . '/../app/Models/Categoria.php';
require_once __DIR__ . '/../app/Models/Municipio.php';
require_once __DIR__ . '/../app/Models/Publicacion.php';
require_once __DIR__ . '/../app/Models/Fotografia.php';
require_once __DIR__ . '/../app/Models/CuentaNegocio.php';
require_once __DIR__ . '/../app/Models/Tarifa.php';
require_once __DIR__ . '/../app/Models/Vigencia.php';
require_once __DIR__ . '/../app/Models/Solicitud.php';
require_once __DIR__ . '/../app/Services/SolicitudService.php';
require_once __DIR__ . '/../app/Services/SolicitudEntradaService.php';
require_once __DIR__ . '/../app/Services/CredencialService.php';
require_once __DIR__ . '/../app/Services/VigenciaService.php';
require_once __DIR__ . '/../app/Services/TelegramService.php';

use App\Core\Database;
use App\Models\Lugar;
use App\Models\CuentaNegocio;
use App\Models\Tarifa;
use App\Models\Vigencia;
use App\Models\Solicitud;
use App\Services\SolicitudService;
use App\Services\SolicitudEntradaService;

$db = Database::getConnection();

function testAssert(bool $cond, string $msg): void {
    if (!$cond) {
        echo "❌ FALLÓ: $msg\n";
        exit(1);
    }
    echo "✅ OK: $msg\n";
}

echo "=== INICIANDO PRUEBAS DE LOS 4 NIVELES DE PAGO EN EFECTIVO Y CREDENCIALES ===\n";

// 1. Obtener un admin válido para autorizar operaciones
$stmt = $db->query('SELECT id_administrador FROM administradores LIMIT 1');
$idAdmin = (int)($stmt->fetchColumn() ?: 1);

// 2. Probar Nivel 3: Alta Comercial Directa
echo "\n--- Probando Nivel 3: Alta Comercial Directa ---\n";
$lugarModel = new Lugar();
$idLugar = $lugarModel->crear([
    'id_municipio'         => 1,
    'nombre'               => 'Restaurante Test Efectivo ' . uniqid(),
    'id_categoria'         => 1,
    'tipo_lugar'           => 'COMERCIAL',
    'descripcion'          => 'Lugar de prueba para alta directa con cobro en mano.',
    'direccion'            => 'Av. Bolívar Nº 100',
    'referencia_ubicacion' => 'Frente a la plaza',
    'coordenadas_gps'      => '-14.8333, -64.9000',
    'telefono_contacto'    => '76891234',
    'whatsapp_contacto'    => '76891234',
    'email_contacto'       => 'test_comercio_' . uniqid() . '@ejemplo.bo',
    'horario_atencion'     => '08:00 - 20:00'
]);

testAssert($idLugar > 0, "Lugar comercial creado con ID #$idLugar");

$datosAprovisionamiento = [
    'codigo_plan'        => 'MENSUAL',
    'metodo_pago'        => 'EFECTIVO',
    'numero_comprobante' => 'REC-EFEC-' . date('Ymd-His'),
];

$resDirecta = SolicitudService::crearCuentaComercialDirecta($idLugar, $datosAprovisionamiento, $idAdmin);

testAssert(!empty($resDirecta['usuario']), "Usuario comercial generado: {$resDirecta['usuario']}");
testAssert(!empty($resDirecta['clave']), "Clave en texto plano generada: {$resDirecta['clave']}");
testAssert(strlen($resDirecta['clave']) === 8, "Longitud de clave es de 8 caracteres");
testAssert(!empty($resDirecta['whatsapp_url']), "URL de WhatsApp generada: {$resDirecta['whatsapp_url']}");
testAssert($resDirecta['metodo_pago'] === 'EFECTIVO', "Método de pago registrado como EFECTIVO");
testAssert(!empty($resDirecta['fecha_inicio']) && !empty($resDirecta['fecha_vencimiento']), "Periodo de vigencia calculado correctamente");

// Verificar en Base de Datos
$cuentaModel = new CuentaNegocio();
$cuentaBD = $cuentaModel->buscarPorLugar($idLugar);
testAssert($cuentaBD !== null, "Cuenta encontrada en BD con buscarPorLugar");
testAssert($cuentaBD['usuario'] === $resDirecta['usuario'], "Usuario en BD coincide");
testAssert(password_verify($resDirecta['clave'], $cuentaBD['password_hash']), "password_verify confirma el hash con la clave retornada");

// Verificar pago en BD
$stmtPago = $db->prepare('SELECT * FROM pagos WHERE id_pago = ?');
$stmtPago->execute([$resDirecta['id_pago']]);
$pagoBD = $stmtPago->fetch(\PDO::FETCH_ASSOC);
testAssert($pagoBD !== false, "Pago confirmado existe en BD");
testAssert($pagoBD['metodo_pago'] === 'EFECTIVO', "Pago guardó método_pago = EFECTIVO");
testAssert($pagoBD['estado'] === 'CONFIRMADO', "Pago quedó CONFIRMADO");

// 3. Probar Nivel 4: Restablecer Clave desde admin/lugares/editar
echo "\n--- Probando Nivel 4: Restablecer Clave Comercial ---\n";
$resReset = SolicitudService::restablecerClaveComercio($idLugar, $idAdmin);

testAssert(!empty($resReset['clave']), "Nueva clave generada: {$resReset['clave']}");
testAssert($resReset['clave'] !== $resDirecta['clave'], "Nueva clave es diferente a la clave anterior");
testAssert(!empty($resReset['whatsapp_url']), "URL de WhatsApp de restablecimiento generada");
testAssert(str_contains($resReset['mensaje_texto'], $resReset['clave']), "Mensaje de WhatsApp contiene la nueva clave");

$cuentaActualizada = $cuentaModel->buscarPorLugar($idLugar);
testAssert(password_verify($resReset['clave'], $cuentaActualizada['password_hash']), "Hash en BD actualizado para la nueva clave");
testAssert(!password_verify($resDirecta['clave'], $cuentaActualizada['password_hash']), "Clave antigua ya no es válida");

// 4. Probar Nivel 2: Solicitud Pública con Pago en Efectivo sin exigir comprobante
echo "\n--- Probando Nivel 2: Solicitud Pública con Pago en Efectivo ---\n";
$datosSolicitud = [
    'nombre_establecimiento' => 'Café Efectivo ' . uniqid(),
    'nombre_solicitante'     => 'Juan Pérez',
    'id_municipio'           => 1,
    'id_categoria'           => 1,
    'telefono_contacto'      => '71234567',
    'email_contacto'         => 'juan_' . uniqid() . '@cafetest.bo',
    'direccion'              => 'Calle Sucre 45',
    'referencia_ubicacion'   => 'Frente a la Catedral',
    'coordenadas_gps'        => '-14.8333, -64.9000',
    'descripcion'            => 'Cafetería tradicional beniana.',
    'plan_solicitado'        => 'MENSUAL',
    'monto_declarado'        => 50.00,
    'metodo_pago'            => 'EFECTIVO',
    'numero_comprobante'     => 'EFECTIVO-EN-OFICINA'
];

// No enviamos archivo de comprobante ya que es efectivo
$idSol = SolicitudEntradaService::recibir($datosSolicitud, null);

testAssert($idSol > 0, "Solicitud en efectivo procesada exitosamente sin requerir comprobante digital (ID: #$idSol)");

$stmtSol = $db->prepare('SELECT * FROM solicitudes WHERE id_solicitud = ?');
$stmtSol->execute([$idSol]);
$solBD = $stmtSol->fetch(\PDO::FETCH_ASSOC);

testAssert($solBD !== false, "Solicitud guardada en BD");
testAssert($solBD['metodo_pago'] === 'EFECTIVO', "solicitudes.metodo_pago es EFECTIVO");
testAssert($solBD['comprobante_archivo'] === null, "comprobante_archivo es NULL");

// Probar Aprovisionamiento Nivel 1 & 2 desde Solicitud en Efectivo
echo "\n--- Probando Nivel 1: Aprovisionamiento con Modal de Credenciales y Recibo ---\n";
$resAprov = SolicitudService::aprovisionarNegocioCompleto(
    $idSol,
    $idAdmin,
    'EFECTIVO',
    'REC-OFICINA-9988'
);

testAssert(!empty($resAprov['usuario']), "Usuario aprovisionado desde solicitud: {$resAprov['usuario']}");
testAssert(!empty($resAprov['clave']), "Clave aprovisionada retornada en texto plano para el modal");
testAssert(strtoupper($resAprov['metodo_pago']) === 'EFECTIVO', "Método de pago registrado como Efectivo");
testAssert(!empty($resAprov['login_url']), "URL de portal provista");
testAssert(!empty($resAprov['whatsapp_url']), "Enlace de WhatsApp provisto");

echo "\n🎉 TODOS LOS CASOS DE PRUEBA DE LOS 4 NIVELES PASARON SATISFACTORIAMENTE!\n";
