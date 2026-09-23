-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 23-09-2026 a las 07:42:04
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `trinidad_turismo_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `administradores`
--

CREATE TABLE `administradores` (
  `id_administrador` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `email` varchar(120) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `ultimo_acceso` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `administradores`
--

INSERT INTO `administradores` (`id_administrador`, `nombre`, `usuario`, `email`, `password_hash`, `activo`, `ultimo_acceso`, `created_at`) VALUES
(1, 'Administrador Central Trinidad', 'admin', 'admin@trinidadturismo.bo', '$2y$10$cVhgQt3yarRcRUgKKPtcRewyfsts/78yiAfxEgq7w1Alux9EKyKBm', 1, '2026-09-23 01:34:37', '2026-09-14 14:52:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id_categoria` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `icono` varchar(50) NOT NULL DEFAULT 'bi-tag',
  `tipo_defecto` enum('PUBLICO','COMERCIAL') NOT NULL DEFAULT 'COMERCIAL',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id_categoria`, `nombre`, `slug`, `descripcion`, `icono`, `tipo_defecto`, `activo`, `created_at`) VALUES
(1, 'Atractivos Turísticos', 'atractivos-turisticos', 'Lagunas, ríos, lomas históricas y museos naturales benianos', 'bi-tree', 'PUBLICO', 1, '2026-09-14 14:52:05'),
(2, 'Restaurantes & Gastronomía', 'restaurantes-gastronomia', 'Comida típica beniana, pescados de río, pacú, surubí y majaditos', 'bi-cup-hot', 'COMERCIAL', 1, '2026-09-14 14:52:05'),
(3, 'Bares & Vida Nocturna', 'bares-vida-nocturna', 'Pubs, karaokes, discotecas y lounges en la ciudad de Trinidad', 'bi-moon-stars', 'COMERCIAL', 1, '2026-09-14 14:52:05'),
(4, 'Hospedaje & Hoteles', 'hospedaje-hoteles', 'Hoteles, residenciales y alojamientos para turistas y profesionales', 'bi-building', 'COMERCIAL', 1, '2026-09-14 14:52:05'),
(5, 'Artesanías & Comercio Local', 'artesanias-comercio', 'Textiles, tallados en madera, recuerdos y productos del Beni', 'bi-shop', 'COMERCIAL', 1, '2026-09-14 14:52:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cuentas_negocio`
--

CREATE TABLE `cuentas_negocio` (
  `id_cuenta` int(11) NOT NULL,
  `id_lugar` int(11) NOT NULL,
  `usuario` varchar(60) NOT NULL,
  `email` varchar(120) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `ultimo_acceso` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `cuentas_negocio`
--

INSERT INTO `cuentas_negocio` (`id_cuenta`, `id_lugar`, `usuario`, `email`, `password_hash`, `activo`, `ultimo_acceso`, `created_at`) VALUES
(1, 1, 'negocio_demo', 'propietario@trinidad.bo', '$2y$10$3YmE6rF.4Q0rZ4lH8c37/eD5WfV97m1K9oP6L5uY3Z1eB7YtC7V5S', 1, '2026-09-15 11:46:14', '2026-09-15 15:33:15'),
(2, 8, 'BenjaMin', 'roblesguamayojonathan@gmail.com', '$2y$10$jin2Pr4nK5t/xj/1cjrxIu217eeKSbKq7XMTErA5xK7r31GVIz7t6', 1, NULL, '2026-09-22 14:13:56'),
(3, 9, 'Cervecina', 'roblesguamayojonathan@gmail.com', '$2y$10$4cXisSlxF52tLZEy7KHsKerjDRmWA51nehLGXQLLNiW6F48n1lB4m', 1, NULL, '2026-09-22 14:32:34');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fotografias`
--

CREATE TABLE `fotografias` (
  `id_fotografia` int(11) NOT NULL,
  `id_lugar` int(11) NOT NULL,
  `nombre_archivo` varchar(255) NOT NULL,
  `nombre_original` varchar(255) NOT NULL,
  `mime_type` varchar(50) NOT NULL,
  `tamano_bytes` int(11) NOT NULL,
  `es_principal` tinyint(1) NOT NULL DEFAULT 0,
  `orden` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `fotografias`
--

INSERT INTO `fotografias` (`id_fotografia`, `id_lugar`, `nombre_archivo`, `nombre_original`, `mime_type`, `tamano_bytes`, `es_principal`, `orden`, `created_at`) VALUES
(1, 4, 'lugar_e65f9f25b7dbfee0_1789400789.webp', 'plaza-de-la-contitucion.jpe', 'image/webp', 27966, 1, 1, '2026-09-14 15:46:29'),
(2, 1, 'lugar_64f62b6996a1b847_1789487447.png', 'Captura de pantalla 2026-09-14 191620.png', 'image/png', 119627, 0, 1, '2026-09-15 15:50:47'),
(3, 5, 'lugar_bc80bf41ae91e99a_1789487624.png', 'Captura de pantalla 2026-09-13 235222.png', 'image/png', 91597, 1, 1, '2026-09-15 15:53:44'),
(4, 9, 'lugar_f95c4fff906f8801_1790089273.jpg', 'Bar1.jpg', 'image/jpeg', 62516, 1, 1, '2026-09-22 15:01:13');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lugares`
--

CREATE TABLE `lugares` (
  `id_lugar` int(11) NOT NULL,
  `id_categoria` int(11) NOT NULL,
  `id_solicitud_origen` int(11) DEFAULT NULL,
  `nombre` varchar(150) NOT NULL,
  `slug` varchar(160) NOT NULL,
  `descripcion` text NOT NULL,
  `direccion` varchar(255) NOT NULL,
  `referencia_ubicacion` varchar(255) DEFAULT NULL,
  `coordenadas_gps` varchar(100) DEFAULT NULL,
  `telefono_contacto` varchar(50) DEFAULT NULL,
  `whatsapp_contacto` varchar(50) DEFAULT NULL,
  `email_contacto` varchar(120) DEFAULT NULL,
  `horario_atencion` varchar(200) DEFAULT NULL,
  `tipo_lugar` enum('PUBLICO','COMERCIAL') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `lugares`
--

INSERT INTO `lugares` (`id_lugar`, `id_categoria`, `id_solicitud_origen`, `nombre`, `slug`, `descripcion`, `direccion`, `referencia_ubicacion`, `coordenadas_gps`, `telefono_contacto`, `whatsapp_contacto`, `email_contacto`, `horario_atencion`, `tipo_lugar`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 'Laguna Suárez', 'laguna-suarez', 'Hermosa laguna artificial prehispánica construida por la civilización de los llanos de Moxos. Ideal para balneario, deportes náuticos y atardeceres.', 'A 5 km al sur de Trinidad', 'Camino asfaltado hacia el balneario Laguna Suárez', NULL, NULL, NULL, NULL, NULL, 'PUBLICO', '2026-09-14 14:52:05', '2026-09-14 14:52:05'),
(2, 1, NULL, 'Plaza Principal Mariscal José Ballivián', 'plaza-principal-ballivian', 'Centro cívico y punto neurálgico de Trinidad, rodeada por la Catedral de la Santísima Trinidad y jardines con vegetación tropical beniana.', 'Centro Histórico de Trinidad', 'Frente a la Catedral de Trinidad', NULL, NULL, NULL, NULL, NULL, 'PUBLICO', '2026-09-14 14:52:05', '2026-09-14 14:52:05'),
(3, 1, NULL, 'Museo Ictícola del Beni', 'museo-icticola-beni', 'El tercer museo de peces de agua dulce más grande de Sudamérica, ubicado dentro de la Universidad Autónoma del Beni (UAB).', 'Campus Universitario UAB, Av. Dr. Antonio Vaca Díez', 'Ingreso principal del campus universitario', NULL, NULL, NULL, NULL, NULL, 'PUBLICO', '2026-09-14 14:52:05', '2026-09-14 14:52:05'),
(4, 1, NULL, 'Plaza Principal', 'plaza-principal', 'Plaza principal centro turistico de Trinidad', 'Av./Mosetene Esq./6 de agosto', 'Frente al juzgado electoral', '14.834742,-64.904185', '', '', '', '', 'PUBLICO', '2026-09-14 15:46:29', '2026-09-14 15:47:10'),
(5, 2, 5, 'Pizza Tatú', 'pizza-tat-u', 'Especialidad en pizza', 'Z/Nuevo Amanecer AV/principal', 'Ingresado mediante solicitud web #5', '14.834742,-64.904185', '67355113', '67355113', 'roblesguamayojonathan@gmail.com', 'Almuerzo y Cena (11:30 a 15:00 y 18:30 a 23:30)', 'COMERCIAL', '2026-09-15 15:49:29', '2026-09-15 15:53:44'),
(6, 3, 6, 'Óasis', 'oasis', 'full karaoke', 'Z/Sitraluz Av/Principal C/ 9na entrada', 'Ingresado mediante solicitud web #6', NULL, '67355113', '67355113', 'roblesguamayojonathan@gmail.com', 'Tarde y Noche / Bar (18:00 a 02:00)', 'COMERCIAL', '2026-09-15 16:22:03', '2026-09-15 16:22:03'),
(7, 4, 9, 'Hotel Don Bernardo', 'hotel-don-bernardo', 'Hotel don bernardo', 'Z/Nuevo Amanecer AV/principal', 'Ingresado mediante solicitud web #9', NULL, '67355113', '67355113', 'roblesguamayojonathan@gmail.com', 'Atención 24 Horas', 'COMERCIAL', '2026-09-21 13:09:52', '2026-09-21 13:09:52'),
(8, 4, NULL, 'Hotel Benjamín', 'hotel-benjam-in', 'churrasquito', 'Z/Sitraluz Av/Principal C/ 9na entrada', NULL, NULL, '67355113', '67355113', 'roblesguamayojonathan@gmail.com', 'Atención 24 Horas', 'COMERCIAL', '2026-09-22 14:13:56', '2026-09-22 14:13:56'),
(9, 3, NULL, 'Cervecina', 'cervecina', 'se vende cerveza', 'Av./18 de Noviembre Esq./Avenida del Mar', 'Frente a Kochalitos Restaurante', '-14.830931,-64.90584', '67355113', '67355113', 'roblesguamayojonathan@gmail.com', 'Tarde y Noche / Bar (18:00 a 02:00)', 'COMERCIAL', '2026-09-22 14:32:34', '2026-09-22 14:59:03');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id_pago` int(11) NOT NULL,
  `id_lugar` int(11) NOT NULL,
  `id_tarifa` int(11) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `meses_duracion` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `fecha_pago_declarada` date NOT NULL,
  `numero_comprobante` varchar(100) DEFAULT NULL,
  `metodo_pago` varchar(50) NOT NULL DEFAULT 'Transferencia bancaria / QR',
  `estado` enum('PENDIENTE','CONFIRMADO','ANULADO') NOT NULL DEFAULT 'PENDIENTE',
  `id_administrador_confirmacion` int(11) DEFAULT NULL,
  `fecha_confirmacion` datetime DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `comprobante_archivo` varchar(255) DEFAULT NULL,
  `es_registro_inicial` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pagos`
--

INSERT INTO `pagos` (`id_pago`, `id_lugar`, `id_tarifa`, `monto`, `meses_duracion`, `fecha_pago_declarada`, `numero_comprobante`, `metodo_pago`, `estado`, `id_administrador_confirmacion`, `fecha_confirmacion`, `observaciones`, `created_at`, `comprobante_archivo`, `es_registro_inicial`) VALUES
(1, 5, 1, 250.00, 1, '2026-09-15', '544714', 'Transferencia bancaria / QR', 'CONFIRMADO', 1, '2026-09-15 12:02:40', 'Por yape', '2026-09-15 16:02:34', NULL, 0),
(2, 7, 1, 250.00, 1, '2026-09-21', '544714', 'Efectivo en oficina', 'CONFIRMADO', 1, '2026-09-21 09:12:27', '', '2026-09-21 13:11:37', NULL, 0),
(3, 6, 1, 250.00, 1, '2026-09-21', '54471454', 'Transferencia bancaria / QR', 'CONFIRMADO', 1, '2026-09-22 10:15:27', 'a', '2026-09-21 13:12:15', NULL, 0),
(4, 8, 1, 250.00, 1, '2026-09-22', '544714', 'Transferencia bancaria / QR', 'CONFIRMADO', 1, '2026-09-22 10:15:10', NULL, '2026-09-22 14:13:56', 'comprobante_21eb1f29dd0535c0db034298cfbc493c.png', 1),
(5, 9, 1, 250.00, 1, '2026-09-22', '544714', 'Transferencia bancaria / QR', 'CONFIRMADO', 1, '2026-09-22 10:37:13', NULL, '2026-09-22 14:32:34', 'comprobante_795a67efaaaa81e48a94f5e80b15fa0d.png', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `promociones`
--

CREATE TABLE `promociones` (
  `id_promocion` int(11) NOT NULL,
  `id_lugar` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `descripcion` text NOT NULL,
  `descuento_texto` varchar(100) DEFAULT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `publicaciones`
--

CREATE TABLE `publicaciones` (
  `id_publicacion` int(11) NOT NULL,
  `id_lugar` int(11) NOT NULL,
  `aprobado` tinyint(1) NOT NULL DEFAULT 0,
  `habilitado` tinyint(1) NOT NULL DEFAULT 1,
  `motivo_suspension` text DEFAULT NULL,
  `id_administrador_aprobacion` int(11) DEFAULT NULL,
  `fecha_aprobacion` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `publicaciones`
--

INSERT INTO `publicaciones` (`id_publicacion`, `id_lugar`, `aprobado`, `habilitado`, `motivo_suspension`, `id_administrador_aprobacion`, `fecha_aprobacion`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, NULL, 1, '2026-09-14 10:52:05', '2026-09-14 14:52:05', '2026-09-14 14:52:05'),
(2, 2, 1, 1, NULL, 1, '2026-09-14 10:52:05', '2026-09-14 14:52:05', '2026-09-14 14:52:05'),
(3, 3, 1, 1, NULL, 1, '2026-09-14 10:52:05', '2026-09-14 14:52:05', '2026-09-14 15:08:42'),
(4, 4, 1, 1, NULL, 1, '2026-09-14 11:46:29', '2026-09-14 15:46:29', '2026-09-14 15:46:29'),
(5, 5, 1, 1, NULL, 1, '2026-09-15 11:49:29', '2026-09-15 15:49:29', '2026-09-15 15:49:29'),
(6, 6, 1, 1, NULL, 1, '2026-09-15 12:22:03', '2026-09-15 16:22:03', '2026-09-15 16:22:03'),
(7, 7, 1, 1, NULL, 1, '2026-09-21 09:09:52', '2026-09-21 13:09:52', '2026-09-21 13:09:52'),
(8, 8, 1, 1, NULL, NULL, '2026-09-22 10:13:56', '2026-09-22 14:13:56', '2026-09-22 14:13:56'),
(9, 9, 1, 1, NULL, NULL, '2026-09-22 10:32:34', '2026-09-22 14:32:34', '2026-09-22 14:32:34');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitudes`
--

CREATE TABLE `solicitudes` (
  `id_solicitud` int(11) NOT NULL,
  `nombre_establecimiento` varchar(150) NOT NULL,
  `id_categoria` int(11) NOT NULL,
  `plan_solicitado` enum('MENSUAL','ANUAL') NOT NULL DEFAULT 'MENSUAL',
  `nombre_solicitante` varchar(120) NOT NULL,
  `telefono_contacto` varchar(30) NOT NULL,
  `email_contacto` varchar(120) DEFAULT NULL,
  `direccion` varchar(255) NOT NULL,
  `descripcion` text NOT NULL,
  `horarios` varchar(150) DEFAULT NULL,
  `estado` enum('PENDIENTE','ACEPTADA','RECHAZADA') NOT NULL DEFAULT 'PENDIENTE',
  `observaciones_admin` text DEFAULT NULL,
  `id_administrador_revision` int(11) DEFAULT NULL,
  `fecha_revision` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `comprobante_archivo` varchar(255) DEFAULT NULL,
  `numero_comprobante` varchar(100) DEFAULT NULL,
  `usuario_solicitado` varchar(60) NOT NULL DEFAULT '',
  `password_hash_solicitado` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `solicitudes`
--

INSERT INTO `solicitudes` (`id_solicitud`, `nombre_establecimiento`, `id_categoria`, `plan_solicitado`, `nombre_solicitante`, `telefono_contacto`, `email_contacto`, `direccion`, `descripcion`, `horarios`, `estado`, `observaciones_admin`, `id_administrador_revision`, `fecha_revision`, `created_at`, `comprobante_archivo`, `numero_comprobante`, `usuario_solicitado`, `password_hash_solicitado`) VALUES
(5, 'Pizza Tatú', 2, 'MENSUAL', 'Jonathan Alvaro Robles Guamayo', '67355113', 'roblesguamayojonathan@gmail.com', 'Z/Nuevo Amanecer AV/principal', 'Especialidad en pizza', 'Almuerzo y Cena (11:30 a 15:00 y 18:30 a 23:30)', 'ACEPTADA', '', 1, '2026-09-15 11:49:26', '2026-09-15 15:27:48', NULL, NULL, '', ''),
(6, 'Óasis', 3, 'MENSUAL', 'Jonathan Alvaro Robles Guamayo', '67355113', 'roblesguamayojonathan@gmail.com', 'Z/Sitraluz Av/Principal C/ 9na entrada', 'full karaoke', 'Tarde y Noche / Bar (18:00 a 02:00)', 'ACEPTADA', '', 1, '2026-09-15 12:17:45', '2026-09-15 16:16:48', NULL, NULL, '', ''),
(9, 'Hotel Don Bernardo', 4, 'MENSUAL', 'Jonathan Alvaro Robles Guamayo', '67355113', 'roblesguamayojonathan@gmail.com', 'Z/Nuevo Amanecer AV/principal', 'Hotel don bernardo', 'Atención 24 Horas', 'ACEPTADA', '', 1, '2026-09-21 09:09:49', '2026-09-21 13:09:21', NULL, NULL, '', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tarifas`
--

CREATE TABLE `tarifas` (
  `id_tarifa` int(11) NOT NULL,
  `monto_mensual` decimal(10,2) NOT NULL,
  `descripcion` varchar(150) NOT NULL,
  `vigente_desde` date NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `id_administrador_registro` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tarifas`
--

INSERT INTO `tarifas` (`id_tarifa`, `monto_mensual`, `descripcion`, `vigente_desde`, `activo`, `id_administrador_registro`, `created_at`) VALUES
(1, 250.00, 'Tarifa mensual estándar de publicación comercial', '2026-01-01', 1, 1, '2026-09-14 14:52:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vigencias`
--

CREATE TABLE `vigencias` (
  `id_vigencia` int(11) NOT NULL,
  `id_lugar` int(11) NOT NULL,
  `id_pago` int(11) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_vencimiento` date NOT NULL,
  `tipo_periodo` enum('NUEVO','RENOVACION_ANTICIPADA','REACTIVACION') NOT NULL DEFAULT 'NUEVO',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `vigencias`
--

INSERT INTO `vigencias` (`id_vigencia`, `id_lugar`, `id_pago`, `fecha_inicio`, `fecha_vencimiento`, `tipo_periodo`, `created_at`) VALUES
(1, 5, 1, '2026-09-15', '2026-10-15', 'NUEVO', '2026-09-15 16:02:40'),
(2, 7, 2, '2026-09-21', '2026-10-21', 'NUEVO', '2026-09-21 13:12:27'),
(3, 8, 4, '2026-09-22', '2026-10-22', 'NUEVO', '2026-09-22 14:15:10'),
(4, 6, 3, '2026-09-21', '2026-10-21', 'NUEVO', '2026-09-22 14:15:28'),
(5, 9, 5, '2026-09-22', '2026-10-22', 'NUEVO', '2026-09-22 14:37:13');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `administradores`
--
ALTER TABLE `administradores`
  ADD PRIMARY KEY (`id_administrador`),
  ADD UNIQUE KEY `usuario` (`usuario`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id_categoria`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indices de la tabla `cuentas_negocio`
--
ALTER TABLE `cuentas_negocio`
  ADD PRIMARY KEY (`id_cuenta`),
  ADD UNIQUE KEY `id_lugar` (`id_lugar`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- Indices de la tabla `fotografias`
--
ALTER TABLE `fotografias`
  ADD PRIMARY KEY (`id_fotografia`),
  ADD KEY `fk_fotografias_lugar` (`id_lugar`);

--
-- Indices de la tabla `lugares`
--
ALTER TABLE `lugares`
  ADD PRIMARY KEY (`id_lugar`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD UNIQUE KEY `id_solicitud_origen` (`id_solicitud_origen`),
  ADD KEY `fk_lugares_categoria` (`id_categoria`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id_pago`),
  ADD KEY `fk_pagos_lugar` (`id_lugar`),
  ADD KEY `fk_pagos_tarifa` (`id_tarifa`),
  ADD KEY `fk_pagos_admin` (`id_administrador_confirmacion`);

--
-- Indices de la tabla `promociones`
--
ALTER TABLE `promociones`
  ADD PRIMARY KEY (`id_promocion`),
  ADD KEY `fk_promos_lugar` (`id_lugar`);

--
-- Indices de la tabla `publicaciones`
--
ALTER TABLE `publicaciones`
  ADD PRIMARY KEY (`id_publicacion`),
  ADD UNIQUE KEY `id_lugar` (`id_lugar`),
  ADD KEY `fk_publicaciones_admin` (`id_administrador_aprobacion`);

--
-- Indices de la tabla `solicitudes`
--
ALTER TABLE `solicitudes`
  ADD PRIMARY KEY (`id_solicitud`),
  ADD KEY `fk_solicitudes_categoria` (`id_categoria`),
  ADD KEY `fk_solicitudes_admin` (`id_administrador_revision`),
  ADD KEY `idx_solicitudes_purga` (`estado`,`created_at`);

--
-- Indices de la tabla `tarifas`
--
ALTER TABLE `tarifas`
  ADD PRIMARY KEY (`id_tarifa`),
  ADD KEY `fk_tarifas_admin` (`id_administrador_registro`);

--
-- Indices de la tabla `vigencias`
--
ALTER TABLE `vigencias`
  ADD PRIMARY KEY (`id_vigencia`),
  ADD UNIQUE KEY `id_pago` (`id_pago`),
  ADD KEY `fk_vigencias_lugar` (`id_lugar`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `administradores`
--
ALTER TABLE `administradores`
  MODIFY `id_administrador` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `cuentas_negocio`
--
ALTER TABLE `cuentas_negocio`
  MODIFY `id_cuenta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `fotografias`
--
ALTER TABLE `fotografias`
  MODIFY `id_fotografia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `lugares`
--
ALTER TABLE `lugares`
  MODIFY `id_lugar` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id_pago` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `promociones`
--
ALTER TABLE `promociones`
  MODIFY `id_promocion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `publicaciones`
--
ALTER TABLE `publicaciones`
  MODIFY `id_publicacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `solicitudes`
--
ALTER TABLE `solicitudes`
  MODIFY `id_solicitud` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `tarifas`
--
ALTER TABLE `tarifas`
  MODIFY `id_tarifa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `vigencias`
--
ALTER TABLE `vigencias`
  MODIFY `id_vigencia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `cuentas_negocio`
--
ALTER TABLE `cuentas_negocio`
  ADD CONSTRAINT `fk_cuentas_lugar` FOREIGN KEY (`id_lugar`) REFERENCES `lugares` (`id_lugar`) ON DELETE CASCADE;

--
-- Filtros para la tabla `fotografias`
--
ALTER TABLE `fotografias`
  ADD CONSTRAINT `fk_fotografias_lugar` FOREIGN KEY (`id_lugar`) REFERENCES `lugares` (`id_lugar`) ON DELETE CASCADE;

--
-- Filtros para la tabla `lugares`
--
ALTER TABLE `lugares`
  ADD CONSTRAINT `fk_lugares_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`),
  ADD CONSTRAINT `fk_lugares_solicitud` FOREIGN KEY (`id_solicitud_origen`) REFERENCES `solicitudes` (`id_solicitud`) ON DELETE SET NULL;

--
-- Filtros para la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD CONSTRAINT `fk_pagos_admin` FOREIGN KEY (`id_administrador_confirmacion`) REFERENCES `administradores` (`id_administrador`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_pagos_lugar` FOREIGN KEY (`id_lugar`) REFERENCES `lugares` (`id_lugar`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pagos_tarifa` FOREIGN KEY (`id_tarifa`) REFERENCES `tarifas` (`id_tarifa`);

--
-- Filtros para la tabla `promociones`
--
ALTER TABLE `promociones`
  ADD CONSTRAINT `fk_promos_lugar` FOREIGN KEY (`id_lugar`) REFERENCES `lugares` (`id_lugar`) ON DELETE CASCADE;

--
-- Filtros para la tabla `publicaciones`
--
ALTER TABLE `publicaciones`
  ADD CONSTRAINT `fk_publicaciones_admin` FOREIGN KEY (`id_administrador_aprobacion`) REFERENCES `administradores` (`id_administrador`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_publicaciones_lugar` FOREIGN KEY (`id_lugar`) REFERENCES `lugares` (`id_lugar`) ON DELETE CASCADE;

--
-- Filtros para la tabla `solicitudes`
--
ALTER TABLE `solicitudes`
  ADD CONSTRAINT `fk_solicitudes_admin` FOREIGN KEY (`id_administrador_revision`) REFERENCES `administradores` (`id_administrador`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_solicitudes_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`);

--
-- Filtros para la tabla `tarifas`
--
ALTER TABLE `tarifas`
  ADD CONSTRAINT `fk_tarifas_admin` FOREIGN KEY (`id_administrador_registro`) REFERENCES `administradores` (`id_administrador`) ON DELETE SET NULL;

--
-- Filtros para la tabla `vigencias`
--
ALTER TABLE `vigencias`
  ADD CONSTRAINT `fk_vigencias_lugar` FOREIGN KEY (`id_lugar`) REFERENCES `lugares` (`id_lugar`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_vigencias_pago` FOREIGN KEY (`id_pago`) REFERENCES `pagos` (`id_pago`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
