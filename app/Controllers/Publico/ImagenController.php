<?php
namespace App\Controllers\Publico;

use App\Core\Controller;
use App\Models\Fotografia;
use App\Services\PublicacionService;
use App\Services\ImagenService;

class ImagenController extends Controller {

    /**
     * Sirve la fotografía comprobando permisos de visualización pública o admin
     */
    public function ver(): void {
        $nombreArchivo = basename($_GET['f'] ?? '');
        if (empty($nombreArchivo)) {
            http_response_code(404);
            exit();
        }

        $fotoModel = new Fotografia();
        $foto = $fotoModel->obtenerPorNombreArchivo($nombreArchivo);

        if (!$foto) {
            http_response_code(404);
            exit();
        }

        // Comprobación de seguridad: Permitir si es Admin logueado O si la ficha es visible
        $esAdmin = !empty($_SESSION['admin_id']);
        $esVisible = PublicacionService::esFichaVisible((int)$foto['id_lugar']);

        if (!$esAdmin && !$esVisible) {
            http_response_code(403);
            exit();
        }

        $rutaFisica = ImagenService::getDirectorioStorage() . $foto['nombre_archivo'];
        if (!file_exists($rutaFisica)) {
            http_response_code(404);
            exit();
        }

        header('Content-Type: ' . $foto['mime_type']);
        header('Content-Length: ' . filesize($rutaFisica));
        header('Cache-Control: public, max-age=86400');
        readfile($rutaFisica);
        exit();
    }
}