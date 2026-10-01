<?php
namespace App\Controllers\Negocio;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Lugar;
use App\Models\Fotografia;
use App\Models\Promocion;
use App\Services\ImagenService;
use App\Middleware\NegocioAuthMiddleware;
use App\Middleware\CsrfMiddleware;

class PanelController extends Controller {
    private Lugar $lugarModel;
    private Fotografia $fotografiaModel;
    private Promocion $promocionModel;
    private int $idLugar;

    public function __construct() {
        parent::__construct();
        NegocioAuthMiddleware::autenticar();
        $this->lugarModel       = new Lugar();
        $this->fotografiaModel  = new Fotografia();
        $this->promocionModel   = new Promocion();
        $this->idLugar          = (int)$_SESSION['negocio_lugar_id'];
    }

    public function dashboard(): void {
        $lugar = $this->lugarModel->buscarPorId($this->idLugar);
        $promociones = $this->promocionModel->listarPorLugar($this->idLugar);
        
        $db = Database::getConnection();
        $stmtFotos = $db->prepare("SELECT COUNT(*) FROM fotografias WHERE id_lugar = :id");
        $stmtFotos->execute([':id' => $this->idLugar]);
        $totalFotos = (int)$stmtFotos->fetchColumn();
        $pendiente = $db->prepare("SELECT COUNT(*) FROM pagos WHERE id_lugar = ? AND estado = 'PENDIENTE'");
        $pendiente->execute([$this->idLugar]);
        $vencimiento = (new \App\Models\Vigencia())->obtenerUltimoVencimiento($this->idLugar);
        $mensaje = $_SESSION['negocio_flash'] ?? null;
        $error   = $_SESSION['negocio_error'] ?? null;
        unset($_SESSION['negocio_flash'], $_SESSION['negocio_error']);

        $hoy = date('Y-m-d');
        $promocionesActivas = array_values(array_filter($promociones, function($p) use ($hoy) {
            return (int)$p['activo'] === 1 && $p['fecha_fin'] >= $hoy;
        }));
        $tienePromoActiva = count($promocionesActivas) > 0;

        // Evaluación de completitud del perfil (Checklist de 5 criterios de 20% cada uno)
        $criteriosSalud = [
            [
                'id' => 'contacto',
                'titulo' => 'Teléfono o WhatsApp de contacto',
                'descripcion' => 'Permite que los turistas te escriban o llamen para consultas y reservas directas.',
                'cumplido' => !empty(trim($lugar['whatsapp_contacto'] ?? '')) || !empty(trim($lugar['telefono_contacto'] ?? '')),
                'enlace' => $this->config['base_url'] . '/negocio/perfil',
                'texto_accion' => 'Añadir teléfono'
            ],
            [
                'id' => 'horario',
                'titulo' => 'Horarios de atención al público',
                'descripcion' => 'Informa a los visitantes tus días y horas de apertura para que te visiten con certeza.',
                'cumplido' => !empty(trim($lugar['horario_atencion'] ?? '')),
                'enlace' => $this->config['base_url'] . '/negocio/perfil',
                'texto_accion' => 'Fijar horarios'
            ],
            [
                'id' => 'ubicacion',
                'titulo' => 'Ubicación GPS fijada en el mapa',
                'descripcion' => 'Permite trazar la ruta en Google Maps y aparecer con el radar de cercanía en Trinidad.',
                'cumplido' => !empty($lugar['latitud']) && !empty($lugar['longitud']),
                'enlace' => $this->config['base_url'] . '/negocio/ubicacion',
                'texto_accion' => 'Fijar en mapa'
            ],
            [
                'id' => 'fotos',
                'titulo' => 'Galería con al menos 3 fotos',
                'descripcion' => "Una ficha con fotos atractivas recibe hasta 4 veces más visitas ({$totalFotos}/3 fotos subidas).",
                'cumplido' => $totalFotos >= 3,
                'enlace' => $this->config['base_url'] . '/negocio/fotos',
                'texto_accion' => 'Subir fotos'
            ],
            [
                'id' => 'promocion',
                'titulo' => 'Al menos una promoción u oferta activa',
                'descripcion' => 'Las ofertas atraen clientes en días clave y te destacan entre los comercios del Beni.',
                'cumplido' => $tienePromoActiva,
                'enlace' => $this->config['base_url'] . '/negocio/promociones',
                'texto_accion' => 'Lanzar promo'
            ]
        ];

        $cumplidosCount = count(array_filter($criteriosSalud, fn($c) => $c['cumplido']));
        $porcentajeSalud = $cumplidosCount * 20;

        // Semáforo de vigencia y cobertura publicitaria
        $diasRestantes = null;
        $estadoVigencia = [
            'badge_clase' => 'bg-secondary',
            'badge_texto' => 'Ficha inactiva',
            'titulo'      => 'Ficha sin cobertura activa',
            'mensaje'     => 'Tu negocio no cuenta con vigencia activa. Renueva para aparecer en la guía turística.',
            'dias'        => 0,
            'progreso'    => 0,
            'color'       => 'secondary'
        ];

        if ($vencimiento) {
            $diasRestantes = (int)(ceil((strtotime($vencimiento) - strtotime($hoy)) / 86400));
            if ($diasRestantes > 15) {
                $estadoVigencia = [
                    'badge_clase' => 'bg-success',
                    'badge_texto' => 'Membresía al día',
                    'titulo'      => '¡Tu negocio está activo y visible para los turistas!',
                    'mensaje'     => "Cuentas con cobertura continua por {$diasRestantes} días más (hasta el " . date('d/m/Y', strtotime($vencimiento)) . ").",
                    'dias'        => $diasRestantes,
                    'progreso'    => min(100, max(15, (int)(($diasRestantes / 30) * 100))),
                    'color'       => 'success'
                ];
            } elseif ($diasRestantes >= 0) {
                $estadoVigencia = [
                    'badge_clase' => 'bg-warning text-dark fw-bold',
                    'badge_texto' => $diasRestantes === 0 ? '¡Vence HOY!' : "Vence en {$diasRestantes}d",
                    'titulo'      => 'Tu membresía comercial vence pronto',
                    'mensaje'     => "Te quedan {$diasRestantes} días de cobertura (vence el " . date('d/m/Y', strtotime($vencimiento)) . "). Renueva con tiempo para no perder visibilidad.",
                    'dias'        => $diasRestantes,
                    'progreso'    => min(100, max(20, (int)(($diasRestantes / 30) * 100))),
                    'color'       => 'warning'
                ];
            } else {
                $diasPasados = abs($diasRestantes);
                $estadoVigencia = [
                    'badge_clase' => 'bg-danger text-white',
                    'badge_texto' => "Vencido hace {$diasPasados}d",
                    'titulo'      => 'Membresía comercial expirada',
                    'mensaje'     => "Tu período venció el " . date('d/m/Y', strtotime($vencimiento)) . ". Tu ficha está pausada en el catálogo. Renueva para reactivarla de inmediato.",
                    'dias'        => $diasRestantes,
                    'progreso'    => 0,
                    'color'       => 'danger'
                ];
            }
        }

        $urlFichaPublica = rtrim($this->config['base_url'], '/') . '/catalogo/detalle?slug=' . urlencode($lugar['slug'] ?? '');

        $this->render('negocio/dashboard', [
            'titulo'             => 'Panel de Control - ' . $lugar['nombre'],
            'lugar'              => $lugar,
            'promociones'        => $promociones,
            'promocionesActivas' => $promocionesActivas,
            'totalFotos'         => $totalFotos,
            'pagoPendiente'      => (bool)$pendiente->fetchColumn(),
            'esVisible'          => \App\Services\PublicacionService::esFichaVisible($this->idLugar),
            'fechaVencimiento'   => $vencimiento,
            'diasRestantes'      => $diasRestantes,
            'criteriosSalud'     => $criteriosSalud,
            'porcentajeSalud'    => $porcentajeSalud,
            'estadoVigencia'     => $estadoVigencia,
            'urlFichaPublica'    => $urlFichaPublica,
            'mensaje'            => $mensaje,
            'error'              => $error
        ], 'negocio');
    }

    public function fotos(): void {
        $lugar = $this->lugarModel->buscarPorId($this->idLugar);
        
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM fotografias WHERE id_lugar = :id ORDER BY es_principal DESC, id_fotografia DESC");
        $stmt->execute([':id' => $this->idLugar]);
        $fotos = $stmt->fetchAll();

        $mensaje = $_SESSION['negocio_flash'] ?? null;
        $error   = $_SESSION['negocio_error'] ?? null;
        unset($_SESSION['negocio_flash'], $_SESSION['negocio_error']);

        $this->render('negocio/fotos', [
            'titulo'    => 'Gestión de Fotos - ' . $lugar['nombre'],
            'lugar'     => $lugar,
            'fotos'     => $fotos,
            'mensaje'   => $mensaje,
            'error'     => $error,
            'csrfToken' => CsrfMiddleware::obtenerToken()
        ], 'negocio');
    }

    public function ubicacion(): void {
        $lugar = $this->lugarModel->buscarPorId($this->idLugar);
        $datosUbicacion = $_SESSION['negocio_ubicacion_datos'] ?? $lugar;
        $mensaje = $_SESSION['negocio_flash'] ?? null;
        $error = $_SESSION['negocio_error'] ?? null;
        unset($_SESSION['negocio_flash'], $_SESSION['negocio_error'], $_SESSION['negocio_ubicacion_datos']);
        $this->render('negocio/ubicacion', [
            'titulo' => 'Ubicación de mi local',
            'lugar' => $lugar,
            'datosUbicacion' => $datosUbicacion,
            'mensaje' => $mensaje,
            'error' => $error,
            'mapaEditable' => true,
            'csrfToken' => CsrfMiddleware::obtenerToken(),
        ], 'negocio');
    }

    public function guardarUbicacion(): void {
        if (!CsrfMiddleware::validarToken(is_string($_POST['csrf_token'] ?? null) ? $_POST['csrf_token'] : '')) {
            $_SESSION['negocio_error'] = 'Tu sesión venció. Intenta guardar nuevamente.';
        } else {
            try {
                \App\Services\UbicacionNegocioService::guardar((int)$_SESSION['negocio_id'], $this->idLugar, $_POST);
                unset($_SESSION['negocio_ubicacion_datos']);
                $_SESSION['negocio_flash'] = 'Ubicación guardada. Este punto aparecerá en el mapa cuando tu ficha esté visible.';
            } catch (\InvalidArgumentException $e) {
                $_SESSION['negocio_error'] = $e->getMessage();
            } catch (\Throwable $e) {
                error_log('Error al guardar ubicación del negocio: ' . $e->getMessage());
                $_SESSION['negocio_error'] = 'No pudimos guardar la ubicación. Intenta nuevamente.';
            }
        }
        if (!empty($_SESSION['negocio_error'])) {
            foreach (['direccion', 'referencia_ubicacion', 'coordenadas_gps'] as $campo) {
                $_SESSION['negocio_ubicacion_datos'][$campo] = is_string($_POST[$campo] ?? null) ? $_POST[$campo] : '';
            }
        }
        header("Location: {$this->config['base_url']}/negocio/ubicacion");
        exit();
    }

    public function subirFoto(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !CsrfMiddleware::validarToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['negocio_error'] = 'Token de seguridad inválido.';
            header("Location: {$this->config['base_url']}/negocio/fotos");
            exit();
        }

        $db = Database::getConnection();
        $archivo = null;
        $db->beginTransaction();
        try {
            $lock = $db->prepare('SELECT id_lugar FROM lugares WHERE id_lugar = ? FOR UPDATE');
            $lock->execute([$this->idLugar]);
            if (!$lock->fetchColumn()) throw new \RuntimeException('El negocio ya no está disponible.');
            if (empty($_FILES['fotografia']['tmp_name'])) {
                throw new \RuntimeException('Por favor seleccione una imagen.');
            }

            $archivo = ImagenService::subir($_FILES['fotografia']);
            if ($archivo) {
                $esPrincipal = isset($_POST['es_principal']) ? 1 : 0;
                $this->fotografiaModel->registrar($this->idLugar, $archivo, $esPrincipal);
                $_SESSION['negocio_flash'] = 'Fotografía guardada en tu galería. Su visibilidad depende de la activación de tu ficha.';
            }
            $db->commit();
        } catch (\Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            if ($archivo) ImagenService::eliminarArchivo($archivo['nombre_archivo']);
            $_SESSION['negocio_error'] = $e->getMessage();
        }

        header("Location: {$this->config['base_url']}/negocio/fotos");
        exit();
    }

    public function eliminarFoto(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !CsrfMiddleware::validarToken($_POST['csrf_token'] ?? '')) {
            header("Location: {$this->config['base_url']}/negocio/fotos");
            exit();
        }

        $idFoto = (int)($_POST['id_fotografia'] ?? 0);
        $db = Database::getConnection();
        
        $stmt = $db->prepare("SELECT * FROM fotografias WHERE id_fotografia = :id AND id_lugar = :id_lugar");
        $stmt->execute([':id' => $idFoto, ':id_lugar' => $this->idLugar]);
        $foto = $stmt->fetch();

        if ($foto) {
            $archivoFisico = ImagenService::getDirectorioStorage() . $foto['nombre_archivo'];
            if (file_exists($archivoFisico)) {
                @unlink($archivoFisico);
            }
            $stmtDel = $db->prepare("DELETE FROM fotografias WHERE id_fotografia = :id");
            $stmtDel->execute([':id' => $idFoto]);
            $_SESSION['negocio_flash'] = 'Fotografía eliminada.';
        }

        header("Location: {$this->config['base_url']}/negocio/fotos");
        exit();
    }

    public function promociones(): void {
        $lugar = $this->lugarModel->buscarPorId($this->idLugar);
        $promociones = $this->promocionModel->listarPorLugar($this->idLugar);

        $mensaje = $_SESSION['negocio_flash'] ?? null;
        $error   = $_SESSION['negocio_error'] ?? null;
        unset($_SESSION['negocio_flash'], $_SESSION['negocio_error']);

        $this->render('negocio/promociones', [
            'titulo'      => 'Promociones y Ofertas - ' . $lugar['nombre'],
            'lugar'       => $lugar,
            'promociones' => $promociones,
            'mensaje'     => $mensaje,
            'error'       => $error,
            'csrfToken'   => CsrfMiddleware::obtenerToken()
        ], 'negocio');
    }

    public function guardarPromocion(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !CsrfMiddleware::validarToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['negocio_error'] = 'Petición no permitida.';
            header("Location: {$this->config['base_url']}/negocio/promociones");
            exit();
        }

        $titulo      = trim($_POST['titulo'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $descuento   = trim($_POST['descuento_texto'] ?? '');
        $inicio      = trim($_POST['fecha_inicio'] ?? '');
        $fin         = trim($_POST['fecha_fin'] ?? '');

        if (empty($titulo) || empty($descripcion) || empty($inicio) || empty($fin)) {
            $_SESSION['negocio_error'] = 'Complete todos los campos requeridos (*).';
            header("Location: {$this->config['base_url']}/negocio/promociones");
            exit();
        }

        $this->promocionModel->crear([
            'id_lugar'        => $this->idLugar,
            'titulo'          => $titulo,
            'descripcion'     => $descripcion,
            'descuento_texto' => $descuento,
            'fecha_inicio'    => $inicio,
            'fecha_fin'       => $fin
        ]);

        $_SESSION['negocio_flash'] = 'Promoción guardada. Se mostrará en sus fechas cuando tu ficha esté activa.';
        header("Location: {$this->config['base_url']}/negocio/promociones");
        exit();
    }

    public function estadoPromocion(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !CsrfMiddleware::validarToken($_POST['csrf_token'] ?? '')) {
            header("Location: {$this->config['base_url']}/negocio/promociones");
            exit();
        }

        $idPromo = (int)($_POST['id_promocion'] ?? 0);
        $activo  = (int)($_POST['activo'] ?? 0);

        $this->promocionModel->cambiarEstado($idPromo, $this->idLugar, $activo);
        $_SESSION['negocio_flash'] = $activo ? 'Promoción activada.' : 'Promoción pausada.';
        header("Location: {$this->config['base_url']}/negocio/promociones");
        exit();
    }

    public function perfil(): void {
        $lugar = $this->lugarModel->buscarPorId($this->idLugar);
        $categoria = (new \App\Models\Categoria())->buscarPorId((int)$lugar['id_categoria']);
        $mensaje = $_SESSION['negocio_flash'] ?? null;
        $error   = $_SESSION['negocio_error'] ?? null;
        unset($_SESSION['negocio_flash'], $_SESSION['negocio_error']);

        $this->render('negocio/perfil', [
            'titulo'    => 'Editar Perfil - ' . $lugar['nombre'],
            'lugar'     => $lugar,
            'categoria' => $categoria,
            'mensaje'   => $mensaje,
            'error'     => $error,
            'csrfToken' => CsrfMiddleware::obtenerToken()
        ], 'negocio');
    }

    public function guardarPerfil(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !CsrfMiddleware::validarToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['negocio_error'] = 'Sesión expirada o token inválido.';
            header("Location: {$this->config['base_url']}/negocio/perfil");
            exit();
        }

        $campos = [
            'descripcion' => 10000,
            'direccion' => 255,
            'referencia_ubicacion' => 255,
            'whatsapp_contacto' => 30,
            'telefono_contacto' => 30,
            'email_contacto' => 120,
            'horario_atencion' => 150,
        ];
        $datos = [];
        foreach ($campos as $campo => $longitudMaxima) {
            $valor = $_POST[$campo] ?? '';
            if (!is_string($valor)) {
                $_SESSION['negocio_error'] = 'Los datos enviados no son válidos.';
                header("Location: {$this->config['base_url']}/negocio/perfil");
                exit();
            }
            $datos[$campo] = trim($valor);
            if (mb_strlen($datos[$campo]) > $longitudMaxima) {
                $_SESSION['negocio_error'] = 'Uno de los campos supera la longitud permitida.';
                header("Location: {$this->config['base_url']}/negocio/perfil");
                exit();
            }
        }

        if ($datos['descripcion'] === '' || $datos['direccion'] === '' || $datos['whatsapp_contacto'] === '') {
            $_SESSION['negocio_error'] = 'Por favor completa los campos obligatorios: Descripción, Dirección y WhatsApp.';
            header("Location: {$this->config['base_url']}/negocio/perfil");
            exit();
        }

        if ($datos['email_contacto'] !== '' && !filter_var($datos['email_contacto'], FILTER_VALIDATE_EMAIL)) {
            $_SESSION['negocio_error'] = 'El correo electrónico ingresado no tiene un formato válido.';
            header("Location: {$this->config['base_url']}/negocio/perfil");
            exit();
        }

        try {
            $this->lugarModel->actualizarPerfilNegocio($this->idLugar, $datos);
            $_SESSION['negocio_flash'] = '¡Datos de tu negocio actualizados correctamente!';
        } catch (\Throwable $e) {
            error_log('Error al actualizar perfil de negocio: ' . $e->getMessage());
            $_SESSION['negocio_error'] = 'No se pudieron guardar los cambios. Intenta nuevamente.';
        }

        header("Location: {$this->config['base_url']}/negocio/perfil");
        exit();
    }

    public function renovar(): void {
        $lugar = $this->lugarModel->buscarPorId($this->idLugar);
        $db = Database::getConnection();
        $pendiente = $db->prepare("SELECT COUNT(*) FROM pagos WHERE id_lugar = ? AND estado = 'PENDIENTE'");
        $pendiente->execute([$this->idLugar]);
        $vencimiento = (new \App\Models\Vigencia())->obtenerUltimoVencimiento($this->idLugar);
        $esVisible = \App\Services\PublicacionService::esFichaVisible($this->idLugar);
        $cobro = require dirname(__DIR__, 3) . '/config/comercial.php';

        $mensaje = $_SESSION['negocio_flash'] ?? null;
        $error   = $_SESSION['negocio_error'] ?? null;
        unset($_SESSION['negocio_flash'], $_SESSION['negocio_error']);

        $this->render('negocio/renovar', [
            'titulo'           => 'Renovación de Membresía - ' . $lugar['nombre'],
            'lugar'            => $lugar,
            'pagoPendiente'    => (bool)$pendiente->fetchColumn(),
            'planes'          => (new \App\Models\Tarifa())->listarPlanesActivos(),
            'esVisible'        => $esVisible,
            'fechaVencimiento' => $vencimiento,
            'cobro'            => $cobro,
            'mensaje'          => $mensaje,
            'error'            => $error,
            'csrfToken'        => CsrfMiddleware::obtenerToken()
        ], 'negocio');
    }

    public function guardarRenovacion(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !CsrfMiddleware::validarToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['negocio_error'] = 'Sesión expirada o token inválido.';
            header("Location: {$this->config['base_url']}/negocio/renovar");
            exit();
        }

        $plan = trim($_POST['plan'] ?? 'MENSUAL');
        if (!in_array($plan, ['MENSUAL', 'ANUAL'], true)) {
            $_SESSION['negocio_error'] = 'Plan seleccionado inválido.';
            header("Location: {$this->config['base_url']}/negocio/renovar");
            exit();
        }

        $numeroComprobante = trim(is_string($_POST['numero_comprobante'] ?? null) ? $_POST['numero_comprobante'] : '');
        if (mb_strlen($numeroComprobante) > 100) {
            $_SESSION['negocio_error'] = 'El número de comprobante es demasiado largo.';
            header("Location: {$this->config['base_url']}/negocio/renovar");
            exit();
        }

        if (empty($_FILES['comprobante']['name'])) {
            $_SESSION['negocio_error'] = 'Debe adjuntar el archivo del comprobante de pago.';
            header("Location: {$this->config['base_url']}/negocio/renovar");
            exit();
        }

        $nombreArchivo = null;
        $db = Database::getConnection();
        try {
            $nombreArchivo = \App\Services\ComprobanteService::subir($_FILES['comprobante']);
            if (!Database::beginTransaction()) {
                throw new \RuntimeException('No se pudo iniciar el registro de renovación.');
            }
            $lock = $db->prepare('SELECT id_lugar FROM lugares WHERE id_lugar = ? FOR UPDATE');
            $lock->execute([$this->idLugar]);
            if (!$lock->fetchColumn()) throw new \RuntimeException('El negocio ya no está disponible.');
            $pendiente = $db->prepare("SELECT id_pago FROM pagos WHERE id_lugar = ? AND estado = 'PENDIENTE' LIMIT 1 FOR UPDATE");
            $pendiente->execute([$this->idLugar]);
            if ($pendiente->fetchColumn()) {
                throw new \RuntimeException('Ya existe un comprobante de renovación pendiente de verificación.');
            }
            $tarifa = (new \App\Models\Tarifa())->obtenerTarifaVigente($plan);
            if (!$tarifa) {
                throw new \RuntimeException('No hay una tarifa vigente configurada.');
            }

            $idPago = (new \App\Models\Pago())->registrar([
                'id_lugar'             => $this->idLugar,
                'id_tarifa'            => $tarifa['id_tarifa'],
                'monto'                => (float)$tarifa['monto'],
                'meses_duracion'       => (int)$tarifa['meses_duracion'],
                'fecha_pago_declarada' => date('Y-m-d'),
                'numero_comprobante'   => $numeroComprobante,
                'comprobante_archivo'  => $nombreArchivo,
                'metodo_pago'          => 'Transferencia bancaria / QR',
                'observaciones'        => 'Renovación de membresía desde portal comercial (' . $plan . ')',
                'es_registro_inicial'  => 0
            ]);
            $db->commit();
            $_SESSION['negocio_flash'] = "¡Comprobante de renovación enviado con éxito! Pago #{$idPago} en revisión por administración.";
            header("Location: {$this->config['base_url']}/negocio/dashboard");
            exit();
        } catch (\Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            if ($nombreArchivo !== null) {
                try { \App\Services\ComprobanteService::eliminar($nombreArchivo); } catch (\Throwable $ignored) {}
            }
            error_log('Error en renovación de negocio: ' . $e->getMessage());
            $_SESSION['negocio_error'] = 'No se pudo registrar la renovación. Intenta nuevamente.';
            header("Location: {$this->config['base_url']}/negocio/renovar");
            exit();
        }
    }
}
