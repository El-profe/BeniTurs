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

        $this->render('negocio/dashboard', [
            'titulo'      => 'Panel de Control - ' . $lugar['nombre'],
            'lugar'       => $lugar,
            'promociones' => $promociones,
            'totalFotos'  => $totalFotos,
            'pagoPendiente' => (bool)$pendiente->fetchColumn(),
            'esVisible' => \App\Services\PublicacionService::esFichaVisible($this->idLugar),
            'fechaVencimiento' => $vencimiento
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
}
