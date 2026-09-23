<?php
namespace App\Controllers\Publico;

use App\Core\Controller;
use App\Models\Lugar;
use App\Models\Categoria;
use App\Models\Promocion;
use App\Models\Fotografia;
use App\Services\MapaService;

class CatalogoController extends Controller {
    private Lugar $lugarModel;
    private Categoria $categoriaModel;

    public function __construct() {
        parent::__construct();
        $this->lugarModel = new Lugar();
        $this->categoriaModel = new Categoria();
    }

    public function index(): void {
        $categorias = $this->categoriaModel->listarTodasActivas();
        $categoriaSeleccionada = null;
        $slugCategoria = is_string($_GET['categoria'] ?? null) ? $_GET['categoria'] : '';
        foreach ($categorias as $categoria) {
            if ($categoria['slug'] === $slugCategoria) {
                $categoriaSeleccionada = (int)$categoria['id_categoria'];
                break;
            }
        }
        $lugares = $this->lugarModel->listarPublicos($categoriaSeleccionada);

        $this->render('publico/catalogo/index', [
            'titulo'     => 'Guía Turística y Comercial de Trinidad',
            'heroPantallaCompleta' => true,
            'categorias' => $categorias,
            'categoriaSeleccionada' => $categoriaSeleccionada,
            'lugares'    => $lugares
        ], 'publico');
    }

    public function detalle(): void {
        $slug = trim($_GET['slug'] ?? '');
        if (empty($slug)) {
            header("Location: {$this->config['base_url']}/");
            exit();
        }

        $lugar = $this->lugarModel->buscarPublicoPorSlug($slug);
        if (!$lugar) {
            http_response_code(404);
            $appName = $this->config['app_name'];
            $baseUrl = $this->config['base_url'];
            require dirname(__DIR__, 2) . '/Views/errors/404.php';
            exit();
        }

        $promocionModel = new Promocion();
        $promociones = $promocionModel->listarVigentesPublicas((int)$lugar['id_lugar']);
        $fotografias = (new Fotografia())->listarPorLugar((int)$lugar['id_lugar']);

        $this->render('publico/catalogo/detalle', [
            'titulo' => $lugar['nombre'],
            'lugar'  => $lugar,
            'fotografias' => $fotografias,
            'mapa' => MapaService::desdeCoordenadas($lugar['coordenadas_gps'] ?? null),
            'promociones' => $promociones
        ], 'publico');
    }

    /**
     * Endpoint API para búsquedas y filtros en vivo vía Fetch.
     */
    public function apiBuscar(): void {
        $idCategoria = !empty($_GET['categoria']) ? (int)$_GET['categoria'] : null;
        $termino = !empty($_GET['q']) ? trim($_GET['q']) : null;

        $resultados = $this->lugarModel->listarPublicos($idCategoria, $termino);

        $this->json([
            'success'      => true,
            'total'        => count($resultados),
            'resultados'   => $resultados
        ]);
    }
}
