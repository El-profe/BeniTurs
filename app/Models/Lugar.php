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

    /**
     * Lista lugares públicos visibles con filtros opcionales de categoría, búsqueda textual y municipio.
     */
    public function listarPublicos(?int $idCategoria = null, ?string $termino = null, ?int $idMunicipio = null): array {
        $condicionVisibilidad = PublicacionService::getSqlCondicionVisibilidad('l', 'pub');
        
        $sql = "SELECT l.id_lugar, l.id_municipio, l.nombre, l.slug, l.descripcion, l.direccion, 
                       l.referencia_ubicacion, l.horario_atencion, l.tipo_lugar,
                       l.whatsapp_contacto, l.telefono_contacto, l.id_categoria,
                       l.latitud, l.longitud,
                       CONCAT(COALESCE(l.latitud, ''), ',', COALESCE(l.longitud, '')) AS coordenadas_gps,
                       c.nombre AS categoria, c.icono AS categoria_icono,
                       m.nombre AS municipio, m.provincia AS municipio_provincia, m.slug AS municipio_slug,
                       (SELECT f.nombre_archivo FROM fotografias f WHERE f.id_lugar = l.id_lugar AND f.es_principal = 1 LIMIT 1) AS imagen
                FROM lugares l
                INNER JOIN categorias c ON l.id_categoria = c.id_categoria
                INNER JOIN municipios m ON l.id_municipio = m.id_municipio
                INNER JOIN publicaciones pub ON l.id_lugar = pub.id_lugar
                WHERE {$condicionVisibilidad}";

        $params = [];

        if ($idCategoria !== null && $idCategoria > 0) {
            $sql .= " AND l.id_categoria = :id_categoria";
            $params[':id_categoria'] = $idCategoria;
        }

        if ($idMunicipio !== null && $idMunicipio > 0) {
            $sql .= " AND l.id_municipio = :id_municipio";
            $params[':id_municipio'] = $idMunicipio;
        }

        if (!empty($termino)) {
            $sql .= " AND (l.nombre LIKE :nombre OR l.descripcion LIKE :descripcion OR l.direccion LIKE :direccion OR m.nombre LIKE :municipio)";
            foreach ([':nombre', ':descripcion', ':direccion', ':municipio'] as $param) {
                $params[$param] = '%' . $termino . '%';
            }
        }

        $sql .= " ORDER BY l.id_lugar DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Busca la ficha pública visible de un lugar por su slug.
     */
    public function buscarPublicoPorSlug(string $slug): ?array {
        $condicionVisibilidad = PublicacionService::getSqlCondicionVisibilidad('l', 'pub');

        $sql = "SELECT l.*,
                       CONCAT(COALESCE(l.latitud, ''), ',', COALESCE(l.longitud, '')) AS coordenadas_gps,
                       c.nombre AS categoria, c.icono AS categoria_icono,
                       m.nombre AS municipio, m.provincia AS municipio_provincia, m.slug AS municipio_slug,
                       (SELECT f.nombre_archivo FROM fotografias f WHERE f.id_lugar = l.id_lugar AND f.es_principal = 1 LIMIT 1) AS imagen
                FROM lugares l
                INNER JOIN categorias c ON l.id_categoria = c.id_categoria
                INNER JOIN municipios m ON l.id_municipio = m.id_municipio
                INNER JOIN publicaciones pub ON l.id_lugar = pub.id_lugar
                WHERE l.slug = :slug AND {$condicionVisibilidad}
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':slug' => $slug]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    /**
     * Lista todos los lugares para el panel administrativo con detalles de visibilidad y municipio.
     */
    public function listarParaAdmin(): array {
        $visibilidad = PublicacionService::getSqlCondicionVisibilidad('l', 'pub');
        $sql = "SELECT l.id_lugar, l.id_municipio, l.nombre, l.slug, l.tipo_lugar, l.created_at,
                       l.latitud, l.longitud,
                       CONCAT(COALESCE(l.latitud, ''), ',', COALESCE(l.longitud, '')) AS coordenadas_gps,
                       c.nombre AS categoria,
                       m.nombre AS municipio, m.provincia AS municipio_provincia,
                       pub.aprobado, pub.habilitado, pub.motivo_suspension,
                       v.fecha_vencimiento,
                       (SELECT f.nombre_archivo FROM fotografias f WHERE f.id_lugar = l.id_lugar AND f.es_principal = 1 LIMIT 1) AS imagen,
                       CASE WHEN {$visibilidad} THEN 1 ELSE 0 END AS es_visible
                FROM lugares l
                INNER JOIN categorias c ON l.id_categoria = c.id_categoria
                INNER JOIN municipios m ON l.id_municipio = m.id_municipio
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

    /**
     * Busca un lugar por su clave primaria.
     */
    public function buscarPorId(int $id): ?array {
        $sql = "SELECT l.*,
                       CONCAT(COALESCE(l.latitud, ''), ',', COALESCE(l.longitud, '')) AS coordenadas_gps,
                       c.nombre AS categoria,
                       m.nombre AS municipio, m.provincia AS municipio_provincia,
                       pub.aprobado, pub.habilitado, pub.motivo_suspension,
                       (SELECT f.nombre_archivo FROM fotografias f WHERE f.id_lugar = l.id_lugar AND f.es_principal = 1 LIMIT 1) AS imagen
                FROM lugares l
                LEFT JOIN categorias c ON l.id_categoria = c.id_categoria
                LEFT JOIN municipios m ON l.id_municipio = m.id_municipio
                LEFT JOIN publicaciones pub ON l.id_lugar = pub.id_lugar
                WHERE l.id_lugar = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    /**
     * Crea un nuevo registro de lugar con soporte para id_municipio, latitud y longitud.
     */
    public function crear(array $datos): int {
        $slugBase = $this->generarSlug($datos['nombre']);
        $slugFinal = $this->asegurarSlugUnico($slugBase);

        $coords = $this->extraerCoordenadas($datos);

        $sql = "INSERT INTO lugares (
                    id_municipio, id_categoria, nombre, slug, descripcion, direccion, 
                    referencia_ubicacion, latitud, longitud, telefono_contacto, 
                    whatsapp_contacto, email_contacto, horario_atencion, tipo_lugar
                ) VALUES (
                    :id_municipio, :id_categoria, :nombre, :slug, :descripcion, :direccion,
                    :referencia, :latitud, :longitud, :telefono,
                    :whatsapp, :email, :horario, :tipo_lugar
                )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_municipio' => (int)($datos['id_municipio'] ?? 1),
            ':id_categoria' => (int)$datos['id_categoria'],
            ':nombre'       => $datos['nombre'],
            ':slug'         => $slugFinal,
            ':descripcion'  => $datos['descripcion'],
            ':direccion'    => $datos['direccion'],
            ':referencia'   => $datos['referencia_ubicacion'] ?? null,
            ':latitud'      => $coords['latitud'],
            ':longitud'     => $coords['longitud'],
            ':telefono'     => $datos['telefono_contacto'] ?? null,
            ':whatsapp'     => $datos['whatsapp_contacto'] ?? null,
            ':email'        => $datos['email_contacto'] ?? null,
            ':horario'      => $datos['horario_atencion'] ?? null,
            ':tipo_lugar'   => $datos['tipo_lugar']
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Actualiza un registro existente de lugar.
     */
    public function actualizar(int $id, array $datos): bool {
        $coords = $this->extraerCoordenadas($datos);

        $sql = "UPDATE lugares SET
                    id_municipio = :id_municipio,
                    id_categoria = :id_categoria,
                    nombre = :nombre,
                    descripcion = :descripcion,
                    direccion = :direccion,
                    referencia_ubicacion = :referencia,
                    latitud = :latitud,
                    longitud = :longitud,
                    telefono_contacto = :telefono,
                    whatsapp_contacto = :whatsapp,
                    email_contacto = :email,
                    horario_atencion = :horario,
                    tipo_lugar = :tipo_lugar
                WHERE id_lugar = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id_municipio' => (int)($datos['id_municipio'] ?? 1),
            ':id_categoria' => (int)$datos['id_categoria'],
            ':nombre'       => $datos['nombre'],
            ':descripcion'  => $datos['descripcion'],
            ':direccion'    => $datos['direccion'],
            ':referencia'   => $datos['referencia_ubicacion'] ?? null,
            ':latitud'      => $coords['latitud'],
            ':longitud'     => $coords['longitud'],
            ':telefono'     => $datos['telefono_contacto'] ?? null,
            ':whatsapp'     => $datos['whatsapp_contacto'] ?? null,
            ':email'        => $datos['email_contacto'] ?? null,
            ':horario'      => $datos['horario_atencion'] ?? null,
            ':tipo_lugar'   => $datos['tipo_lugar'],
            ':id'           => $id
        ]);
    }

    /**
     * Permite al comerciante actualizar su propio perfil desde el panel de autoservicio.
     */
    public function actualizarPerfilNegocio(int $id, array $datos): bool {
        $sql = "UPDATE lugares SET
                    descripcion = :descripcion,
                    direccion = :direccion,
                    referencia_ubicacion = :referencia,
                    telefono_contacto = :telefono,
                    whatsapp_contacto = :whatsapp,
                    email_contacto = :email,
                    horario_atencion = :horario";

        $params = [
            ':descripcion'  => $datos['descripcion'],
            ':direccion'    => $datos['direccion'],
            ':referencia'   => $datos['referencia_ubicacion'] ?? null,
            ':telefono'     => $datos['telefono_contacto'] ?? null,
            ':whatsapp'     => $datos['whatsapp_contacto'] ?? null,
            ':email'        => $datos['email_contacto'] ?? null,
            ':horario'      => $datos['horario_atencion'] ?? null,
            ':id'           => $id
        ];

        if (isset($datos['id_municipio']) && (int)$datos['id_municipio'] > 0) {
            $sql .= ", id_municipio = :id_municipio";
            $params[':id_municipio'] = (int)$datos['id_municipio'];
        }

        $sql .= " WHERE id_lugar = :id AND tipo_lugar = 'COMERCIAL'";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Extrae y normaliza latitud y longitud tanto si vienen como campos separados o como string coordenadas_gps.
     */
    private function extraerCoordenadas(array $datos): array {
        if (isset($datos['latitud']) && isset($datos['longitud']) && is_numeric($datos['latitud']) && is_numeric($datos['longitud'])) {
            return [
                'latitud'  => (float)$datos['latitud'],
                'longitud' => (float)$datos['longitud']
            ];
        }

        if (!empty($datos['coordenadas_gps']) && is_string($datos['coordenadas_gps'])) {
            $parts = explode(',', $datos['coordenadas_gps']);
            if (count($parts) === 2 && is_numeric(trim($parts[0])) && is_numeric(trim($parts[1]))) {
                return [
                    'latitud'  => (float)trim($parts[0]),
                    'longitud' => (float)trim($parts[1])
                ];
            }
        }

        return ['latitud' => null, 'longitud' => null];
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
