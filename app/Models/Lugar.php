<?php
namespace App\Models;

use App\Core\Database;
use App\Services\PublicacionService;
use PDO;

class Lugar {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function listarPublicos(?int $idCategoria = null, ?string $termino = null): array {
        $condicionVisibilidad = PublicacionService::getSqlCondicionVisibilidad('l', 'pub');
        
        $sql = "SELECT l.id_lugar, l.nombre, l.slug, l.descripcion, l.direccion, 
                       l.referencia_ubicacion, l.horario_atencion, l.tipo_lugar,
                       l.whatsapp_contacto, l.telefono_contacto,
                       c.nombre AS categoria, c.icono AS categoria_icono,
                       (SELECT f.nombre_archivo FROM fotografias f WHERE f.id_lugar = l.id_lugar AND f.es_principal = 1 LIMIT 1) AS imagen
                FROM lugares l
                INNER JOIN categorias c ON l.id_categoria = c.id_categoria
                INNER JOIN publicaciones pub ON l.id_lugar = pub.id_lugar
                WHERE {$condicionVisibilidad}";

        $params = [];

        if ($idCategoria !== null && $idCategoria > 0) {
            $sql .= " AND l.id_categoria = :id_categoria";
            $params[':id_categoria'] = $idCategoria;
        }

        if (!empty($termino)) {
            $sql .= " AND (l.nombre LIKE :nombre OR l.descripcion LIKE :descripcion OR l.direccion LIKE :direccion)";
            foreach ([':nombre', ':descripcion', ':direccion'] as $param) {
                $params[$param] = '%' . $termino . '%';
            }
        }

        $sql .= " ORDER BY l.id_lugar DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function buscarPublicoPorSlug(string $slug): ?array {
        $condicionVisibilidad = PublicacionService::getSqlCondicionVisibilidad('l', 'pub');

        $sql = "SELECT l.*, c.nombre AS categoria, c.icono AS categoria_icono,
                       (SELECT f.nombre_archivo FROM fotografias f WHERE f.id_lugar = l.id_lugar AND f.es_principal = 1 LIMIT 1) AS imagen
                FROM lugares l
                INNER JOIN categorias c ON l.id_categoria = c.id_categoria
                INNER JOIN publicaciones pub ON l.id_lugar = pub.id_lugar
                WHERE l.slug = :slug AND {$condicionVisibilidad}
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':slug' => $slug]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public function listarParaAdmin(): array {
        $visibilidad = PublicacionService::getSqlCondicionVisibilidad('l', 'pub');
        $sql = "SELECT l.id_lugar, l.nombre, l.slug, l.tipo_lugar, l.created_at,
                       c.nombre AS categoria,
                       pub.aprobado, pub.habilitado, pub.motivo_suspension,
                       v.fecha_vencimiento,
                       (SELECT f.nombre_archivo FROM fotografias f WHERE f.id_lugar = l.id_lugar AND f.es_principal = 1 LIMIT 1) AS imagen,
                       CASE WHEN {$visibilidad} THEN 1 ELSE 0 END AS es_visible
                FROM lugares l
                INNER JOIN categorias c ON l.id_categoria = c.id_categoria
                LEFT JOIN publicaciones pub ON l.id_lugar = pub.id_lugar
                LEFT JOIN (
                    SELECT vg.id_lugar, MAX(vg.fecha_vencimiento) AS fecha_vencimiento
                    FROM vigencias vg INNER JOIN pagos pg ON pg.id_pago = vg.id_pago
                    WHERE pg.estado = 'CONFIRMADO'
                    GROUP BY vg.id_lugar
                ) v ON l.id_lugar = v.id_lugar
                ORDER BY l.id_lugar DESC";

        return $this->db->query($sql)->fetchAll();
    }

    public function buscarPorId(int $id): ?array {
        $sql = "SELECT l.*, pub.aprobado, pub.habilitado, pub.motivo_suspension,
                       (SELECT f.nombre_archivo FROM fotografias f WHERE f.id_lugar = l.id_lugar AND f.es_principal = 1 LIMIT 1) AS imagen
                FROM lugares l
                LEFT JOIN publicaciones pub ON l.id_lugar = pub.id_lugar
                WHERE l.id_lugar = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public function crear(array $datos): int {
        $slugBase = $this->generarSlug($datos['nombre']);
        $slugFinal = $this->asegurarSlugUnico($slugBase);

        $sql = "INSERT INTO lugares (
                    id_categoria, nombre, slug, descripcion, direccion, 
                    referencia_ubicacion, coordenadas_gps, telefono_contacto, 
                    whatsapp_contacto, email_contacto, horario_atencion, tipo_lugar
                ) VALUES (
                    :id_categoria, :nombre, :slug, :descripcion, :direccion,
                    :referencia, :coordenadas, :telefono,
                    :whatsapp, :email, :horario, :tipo_lugar
                )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_categoria' => $datos['id_categoria'],
            ':nombre'       => $datos['nombre'],
            ':slug'         => $slugFinal,
            ':descripcion'  => $datos['descripcion'],
            ':direccion'    => $datos['direccion'],
            ':referencia'   => $datos['referencia_ubicacion'] ?? null,
            ':coordenadas'  => $datos['coordenadas_gps'] ?? null,
            ':telefono'     => $datos['telefono_contacto'] ?? null,
            ':whatsapp'     => $datos['whatsapp_contacto'] ?? null,
            ':email'        => $datos['email_contacto'] ?? null,
            ':horario'      => $datos['horario_atencion'] ?? null,
            ':tipo_lugar'   => $datos['tipo_lugar']
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function actualizar(int $id, array $datos): bool {
        $sql = "UPDATE lugares SET
                    id_categoria = :id_categoria,
                    nombre = :nombre,
                    descripcion = :descripcion,
                    direccion = :direccion,
                    referencia_ubicacion = :referencia,
                    coordenadas_gps = :coordenadas,
                    telefono_contacto = :telefono,
                    whatsapp_contacto = :whatsapp,
                    email_contacto = :email,
                    horario_atencion = :horario,
                    tipo_lugar = :tipo_lugar
                WHERE id_lugar = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id_categoria' => $datos['id_categoria'],
            ':nombre'       => $datos['nombre'],
            ':descripcion'  => $datos['descripcion'],
            ':direccion'    => $datos['direccion'],
            ':referencia'   => $datos['referencia_ubicacion'] ?? null,
            ':coordenadas'  => $datos['coordenadas_gps'] ?? null,
            ':telefono'     => $datos['telefono_contacto'] ?? null,
            ':whatsapp'     => $datos['whatsapp_contacto'] ?? null,
            ':email'        => $datos['email_contacto'] ?? null,
            ':horario'      => $datos['horario_atencion'] ?? null,
            ':tipo_lugar'   => $datos['tipo_lugar'],
            ':id'           => $id
        ]);
    }

    private function generarSlug(string $texto): string {
        $texto = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
        $texto = preg_replace('~[^\pL\d]+~u', '-', $texto);
        $texto = trim($texto, '-');
        $texto = strtolower($texto);
        return empty($texto) ? 'lugar-' . time() : $texto;
    }

    private function asegurarSlugUnico(string $slug, ?int $ignorarId = null): string {
        $slugFinal = $slug;
        $i = 1;
        while (true) {
            $sql = "SELECT id_lugar FROM lugares WHERE slug = :slug";
            if ($ignorarId) {
                $sql .= " AND id_lugar != :id";
            }
            $stmt = $this->db->prepare($sql);
            $params = [':slug' => $slugFinal];
            if ($ignorarId) {
                $params[':id'] = $ignorarId;
            }
            $stmt->execute($params);

            if (!$stmt->fetch()) {
                break;
            }
            $slugFinal = $slug . '-' . $i++;
        }
        return $slugFinal;
    }
}
