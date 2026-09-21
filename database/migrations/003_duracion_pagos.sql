-- Conservar la interpretacion anterior SOLO para los pagos historicos.
-- Los nuevos pagos reciben una duracion explicita desde el formulario.
ALTER TABLE pagos ADD COLUMN IF NOT EXISTS meses_duracion TINYINT UNSIGNED NULL DEFAULT NULL AFTER monto;
UPDATE pagos SET meses_duracion = CASE WHEN monto >= 2500.00 THEN 12 ELSE 1 END
WHERE meses_duracion IS NULL;
ALTER TABLE pagos MODIFY COLUMN meses_duracion TINYINT UNSIGNED NOT NULL DEFAULT 1;
