ALTER TABLE publicaciones
    ADD COLUMN IF NOT EXISTS porcentaje_minimo_aprobacion SMALLINT NOT NULL DEFAULT 70;

CREATE TABLE IF NOT EXISTS curso_recursos_completados (
    id_completado SERIAL PRIMARY KEY,
    id_usuario INT NOT NULL REFERENCES usuarios(id_usuario) ON DELETE CASCADE ON UPDATE CASCADE,
    id_recurso INT NOT NULL REFERENCES curso_recursos(id_recurso) ON DELETE CASCADE ON UPDATE CASCADE,
    fecha_completado TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_recurso_completado_usuario UNIQUE (id_usuario, id_recurso)
);
CREATE INDEX IF NOT EXISTS idx_recursos_completados_recurso ON curso_recursos_completados (id_recurso);

CREATE TABLE IF NOT EXISTS certificados (
    id_certificado SERIAL PRIMARY KEY,
    id_usuario INT NOT NULL REFERENCES usuarios(id_usuario) ON DELETE CASCADE ON UPDATE CASCADE,
    id_curso INT NOT NULL REFERENCES publicaciones(id_publicacion) ON DELETE CASCADE ON UPDATE CASCADE,
    codigo_verificacion VARCHAR(32) NOT NULL,
    fecha_emision TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    porcentaje_aprobacion NUMERIC(5,2) NOT NULL,
    CONSTRAINT uq_certificado_usuario_curso UNIQUE (id_usuario, id_curso),
    CONSTRAINT uq_certificado_codigo UNIQUE (codigo_verificacion)
);
CREATE INDEX IF NOT EXISTS idx_certificados_curso ON certificados (id_curso);
