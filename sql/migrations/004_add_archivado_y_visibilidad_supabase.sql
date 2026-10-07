-- =========================================================
-- Migración 004 (Supabase/PostgreSQL): Estado Archivado + visibilidad
-- Implementa: RN-05, REQ-CON-04
-- =========================================================

-- 1. Agregar 'Archivado' al tipo ENUM de publicaciones en PostgreSQL
--    PostgreSQL no soporta ALTER TYPE directamente en CHECK constraints,
--    por lo que actualizamos la restricción existente.
--    Si 'estado' usa un tipo ENUM o CHECK, ajustar según el esquema Supabase.
DO $$
BEGIN
    -- Intenta agregar el valor al tipo enum si existe
    BEGIN
        ALTER TYPE estado_publicacion ADD VALUE IF NOT EXISTS 'Archivado';
    EXCEPTION WHEN undefined_object THEN
        -- Si no hay tipo ENUM, el CHECK se actualiza abajo
        NULL;
    END;
END;
$$;

-- Si usa CHECK constraint en lugar de tipo ENUM:
ALTER TABLE publicaciones
    DROP CONSTRAINT IF EXISTS publicaciones_estado_check;

ALTER TABLE publicaciones
    ADD CONSTRAINT publicaciones_estado_check
        CHECK (estado IN ('Activo', 'Inactivo', 'Pausado', 'Eliminado', 'Archivado'));

-- 2. Visibilidad de módulos (REQ-CON-04)
ALTER TABLE curso_modulos
    ADD COLUMN IF NOT EXISTS visible_alumnos SMALLINT NOT NULL DEFAULT 1;

COMMENT ON COLUMN curso_modulos.visible_alumnos IS
    'REQ-CON-04: 0 = oculto para alumnos, 1 = visible';

-- 3. Visibilidad de recursos (REQ-CON-04)
ALTER TABLE curso_recursos
    ADD COLUMN IF NOT EXISTS visible_alumnos SMALLINT NOT NULL DEFAULT 1;

COMMENT ON COLUMN curso_recursos.visible_alumnos IS
    'REQ-CON-04: 0 = oculto para alumnos, 1 = visible';
