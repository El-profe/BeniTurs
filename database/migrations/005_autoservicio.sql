-- Distingue registros iniciales del autoservicio de pagos historicos/renovaciones.
ALTER TABLE pagos ADD COLUMN IF NOT EXISTS es_registro_inicial TINYINT(1) NOT NULL DEFAULT 0;
