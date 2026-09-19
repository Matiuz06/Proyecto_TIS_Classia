-- ==========================================================
-- Indices para optimizar consultas en Classia
-- Compatible con MySQL / MariaDB y PostgreSQL (Supabase)
-- ==========================================================

-- 1. Publicaciones y catálogo
CREATE INDEX idx_pub_usuario ON publicaciones (id_usuario);
CREATE INDEX idx_pub_categoria ON publicaciones (id_categoria);
CREATE INDEX idx_pub_estado_tipo ON publicaciones (estado, tipo);
CREATE INDEX idx_pub_fecha ON publicaciones (fecha_creacion);

-- 2. Solicitudes de servicios
CREATE INDEX idx_sol_usuario ON solicitudes (id_usuario);
CREATE INDEX idx_sol_publicacion ON solicitudes (id_publicacion);
CREATE INDEX idx_sol_estado ON solicitudes (estado);
CREATE INDEX idx_sol_contratacion ON solicitudes (id_contratacion);

-- 3. Contrataciones y detalles
CREATE INDEX idx_cont_usuario ON contrataciones (id_usuario);
CREATE INDEX idx_cont_estado ON contrataciones (estado);
CREATE INDEX idx_cont_fecha ON contrataciones (fecha_contratacion);
CREATE INDEX idx_det_contratacion ON detalles_contratacion (id_contratacion);
CREATE INDEX idx_det_publicacion ON detalles_contratacion (id_publicacion);

-- 4. Pagos
CREATE INDEX idx_pagos_contratacion ON pagos (id_contratacion);
CREATE INDEX idx_pagos_estado ON pagos (estado_pago);

-- 5. Valoraciones
CREATE INDEX idx_val_publicacion ON valoraciones (id_publicacion);
CREATE INDEX idx_val_usuario ON valoraciones (id_usuario);
CREATE INDEX idx_val_contratacion ON valoraciones (id_contratacion);

-- 6. Usuarios
CREATE INDEX idx_usr_rol ON usuarios (id_rol);
CREATE INDEX idx_usr_activo ON usuarios (activo);

-- 7. Contenido de cursos
CREATE INDEX idx_mod_pub ON curso_modulos (id_publicacion, orden);
CREATE INDEX idx_uni_mod ON curso_unidades (id_modulo, orden);
CREATE INDEX idx_rec_uni ON curso_recursos (id_unidad, orden);
