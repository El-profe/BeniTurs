<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Lugar;
use App\Models\Categoria;
use App\Models\Municipio;
use App\Models\Publicacion;
use App\Models\Fotografia;
use App\Models\CuentaNegocio;
use App\Models\Vigencia;
use App\Models\Tarifa;
use App\Services\ImagenService;
use App\Services\SolicitudService;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;

class LugarController extends Controller {
    private Lugar $lugarModel;
    private Categoria $categoriaModel;
    private Municipio $municipioModel;
    private Publicacion $publicacionModel;
    private Fotografia $fotografiaModel;

    public function __construct() {
        parent::__construct();
        AuthMiddleware::autenticar();
        $this->lugarModel = new Lugar();
        $this->categoriaModel = new Categoria();
        $this->municipioModel = new Municipio();
        $this->publicacionModel = new Publicacion();
        $this->fotografiaModel = new Fotografia();
    }

    public function index(): void {
        $lugares = $this->lugarModel->listarParaAdmin();
        $mensaje = $_SESSION['admin_flash'] ?? null;
        unset($_SESSION['admin_flash']);
        $credencialesGeneradas = $_SESSION['credenciales_generadas'] ?? null;
        unset($_SESSION['credenciales_generadas']);

        $this->render('admin/lugares/index', [
            'titulo'                => 'Gestión de Lugares y Negocios',
            'lugares'               => $lugares,
            'mensaje'               => $mensaje,
            'credencialesGeneradas' => $credencialesGeneradas,
            'csrfToken'             => CsrfMiddleware::obtenerToken()
        ], 'admin');
    }

    public function crear(): void {
        $categorias = $this->categoriaModel->listarTodasActivas();
        $municipios = $this->municipioModel->listarActivos();
        $tarifas    = (new Tarifa())->listarPlanesActivos();
        $tarifaVigente = (new Tarifa())->obtenerTarifaVigente();
        $error = $_SESSION['admin_error'] ?? null;
        unset($_SESSION['admin_error']);

        $this->render('admin/lugares/crear', [
            'titulo'        => 'Registrar Nueva Ficha',
            'categorias'    => $categorias,
            'municipios'    => $municipios,
            'tarifas'       => $tarifas,
            'tarifaVigente' => $tarifaVigente,
            'error'         => $error,
            'csrfToken'     => CsrfMiddleware::obtenerToken()
        ], 'admin');
    }

    public function guardar(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !CsrfMiddleware::validarToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['admin_error'] = 'Petición no permitida o sesión expirada.';
            header("Location: {$this->config['base_url']}/admin/lugares/crear");
            exit();
        }

        $nombre = trim($_POST['nombre'] ?? '');
        $idCategoria = (int)($_POST['id_categoria'] ?? 0);
        $tipoLugar = in_array($_POST['tipo_lugar'] ?? '', ['PUBLICO', 'COMERCIAL']) ? $_POST['tipo_lugar'] : 'COMERCIAL';
        $descripcion = trim($_POST['descripcion'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '');

        if (empty($nombre) || $idCategoria === 0 || empty($descripcion) || empty($direccion)) {
            $_SESSION['admin_error'] = 'Por favor complete los campos obligatorios (*).';
            header("Location: {$this->config['base_url']}/admin/lugares/crear");
            exit();
        }

        try {
            $idLugar = $this->lugarModel->crear([
                'id_municipio'         => !empty($_POST['id_municipio']) ? (int)$_POST['id_municipio'] : 1,
                'nombre'               => $nombre,
                'id_categoria'         => $idCategoria,
                'tipo_lugar'           => $tipoLugar,
                'descripcion'          => $descripcion,
                'direccion'            => $direccion,
                'referencia_ubicacion' => trim($_POST['referencia_ubicacion'] ?? ''),
                'coordenadas_gps'      => trim($_POST['coordenadas_gps'] ?? ''),
                'telefono_contacto'    => trim($_POST['telefono_contacto'] ?? ''),
                'whatsapp_contacto'    => trim($_POST['whatsapp_contacto'] ?? ''),
                'email_contacto'       => trim($_POST['email_contacto'] ?? ''),
                'horario_atencion'     => trim($_POST['horario_atencion'] ?? '')
            ]);

            // Procesar hasta seis fotografías y respetar la portada elegida en el formulario.
            $archivos = $_FILES['fotografias'] ?? null;
            $portada = filter_var($_POST['foto_portada'] ?? 0, FILTER_VALIDATE_INT);
            $portada = $portada === false || $portada < 0 ? 0 : $portada;
            if (is_array($archivos['tmp_name'] ?? null)) {
                $totalFotos = min(count($archivos['tmp_name']), 6);
                $hayPortada = false;
                for ($indice = 0; $indice < $totalFotos; $indice++) {
                    if (($archivos['error'][$indice] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) continue;
                    $foto = [
                        'name' => $archivos['name'][$indice] ?? '',
                        'type' => $archivos['type'][$indice] ?? '',
                        'tmp_name' => $archivos['tmp_name'][$indice] ?? '',
                        'error' => $archivos['error'][$indice] ?? UPLOAD_ERR_NO_FILE,
                        'size' => $archivos['size'][$indice] ?? 0,
                    ];
                    $fotoSubida = ImagenService::subir($foto);
                    if ($fotoSubida) {
                        $esPortada = !$hayPortada && ($indice === $portada || $portada >= $totalFotos);
                        $this->fotografiaModel->registrar($idLugar, $fotoSubida, $esPortada ? 1 : 0);
                        $hayPortada = $hayPortada || $esPortada;
                    }
                }
            }

            $this->publicacionModel->crear($idLugar, 1, 1, (int)$_SESSION['admin_id']);

            if ($tipoLugar === 'COMERCIAL' && !empty($_POST['crear_cuenta'])) {
                $codigoPlan = trim($_POST['codigo_plan'] ?? 'MENSUAL');
                $metodoPago = trim($_POST['metodo_pago'] ?? 'EFECTIVO');
                $numRecibo = trim($_POST['numero_comprobante'] ?? '');
                $meses = filter_var($_POST['meses_duracion'] ?? null, FILTER_VALIDATE_INT);
                $monto = (float)($_POST['monto'] ?? 0);
                $idTarifa = filter_var($_POST['id_tarifa'] ?? null, FILTER_VALIDATE_INT);
                $tipoPlan = trim($_POST['tipo_plan_modalidad'] ?? 'ESTANDAR');

                // Si viene un monto o meses personalizados, estructurar la nota de auditoría de ahorro
                $observaciones = '';
                $tarifaVigente = (new Tarifa())->obtenerTarifaVigente();
                $tarifaBaseMensual = (float)($tarifaVigente['monto_mensual'] ?? 250.00);
                if ($meses && $meses > 0 && $monto > 0) {
                    $precioRegular = round($meses * $tarifaBaseMensual, 2);
                    $ahorro = max(0, round($precioRegular - $monto, 2));
                    if ($ahorro > 0 || $tipoPlan === 'PERSONALIZADO') {
                        $porcentaje = $precioRegular > 0 ? round(($ahorro / $precioRegular) * 100, 1) : 0;
                        $observaciones = sprintf(
                            "[Alta Inicial - Plan Personalizado: %d mes(es) | Tarifa regular: Bs %.2f | Cobrado: Bs %.2f | Ahorro: Bs %.2f (%.1f%%)]",
                            $meses,
                            $precioRegular,
                            $monto,
                            $ahorro,
                            $porcentaje
                        );
                    }
                }

                $resultado = SolicitudService::crearCuentaComercialDirecta(
                    $idLugar,
                    [
                        'codigo_plan'        => $codigoPlan,
                        'metodo_pago'        => $metodoPago,
                        'numero_comprobante' => $numRecibo,
                        'meses_duracion'     => ($meses && $meses > 0) ? $meses : null,
                        'monto'              => $monto > 0 ? $monto : null,
                        'id_tarifa'          => $idTarifa ?: null,
                        'observaciones'      => $observaciones
                    ],
                    (int)$_SESSION['admin_id']
                );
                $_SESSION['credenciales_generadas'] = $resultado;
                $_SESSION['admin_flash'] = "La ficha '{$nombre}' fue creada y su cuenta comercial aprovisionada exitosamente.";
            } else {
                $_SESSION['admin_flash'] = "La ficha '{$nombre}' fue creada correctamente con su fotografía.";
            }

            header("Location: {$this->config['base_url']}/admin/lugares");
            exit();
        } catch (\Exception $e) {
            $_SESSION['admin_error'] = $e->getMessage();
            header("Location: {$this->config['base_url']}/admin/lugares/crear");
            exit();
        }
    }

    public function editar(): void {
        $id = (int)($_GET['id'] ?? 0);
        $lugar = $this->lugarModel->buscarPorId($id);

        if (!$lugar) {
            header("Location: {$this->config['base_url']}/admin/lugares");
            exit();
        }

        $categorias  = $this->categoriaModel->listarTodasActivas();
        $municipios  = $this->municipioModel->listarActivos();
        $fotografias = $this->fotografiaModel->listarPorLugar($id);
        $cuentaNegocio = (new CuentaNegocio())->buscarPorLugar($id);
        $ultimoVencimiento = (new Vigencia())->obtenerUltimoVencimiento($id);
        $tarifas = (new Tarifa())->listarPlanesActivos();
        $mensaje     = $_SESSION['admin_flash'] ?? null;
        $error       = $_SESSION['admin_error'] ?? null;
        unset($_SESSION['admin_flash'], $_SESSION['admin_error']);

        $this->render('admin/lugares/editar', [
            'titulo'            => 'Editar Ficha: ' . $lugar['nombre'],
            'lugar'             => $lugar,
            'categorias'        => $categorias,
            'municipios'        => $municipios,
            'fotografias'       => $fotografias,
            'cuentaNegocio'     => $cuentaNegocio,
            'ultimoVencimiento' => $ultimoVencimiento,
            'tarifas'           => $tarifas,
            'mensaje'           => $mensaje,
            'error'             => $error,
            'csrfToken'         => CsrfMiddleware::obtenerToken()
        ], 'admin');
    }

    public function actualizar(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !CsrfMiddleware::validarToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['admin_error'] = 'Token inválido o sesión expirada.';
            header("Location: {$this->config['base_url']}/admin/lugares");
            exit();
        }

        $id = (int)($_POST['id_lugar'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $idCategoria = (int)($_POST['id_categoria'] ?? 0);
        $tipoLugar = in_array($_POST['tipo_lugar'] ?? '', ['PUBLICO', 'COMERCIAL']) ? $_POST['tipo_lugar'] : 'COMERCIAL';
        $descripcion = trim($_POST['descripcion'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '');

        if ($id === 0 || empty($nombre) || $idCategoria === 0 || empty($descripcion) || empty($direccion)) {
            $_SESSION['admin_error'] = 'Datos incompletos para actualizar la ficha. Por favor revisa los campos obligatorios.';
            header("Location: {$this->config['base_url']}/admin/lugares/editar?id={$id}");
            exit();
        }

        try {
            $this->lugarModel->actualizar($id, [
                'id_municipio'         => !empty($_POST['id_municipio']) ? (int)$_POST['id_municipio'] : 1,
                'nombre'               => $nombre,
                'id_categoria'         => $idCategoria,
                'tipo_lugar'           => $tipoLugar,
                'descripcion'          => $descripcion,
                'direccion'            => $direccion,
                'referencia_ubicacion' => trim($_POST['referencia_ubicacion'] ?? ''),
                'coordenadas_gps'      => trim($_POST['coordenadas_gps'] ?? ''),
                'telefono_contacto'    => trim($_POST['telefono_contacto'] ?? ''),
                'whatsapp_contacto'    => trim($_POST['whatsapp_contacto'] ?? ''),
                'email_contacto'       => trim($_POST['email_contacto'] ?? ''),
                'horario_atencion'     => trim($_POST['horario_atencion'] ?? '')
            ]);

            // Procesar subida de múltiples fotografías nuevas
            $fotosSubidasCount = 0;
            $tieneFotosPrevias = count($this->fotografiaModel->listarPorLugar($id)) > 0;

            if (!empty($_FILES['fotografias']['name']) && is_array($_FILES['fotografias']['name'])) {
                $totalFotos = count($_FILES['fotografias']['name']);
                for ($indice = 0; $indice < $totalFotos; $indice++) {
                    if (($_FILES['fotografias']['error'][$indice] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                        continue;
                    }
                    $foto = [
                        'name'     => $_FILES['fotografias']['name'][$indice] ?? '',
                        'type'     => $_FILES['fotografias']['type'][$indice] ?? '',
                        'tmp_name' => $_FILES['fotografias']['tmp_name'][$indice] ?? '',
                        'error'    => $_FILES['fotografias']['error'][$indice] ?? UPLOAD_ERR_NO_FILE,
                        'size'     => $_FILES['fotografias']['size'][$indice] ?? 0
                    ];
                    $subida = ImagenService::subir($foto);
                    if ($subida) {
                        $esPrincipal = (!$tieneFotosPrevias && $fotosSubidasCount === 0) ? 1 : 0;
                        $this->fotografiaModel->registrar($id, $subida, $esPrincipal);
                        $fotosSubidasCount++;
                    }
                }
            } elseif (!empty($_FILES['fotografia']['tmp_name'])) {
                $subida = ImagenService::subir($_FILES['fotografia']);
                if ($subida) {
                    $esPrincipal = !$tieneFotosPrevias ? 1 : 0;
                    $this->fotografiaModel->registrar($id, $subida, $esPrincipal);
                    $fotosSubidasCount++;
                }
            }

            // Garantizar que siempre haya al menos una foto principal
            $this->fotografiaModel->asegurarPrincipal($id);

            $msg = "Ficha '{$nombre}' actualizada con éxito.";
            if ($fotosSubidasCount > 0) {
                $msg .= " Se agregaron {$fotosSubidasCount} nueva(s) fotografía(s).";
            }
            $_SESSION['admin_flash'] = $msg;

            $redireccion = !empty($_POST['guardar_y_continuar'])
                ? "{$this->config['base_url']}/admin/lugares/editar?id={$id}"
                : "{$this->config['base_url']}/admin/lugares";

            header("Location: {$redireccion}");
            exit();
        } catch (\Exception $e) {
            $_SESSION['admin_error'] = $e->getMessage();
            header("Location: {$this->config['base_url']}/admin/lugares/editar?id={$id}");
            exit();
        }
    }

    public function eliminarFoto(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !CsrfMiddleware::validarToken($_POST['csrf_token'] ?? '')) {
            $this->responderOredireccionar(false, 'Token CSRF inválido o sesión expirada.', 403);
            return;
        }

        $idFoto = (int)($_POST['id_fotografia'] ?? 0);
        $idLugar = (int)($_POST['id_lugar'] ?? 0);

        if ($idFoto <= 0 || $idLugar <= 0) {
            $this->responderOredireccionar(false, 'Parámetros de fotografía inválidos.', 400);
            return;
        }

        $foto = $this->fotografiaModel->buscarPorId($idFoto);
        if (!$foto || (int)$foto['id_lugar'] !== $idLugar) {
            $this->responderOredireccionar(false, 'Fotografía no encontrada o no pertenece a este lugar.', 404, $idLugar);
            return;
        }

        try {
            if (!empty($foto['nombre_archivo'])) {
                try {
                    ImagenService::eliminarArchivo($foto['nombre_archivo']);
                } catch (\Throwable $ignored) {}
            }
            $this->fotografiaModel->eliminar($idFoto, $idLugar);
            $this->responderOredireccionar(true, 'Fotografía eliminada correctamente de la ficha.', 200, $idLugar);
        } catch (\Throwable $e) {
            error_log('Error al eliminar fotografía admin: ' . $e->getMessage());
            $this->responderOredireccionar(false, 'No se pudo eliminar la fotografía del servidor.', 500, $idLugar);
        }
    }

    public function establecerPortada(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !CsrfMiddleware::validarToken($_POST['csrf_token'] ?? '')) {
            $this->responderOredireccionar(false, 'Token CSRF inválido o sesión expirada.', 403);
            return;
        }

        $idFoto = (int)($_POST['id_fotografia'] ?? 0);
        $idLugar = (int)($_POST['id_lugar'] ?? 0);

        if ($idFoto <= 0 || $idLugar <= 0) {
            $this->responderOredireccionar(false, 'Parámetros inválidos.', 400);
            return;
        }

        $foto = $this->fotografiaModel->buscarPorId($idFoto);
        if (!$foto || (int)$foto['id_lugar'] !== $idLugar) {
            $this->responderOredireccionar(false, 'Fotografía no encontrada o no pertenece a este lugar.', 404, $idLugar);
            return;
        }

        try {
            $this->fotografiaModel->establecerPrincipal($idFoto, $idLugar);
            $this->responderOredireccionar(true, 'Fotografía establecida como portada principal de la ficha.', 200, $idLugar);
        } catch (\Throwable $e) {
            error_log('Error al establecer portada admin: ' . $e->getMessage());
            $this->responderOredireccionar(false, 'No se pudo actualizar la portada principal.', 500, $idLugar);
        }
    }

    public function cambiarHabilitacion(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !CsrfMiddleware::validarToken($_POST['csrf_token'] ?? '')) {
            header("Location: {$this->config['base_url']}/admin/lugares");
            exit();
        }

        $id = (int)($_POST['id_lugar'] ?? 0);
        $nuevoEstado = ((int)($_POST['habilitado'] ?? 0)) === 1 ? 1 : 0;
        $motivo = $nuevoEstado === 0 ? trim($_POST['motivo'] ?? 'Deshabilitado por administración') : null;

        $this->publicacionModel->cambiarHabilitacion($id, $nuevoEstado, $motivo);

        $_SESSION['admin_flash'] = $nuevoEstado ? "La ficha ha sido habilitada." : "La ficha ha sido deshabilitada.";
        header("Location: {$this->config['base_url']}/admin/lugares");
        exit();
    }

    private function responderOredireccionar(bool $exito, string $mensaje, int $statusCode = 200, int $idLugar = 0): void {
        $esAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        if ($esAjax || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'))) {
            http_response_code($statusCode);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => $exito, 'message' => $mensaje]);
            exit();
        }

        if ($exito) {
            $_SESSION['admin_flash'] = $mensaje;
        } else {
            $_SESSION['admin_error'] = $mensaje;
        }

        $destino = $idLugar > 0 
            ? "{$this->config['base_url']}/admin/lugares/editar?id={$idLugar}#seccion-galeria" 
            : "{$this->config['base_url']}/admin/lugares";
        header("Location: {$destino}");
        exit();
    }

    public function restablecerClave(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !CsrfMiddleware::validarToken($_POST['csrf_token'] ?? '')) {
            $this->responderJson(false, 'Token CSRF inválido o sesión expirada.', null, 403);
            return;
        }

        $idLugar = (int)($_POST['id_lugar'] ?? 0);
        if ($idLugar <= 0) {
            $this->responderJson(false, 'Identificador de lugar inválido.', null, 400);
            return;
        }

        try {
            $resultado = SolicitudService::restablecerClaveComercio($idLugar, (int)$_SESSION['admin_id']);
            $this->responderJson(true, 'Contraseña restablecida correctamente.', $resultado, 200);
        } catch (\Throwable $e) {
            $this->responderJson(false, $e->getMessage(), null, 400);
        }
    }

    public function crearCuenta(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !CsrfMiddleware::validarToken($_POST['csrf_token'] ?? '')) {
            $this->responderJson(false, 'Token CSRF inválido o sesión expirada.', null, 403);
            return;
        }

        $idLugar = (int)($_POST['id_lugar'] ?? 0);
        if ($idLugar <= 0) {
            $this->responderJson(false, 'Identificador de lugar inválido.', null, 400);
            return;
        }

        $codigoPlan = trim($_POST['codigo_plan'] ?? 'MENSUAL');
        $metodoPago = trim($_POST['metodo_pago'] ?? 'EFECTIVO');
        $numRecibo = trim($_POST['numero_comprobante'] ?? '');

        try {
            $resultado = SolicitudService::crearCuentaComercialDirecta(
                $idLugar,
                [
                    'codigo_plan'        => $codigoPlan,
                    'metodo_pago'        => $metodoPago,
                    'numero_comprobante' => $numRecibo,
                ],
                (int)$_SESSION['admin_id']
            );
            $this->responderJson(true, 'Cuenta comercial aprovisionada exitosamente.', $resultado, 200);
        } catch (\Throwable $e) {
            $this->responderJson(false, $e->getMessage(), null, 400);
        }
    }

    private function responderJson(bool $exito, string $mensaje, ?array $datos = null, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => $exito, 'message' => $mensaje, 'data' => $datos]);
        exit();
    }
}

