-- =========================================================
-- Migración 004: Estado Archivado + visibilidad de módulos y recursos
-- Implementa: RN-05, REQ-CON-04, RD-09
-- =========================================================

-- 1. Agregar estado 'Archivado' al ENUM de publicaciones (MySQL/MariaDB)
--    Los cursos archivados conservan su histórico pero no admiten
--    nuevas actividades (RN-05).
ALTER TABLE publicaciones
    MODIFY COLUMN estado
        ENUM('Activo', 'Inactivo', 'Pausado', 'Eliminado', 'Archivado')
        NOT NULL DEFAULT 'Activo';

-- 2. Columna de visibilidad para módulos (REQ-CON-04)
--    visible_alumnos = 1 → visible para estudiantes matriculados
--    visible_alumnos = 0 → oculto temporalmente; solo docente/admin lo ve
SET @dbname = DATABASE();
SET @tablename = "curso_modulos";
SET @columnname = "visible_alumnos";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(1) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE table_schema = @dbname
      AND table_name = @tablename
      AND column_name = @columnname
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'REQ-CON-04: 0 = oculto para alumnos, 1 = visible'")
));
PREPARE addColIfNotExists FROM @preparedStatement;
EXECUTE addColIfNotExists;
DEALLOCATE PREPARE addColIfNotExists;

-- 3. Columna de visibilidad para recursos (REQ-CON-04)
SET @tablename = "curso_recursos";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(1) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE table_schema = @dbname
      AND table_name = @tablename
      AND column_name = @columnname
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'REQ-CON-04: 0 = oculto para alumnos, 1 = visible'")
));
PREPARE addColIfNotExists FROM @preparedStatement;
EXECUTE addColIfNotExists;
DEALLOCATE PREPARE addColIfNotExists;
