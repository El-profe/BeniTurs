<?php
namespace App\Controllers\Publico;

use App\Core\Controller;
use App\Models\Lugar;
use App\Models\Categoria;
use App\Models\Municipio;
use App\Models\Promocion;
use App\Models\Fotografia;
use App\Services\MapaService;
use App\Services\AnaliticaVisitasService;

class CatalogoController extends Controller {
    private Lugar $lugarModel;
    private Categoria $categoriaModel;
    private Municipio $municipioModel;

    public function __construct() {
        parent::__construct();
        $this->lugarModel = new Lugar();
        $this->categoriaModel = new Categoria();
        $this->municipioModel = new Municipio();
    }

    public function index(): void {
        AnaliticaVisitasService::registrarVisita();
        $categorias = $this->categoriaModel->listarTodasActivas();
        $municipios = $this->municipioModel->listarActivos();

        $categoriaSeleccionada = null;
        $slugCategoria = is_string($_GET['categoria'] ?? null) ? $_GET['categoria'] : '';
        foreach ($categorias as $categoria) {
            if ($categoria['slug'] === $slugCategoria) {
                $categoriaSeleccionada = (int)$categoria['id_categoria'];
                break;
            }
        }

        $idMunicipio = !empty($_GET['municipio']) ? (int)$_GET['municipio'] : null;
        $lugares = $this->lugarModel->listarPublicos($categoriaSeleccionada, null, $idMunicipio);

        $this->render('publico/catalogo/index', [
            'titulo'                => 'BeniTurs - Directorio Turístico y Comercial del Beni',
            'heroPantallaCompleta'  => true,
            'categorias'            => $categorias,
            'municipios'            => $municipios,
            'categoriaSeleccionada' => $categoriaSeleccionada,
            'municipioSeleccionado' => $idMunicipio,
            'lugares'               => $lugares
        ], 'publico');
    }

    public function detalle(): void {
        AnaliticaVisitasService::registrarVisita();
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
        $imgUrl = !empty($lugar['imagen'])
            ? "{$this->config['base_url']}/imagen?f=" . urlencode($lugar['imagen'])
            : "{$this->config['base_url']}/assets/img/logo2.png";
        $ogMeta = [
            'title' => "{$lugar['nombre']} | BeniTurs",
            'description' => mb_substr(strip_tags($lugar['descripcion'] ?? ''), 0, 160, 'UTF-8'),
            'image' => $imgUrl,
            'url' => "{$this->config['base_url']}/catalogo/detalle?slug=" . urlencode($lugar['slug'])
        ];

        $this->render('publico/catalogo/detalle', [
            'titulo'      => $lugar['nombre'],
            'lugar'       => $lugar,
            'fotografias' => $fotografias,
            'mapa'        => MapaService::desdeCoordenadas($lugar['coordenadas_gps'] ?? null),
            'promociones' => $promociones,
            'ogMeta'      => $ogMeta
        ], 'publico');
    }

    /**
     * Endpoint API para búsquedas y filtros en vivo vía Fetch.
     */
    public function apiBuscar(): void {
        $idCategoria = !empty($_GET['categoria']) ? (int)$_GET['categoria'] : null;
        $idMunicipio = !empty($_GET['municipio']) ? (int)$_GET['municipio'] : null;
        $termino = !empty($_GET['q']) ? trim($_GET['q']) : null;

        $resultados = $this->lugarModel->listarPublicos($idCategoria, $termino, $idMunicipio);

        $this->json([
            'success'      => true,
            'total'        => count($resultados),
            'resultados'   => $resultados
        ]);
    }
}
