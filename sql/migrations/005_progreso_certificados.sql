ALTER TABLE publicaciones
    ADD COLUMN porcentaje_minimo_aprobacion TINYINT UNSIGNED NOT NULL DEFAULT 70;

CREATE TABLE IF NOT EXISTS curso_recursos_completados (
    id_completado INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_recurso INT NOT NULL,
    fecha_completado DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_recursos_completados_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_recursos_completados_recurso FOREIGN KEY (id_recurso) REFERENCES curso_recursos(id_recurso) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY uq_recurso_completado_usuario (id_usuario, id_recurso),
    INDEX idx_recursos_completados_recurso (id_recurso)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS certificados (
    id_certificado INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_curso INT NOT NULL,
    codigo_verificacion VARCHAR(32) NOT NULL,
    fecha_emision DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    porcentaje_aprobacion DECIMAL(5,2) NOT NULL,
    CONSTRAINT fk_certificados_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_certificados_curso FOREIGN KEY (id_curso) REFERENCES publicaciones(id_publicacion) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY uq_certificado_usuario_curso (id_usuario, id_curso),
    UNIQUE KEY uq_certificado_codigo (codigo_verificacion),
    INDEX idx_certificados_curso (id_curso)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
