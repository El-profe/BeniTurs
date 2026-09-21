USE `trinidad_turismo_db`;

-- 1. Administrador inicial
-- Credenciales por defecto: usuario -> admin | contraseña -> AdminTrinidad2026!
INSERT INTO `administradores` (`id_administrador`, `nombre`, `usuario`, `email`, `password_hash`, `activo`) 
VALUES (
    1,
    'Administrador Central Trinidad',
    'admin',
    'admin@trinidadturismo.bo',
    '$2y$10$vN9m8zWqyD4vKqCiqYlCtebW1G4h6eWd5E8j1a5N2p.c7c1D8I2aG', 
    1
) ON DUPLICATE KEY UPDATE `usuario` = `usuario`;

-- 2. Tarifa comercial de referencia acordada: Bs 250.00 mensual
INSERT INTO `tarifas` (`id_tarifa`, `monto_mensual`, `descripcion`, `vigente_desde`, `activo`, `id_administrador_registro`) 
VALUES (
    1,
    250.00,
    'Tarifa mensual estándar de publicación comercial',
    '2026-01-01',
    1,
    1
) ON DUPLICATE KEY UPDATE `monto_mensual` = VALUES(`monto_mensual`);

-- 3. Categorías oficiales adaptadas a Trinidad, Beni
INSERT INTO `categorias` (`id_categoria`, `nombre`, `slug`, `descripcion`, `icono`, `tipo_defecto`, `activo`) 
VALUES
(1, 'Atractivos Turísticos', 'atractivos-turisticos', 'Lagunas, ríos, lomas históricas y museos naturales benianos', 'bi-tree', 'PUBLICO', 1),
(2, 'Restaurantes & Gastronomía', 'restaurantes-gastronomia', 'Comida típica beniana, pescados de río, pacú, surubí y majaditos', 'bi-cup-hot', 'COMERCIAL', 1),
(3, 'Bares & Vida Nocturna', 'bares-vida-nocturna', 'Pubs, karaokes, discotecas y lounges en la ciudad de Trinidad', 'bi-moon-stars', 'COMERCIAL', 1),
(4, 'Hospedaje & Hoteles', 'hospedaje-hoteles', 'Hoteles, residenciales y alojamientos para turistas y profesionales', 'bi-building', 'COMERCIAL', 1),
(5, 'Artesanías & Comercio Local', 'artesanias-comercio', 'Textiles, tallados en madera, recuerdos y productos del Beni', 'bi-shop', 'COMERCIAL', 1)
ON DUPLICATE KEY UPDATE `nombre` = VALUES(`nombre`);

-- 4. Lugares públicos gratuitos de referencia en Trinidad (demostración inmediata)
INSERT INTO `lugares` (`id_lugar`, `id_categoria`, `nombre`, `slug`, `descripcion`, `direccion`, `referencia_ubicacion`, `tipo_lugar`) 
VALUES
(
    1, 
    1, 
    'Laguna Suárez', 
    'laguna-suarez', 
    'Hermosa laguna artificial prehispánica construida por la civilización de los llanos de Moxos. Ideal para balneario, deportes náuticos y atardeceres.', 
    'A 5 km al sur de Trinidad', 
    'Camino asfaltado hacia el balneario Laguna Suárez', 
    'PUBLICO'
),
(
    2, 
    1, 
    'Plaza Principal Mariscal José Ballivián', 
    'plaza-principal-ballivian', 
    'Centro cívico y punto neurálgico de Trinidad, rodeada por la Catedral de la Santísima Trinidad y jardines con vegetación tropical beniana.', 
    'Centro Histórico de Trinidad', 
    'Frente a la Catedral de Trinidad', 
    'PUBLICO'
),
(
    3, 
    1, 
    'Museo Ictícola del Beni', 
    'museo-icticola-beni', 
    'El tercer museo de peces de agua dulce más grande de Sudamérica, ubicado dentro de la Universidad Autónoma del Beni (UAB).', 
    'Campus Universitario UAB, Av. Dr. Antonio Vaca Díez', 
    'Ingreso principal del campus universitario', 
    'PUBLICO'
)
ON DUPLICATE KEY UPDATE `nombre` = VALUES(`nombre`);

-- 5. Publicaciones aprobadas y habilitadas para los lugares públicos
INSERT INTO `publicaciones` (`id_lugar`, `aprobado`, `habilitado`, `id_administrador_aprobacion`, `fecha_aprobacion`) 
VALUES
(1, 1, 1, 1, NOW()),
(2, 1, 1, 1, NOW()),
(3, 1, 1, 1, NOW())
ON DUPLICATE KEY UPDATE `aprobado` = VALUES(`aprobado`);