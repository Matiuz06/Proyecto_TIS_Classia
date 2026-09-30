-- ============================================================================
-- MIGRACIÓN: 002_add_indexes_ordenamiento_catalogo
-- Compatible con: PostgreSQL (Supabase) 15 / 16
-- Fecha: 2026-09-30
-- Descripción: Índices de soporte para el ordenamiento dinámico del catálogo
--              introducido por REQ-SER-06 (ORDER BY valoración, popularidad, precio).
-- ============================================================================

-- Índice en precio para ORDER BY precio ASC / DESC
CREATE INDEX IF NOT EXISTS idx_pub_precio
    ON publicaciones (precio);

-- Índice compuesto tipo + precio (cubre filtros de tipo + ordenar por precio en un solo scan)
CREATE INDEX IF NOT EXISTS idx_pub_tipo_precio
    ON publicaciones (tipo, precio);

-- Índice compuesto en valoraciones para acelerar AVG(puntuacion) en GROUP BY
CREATE INDEX IF NOT EXISTS idx_val_pub_puntuacion
    ON valoraciones (id_publicacion, puntuacion);

-- Índice en detalles_contratacion para COUNT(DISTINCT dc.id_contratacion) por publicación
CREATE INDEX IF NOT EXISTS idx_det_publicacion
    ON detalles_contratacion (id_publicacion);
