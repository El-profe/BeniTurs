-- MariaDB, ejecutar sobre la base seleccionada tras 001..005. No elimina registros.
ALTER TABLE solicitudes
    ADD COLUMN IF NOT EXISTS plan_solicitado ENUM('MENSUAL','ANUAL') NOT NULL DEFAULT 'MENSUAL',
    ADD COLUMN IF NOT EXISTS monto_declarado DECIMAL(10,2) NOT NULL DEFAULT 250.00,
    ADD COLUMN IF NOT EXISTS comprobante_archivo VARCHAR(255) NULL,
    ADD COLUMN IF NOT EXISTS id_lugar_creado INT NULL,
    ADD COLUMN IF NOT EXISTS id_cuenta_creada INT NULL,
    ADD COLUMN IF NOT EXISTS credenciales_cifradas TEXT NULL,
    ADD COLUMN IF NOT EXISTS telegram_message_id BIGINT NULL;

ALTER TABLE solicitudes MODIFY plan_solicitado ENUM('MENSUAL','ANUAL') NOT NULL DEFAULT 'MENSUAL';
UPDATE solicitudes SET monto_declarado = 2500.00 WHERE plan_solicitado = 'ANUAL' AND monto_declarado = 250.00;
UPDATE solicitudes s JOIN lugares l ON l.id_solicitud_origen = s.id_solicitud
    LEFT JOIN cuentas_negocio c ON c.id_lugar = l.id_lugar
    SET s.id_lugar_creado = l.id_lugar, s.id_cuenta_creada = c.id_cuenta
    WHERE s.id_lugar_creado IS NULL;

SET @ddl = IF(EXISTS(SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=DATABASE() AND TABLE_NAME='solicitudes' AND CONSTRAINT_NAME='fk_solicitud_lugar_creado'),
    'SELECT 1', 'ALTER TABLE solicitudes ADD CONSTRAINT fk_solicitud_lugar_creado FOREIGN KEY (id_lugar_creado) REFERENCES lugares(id_lugar) ON DELETE SET NULL');
PREPARE migration_stmt FROM @ddl;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;
SET @ddl = IF(EXISTS(SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=DATABASE() AND TABLE_NAME='solicitudes' AND CONSTRAINT_NAME='fk_solicitud_cuenta_creada'),
    'SELECT 1', 'ALTER TABLE solicitudes ADD CONSTRAINT fk_solicitud_cuenta_creada FOREIGN KEY (id_cuenta_creada) REFERENCES cuentas_negocio(id_cuenta) ON DELETE SET NULL');
PREPARE migration_stmt FROM @ddl;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

-- Cola transaccional: nunca contiene contraseñas ni tokens en texto plano.
CREATE TABLE IF NOT EXISTS telegram_notificaciones (
    id_notificacion INT AUTO_INCREMENT PRIMARY KEY,
    id_solicitud INT NOT NULL,
    tipo ENUM('NUEVA','RESOLUCION') NOT NULL,
    estado ENUM('PENDIENTE','ENVIADA') NOT NULL DEFAULT 'PENDIENTE',
    intentos INT NOT NULL DEFAULT 0,
    disponible_desde DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ultimo_error VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_telegram_solicitud_tipo (id_solicitud,tipo),
    KEY idx_telegram_pendientes (estado,disponible_desde),
    CONSTRAINT fk_telegram_solicitud FOREIGN KEY (id_solicitud) REFERENCES solicitudes(id_solicitud) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
