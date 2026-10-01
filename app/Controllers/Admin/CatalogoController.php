<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Categoria;
use App\Models\Municipio;
use App\Models\Tarifa;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;

class CatalogoController extends Controller {
    private Categoria $categoriaModel;
    private Municipio $municipioModel;
    private Tarifa $tarifaModel;

    public function __construct() {
        parent::__construct();
        AuthMiddleware::autenticar();
        $this->categoriaModel = new Categoria();
        $this->municipioModel = new Municipio();
        $this->tarifaModel = new Tarifa();
    }

    /**
     * Muestra el panel unificado de gestión de Catálogos (Categorías, Municipios, Planes).
     */
    public function index(): void {
        $tabActivo = trim($_GET['tab'] ?? 'categorias');
        if (!in_array($tabActivo, ['categorias', 'municipios', 'planes'], true)) {
            $tabActivo = 'categorias';
        }

        $categorias = $this->categoriaModel->listarTodas();
        $municipios = $this->municipioModel->listarTodosConConteo();
        $planes     = $this->tarifaModel->listarTodas();

        $mensaje = $_SESSION['admin_flash'] ?? null;
        $error   = $_SESSION['admin_error'] ?? null;
        unset($_SESSION['admin_flash'], $_SESSION['admin_error']);

        $this->render('admin/catalogos/index', [
            'titulo'      => 'Gestión de Parámetros y Catálogos',
            'tabActivo'   => $tabActivo,
            'categorias'  => $categorias,
            'municipios'  => $municipios,
            'planes'      => $planes,
            'mensaje'     => $mensaje,
            'error'       => $error,
            'csrfToken'   => CsrfMiddleware::obtenerToken()
        ], 'admin');
    }

    // =========================================================================
    // GESTIÓN DE CATEGORÍAS
    // =========================================================================

    public function guardarCategoria(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !CsrfMiddleware::validarToken($_POST['csrf_token'] ?? '')) {
            $this->redirigirConError('Token CSRF inválido o sesión expirada.', 'categorias');
            return;
        }

        $id = (int)($_POST['id_categoria'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $icono = trim($_POST['icono'] ?? 'bi-tag');
        $tipoDefecto = in_array($_POST['tipo_defecto'] ?? '', ['PUBLICO', 'COMERCIAL'], true) ? $_POST['tipo_defecto'] : 'COMERCIAL';
        $activo = isset($_POST['activo']) ? (int)$_POST['activo'] : 1;

        if (empty($nombre)) {
            $this->redirigirConError('El nombre de la categoría es obligatorio.', 'categorias');
            return;
        }

        $slug = trim($_POST['slug'] ?? '');
        if (empty($slug)) {
            $slug = $this->generarSlug($nombre);
        } else {
            $slug = $this->generarSlug($slug);
        }

        // Verificar unicidad de slug
        $existente = $this->categoriaModel->buscarPorSlug($slug);
        if ($existente && (int)$existente['id_categoria'] !== $id) {
            $slug .= '-' . substr(uniqid(), -4);
        }

        $datos = [
            'nombre'       => $nombre,
            'slug'         => $slug,
            'descripcion'  => $descripcion,
            'icono'        => $icono,
            'tipo_defecto' => $tipoDefecto,
            'activo'       => $activo
        ];

        try {
            if ($id > 0) {
                $this->categoriaModel->actualizar($id, $datos);
                $this->redirigirConExito("Categoría '{$nombre}' actualizada con éxito.", 'categorias');
            } else {
                $this->categoriaModel->crear($datos);
                $this->redirigirConExito("Nueva categoría '{$nombre}' añadida al sistema.", 'categorias');
            }
        } catch (\Throwable $e) {
            $this->redirigirConError('Error al guardar la categoría: ' . $e->getMessage(), 'categorias');
        }
    }

    public function cambiarEstadoCategoria(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !CsrfMiddleware::validarToken($_POST['csrf_token'] ?? '')) {
            $this->redirigirConError('Token inválido.', 'categorias');
            return;
        }

        $id = (int)($_POST['id_categoria'] ?? 0);
        $nuevoEstado = ((int)($_POST['activo'] ?? 0)) === 1 ? 1 : 0;

        if ($id <= 0) {
            $this->redirigirConError('Identificador de categoría no válido.', 'categorias');
            return;
        }

        $this->categoriaModel->cambiarEstado($id, $nuevoEstado);
        $txt = $nuevoEstado ? 'activada' : 'desactivada';
        $this->redirigirConExito("Categoría {$txt} correctamente.", 'categorias');
    }

    // =========================================================================
    // GESTIÓN DE MUNICIPIOS
    // =========================================================================

    public function guardarMunicipio(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !CsrfMiddleware::validarToken($_POST['csrf_token'] ?? '')) {
            $this->redirigirConError('Token CSRF inválido o sesión expirada.', 'municipios');
            return;
        }

        $id = (int)($_POST['id_municipio'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $provincia = trim($_POST['provincia'] ?? '');
        $latitud = !empty($_POST['latitud_defecto']) ? trim($_POST['latitud_defecto']) : null;
        $longitud = !empty($_POST['longitud_defecto']) ? trim($_POST['longitud_defecto']) : null;
        $activo = isset($_POST['activo']) ? (int)$_POST['activo'] : 1;

        if (empty($nombre) || empty($provincia)) {
            $this->redirigirConError('El nombre del municipio y la provincia son obligatorios.', 'municipios');
            return;
        }

        $slug = trim($_POST['slug'] ?? '');
        if (empty($slug)) {
            $slug = $this->generarSlug($nombre);
        } else {
            $slug = $this->generarSlug($slug);
        }

        // Verificar unicidad de slug
        $existente = $this->municipioModel->buscarPorSlug($slug);
        if ($existente && (int)$existente['id_municipio'] !== $id) {
            $slug .= '-' . substr(uniqid(), -4);
        }

        $datos = [
            'nombre'           => $nombre,
            'provincia'        => $provincia,
            'slug'             => $slug,
            'latitud_defecto'  => $latitud,
            'longitud_defecto' => $longitud,
            'activo'           => $activo
        ];

        try {
            if ($id > 0) {
                $this->municipioModel->actualizar($id, $datos);
                $this->redirigirConExito("Municipio '{$nombre}' actualizado con éxito.", 'municipios');
            } else {
                $this->municipioModel->crear($datos);
                $this->redirigirConExito("Nuevo municipio '{$nombre}' ({$provincia}) añadido al departamento.", 'municipios');
            }
        } catch (\Throwable $e) {
            $this->redirigirConError('Error al guardar el municipio: ' . $e->getMessage(), 'municipios');
        }
    }

    public function cambiarEstadoMunicipio(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !CsrfMiddleware::validarToken($_POST['csrf_token'] ?? '')) {
            $this->redirigirConError('Token inválido.', 'municipios');
            return;
        }

        $id = (int)($_POST['id_municipio'] ?? 0);
        $nuevoEstado = ((int)($_POST['activo'] ?? 0)) === 1 ? 1 : 0;

        if ($id <= 0) {
            $this->redirigirConError('Identificador de municipio no válido.', 'municipios');
            return;
        }

        $this->municipioModel->cambiarEstado($id, $nuevoEstado);
        $txt = $nuevoEstado ? 'activado' : 'desactivado';
        $this->redirigirConExito("Municipio {$txt} correctamente.", 'municipios');
    }

    // =========================================================================
    // GESTIÓN DE PLANES Y TARIFAS
    // =========================================================================

    public function guardarPlan(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !CsrfMiddleware::validarToken($_POST['csrf_token'] ?? '')) {
            $this->redirigirConError('Token CSRF inválido o sesión expirada.', 'planes');
            return;
        }

        $id = (int)($_POST['id_tarifa'] ?? 0);
        $codigoPlan = strtoupper(preg_replace('/[^A-Z0-9_]/', '', strtoupper(trim($_POST['codigo_plan'] ?? ''))));
        $nombre = trim($_POST['nombre'] ?? '');
        $monto = (float)($_POST['monto'] ?? 0);
        $meses = max(1, (int)($_POST['meses_duracion'] ?? 1));
        $descripcion = trim($_POST['descripcion'] ?? '');
        $vigenteDesde = !empty($_POST['vigente_desde']) ? trim($_POST['vigente_desde']) : date('Y-m-d');
        $activo = isset($_POST['activo']) ? (int)$_POST['activo'] : 1;

        if (empty($codigoPlan) || empty($nombre) || $monto <= 0) {
            $this->redirigirConError('El código de plan, nombre descriptivo y un monto mayor a 0 son obligatorios.', 'planes');
            return;
        }

        // Verificar unicidad de código de plan
        $existente = $this->tarifaModel->buscarPorCodigoPlan($codigoPlan);
        if ($existente && (int)$existente['id_tarifa'] !== $id) {
            $this->redirigirConError("Ya existe un plan registrado con el código '{$codigoPlan}'.", 'planes');
            return;
        }

        $datos = [
            'codigo_plan'    => $codigoPlan,
            'nombre'         => $nombre,
            'monto'          => $monto,
            'meses_duracion' => $meses,
            'descripcion'    => $descripcion,
            'vigente_desde'  => $vigenteDesde,
            'activo'         => $activo
        ];

        try {
            if ($id > 0) {
                $this->tarifaModel->actualizar($id, $datos);
                $this->redirigirConExito("Plan '{$nombre}' ({$codigoPlan}) actualizado con éxito.", 'planes');
            } else {
                $this->tarifaModel->crear($datos);
                $this->redirigirConExito("Nuevo plan comercial '{$nombre}' registrado con éxito.", 'planes');
            }
        } catch (\Throwable $e) {
            $this->redirigirConError('Error al guardar el plan comercial: ' . $e->getMessage(), 'planes');
        }
    }

    public function cambiarEstadoPlan(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !CsrfMiddleware::validarToken($_POST['csrf_token'] ?? '')) {
            $this->redirigirConError('Token inválido.', 'planes');
            return;
        }

        $id = (int)($_POST['id_tarifa'] ?? 0);
        $nuevoEstado = ((int)($_POST['activo'] ?? 0)) === 1 ? 1 : 0;

        if ($id <= 0) {
            $this->redirigirConError('Identificador de plan no válido.', 'planes');
            return;
        }

        $this->tarifaModel->cambiarEstado($id, $nuevoEstado);
        $txt = $nuevoEstado ? 'activado' : 'desactivado';
        $this->redirigirConExito("Plan comercial {$txt} correctamente.", 'planes');
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    private function redirigirConExito(string $mensaje, string $tab = 'categorias'): void {
        $_SESSION['admin_flash'] = $mensaje;
        header("Location: {$this->config['base_url']}/admin/catalogos?tab={$tab}");
        exit();
    }

    private function redirigirConError(string $mensaje, string $tab = 'categorias'): void {
        $_SESSION['admin_error'] = $mensaje;
        header("Location: {$this->config['base_url']}/admin/catalogos?tab={$tab}");
        exit();
    }

    private function generarSlug(string $texto): string {
        $slug = mb_strtolower(trim($texto), 'UTF-8');
        $slug = strtr($slug, [
            'á'=>'a', 'é'=>'e', 'í'=>'i', 'ó'=>'o', 'ú'=>'u',
            'ñ'=>'n', 'ü'=>'u', 'Á'=>'a', 'É'=>'e', 'Í'=>'i',
            'Ó'=>'o', 'Ú'=>'u', 'Ñ'=>'n', 'Ü'=>'u'
        ]);
        $slug = preg_replace('/[^a-z0-9\-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }
}
