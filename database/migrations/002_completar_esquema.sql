-- Actualizacion no destructiva para instalaciones anteriores (MariaDB).
ALTER TABLE solicitudes ADD COLUMN IF NOT EXISTS plan_solicitado
    ENUM('MENSUAL','ANUAL') NOT NULL DEFAULT 'MENSUAL' AFTER id_categoria;

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
