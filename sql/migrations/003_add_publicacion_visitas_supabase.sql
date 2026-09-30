-- ============================================================================
-- MIGRACIÓN: 003_add_publicacion_visitas_supabase
-- Compatible con: PostgreSQL (Supabase) 15 / 16
-- Requerimientos: REQ-ADM-02, REQ-ADM-04
-- Fecha: 2026-09-30
-- ============================================================================

CREATE TABLE IF NOT EXISTS publicacion_visitas (
    id_visita SERIAL PRIMARY KEY,
    id_publicacion INT NOT NULL REFERENCES publicaciones(id_publicacion) ON DELETE CASCADE ON UPDATE CASCADE,
    fecha_visita DATE NOT NULL DEFAULT CURRENT_DATE,
    visitas INT NOT NULL DEFAULT 1,
    CONSTRAINT uq_pub_visita_fecha UNIQUE (id_publicacion, fecha_visita)
);

CREATE INDEX IF NOT EXISTS idx_visitas_pub ON publicacion_visitas (id_publicacion);
CREATE INDEX IF NOT EXISTS idx_visitas_fecha ON publicacion_visitas (fecha_visita);

-- Datos semilla para Supabase
INSERT INTO publicacion_visitas (id_publicacion, fecha_visita, visitas)
SELECT p.id_publicacion, CURRENT_DATE - (d.dia || ' day')::INTERVAL, 
       FLOOR(5 + (RANDOM() * 25))::INT
FROM publicaciones p
CROSS JOIN generate_series(0, 29) AS d(dia)
WHERE p.estado = 'Activo'
ON CONFLICT (id_publicacion, fecha_visita) DO UPDATE SET visitas = EXCLUDED.visitas;
