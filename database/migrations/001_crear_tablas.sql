-- Base de datos: trinidad_turismo_db
-- Motor: MariaDB / InnoDB con UTF-8 multibyte para caracteres en español

CREATE DATABASE IF NOT EXISTS `trinidad_turismo_db` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `trinidad_turismo_db`;

-- Desactivar llaves foráneas temporalmente para reconstrucción limpia
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `promociones`;
DROP TABLE IF EXISTS `cuentas_negocio`;
DROP TABLE IF EXISTS `fotografias`;
DROP TABLE IF EXISTS `vigencias`;
DROP TABLE IF EXISTS `pagos`;
DROP TABLE IF EXISTS `publicaciones`;
DROP TABLE IF EXISTS `lugares`;
DROP TABLE IF EXISTS `solicitudes`;
DROP TABLE IF EXISTS `tarifas`;
DROP TABLE IF EXISTS `categorias`;
DROP TABLE IF EXISTS `administradores`;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. Tabla: administradores (RF-08, RF-09)
CREATE TABLE `administradores` (
    `id_administrador` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(100) NOT NULL,
    `usuario` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(120) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `ultimo_acceso` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Tabla: categorias (RF-03, RF-12)
CREATE TABLE `categorias` (
    `id_categoria` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `descripcion` VARCHAR(255) NULL,
    `icono` VARCHAR(50) NOT NULL DEFAULT 'bi-tag',
    `tipo_defecto` ENUM('PUBLICO', 'COMERCIAL') NOT NULL DEFAULT 'COMERCIAL',
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 3. Tabla: tarifas (RF-28)
CREATE TABLE `tarifas` (
    `id_tarifa` INT AUTO_INCREMENT PRIMARY KEY,
    `monto_mensual` DECIMAL(10,2) NOT NULL,
    `descripcion` VARCHAR(150) NOT NULL,
    `vigente_desde` DATE NOT NULL,
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `id_administrador_registro` INT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_tarifas_admin` FOREIGN KEY (`id_administrador_registro`) 
        REFERENCES `administradores` (`id_administrador`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 4. Tabla: solicitudes de incorporación (RF-07, RF-19 al RF-21)
-- Estado independiente de pago y de publicación
CREATE TABLE `solicitudes` (
    `id_solicitud` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre_establecimiento` VARCHAR(150) NOT NULL,
    `id_categoria` INT NOT NULL,
    `plan_solicitado` ENUM('MENSUAL','ANUAL') NOT NULL DEFAULT 'MENSUAL',
    `nombre_solicitante` VARCHAR(120) NOT NULL,
    `telefono_contacto` VARCHAR(30) NOT NULL,
    `email_contacto` VARCHAR(120) NULL,
    `direccion` VARCHAR(255) NOT NULL,
    `descripcion` TEXT NOT NULL,
    `horarios` VARCHAR(150) NULL,
    `estado` ENUM('PENDIENTE', 'ACEPTADA', 'RECHAZADA') NOT NULL DEFAULT 'PENDIENTE',
    `observaciones_admin` TEXT NULL,
    `id_administrador_revision` INT NULL,
    `fecha_revision` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_solicitudes_categoria` FOREIGN KEY (`id_categoria`) 
        REFERENCES `categorias` (`id_categoria`) ON DELETE RESTRICT,
    CONSTRAINT `fk_solicitudes_admin` FOREIGN KEY (`id_administrador_revision`) 
        REFERENCES `administradores` (`id_administrador`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 5. Tabla: lugares (Ficha común para atractivos turísticos y comercios) (RF-10 al RF-18)
CREATE TABLE `lugares` (
    `id_lugar` INT AUTO_INCREMENT PRIMARY KEY,
    `id_categoria` INT NOT NULL,
    `id_solicitud_origen` INT NULL UNIQUE,
    `nombre` VARCHAR(150) NOT NULL,
    `slug` VARCHAR(160) NOT NULL UNIQUE,
    `descripcion` TEXT NOT NULL,
    `direccion` VARCHAR(255) NOT NULL,
    `referencia_ubicacion` VARCHAR(255) NULL,
    `coordenadas_gps` VARCHAR(100) NULL,
    `telefono_contacto` VARCHAR(50) NULL,
    `whatsapp_contacto` VARCHAR(50) NULL,
    `email_contacto` VARCHAR(120) NULL,
    `horario_atencion` VARCHAR(200) NULL,
    `tipo_lugar` ENUM('PUBLICO', 'COMERCIAL') NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_lugares_categoria` FOREIGN KEY (`id_categoria`) 
        REFERENCES `categorias` (`id_categoria`) ON DELETE RESTRICT,
    CONSTRAINT `fk_lugares_solicitud` FOREIGN KEY (`id_solicitud_origen`) 
        REFERENCES `solicitudes` (`id_solicitud`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 6. Tabla: publicaciones (Aprobación y visibilidad administrativa) (RF-15, RF-16)
-- Estado de publicación independiente del pago
CREATE TABLE `publicaciones` (
    `id_publicacion` INT AUTO_INCREMENT PRIMARY KEY,
    `id_lugar` INT NOT NULL UNIQUE,
    `aprobado` TINYINT(1) NOT NULL DEFAULT 0,
    `habilitado` TINYINT(1) NOT NULL DEFAULT 1,
    `motivo_suspension` TEXT NULL,
    `id_administrador_aprobacion` INT NULL,
    `fecha_aprobacion` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_publicaciones_lugar` FOREIGN KEY (`id_lugar`) 
        REFERENCES `lugares` (`id_lugar`) ON DELETE CASCADE,
    CONSTRAINT `fk_publicaciones_admin` FOREIGN KEY (`id_administrador_aprobacion`) 
        REFERENCES `administradores` (`id_administrador`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 7. Tabla: pagos (Registro y confirmación manual) (RF-22 al RF-27)
CREATE TABLE `pagos` (
    `id_pago` INT AUTO_INCREMENT PRIMARY KEY,
    `id_lugar` INT NOT NULL,
    `id_tarifa` INT NOT NULL,
    `monto` DECIMAL(10,2) NOT NULL,
    `meses_duracion` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `fecha_pago_declarada` DATE NOT NULL,
    `numero_comprobante` VARCHAR(100) NULL,
    `metodo_pago` VARCHAR(50) NOT NULL DEFAULT 'Transferencia bancaria / QR',
    `estado` ENUM('PENDIENTE', 'CONFIRMADO', 'ANULADO') NOT NULL DEFAULT 'PENDIENTE',
    `id_administrador_confirmacion` INT NULL,
    `fecha_confirmacion` DATETIME NULL,
    `observaciones` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_pagos_lugar` FOREIGN KEY (`id_lugar`) 
        REFERENCES `lugares` (`id_lugar`) ON DELETE CASCADE,
    CONSTRAINT `fk_pagos_tarifa` FOREIGN KEY (`id_tarifa`) 
        REFERENCES `tarifas` (`id_tarifa`) ON DELETE RESTRICT,
    CONSTRAINT `fk_pagos_admin` FOREIGN KEY (`id_administrador_confirmacion`) 
        REFERENCES `administradores` (`id_administrador`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 8. Tabla: vigencias (Mensualidades cubiertas por pagos confirmados) (RF-24 al RF-27)
-- Regla: inicio incluido, vencimiento excluido
CREATE TABLE `vigencias` (
    `id_vigencia` INT AUTO_INCREMENT PRIMARY KEY,
    `id_lugar` INT NOT NULL,
    `id_pago` INT NOT NULL UNIQUE,
    `fecha_inicio` DATE NOT NULL,
    `fecha_vencimiento` DATE NOT NULL,
    `tipo_periodo` ENUM('NUEVO', 'RENOVACION_ANTICIPADA', 'REACTIVACION') NOT NULL DEFAULT 'NUEVO',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_vigencias_lugar` FOREIGN KEY (`id_lugar`) 
        REFERENCES `lugares` (`id_lugar`) ON DELETE CASCADE,
    CONSTRAINT `fk_vigencias_pago` FOREIGN KEY (`id_pago`) 
        REFERENCES `pagos` (`id_pago`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 9. Tabla: fotografias (Metadatos de imágenes en storage) (RF-13)
CREATE TABLE `fotografias` (
    `id_fotografia` INT AUTO_INCREMENT PRIMARY KEY,
    `id_lugar` INT NOT NULL,
    `nombre_archivo` VARCHAR(255) NOT NULL,
    `nombre_original` VARCHAR(255) NOT NULL,
    `mime_type` VARCHAR(50) NOT NULL,
    `tamano_bytes` INT NOT NULL,
    `es_principal` TINYINT(1) NOT NULL DEFAULT 0,
    `orden` INT NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_fotografias_lugar` FOREIGN KEY (`id_lugar`) 
        REFERENCES `lugares` (`id_lugar`) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS cuentas_negocio (
    id_cuenta INT AUTO_INCREMENT PRIMARY KEY,
    id_lugar INT NOT NULL UNIQUE,
    usuario VARCHAR(60) NOT NULL UNIQUE,
    email VARCHAR(120) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    ultimo_acceso DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_cuentas_lugar FOREIGN KEY (id_lugar) REFERENCES lugares(id_lugar) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS promociones (
    id_promocion INT AUTO_INCREMENT PRIMARY KEY,
    id_lugar INT NOT NULL,
    titulo VARCHAR(150) NOT NULL,
    descripcion TEXT NOT NULL,
    descuento_texto VARCHAR(100) NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_promos_lugar FOREIGN KEY (id_lugar) REFERENCES lugares(id_lugar) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
