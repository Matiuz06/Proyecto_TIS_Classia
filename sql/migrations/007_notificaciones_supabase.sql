-- Centro de notificaciones persistentes (PostgreSQL/Supabase)
CREATE TABLE IF NOT EXISTS notificaciones (
    id_notificacion BIGSERIAL PRIMARY KEY,
    id_usuario INTEGER NOT NULL REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    categoria VARCHAR(60) NOT NULL,
    titulo VARCHAR(180) NOT NULL,
    mensaje TEXT NOT NULL,
    url_destino VARCHAR(500),
    tipo_entidad VARCHAR(80),
    id_entidad INTEGER,
    leida BOOLEAN NOT NULL DEFAULT FALSE,
    fecha_creacion TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_leida TIMESTAMPTZ,
    clave_idempotencia VARCHAR(64) NOT NULL,
    CONSTRAINT uq_notificaciones_usuario_idempotencia UNIQUE (id_usuario, clave_idempotencia)
);

CREATE INDEX IF NOT EXISTS idx_notificaciones_usuario_lectura_fecha
    ON notificaciones (id_usuario, leida, fecha_creacion);
CREATE INDEX IF NOT EXISTS idx_notificaciones_usuario_fecha
    ON notificaciones (id_usuario, fecha_creacion);
CREATE INDEX IF NOT EXISTS idx_notificaciones_entidad
    ON notificaciones (tipo_entidad, id_entidad);
