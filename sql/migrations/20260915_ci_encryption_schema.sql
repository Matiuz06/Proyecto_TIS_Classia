-- Migración para cifrado de Cédula de Identidad (Ley N° 18.331 de Protección de Datos Personales)
-- Modifica cedula_identidad a VARCHAR(255) para almacenar ciphertext AES-256-GCM y añade cedula_hash para búsquedas seguras

ALTER TABLE usuarios MODIFY COLUMN cedula_identidad VARCHAR(255) NULL;

-- Dropear el índice único directo sobre cedula_identidad si existe para permitir almacenar ciphertext variable
SET @drop_idx = (SELECT IF(
    EXISTS(SELECT 1 FROM information_schema.statistics WHERE table_schema=DATABASE() AND table_name='usuarios' AND index_name='cedula_identidad'),
    'ALTER TABLE usuarios DROP INDEX cedula_identidad',
    'SELECT 1'
));
PREPARE stmt FROM @drop_idx;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @drop_idx_ci = (SELECT IF(
    EXISTS(SELECT 1 FROM information_schema.statistics WHERE table_schema=DATABASE() AND table_name='usuarios' AND index_name='idx_usuarios_ci'),
    'ALTER TABLE usuarios DROP INDEX idx_usuarios_ci',
    'SELECT 1'
));
PREPARE stmt_ci FROM @drop_idx_ci;
EXECUTE stmt_ci;
DEALLOCATE PREPARE stmt_ci;

-- Agregar columna cedula_hash para blind index HMAC-SHA256 si no existe
SET @add_col = (SELECT IF(
    NOT EXISTS(SELECT 1 FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='usuarios' AND column_name='cedula_hash'),
    'ALTER TABLE usuarios ADD COLUMN cedula_hash VARCHAR(64) NULL AFTER cedula_identidad',
    'SELECT 1'
));
PREPARE stmt_col FROM @add_col;
EXECUTE stmt_col;
DEALLOCATE PREPARE stmt_col;

-- Agregar índice único sobre cedula_hash
SET @add_idx_hash = (SELECT IF(
    NOT EXISTS(SELECT 1 FROM information_schema.statistics WHERE table_schema=DATABASE() AND table_name='usuarios' AND index_name='idx_usuarios_ci_hash'),
    'ALTER TABLE usuarios ADD UNIQUE INDEX idx_usuarios_ci_hash (cedula_hash)',
    'SELECT 1'
));
PREPARE stmt_hash FROM @add_idx_hash;
EXECUTE stmt_hash;
DEALLOCATE PREPARE stmt_hash;
