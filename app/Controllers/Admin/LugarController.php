<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Lugar;
use App\Models\Categoria;
use App\Models\Publicacion;
use App\Models\Fotografia;
use App\Services\ImagenService;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;

class LugarController extends Controller {
    private Lugar $lugarModel;
    private Categoria $categoriaModel;
    private Publicacion $publicacionModel;
    private Fotografia $fotografiaModel;

    public function __construct() {
        parent::__construct();
        AuthMiddleware::autenticar();
        $this->lugarModel = new Lugar();
        $this->categoriaModel = new Categoria();
        $this->publicacionModel = new Publicacion();
        $this->fotografiaModel = new Fotografia();
    }

    public function index(): void {
        $lugares = $this->lugarModel->listarParaAdmin();
        $mensaje = $_SESSION['admin_flash'] ?? null;
        unset($_SESSION['admin_flash']);

        $this->render('admin/lugares/index', [
            'titulo'    => 'Gestión de Lugares y Negocios',
            'lugares'   => $lugares,
            'mensaje'   => $mensaje,
            'csrfToken' => CsrfMiddleware::obtenerToken()
        ], 'admin');
    }

    public function crear(): void {
        $categorias = $this->categoriaModel->listarTodasActivas();
        $error = $_SESSION['admin_error'] ?? null;
        unset($_SESSION['admin_error']);

        $this->render('admin/lugares/crear', [
            'titulo'     => 'Registrar Nueva Ficha',
            'categorias' => $categorias,
            'error'      => $error,
            'csrfToken'  => CsrfMiddleware::obtenerToken()
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

            // Procesar fotografía de referencia si fue subida
            if (!empty($_FILES['fotografia']['tmp_name'])) {
                $fotoSubida = ImagenService::subir($_FILES['fotografia']);
                if ($fotoSubida) {
                    $this->fotografiaModel->registrar($idLugar, $fotoSubida, 1);
                }
            }

            $this->publicacionModel->crear($idLugar, 1, 1, (int)$_SESSION['admin_id']);

            $_SESSION['admin_flash'] = "La ficha '{$nombre}' fue creada correctamente con su fotografía.";
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

        $categorias = $this->categoriaModel->listarTodasActivas();
        $error = $_SESSION['admin_error'] ?? null;
        unset($_SESSION['admin_error']);

        $this->render('admin/lugares/editar', [
            'titulo'     => 'Editar Ficha: ' . $lugar['nombre'],
            'lugar'      => $lugar,
            'categorias' => $categorias,
            'error'      => $error,
            'csrfToken'  => CsrfMiddleware::obtenerToken()
        ], 'admin');
    }

    public function actualizar(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !CsrfMiddleware::validarToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['admin_error'] = 'Token inválido.';
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
            $_SESSION['admin_error'] = 'Datos incompletos para actualizar la ficha.';
            header("Location: {$this->config['base_url']}/admin/lugares/editar?id={$id}");
            exit();
        }

        try {
            $this->lugarModel->actualizar($id, [
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

            // Si se seleccionó una nueva foto, subirla y actualizar la principal
            if (!empty($_FILES['fotografia']['tmp_name'])) {
                $fotoSubida = ImagenService::subir($_FILES['fotografia']);
                if ($fotoSubida) {
                    $this->fotografiaModel->registrar($id, $fotoSubida, 1);
                }
            }

            $_SESSION['admin_flash'] = "Ficha '{$nombre}' actualizada con éxito.";
            header("Location: {$this->config['base_url']}/admin/lugares");
            exit();
        } catch (\Exception $e) {
            $_SESSION['admin_error'] = $e->getMessage();
            header("Location: {$this->config['base_url']}/admin/lugares/editar?id={$id}");
            exit();
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
}