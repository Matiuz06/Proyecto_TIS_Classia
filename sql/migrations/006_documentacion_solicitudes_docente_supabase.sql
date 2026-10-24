-- Issue: comprobante obligatorio para solicitudes docentes (PostgreSQL/Supabase)
ALTER TABLE solicitudes_docente
    ADD COLUMN IF NOT EXISTS documentacion_ruta VARCHAR(500),
    ADD COLUMN IF NOT EXISTS documentacion_nombre VARCHAR(255),
    ADD COLUMN IF NOT EXISTS documentacion_mime VARCHAR(100),
    ADD COLUMN IF NOT EXISTS documentacion_tamano INTEGER,
    ADD COLUMN IF NOT EXISTS documentacion_fecha TIMESTAMPTZ;
