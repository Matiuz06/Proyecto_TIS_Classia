-- Centro de notificaciones persistentes (MySQL)
CREATE TABLE IF NOT EXISTS notificaciones (
    id_notificacion INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    categoria VARCHAR(60) NOT NULL,
    titulo VARCHAR(180) NOT NULL,
    mensaje TEXT NOT NULL,
    url_destino VARCHAR(500) NULL,
    tipo_entidad VARCHAR(80) NULL,
    id_entidad INT NULL,
    leida TINYINT(1) NOT NULL DEFAULT 0,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_leida DATETIME NULL,
    clave_idempotencia VARCHAR(64) NOT NULL,
    CONSTRAINT fk_notificaciones_usuario
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY uq_notificaciones_usuario_idempotencia (id_usuario, clave_idempotencia),
    INDEX idx_notificaciones_usuario_lectura_fecha (id_usuario, leida, fecha_creacion),
    INDEX idx_notificaciones_usuario_fecha (id_usuario, fecha_creacion),
    INDEX idx_notificaciones_entidad (tipo_entidad, id_entidad)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
