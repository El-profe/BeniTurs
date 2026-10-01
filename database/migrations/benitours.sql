-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 27-09-2026 a las 19:59:51
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
-- Base de datos: `benitours`
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
(1, 'Administrador BeniTurs', 'admin', 'admin@beniturs.bo', '$2y$10$cVhgQt3yarRcRUgKKPtcRewyfsts/78yiAfxEgq7w1Alux9EKyKBm', 1, '2026-09-25 08:30:40', '2026-09-24 14:06:53');

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
(1, 'Gastronomía y Sabores', 'gastronomia', 'Restaurantes, comida típica beniana, keoperí y pescados de río.', 'bi-egg-fried', 'COMERCIAL', 1, '2026-09-24 14:06:53'),
(2, 'Hoteles y Alojamientos', 'hoteles-alojamientos', 'Hoteles, hostales, cabañas y eco-lodges en el Beni.', 'bi-building', 'COMERCIAL', 1, '2026-09-24 14:06:53'),
(3, 'Bares y Vida Nocturna', 'vida-nocturna', 'Bares, pubs, discotecas y centros de esparcimiento nocturno.', 'bi-cup-straw', 'COMERCIAL', 1, '2026-09-24 14:06:53'),
(4, 'Atractivos y Naturaleza', 'atractivos-turisticos', 'Plazas históricas, lagunas naturales, museos y áreas protegidas.', 'bi-compass', 'PUBLICO', 1, '2026-09-24 14:06:53'),
(5, 'Balnearios y Recreación', 'balnearios-recreacion', 'Balnearios ecológicos, piscinas y puertos turísticos sobre ríos.', 'bi-water', 'COMERCIAL', 1, '2026-09-24 14:06:53'),
(8, 'Museos y Patrimonio Cultural', 'museos-y-patrimonio-cultural', 'Centro científico y turístico de referencia internacional, considerado la tercera colección de peces de agua dulce más grande de Sudamérica. Alberga cientos de especies acuáticas nativas de la cuenca amazónica y los ríos del Beni, exhibiendo ejemplares em', 'bi-bank', 'PUBLICO', 1, '2026-09-25 06:45:41');

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
(1, 1, 'pizza_tat_u_1_c0fcca', 'roblesguamayojonathan@gmail.com', '$2y$10$0rWT.lKJHaAYrXacL132o.xDA0bp5ehm0nm2IqwnsInFIxRSKGEVe', 1, '2026-09-24 20:09:24', '2026-09-25 00:07:40'),
(2, 8, 'hotel_don_bernardo_2_87380f', 'roblesguamayojonathan@gmail.com', '$2y$10$5tEy8fwtcPuR1JzWEsVT4eBH4ebXC2h7VAyJPS.CttFtv7n8zzNca', 1, '2026-09-25 03:06:20', '2026-09-25 07:02:44'),
(3, 9, 'karaoke_la_mentirosa_show_3_775dea', 'roblesguamayojonathan@gmail.com', '$2y$10$Zx2hdrQvPP.ON0SQPPVnV.LRpWol7gI.otYufiPnDYvuoRpo6OgNK', 1, '2026-09-25 03:43:34', '2026-09-25 07:24:05'),
(4, 11, 'restaurante_test_efectivo_6ab69077e7803_f5f72f', 'test_comercio_6ab69077e7809@ejemplo.bo', '$2y$10$f7VdpnbOdxTv2ovDtG.qSeYx9gvgskhvWAQG5QezWaa45yhqasND6', 1, '2026-09-25 11:40:42', '2026-09-25 15:17:12'),
(5, 12, 'restaurante_test_efectivo_6ab6909a46fb6_b0f3a1', 'test_comercio_6ab6909a46fba@ejemplo.bo', '$2y$10$bBCUFJTxjkyu5bfLNM6A6eyq8o1XGpsHPwRRCvfwrBfTbgQj7XcWm', 1, NULL, '2026-09-25 15:17:46'),
(6, 13, 'restaurante_test_efectivo_6ab690a7624fb_77abee', 'test_comercio_6ab690a7624ff@ejemplo.bo', '$2y$10$0MFGuZA3yt6BeTd4Pg.UAu/b.JTIUQ.ivjDETptxjp5JcKFDIlSOy', 1, NULL, '2026-09-25 15:17:59'),
(7, 15, 'restaurante_test_efectivo_6ab690b7c49d4_29cb6f', 'test_comercio_6ab690b7c49d8@ejemplo.bo', '$2y$10$WOAdT2ZQNYktDivZIomKWOHfCS93MAgUtNguVrtPY1Ncylz/qQuFG', 1, NULL, '2026-09-25 15:18:15'),
(8, 16, 'caf_e_efectivo_6ab690b811e22_5_de6906', 'juan_6ab690b811e26@cafetest.bo', '$2y$10$K.WYDsWB.GgP/b/0sZEjh.kDA6BJgA6uLPRj0tdgylNkFTayVZUSC', 1, NULL, '2026-09-25 15:18:16'),
(9, 17, 'restaurante_test_efectivo_6ab690c830bce_1717b3', 'test_comercio_6ab690c830bd2@ejemplo.bo', '$2y$10$O.dMibBAyxdR3yrHg1DQHuOmJaJWqN5RIeWdOJnrBwlpCFIWKJjki', 1, NULL, '2026-09-25 15:18:32'),
(10, 18, 'caf_e_efectivo_6ab690c8770d4_6_5a04ca', 'juan_6ab690c8770d8@cafetest.bo', '$2y$10$.3QGFbGNInsXnuHx5e4M/.cwK/DkzFtUZ2rDvPrAN77uVKjTWvTJe', 1, NULL, '2026-09-25 15:18:32'),
(11, 19, 'churrasquer_ia_el_pacumuto_trinitario_707d8c', 'roblesguamayojonathan@gmail.com', '$2y$10$GEwUwmIH3TJO.QTtlqA4j.28YKgI6Sj8RzkX.GurrGG34YxJAt33G', 1, '2026-09-25 11:55:02', '2026-09-25 15:54:18');

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
(1, 1, 'lugar_c569141a67a8ba4b_1790295002.jpg', 'WhatsApp Image 2026-09-24 at 5.50.25 AM.jpeg', 'image/jpeg', 211211, 1, 1, '2026-09-25 00:10:02'),
(2, 2, 'lugar_8570b95699c5b12b_1790310877.png', 'Imagen de ChatGPT 25 sept 2026, 00_27_25.png', 'image/png', 3347786, 1, 1, '2026-09-25 04:34:37'),
(3, 2, 'lugar_83571d91d14327cd_1790310877.png', 'Imagen de ChatGPT 25 sept 2026, 00_27_16.png', 'image/png', 3407218, 0, 1, '2026-09-25 04:34:37'),
(4, 2, 'lugar_fafdb6c197e08fa6_1790310877.png', 'Imagen de ChatGPT 25 sept 2026, 00_26_46-2.png', 'image/png', 3230588, 0, 1, '2026-09-25 04:34:37'),
(5, 2, 'lugar_4ec5d5e5d7f01c23_1790310877.png', 'plaza principal.png', 'image/png', 3195883, 0, 1, '2026-09-25 04:34:37'),
(6, 3, 'lugar_b7e253b7a1da8b23_1790311818.png', 'Imagen de ChatGPT 25 sept 2026, 00_47_00.png', 'image/png', 2894351, 1, 1, '2026-09-25 04:50:18'),
(7, 3, 'lugar_044242d061529e59_1790311818.png', 'Imagen de ChatGPT 25 sept 2026, 00_46_52-3.png', 'image/png', 2742211, 0, 1, '2026-09-25 04:50:18'),
(8, 3, 'lugar_3e8e38932f9a769b_1790311818.png', 'Imagen de ChatGPT 25 sept 2026, 00_46_51-2.png', 'image/png', 2728623, 0, 1, '2026-09-25 04:50:18'),
(9, 3, 'lugar_481f3d0c400ede85_1790311818.png', 'Imagen de ChatGPT 25 sept 2026, 00_46_32.png', 'image/png', 2325884, 0, 1, '2026-09-25 04:50:18'),
(10, 4, 'lugar_93f0c8ef051a2dda_1790312766.png', 'Imagen de ChatGPT 25 sept 2026, 01_04_53-7.png', 'image/png', 2885122, 1, 1, '2026-09-25 05:06:06'),
(11, 4, 'lugar_cb3cb3d1345d78ba_1790312766.png', 'Imagen de ChatGPT 25 sept 2026, 01_04_51-6.png', 'image/png', 3533490, 0, 1, '2026-09-25 05:06:06'),
(12, 4, 'lugar_76f91ab833cce216_1790312766.png', 'Imagen de ChatGPT 25 sept 2026, 01_04_50-5.png', 'image/png', 3578940, 0, 1, '2026-09-25 05:06:06'),
(13, 4, 'lugar_5b09e5d0da57743a_1790312766.png', 'Imagen de ChatGPT 25 sept 2026, 01_04_49-4.png', 'image/png', 3534242, 0, 1, '2026-09-25 05:06:06'),
(14, 4, 'lugar_cb086037da56b4ab_1790312766.png', 'Imagen de ChatGPT 25 sept 2026, 01_04_46-3.png', 'image/png', 3523599, 0, 1, '2026-09-25 05:06:06'),
(15, 4, 'lugar_1a793c9fe7d868dd_1790312766.png', 'Imagen de ChatGPT 25 sept 2026, 01_04_43-2.png', 'image/png', 3622956, 0, 1, '2026-09-25 05:06:06'),
(16, 5, 'lugar_a7bcca5a02671869_1790314290.jpg', 'Gemini_Generated_Image_u181nbu181nbu181.jpg', 'image/jpeg', 3975303, 1, 1, '2026-09-25 05:31:30'),
(17, 5, 'lugar_b3c1dd47fdfa981b_1790314290.jpg', 'Gemini_Generated_Image_ooft7oooft7oooft.jpg', 'image/jpeg', 3521257, 0, 1, '2026-09-25 05:31:30'),
(18, 5, 'lugar_f3332d340c6b601d_1790314290.jpg', 'Gemini_Generated_Image_t8rmv5t8rmv5t8rm.jpg', 'image/jpeg', 3578261, 0, 1, '2026-09-25 05:31:30'),
(19, 6, 'lugar_74aacedee0a0b235_1790315991.jpg', 'Gemini_Generated_Image_3s7gm23s7gm23s7g.jpg', 'image/jpeg', 3884769, 0, 1, '2026-09-25 05:59:51'),
(20, 6, 'lugar_81a4caeb82c82f95_1790315991.jpg', 'Gemini_Generated_Image_xdjvhxxdjvhxxdjv.jpg', 'image/jpeg', 4084994, 0, 1, '2026-09-25 05:59:51'),
(21, 6, 'lugar_7e61166df8f54849_1790315991.jpg', 'Gemini_Generated_Image_fw1tahfw1tahfw1t.jpg', 'image/jpeg', 3549344, 1, 1, '2026-09-25 05:59:51'),
(22, 6, 'lugar_5d84445a1b5ac2e5_1790317116.jpg', 'Gemini_Generated_Image_l0bcael0bcael0bc.jpg', 'image/jpeg', 4247479, 0, 1, '2026-09-25 06:18:36'),
(23, 6, 'lugar_d2cc439c043620e0_1790317116.jpg', 'afadfa.jpg', 'image/jpeg', 3676778, 0, 1, '2026-09-25 06:18:36'),
(24, 7, 'lugar_a56c88195ba62875_1790319478.jpg', 'pci.jpg', 'image/jpeg', 2983149, 0, 1, '2026-09-25 06:57:58'),
(25, 7, 'lugar_2cf4c818d0c03cd8_1790319478.jpg', 'Gemini_Generated_Image_h8r41h8r41h8r41h.jpg', 'image/jpeg', 2727254, 1, 1, '2026-09-25 06:57:58'),
(26, 7, 'lugar_db2d11243b939100_1790319478.jpg', 'Gemini_Generated_Image_8aekmj8aekmj8aek.jpg', 'image/jpeg', 2761385, 0, 1, '2026-09-25 06:57:58'),
(27, 8, 'lugar_380b2790669cca97_1790320317.jpg', 'dad.jpg', 'image/jpeg', 3260187, 1, 1, '2026-09-25 07:11:57'),
(28, 8, 'lugar_d37b6f2642459f14_1790320337.jpg', 'Gemini_Generated_Image_g6prl1g6prl1g6pr.jpg', 'image/jpeg', 3480398, 0, 1, '2026-09-25 07:12:17'),
(29, 8, 'lugar_e22a741810a382ce_1790320343.jpg', 'adfad.jpg', 'image/jpeg', 3424173, 0, 1, '2026-09-25 07:12:23'),
(30, 8, 'lugar_6b4fdc32056ed0ab_1790320351.jpg', 'Gemini_Generated_Image_nbid9lnbid9lnbid.jpg', 'image/jpeg', 3442442, 0, 1, '2026-09-25 07:12:31'),
(31, 9, 'lugar_3bbc2b2b4d8e0623_1790321153.jpg', 'Gemini_Generated_Image_k8lt68k8lt68k8lt.jpg', 'image/jpeg', 2733821, 1, 1, '2026-09-25 07:25:53'),
(33, 9, 'lugar_2b33619cb6e332b6_1790321169.jpg', 'Gemini_Generated_Image_evf1fgevf1fgevf1.jpg', 'image/jpeg', 2657677, 0, 1, '2026-09-25 07:26:09'),
(34, 9, 'lugar_be08ab55f39b9c05_1790321174.jpg', 'Gemini_Generated_Image_gqui3kgqui3kgqui.jpg', 'image/jpeg', 2332012, 0, 1, '2026-09-25 07:26:14'),
(35, 10, 'lugar_42f7ebd1a33fa9ed_1790347627.jpg', 'Gemini_Generated_Image_b1q264b1q264b1q2.jpg', 'image/jpeg', 3038597, 1, 1, '2026-09-25 14:47:07'),
(36, 11, 'lugar_c138c3d35081f14e_1790350496.jpg', 'cf7fa8a0-48de-45e9-b892-736e5b353460.jpg', 'image/jpeg', 598930, 0, 1, '2026-09-25 15:34:56'),
(37, 11, 'lugar_6876124c80a90c35_1790350496.jpg', '2c92278b-cc8c-46e4-af61-d0374b7b0623.jpg', 'image/jpeg', 901705, 0, 1, '2026-09-25 15:34:56'),
(38, 11, 'lugar_9c5597aa48205b05_1790350496.jpg', '10f5bbaa-cf6b-46f2-88ad-91bb7e8d1c6f.jpg', 'image/jpeg', 469366, 1, 1, '2026-09-25 15:34:56'),
(39, 19, 'lugar_158813ff67a4c1d6_1790351658.jpg', 'WhatsApp Image 2026-09-25 at 11.51.30 AM (3).jpeg', 'image/jpeg', 430590, 0, 1, '2026-09-25 15:54:18'),
(40, 19, 'lugar_3b829222d50fecd3_1790351658.jpg', 'WhatsApp Image 2026-09-25 at 11.51.30 AM (2).jpeg', 'image/jpeg', 614146, 1, 1, '2026-09-25 15:54:18'),
(41, 19, 'lugar_6ed07f9711744a8c_1790351658.jpg', 'WhatsApp Image 2026-09-25 at 11.51.30 AM (1).jpeg', 'image/jpeg', 367737, 0, 1, '2026-09-25 15:54:18'),
(42, 19, 'lugar_6df3f3cb59ee719e_1790351658.jpg', 'WhatsApp Image 2026-09-25 at 11.51.30 AM.jpeg', 'image/jpeg', 521732, 0, 1, '2026-09-25 15:54:18'),
(43, 19, 'lugar_7041437caf431401_1790351658.jpg', 'cf7fa8a0-48de-45e9-b892-736e5b353460.jpg', 'image/jpeg', 598930, 0, 1, '2026-09-25 15:54:18');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lugares`
--

CREATE TABLE `lugares` (
  `id_lugar` int(11) NOT NULL,
  `id_municipio` int(11) NOT NULL DEFAULT 1,
  `id_categoria` int(11) NOT NULL,
  `id_solicitud_origen` int(11) DEFAULT NULL,
  `nombre` varchar(150) NOT NULL,
  `slug` varchar(160) NOT NULL,
  `tipo_lugar` enum('PUBLICO','COMERCIAL') NOT NULL,
  `descripcion` text NOT NULL,
  `direccion` varchar(255) NOT NULL,
  `referencia_ubicacion` varchar(255) DEFAULT NULL,
  `latitud` decimal(10,8) DEFAULT NULL,
  `longitud` decimal(11,8) DEFAULT NULL,
  `telefono_contacto` varchar(50) DEFAULT NULL,
  `whatsapp_contacto` varchar(50) DEFAULT NULL,
  `email_contacto` varchar(120) DEFAULT NULL,
  `horario_atencion` varchar(200) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `lugares`
--

INSERT INTO `lugares` (`id_lugar`, `id_municipio`, `id_categoria`, `id_solicitud_origen`, `nombre`, `slug`, `tipo_lugar`, `descripcion`, `direccion`, `referencia_ubicacion`, `latitud`, `longitud`, `telefono_contacto`, `whatsapp_contacto`, `email_contacto`, `horario_atencion`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 'Pizza Tatú', 'pizza-tat-u', 'COMERCIAL', 'Pizzería tradicional en Trinidad reconocida por sus pizzas artesanales al horno, masa crocante y generosa cobertura de queso y condimentos. Cuenta con un ambiente familiar climatizado, variedad de sabores clásicos y especiales, y servicio rápido de delivery para disfrutar en casa.', 'Av/27 de mayo', 'Frente a la cancha 27 de mayo', -14.82989200, -64.91221800, '67355113', '67355113', 'roblesguamayojonathan@gmail.com', 'Almuerzo y Cena (11:30 a 15:00 y 18:30 a 23:30)', '2026-09-25 00:07:39', '2026-09-25 00:10:30'),
(2, 1, 4, NULL, 'Plaza Principal Mariscal José Ballivián', 'plaza-principal-mariscal-jos-e-ballivi-an', 'PUBLICO', 'La Plaza Principal Mariscal José Ballivián es el corazón social e histórico de Trinidad. Rodeada por la imponente Catedral, edificios coloniales y una exuberante vegetación tropical, es el punto de encuentro ideal para pasear bajo la sombra de sus árboles, disfrutar de la brisa beniana y saborear la gastronomía típica en sus tradicionales quioscos.', 'Av/ Germán Busch', 'Frente a la Catedral de la Santísimas Trinidad', -14.83497500, -64.90414300, '', '', '', 'Atención 24 Horas', '2026-09-25 04:34:37', '2026-09-25 04:34:37'),
(3, 1, 4, NULL, 'Plaza Pompeya', 'plaza-pompeya', 'PUBLICO', 'La Plaza Pompeya es un referente de la vida comunitaria y tradicional de Trinidad. Con sus jardines, amplias caminerías y la cercanía a su parroquia, combina la calma barrial con una variada oferta gastronómica local al caer la tarde.', 'C/Tarope entre C/Sécure', 'Frente a la Parroquia de Pompeya', -14.84046500, -64.90131600, '', '', '', 'Atención 24 Horas', '2026-09-25 04:50:18', '2026-09-25 04:50:18'),
(4, 1, 4, NULL, 'Parque Ecológico El Pantanal', 'parque-ecol-ogico-el-pantanal', 'PUBLICO', 'Espacio ecológico y recreativo que recrea el ecosistema de humedales del oriente boliviano. El Parque El Pantanal destaca por sus lagunas naturales cubiertas de vegetación acuática, pasarelas, áreas verdes y esculturas temáticas de fauna beniana, siendo una parada obligada para pasear y disfrutar del atardecer trinitario.', 'Av/Profesor José Chávez Suaréz Entre', 'Frente al Monumento a la Madre Trinitaria', -14.82798200, -64.91418100, '', '', '', 'Atención ( 08:00 - 18:00 )', '2026-09-25 05:06:06', '2026-09-25 05:06:06'),
(5, 1, 4, NULL, 'Plazuela Fátima', 'plazuela-f-atima', 'PUBLICO', 'Emblemático punto de encuentro del barrio Fátima en Trinidad. Es un espacio tranquilo con áreas verdes, sombra generosa e infraestructura recreativa, ideal para paseos cotidianos y la convivencia familiar en un entorno plenamente beniano.', 'C/ Rene Ibáñez Aponte entre C/Hermanos Pradel Vaca', '', -14.83318300, -64.89690800, '', '', '', 'Atención 24 Horas', '2026-09-25 05:31:30', '2026-09-25 05:31:30'),
(6, 1, 4, NULL, 'Plaza Guillermo \"Chichi\" Chavez Zambrana', 'plaza-guillermo-chichi-chavez-zambrana', 'PUBLICO', 'spacio público temático en Trinidad dedicado al rescate de la memoria cultural y leyendas populares benianas, en honor al cantautor Guillermo \"Chichi\" Chávez. Destaca por sus esculturas alusivas a seres del folklore regional (como el Guajojó o la Llorona), caminerías iluminadas y áreas de descanso, ideales para visitas familiares y fotografías.', 'Av/ Los Tajibos entren Av/ Fabián Monasterio Claure', '', -14.83620100, -64.89900500, '', '', '', 'Atención 24 Horas', '2026-09-25 05:59:51', '2026-09-25 05:59:51'),
(7, 1, 8, NULL, 'Casa de la Cultura del Beni \"Gilfredo Cortés Candia\"', 'casa-de-la-cultura-del-beni-gilfredo-cort-es-candia', 'PUBLICO', 'Picentro artístico e histórico de Trinidad, la Casa de la Cultura del Beni resguarda y promueve el patrimonio cultural regional a través de exhibiciones arqueológicas de Moxos, galerías de arte, muestras folclóricas y eventos literarios, siendo una parada esencial para sumergirse en las raíces benianas.', 'Av/ Mariscal Antonio José de  Sucre entre Av/ Cipriano Barace', 'Frente a Banco Bisa', -14.83301100, -64.90361100, '', '', '', 'Atención ( 08:00 - 18:00 )', '2026-09-25 06:57:58', '2026-09-25 06:57:58'),
(8, 1, 2, 2, 'Hotel Don Bernardo', 'hotel-don-bernardo', 'COMERCIAL', 'El Hotel Don Bernardo es una alternativa clásica de alojamiento en Trinidad, convenientemente ubicada sobre la avenida 18 de Noviembre. Ofrece un ambiente práctico, acogedor y funcional para viajeros de negocios y turistas que buscan una estancia cómoda, con fácil acceso al centro de la ciudad y a los principales puntos comerciales y de transporte.', 'Av/18 de Noviembre', NULL, -14.83376000, -64.90628100, '67355113', '67355113', 'roblesguamayojonathan@gmail.com', 'Atención 24 Horas', '2026-09-25 07:02:44', '2026-09-25 07:07:12'),
(9, 1, 3, 3, 'Karaoke La Mentirosa Show', 'karaoke-la-mentirosa-show', 'COMERCIAL', 'Espacio nocturno y de fiesta en Trinidad ideal para compartir entre amigos. Destaca por su ambiente animado, pista de baile, barra de bebidas y una cartelera musical variada pensada para divertirse el fin de semana.', 'C/ Antonio Vaca Diez', 'Frente a la Plaza Principal', -14.83430900, -64.90422700, '67355113', '67355113', 'roblesguamayojonathan@gmail.com', 'Noche / Bar (21:00 a 04:00)', '2026-09-25 07:24:05', '2026-09-25 07:25:28'),
(10, 1, 1, NULL, 'Salteñeria Chingola', 'salte-neria-chingola', 'COMERCIAL', 'Salteñería Chingola es una parada emblemática para quienes buscan disfrutar del auténtico sabor mañanero en Trinidad. Reconocida por su receta casera, ofrece salteñas de masa dorada y crocante, rellenas con un jigote abundante, bien condimentado y rebosante de jugo, ideales para acompañar con un refresco tradicional a media mañana.', 'Av/ 6 de agosto entre Av/ 18 de Noviembre', '', -14.83544400, -64.90664300, '67355113', '67355113', 'roblesguamayojonathan@gmail.com', 'Atención ( 08:00 - 12:00 )', '2026-09-25 14:47:07', '2026-09-25 14:47:07'),
(11, 1, 1, NULL, 'Churrasquería El Cuadril Trinidad', 'restaurante-test-efectivo-6ab69077e7803', 'COMERCIAL', 'Churrasquería El Cuadril Trinidad es un referente para los amantes de las buenas carnes a la brasa en Trinidad. Ubicada sobre la tradicional Av. 18 de Noviembre, destaca por ofrecer cortes jugosos al punto exacto, acompañados de guarniciones clásicas como arroz con queso, yuca frita y frescas ensaladas, todo en un ambiente familiar y cálido ideal para almuerzos de domingo o cenas entre amigos.', 'Av./ 18 de Noviembre entre C./ Felix Pinto', NULL, -14.82952600, -64.90590000, '67355113', '67355113', 'test_comercio_6ab69077e7809@ejemplo.bo', '11:00 am. - 15:00 pm. / 18:30 pm. - 22:00 pm.', '2026-09-25 15:17:11', '2026-09-25 15:42:47'),
(12, 1, 1, NULL, 'Restaurante Test Efectivo 6ab6909a46fb6', 'restaurante-test-efectivo-6ab6909a46fb6', 'COMERCIAL', 'Lugar de prueba para alta directa con cobro en mano.', 'Av. Bolívar Nº 100', 'Frente a la plaza', -14.83330000, -64.90000000, '76891234', '76891234', 'test_comercio_6ab6909a46fba@ejemplo.bo', '08:00 - 20:00', '2026-09-25 15:17:46', '2026-09-25 15:17:46'),
(13, 1, 1, NULL, 'Restaurante Test Efectivo 6ab690a7624fb', 'restaurante-test-efectivo-6ab690a7624fb', 'COMERCIAL', 'Lugar de prueba para alta directa con cobro en mano.', 'Av. Bolívar Nº 100', 'Frente a la plaza', -14.83330000, -64.90000000, '76891234', '76891234', 'test_comercio_6ab690a7624ff@ejemplo.bo', '08:00 - 20:00', '2026-09-25 15:17:59', '2026-09-25 15:17:59'),
(15, 1, 1, NULL, 'Restaurante Test Efectivo 6ab690b7c49d4', 'restaurante-test-efectivo-6ab690b7c49d4', 'COMERCIAL', 'Lugar de prueba para alta directa con cobro en mano.', 'Av. Bolívar Nº 100', 'Frente a la plaza', -14.83330000, -64.90000000, '76891234', '76891234', 'test_comercio_6ab690b7c49d8@ejemplo.bo', '08:00 - 20:00', '2026-09-25 15:18:15', '2026-09-25 15:18:15'),
(16, 1, 1, 5, 'Café Efectivo 6ab690b811e22', 'caf-e-efectivo-6ab690b811e22', 'COMERCIAL', 'Cafetería tradicional beniana.', 'Calle Sucre 45', NULL, NULL, NULL, '71234567', '71234567', 'juan_6ab690b811e26@cafetest.bo', '', '2026-09-25 15:18:16', '2026-09-25 15:18:16'),
(17, 1, 1, NULL, 'Restaurante Test Efectivo 6ab690c830bce', 'restaurante-test-efectivo-6ab690c830bce', 'COMERCIAL', 'Lugar de prueba para alta directa con cobro en mano.', 'Av. Bolívar Nº 100', 'Frente a la plaza', -14.83330000, -64.90000000, '76891234', '76891234', 'test_comercio_6ab690c830bd2@ejemplo.bo', '08:00 - 20:00', '2026-09-25 15:18:32', '2026-09-25 15:18:32'),
(18, 1, 1, 6, 'Café Efectivo 6ab690c8770d4', 'caf-e-efectivo-6ab690c8770d4', 'COMERCIAL', 'Cafetería tradicional beniana.', 'Calle Sucre 45', NULL, NULL, NULL, '71234567', '71234567', 'juan_6ab690c8770d8@cafetest.bo', '', '2026-09-25 15:18:32', '2026-09-25 15:18:32'),
(19, 1, 1, NULL, 'Churrasquería El Pacumuto Trinitario', 'churrasquer-ia-el-pacumuto-trinitario', 'COMERCIAL', 'Churrasqueria El Pacumuto Trinitario es una parada culinaria emblemática para degustar uno de los platillos más representativos de la gastronomía beniana. Especializada en brochetas de carne tierna y sazonada cocinadas al calor de las brasas, complementa su oferta con guarniciones tradicionales como el infaltable arroz con queso, yuca hervida o frita y ensaladas frescas, todo dentro de un ambiente relajado y de auténtica calidez trinitaria.', 'Av./ Santa Cruz', '', -14.82926100, -64.90667800, '67355113', '67355113', 'roblesguamayojonathan@gmail.com', 'Atención ( 12:00 pm - 15:00 pm. / 18:00 pm. - 22:00 pm. )', '2026-09-25 15:54:18', '2026-09-25 15:54:18');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `municipios`
--

CREATE TABLE `municipios` (
  `id_municipio` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `provincia` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `latitud_defecto` decimal(10,8) DEFAULT NULL,
  `longitud_defecto` decimal(11,8) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `municipios`
--

INSERT INTO `municipios` (`id_municipio`, `nombre`, `provincia`, `slug`, `latitud_defecto`, `longitud_defecto`, `activo`, `created_at`) VALUES
(1, 'Trinidad', 'Cercado', 'trinidad', -14.83333300, -64.90000000, 1, '2026-09-24 23:59:27');

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
  `metodo_pago` varchar(50) NOT NULL DEFAULT 'Transferencia bancaria / QR',
  `numero_comprobante` varchar(100) DEFAULT NULL,
  `comprobante_archivo` varchar(255) DEFAULT NULL,
  `es_registro_inicial` tinyint(1) NOT NULL DEFAULT 0,
  `estado` enum('PENDIENTE','CONFIRMADO','ANULADO') NOT NULL DEFAULT 'PENDIENTE',
  `id_administrador_confirmacion` int(11) DEFAULT NULL,
  `fecha_confirmacion` datetime DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pagos`
--

INSERT INTO `pagos` (`id_pago`, `id_lugar`, `id_tarifa`, `monto`, `meses_duracion`, `fecha_pago_declarada`, `metodo_pago`, `numero_comprobante`, `comprobante_archivo`, `es_registro_inicial`, `estado`, `id_administrador_confirmacion`, `fecha_confirmacion`, `observaciones`, `created_at`) VALUES
(1, 1, 1, 250.00, 1, '2026-09-24', 'Transferencia bancaria / QR', '', 'comprobante_be9b9028e3853c33c03c7ad858b8816a.jpg', 1, 'CONFIRMADO', 1, '2026-09-24 20:07:40', NULL, '2026-09-25 00:07:40'),
(2, 8, 1, 250.00, 1, '2026-09-25', 'Transferencia bancaria / QR', '914033553', 'comprobante_e1a0842c00148b2d1d7f09a0f2928594.jpg', 1, 'CONFIRMADO', 1, '2026-09-25 03:02:44', NULL, '2026-09-25 07:02:44'),
(3, 9, 1, 250.00, 1, '2026-09-25', 'Transferencia bancaria / QR', '54471454', 'comprobante_33a2f8e6bf1d97db1e299045c6fb8d43.jpg', 1, 'CONFIRMADO', 1, '2026-09-25 03:24:05', NULL, '2026-09-25 07:24:05'),
(4, 10, 1, 250.00, 1, '2026-09-25', 'Efectivo en oficina', '914033553', NULL, 0, 'CONFIRMADO', 1, '2026-09-25 10:47:52', '', '2026-09-25 14:47:37'),
(5, 11, 1, 250.00, 1, '2026-09-25', 'EFECTIVO', 'REC-EFEC-20260925-171711', NULL, 1, 'CONFIRMADO', 1, '2026-09-25 11:17:12', NULL, '2026-09-25 15:17:12'),
(6, 12, 1, 250.00, 1, '2026-09-25', 'EFECTIVO', 'REC-EFEC-20260925-171746', NULL, 1, 'CONFIRMADO', 1, '2026-09-25 11:17:46', NULL, '2026-09-25 15:17:46'),
(7, 13, 1, 250.00, 1, '2026-09-25', 'EFECTIVO', 'REC-EFEC-20260925-171759', NULL, 1, 'CONFIRMADO', 1, '2026-09-25 11:17:59', NULL, '2026-09-25 15:17:59'),
(8, 15, 1, 250.00, 1, '2026-09-25', 'EFECTIVO', 'REC-EFEC-20260925-171815', NULL, 1, 'CONFIRMADO', 1, '2026-09-25 11:18:15', NULL, '2026-09-25 15:18:15'),
(9, 16, 1, 250.00, 1, '2026-09-25', 'Efectivo', 'REC-OFICINA-9988', NULL, 1, 'CONFIRMADO', 1, '2026-09-25 11:18:16', NULL, '2026-09-25 15:18:16'),
(10, 17, 1, 250.00, 1, '2026-09-25', 'EFECTIVO', 'REC-EFEC-20260925-171832', NULL, 1, 'CONFIRMADO', 1, '2026-09-25 11:18:32', NULL, '2026-09-25 15:18:32'),
(11, 18, 1, 250.00, 1, '2026-09-25', 'Efectivo', 'REC-OFICINA-9988', NULL, 1, 'CONFIRMADO', 1, '2026-09-25 11:18:32', NULL, '2026-09-25 15:18:32'),
(12, 11, 1, 250.00, 1, '2026-08-29', 'Efectivo en oficina', '9823451', NULL, 0, 'CONFIRMADO', 1, '2026-09-25 11:37:53', '', '2026-09-25 15:37:41'),
(13, 19, 1, 250.00, 1, '2026-09-25', 'EFECTIVO', '547184', NULL, 1, 'CONFIRMADO', 1, '2026-09-25 11:54:18', NULL, '2026-09-25 15:54:18');

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
(1, 1, 1, 1, NULL, 1, '2026-09-24 20:07:39', '2026-09-25 00:07:39', '2026-09-25 00:07:39'),
(2, 2, 1, 1, NULL, 1, '2026-09-25 00:34:37', '2026-09-25 04:34:37', '2026-09-25 04:34:37'),
(3, 3, 1, 1, NULL, 1, '2026-09-25 00:50:18', '2026-09-25 04:50:18', '2026-09-25 04:50:18'),
(4, 4, 1, 1, NULL, 1, '2026-09-25 01:06:06', '2026-09-25 05:06:06', '2026-09-25 05:06:06'),
(5, 5, 1, 1, NULL, 1, '2026-09-25 01:31:30', '2026-09-25 05:31:30', '2026-09-25 05:31:30'),
(6, 6, 1, 1, NULL, 1, '2026-09-25 01:59:51', '2026-09-25 05:59:51', '2026-09-25 05:59:51'),
(7, 7, 1, 1, NULL, 1, '2026-09-25 02:57:58', '2026-09-25 06:57:58', '2026-09-25 06:57:58'),
(8, 8, 1, 1, NULL, 1, '2026-09-25 03:02:44', '2026-09-25 07:02:44', '2026-09-25 07:02:44'),
(9, 9, 1, 1, NULL, 1, '2026-09-25 03:24:05', '2026-09-25 07:24:05', '2026-09-25 07:24:05'),
(10, 10, 1, 1, NULL, 1, '2026-09-25 10:47:07', '2026-09-25 14:47:07', '2026-09-25 14:47:07'),
(11, 16, 1, 0, 'Deshabilitado por administración', 1, '2026-09-25 17:18:16', '2026-09-25 15:18:16', '2026-09-25 15:56:44'),
(12, 18, 1, 0, 'Deshabilitado por administración', 1, '2026-09-25 17:18:32', '2026-09-25 15:18:32', '2026-09-25 15:56:38'),
(13, 19, 1, 1, NULL, 1, '2026-09-25 11:54:18', '2026-09-25 15:54:18', '2026-09-25 15:54:18');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitudes`
--

CREATE TABLE `solicitudes` (
  `id_solicitud` int(11) NOT NULL,
  `id_municipio` int(11) NOT NULL DEFAULT 1,
  `id_categoria` int(11) NOT NULL,
  `id_tarifa` int(11) DEFAULT 1,
  `nombre_establecimiento` varchar(150) NOT NULL,
  `descripcion` text NOT NULL,
  `direccion` varchar(255) NOT NULL,
  `horarios` varchar(150) DEFAULT NULL,
  `nombre_solicitante` varchar(120) NOT NULL,
  `telefono_contacto` varchar(30) NOT NULL,
  `email_contacto` varchar(120) DEFAULT NULL,
  `plan_solicitado` varchar(30) NOT NULL DEFAULT 'MENSUAL',
  `metodo_pago` varchar(50) DEFAULT 'QR / Transferencia',
  `monto_declarado` decimal(10,2) NOT NULL DEFAULT 250.00,
  `numero_comprobante` varchar(100) DEFAULT NULL,
  `comprobante_archivo` varchar(255) DEFAULT NULL,
  `estado` enum('PENDIENTE','ACEPTADA','RECHAZADA') NOT NULL DEFAULT 'PENDIENTE',
  `observaciones_admin` text DEFAULT NULL,
  `id_administrador_revision` int(11) DEFAULT NULL,
  `fecha_revision` datetime DEFAULT NULL,
  `id_lugar_creado` int(11) DEFAULT NULL,
  `id_cuenta_creada` int(11) DEFAULT NULL,
  `credenciales_cifradas` text DEFAULT NULL,
  `telegram_message_id` bigint(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `solicitudes`
--

INSERT INTO `solicitudes` (`id_solicitud`, `id_municipio`, `id_categoria`, `id_tarifa`, `nombre_establecimiento`, `descripcion`, `direccion`, `horarios`, `nombre_solicitante`, `telefono_contacto`, `email_contacto`, `plan_solicitado`, `metodo_pago`, `monto_declarado`, `numero_comprobante`, `comprobante_archivo`, `estado`, `observaciones_admin`, `id_administrador_revision`, `fecha_revision`, `id_lugar_creado`, `id_cuenta_creada`, `credenciales_cifradas`, `telegram_message_id`, `created_at`) VALUES
(1, 1, 1, 1, 'Pizza Tatú', 'Pizzería tradicional en Trinidad reconocida por sus pizzas artesanales al horno, masa crocante y generosa cobertura de queso y condimentos. Cuenta con un ambiente familiar climatizado, variedad de sabores clásicos y especiales, y servicio rápido de delivery para disfrutar en casa.', 'Av/27 de mayo', 'Almuerzo y Cena (11:30 a 15:00 y 18:30 a 23:30)', 'Jonathan Alvaro Robles Guamayo', '67355113', 'roblesguamayojonathan@gmail.com', 'MENSUAL', 'QR / Transferencia', 250.00, '', 'comprobante_be9b9028e3853c33c03c7ad858b8816a.jpg', 'ACEPTADA', 'Comprobante verificado; aprovisionamiento completo.', 1, '2026-09-24 20:07:39', 1, 1, NULL, 14, '2026-09-25 00:06:28'),
(2, 1, 2, 1, 'Hotel Don Bernardo', 'El Hotel Don Bernardo es una alternativa clásica de alojamiento en Trinidad, convenientemente ubicada sobre la avenida 18 de Noviembre. Ofrece un ambiente práctico, acogedor y funcional para viajeros de negocios y turistas que buscan una estancia cómoda, con fácil acceso al centro de la ciudad y a los principales puntos comerciales y de transporte.', 'Av/18 de Noviembre', 'Atención 24 Horas', 'Jonathan Alvaro Robles Guamayo', '67355113', 'roblesguamayojonathan@gmail.com', 'MENSUAL', 'QR / Transferencia', 250.00, '914033553', 'comprobante_e1a0842c00148b2d1d7f09a0f2928594.jpg', 'ACEPTADA', 'Comprobante verificado; aprovisionamiento completo.', 1, '2026-09-25 03:02:44', 8, 2, NULL, 15, '2026-09-25 07:01:50'),
(3, 1, 3, 1, 'Karaoke La Mentirosa Show', 'Espacio nocturno y de fiesta en Trinidad ideal para compartir entre amigos. Destaca por su ambiente animado, pista de baile, barra de bebidas y una cartelera musical variada pensada para divertirse el fin de semana.', 'C/ Antonio Vaca Diez', 'Noche / Bar (21:00 a 04:00)', 'Jonathan Alvaro Robles Guamayo', '67355113', 'roblesguamayojonathan@gmail.com', 'MENSUAL', 'QR / Transferencia', 250.00, '54471454', 'comprobante_33a2f8e6bf1d97db1e299045c6fb8d43.jpg', 'ACEPTADA', 'Comprobante verificado; aprovisionamiento completo.', 1, '2026-09-25 03:24:05', 9, 3, NULL, 16, '2026-09-25 07:23:28'),
(4, 1, 1, 1, 'Café Efectivo 6ab690a7a3d52', 'Cafetería tradicional beniana.', 'Calle Sucre 45', '', 'Juan Pérez', '71234567', 'juan_6ab690a7a3d56@cafetest.bo', 'MENSUAL', 'EFECTIVO', 250.00, 'EFECTIVO-EN-OFICINA', NULL, 'RECHAZADA', '', 1, '2026-09-25 11:56:28', NULL, NULL, NULL, NULL, '2026-09-25 15:17:59'),
(5, 1, 1, 1, 'Café Efectivo 6ab690b811e22', 'Cafetería tradicional beniana.', 'Calle Sucre 45', '', 'Juan Pérez', '71234567', 'juan_6ab690b811e26@cafetest.bo', 'MENSUAL', 'EFECTIVO', 250.00, 'EFECTIVO-EN-OFICINA', NULL, 'ACEPTADA', 'Pago en efectivo cobrado; aprovisionamiento completo.', 1, '2026-09-25 11:18:16', 16, 8, 'o8Luj/dLPRKuwIQZYKeqTaYFiMO+AfGNMkV7fVYEZdAyay1LFB92GGESMvxefvo0FzDA4vIPFJBr3qEAfl0FXKIUZlhdrUG+SdiV/eVLve6dgRTQ3d1h9eRsmCWLk797/WQFU0wGrAiT8AiJhReBc8LWFQ4T6nbiE9BmIY2C+YxXmAKaOBqhFKdy6CuCtAIpBEcBM7xE0PSmBIjZe2YBfWIAr62ixalwjl7P1IAgJlrxuhB31V6Hkjual69tan52B/HU+U0ibUJSWKOad86/oDknWBkYUi+tPB/PGyGRqN0FOcXYeujdlWgSJ4FyMF4g6rvavC2+QVOYMoX7Qf/Y3ZfzsaPzEffUIflG8pQx36BQ4D2va+PfRYdh4lBhT/gdLAuPLKajlyYbE5ezISAtGkTOBhi+nO4yYXSY/NUcBjTkolZf+eZykT+VAySR3JEQkpArF6HXnattD1JvdwUIgtkYghHverGo/U8iEfB/Nx0H19SiejQFkH0F6BisMdPZjuYzNyQJOaHJ77ucDMX5lTkvQ2lBvgwsn78EsQ==', NULL, '2026-09-25 15:18:16'),
(6, 1, 1, 1, 'Café Efectivo 6ab690c8770d4', 'Cafetería tradicional beniana.', 'Calle Sucre 45', '', 'Juan Pérez', '71234567', 'juan_6ab690c8770d8@cafetest.bo', 'MENSUAL', 'EFECTIVO', 250.00, 'EFECTIVO-EN-OFICINA', NULL, 'ACEPTADA', 'Pago en efectivo cobrado; aprovisionamiento completo.', 1, '2026-09-25 11:18:32', 18, 10, 'BhWf3hxlcbM9bjxCTIDLqR/9jV734XC/6LrOzl8Oxe8WToEzGS6abYlAutUQYk1fA/yiefnLAtSezWb6MhGD5NsdcysNww1Se4eJuK55hP6Z8o6mh8DO0+oW1H1mLtfrEGiWd/0uUXK0Xkc4G5JstPB6ALWlDuMP2ZgWl45kAWA7OS0eBxEvd4+tNSruWYoVyNK7vP0qNW0H4Q0ePP1NZAd0xc7WkCw3IgajRxWSTfQU8nyAaRA+TsNx0kyUBcpYus+Cu42aQcQ24b6nwqf7kYwqr8hkBJGIeUnGyO4Sg8tOcPC6yBhK9GhMv8U9bi1kViqz7bkCVcNFrN70QnUHuEdFMnewItpwaJEyJVr6s7apt+dHQpS6ej/Ira7WJ01qpYZBpW6Z8YOPrZoEOySYeFj9fNkxYvXHxz9PWbV0gMUMl4HXKozg6WpitknCJXb0wG4SBYQ7YUueLvruJhnYf/LFo0c2a6T3Qtl7mEu3XdilO5VlGCAdyrmwBpEZCtjulnlR2V95xozmKZpQNWxXZqXFlz5CMoCw2tYEcQ==', NULL, '2026-09-25 15:18:32');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tarifas`
--

CREATE TABLE `tarifas` (
  `id_tarifa` int(11) NOT NULL,
  `codigo_plan` varchar(30) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `meses_duracion` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `descripcion` varchar(255) NOT NULL,
  `vigente_desde` date NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tarifas`
--

INSERT INTO `tarifas` (`id_tarifa`, `codigo_plan`, `nombre`, `monto`, `meses_duracion`, `descripcion`, `vigente_desde`, `activo`, `created_at`) VALUES
(1, 'MENSUAL', 'Plan Mensual Estándar', 250.00, 1, 'Un mes de publicación comercial con catálogo interactivo', '2026-01-01', 1, '2026-09-24 14:06:53'),
(2, 'ANUAL', 'Plan Anual Preferencial', 2500.00, 12, '12 meses continuos de publicación comercial con ahorro de Bs 500', '2026-01-01', 1, '2026-09-24 14:06:53');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `telegram_notificaciones`
--

CREATE TABLE `telegram_notificaciones` (
  `id_notificacion` int(11) NOT NULL,
  `id_solicitud` int(11) NOT NULL,
  `tipo` enum('NUEVA','RESOLUCION') NOT NULL,
  `estado` enum('PENDIENTE','ENVIADA') NOT NULL DEFAULT 'PENDIENTE',
  `intentos` int(11) NOT NULL DEFAULT 0,
  `disponible_desde` datetime NOT NULL DEFAULT current_timestamp(),
  `ultimo_error` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `telegram_notificaciones`
--

INSERT INTO `telegram_notificaciones` (`id_notificacion`, `id_solicitud`, `tipo`, `estado`, `intentos`, `disponible_desde`, `ultimo_error`, `created_at`) VALUES
(1, 1, 'NUEVA', 'ENVIADA', 1, '2026-09-24 20:08:28', NULL, '2026-09-25 00:06:28'),
(2, 1, 'RESOLUCION', 'PENDIENTE', 0, '2026-09-24 20:07:40', NULL, '2026-09-25 00:07:40'),
(3, 2, 'NUEVA', 'ENVIADA', 1, '2026-09-25 03:03:50', NULL, '2026-09-25 07:01:50'),
(4, 2, 'RESOLUCION', 'PENDIENTE', 0, '2026-09-25 03:02:44', NULL, '2026-09-25 07:02:44'),
(5, 3, 'NUEVA', 'ENVIADA', 1, '2026-09-25 03:25:28', NULL, '2026-09-25 07:23:28'),
(6, 3, 'RESOLUCION', 'PENDIENTE', 0, '2026-09-25 03:24:05', NULL, '2026-09-25 07:24:05'),
(7, 4, 'NUEVA', 'PENDIENTE', 0, '2026-09-25 11:17:59', NULL, '2026-09-25 15:17:59'),
(8, 5, 'NUEVA', 'PENDIENTE', 0, '2026-09-25 11:18:16', NULL, '2026-09-25 15:18:16'),
(9, 5, 'RESOLUCION', 'PENDIENTE', 0, '2026-09-25 11:18:16', NULL, '2026-09-25 15:18:16'),
(10, 6, 'NUEVA', 'PENDIENTE', 0, '2026-09-25 11:18:32', NULL, '2026-09-25 15:18:32'),
(11, 6, 'RESOLUCION', 'PENDIENTE', 0, '2026-09-25 11:18:32', NULL, '2026-09-25 15:18:32'),
(12, 4, 'RESOLUCION', 'PENDIENTE', 0, '2026-09-25 11:56:28', NULL, '2026-09-25 15:56:28');

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
(1, 1, 1, '2026-09-24', '2026-10-24', 'NUEVO', '2026-09-25 00:07:40'),
(2, 8, 2, '2026-09-25', '2026-10-25', 'NUEVO', '2026-09-25 07:02:44'),
(3, 9, 3, '2026-09-25', '2026-10-25', 'NUEVO', '2026-09-25 07:24:05'),
(4, 10, 4, '2026-09-25', '2026-10-25', 'NUEVO', '2026-09-25 14:47:52'),
(5, 11, 5, '2026-09-25', '2026-10-25', 'NUEVO', '2026-09-25 15:17:12'),
(6, 12, 6, '2026-09-25', '2026-10-25', 'NUEVO', '2026-09-25 15:17:46'),
(7, 13, 7, '2026-09-25', '2026-10-25', 'NUEVO', '2026-09-25 15:17:59'),
(8, 15, 8, '2026-09-25', '2026-10-25', 'NUEVO', '2026-09-25 15:18:15'),
(9, 16, 9, '2026-09-25', '2026-10-25', 'NUEVO', '2026-09-25 15:18:16'),
(10, 17, 10, '2026-09-25', '2026-10-25', 'NUEVO', '2026-09-25 15:18:32'),
(11, 18, 11, '2026-09-25', '2026-10-25', 'NUEVO', '2026-09-25 15:18:32'),
(12, 11, 12, '2026-10-25', '2026-11-25', 'RENOVACION_ANTICIPADA', '2026-09-25 15:37:53'),
(13, 19, 13, '2026-09-25', '2026-10-25', 'NUEVO', '2026-09-25 15:54:18');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `visitas_diarias`
--

CREATE TABLE `visitas_diarias` (
  `fecha` date NOT NULL,
  `visitante_hash` char(64) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `visitas_diarias`
--

INSERT INTO `visitas_diarias` (`fecha`, `visitante_hash`, `created_at`) VALUES
('2026-09-24', '17653adc9f1aafbdcdd3b8db842883b3df62b26aecd3352d9e7aa3c3fc658e6b', '2026-09-24 18:41:34'),
('2026-09-24', '26cc1115223944f2d14065ddaf7d1fbef6a6fd74a9dd34364283dd2324992adc', '2026-09-24 19:57:26'),
('2026-09-25', '143f6e2bd938fa1d9f26c7644819afe74163f6331790d642074d0ae28f2f5a56', '2026-09-25 00:32:32'),
('2026-09-25', '26cc1115223944f2d14065ddaf7d1fbef6a6fd74a9dd34364283dd2324992adc', '2026-09-25 00:18:18'),
('2026-09-25', '8f9624d964239814816d953cb6ae792a3d3a166c108ef355fbeb000516ddb431', '2026-09-25 00:31:45'),
('2026-09-25', 'dc23621bef40dd72754a9047179d08b657a621649098f9b420eed4feb1f35b59', '2026-09-25 00:31:31');

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
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_categorias_activo_tipo` (`activo`,`tipo_defecto`);

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
  ADD KEY `fk_lugares_municipio` (`id_municipio`),
  ADD KEY `fk_lugares_categoria` (`id_categoria`),
  ADD KEY `idx_lugares_tipo_cat` (`tipo_lugar`,`id_categoria`),
  ADD KEY `idx_lugares_geolocalizacion` (`latitud`,`longitud`);

--
-- Indices de la tabla `municipios`
--
ALTER TABLE `municipios`
  ADD PRIMARY KEY (`id_municipio`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_municipios_activo` (`activo`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id_pago`),
  ADD KEY `fk_pagos_lugar` (`id_lugar`),
  ADD KEY `fk_pagos_tarifa` (`id_tarifa`),
  ADD KEY `fk_pagos_admin` (`id_administrador_confirmacion`),
  ADD KEY `idx_pagos_estado_fecha` (`estado`,`fecha_confirmacion`);

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
  ADD KEY `fk_solicitudes_municipio` (`id_municipio`),
  ADD KEY `fk_solicitudes_categoria` (`id_categoria`),
  ADD KEY `fk_solicitudes_tarifa` (`id_tarifa`),
  ADD KEY `fk_solicitudes_admin` (`id_administrador_revision`),
  ADD KEY `idx_solicitudes_purga` (`estado`,`created_at`),
  ADD KEY `fk_solicitud_lugar_creado` (`id_lugar_creado`),
  ADD KEY `fk_solicitud_cuenta_creada` (`id_cuenta_creada`);

--
-- Indices de la tabla `tarifas`
--
ALTER TABLE `tarifas`
  ADD PRIMARY KEY (`id_tarifa`),
  ADD UNIQUE KEY `codigo_plan` (`codigo_plan`),
  ADD KEY `idx_tarifas_vigencia` (`activo`,`vigente_desde`);

--
-- Indices de la tabla `telegram_notificaciones`
--
ALTER TABLE `telegram_notificaciones`
  ADD PRIMARY KEY (`id_notificacion`),
  ADD UNIQUE KEY `uq_telegram_solicitud_tipo` (`id_solicitud`,`tipo`),
  ADD KEY `idx_telegram_pendientes` (`estado`,`disponible_desde`);

--
-- Indices de la tabla `vigencias`
--
ALTER TABLE `vigencias`
  ADD PRIMARY KEY (`id_vigencia`),
  ADD UNIQUE KEY `id_pago` (`id_pago`),
  ADD KEY `idx_vigencias_lugar_venc` (`id_lugar`,`fecha_vencimiento`);

--
-- Indices de la tabla `visitas_diarias`
--
ALTER TABLE `visitas_diarias`
  ADD PRIMARY KEY (`fecha`,`visitante_hash`),
  ADD KEY `idx_visitas_diarias_fecha` (`fecha`);

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
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `cuentas_negocio`
--
ALTER TABLE `cuentas_negocio`
  MODIFY `id_cuenta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `fotografias`
--
ALTER TABLE `fotografias`
  MODIFY `id_fotografia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT de la tabla `lugares`
--
ALTER TABLE `lugares`
  MODIFY `id_lugar` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `municipios`
--
ALTER TABLE `municipios`
  MODIFY `id_municipio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id_pago` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `promociones`
--
ALTER TABLE `promociones`
  MODIFY `id_promocion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `publicaciones`
--
ALTER TABLE `publicaciones`
  MODIFY `id_publicacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `solicitudes`
--
ALTER TABLE `solicitudes`
  MODIFY `id_solicitud` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `tarifas`
--
ALTER TABLE `tarifas`
  MODIFY `id_tarifa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `telegram_notificaciones`
--
ALTER TABLE `telegram_notificaciones`
  MODIFY `id_notificacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `vigencias`
--
ALTER TABLE `vigencias`
  MODIFY `id_vigencia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

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
  ADD CONSTRAINT `fk_lugares_municipio` FOREIGN KEY (`id_municipio`) REFERENCES `municipios` (`id_municipio`),
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
  ADD CONSTRAINT `fk_solicitud_cuenta_creada` FOREIGN KEY (`id_cuenta_creada`) REFERENCES `cuentas_negocio` (`id_cuenta`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_solicitud_lugar_creado` FOREIGN KEY (`id_lugar_creado`) REFERENCES `lugares` (`id_lugar`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_solicitudes_admin` FOREIGN KEY (`id_administrador_revision`) REFERENCES `administradores` (`id_administrador`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_solicitudes_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`),
  ADD CONSTRAINT `fk_solicitudes_municipio` FOREIGN KEY (`id_municipio`) REFERENCES `municipios` (`id_municipio`),
  ADD CONSTRAINT `fk_solicitudes_tarifa` FOREIGN KEY (`id_tarifa`) REFERENCES `tarifas` (`id_tarifa`);

--
-- Filtros para la tabla `telegram_notificaciones`
--
ALTER TABLE `telegram_notificaciones`
  ADD CONSTRAINT `fk_telegram_solicitud` FOREIGN KEY (`id_solicitud`) REFERENCES `solicitudes` (`id_solicitud`) ON DELETE CASCADE;

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
