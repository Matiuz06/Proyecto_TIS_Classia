USE classia_db;

ALTER TABLE usuarios
  ADD COLUMN cedula_identidad VARCHAR(20) NULL AFTER apellido,
  ADD COLUMN github_id VARCHAR(100) NULL AFTER foto_perfil,
  ADD COLUMN dos_factores_activo TINYINT(1) NOT NULL DEFAULT 0 AFTER github_id,
  ADD COLUMN dos_factores_secreto VARCHAR(255) NULL AFTER dos_factores_activo,
  ADD COLUMN dos_factores_backup_codes TEXT NULL AFTER dos_factores_secreto;

ALTER TABLE usuarios ADD UNIQUE INDEX idx_usuarios_ci (cedula_identidad);
ALTER TABLE usuarios ADD UNIQUE INDEX idx_usuarios_github (github_id);
