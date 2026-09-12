ALTER TABLE usuarios
    ADD COLUMN nombre_usuario VARCHAR(30) NULL UNIQUE,
    ADD COLUMN genero VARCHAR(30) NOT NULL DEFAULT 'sin-especificar',
    ADD COLUMN email_verificado TINYINT(1) NOT NULL DEFAULT 1,
    ADD COLUMN email_verificacion_token CHAR(64) NULL,
    ADD COLUMN email_verificacion_expira DATETIME NULL,
    ADD COLUMN onboarding_step TINYINT UNSIGNED NOT NULL DEFAULT 1,
    ADD COLUMN onboarding_data TEXT NULL;

UPDATE usuarios SET nombre_usuario = CONCAT('usuario_', id_usuario) WHERE nombre_usuario IS NULL;

ALTER TABLE usuarios MODIFY nombre_usuario VARCHAR(30) NOT NULL;

CREATE INDEX idx_usuarios_verificacion_token ON usuarios (email_verificacion_token);

ALTER TABLE publicaciones
    ADD COLUMN modalidad VARCHAR(30) NULL AFTER tipo,
    ADD COLUMN nivel_experiencia VARCHAR(30) NULL AFTER modalidad,
    ADD COLUMN duracion_horas SMALLINT UNSIGNED NULL AFTER nivel_experiencia;
