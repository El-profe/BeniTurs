-- MariaDB. Seleccionar la base existente; no elimina registros.
ALTER TABLE solicitudes
    ADD COLUMN IF NOT EXISTS plan_solicitado ENUM('MENSUAL','ANUAL') NOT NULL DEFAULT 'MENSUAL',
    ADD COLUMN IF NOT EXISTS comprobante_archivo VARCHAR(255) NULL,
    ADD COLUMN IF NOT EXISTS numero_comprobante VARCHAR(100) NULL,
    ADD COLUMN IF NOT EXISTS usuario_solicitado VARCHAR(60) NOT NULL DEFAULT '',
    ADD COLUMN IF NOT EXISTS password_hash_solicitado VARCHAR(255) NOT NULL DEFAULT '';
-- Los vacios identifican solicitudes historicas sin credenciales; no se aprovisionan.
ALTER TABLE pagos ADD COLUMN IF NOT EXISTS comprobante_archivo VARCHAR(255) NULL;
CREATE INDEX IF NOT EXISTS idx_solicitudes_purga ON solicitudes (estado, created_at);
