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
        if ($esAdmin) {
            $admin = \App\Core\Database::getConnection()->prepare('SELECT id_administrador FROM administradores WHERE id_administrador = ? AND activo = 1');
            $admin->execute([(int)$_SESSION['admin_id']]);
            $esAdmin = (bool)$admin->fetchColumn();
        }
        $esVisible = PublicacionService::esFichaVisible((int)$foto['id_lugar']);
        $esPropietario = (int)($_SESSION['negocio_lugar_id'] ?? 0) === (int)$foto['id_lugar']
            && \App\Middleware\NegocioAuthMiddleware::esCuentaActualValida();

        if (!$esAdmin && !$esVisible && !$esPropietario) {
            http_response_code(403);
            exit();
        }

        $rutaFisica = ImagenService::getDirectorioStorage() . $foto['nombre_archivo'];
        if (!file_exists($rutaFisica)) {
            http_response_code(404);
            exit();
        }

        $mtime = filemtime($rutaFisica);
        $etag = '"' . md5($foto['nombre_archivo'] . $mtime) . '"';
        $lastModified = gmdate('D, d M Y H:i:s ', $mtime) . 'GMT';

        header('Content-Type: ' . $foto['mime_type']);
        header('Content-Length: ' . filesize($rutaFisica));
        header('Cache-Control: public, max-age=2592000, stale-while-revalidate=86400');
        header('ETag: ' . $etag);
        header('Last-Modified: ' . $lastModified);
        header('X-Content-Type-Options: nosniff');

        // Soporte de caché del navegador: HTTP 304 Not Modified
        $ifNoneMatch = isset($_SERVER['HTTP_IF_NONE_MATCH']) ? trim($_SERVER['HTTP_IF_NONE_MATCH']) : null;
        $ifModifiedSince = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) : false;

        if (($ifNoneMatch !== null && $ifNoneMatch === $etag) ||
            ($ifModifiedSince !== false && $ifModifiedSince >= $mtime)) {
            http_response_code(304);
            exit();
        }

        readfile($rutaFisica);
        exit();
    }
}
