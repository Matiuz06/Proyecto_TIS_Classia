-- Issue: comprobante obligatorio para solicitudes docentes (MySQL)
SET @dbname = DATABASE();
SET @tablename = "solicitudes_docente";
SET @columnname = "documentacion_ruta";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(1) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE table_schema = @dbname
      AND table_name = @tablename
      AND column_name = @columnname
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, "
    ADD COLUMN documentacion_ruta VARCHAR(500) NULL AFTER motivo,
    ADD COLUMN documentacion_nombre VARCHAR(255) NULL AFTER documentacion_ruta,
    ADD COLUMN documentacion_mime VARCHAR(100) NULL AFTER documentacion_nombre,
    ADD COLUMN documentacion_tamano INT UNSIGNED NULL AFTER documentacion_mime,
    ADD COLUMN documentacion_fecha DATETIME NULL AFTER documentacion_tamano")
));
PREPARE addColsIfNotExists FROM @preparedStatement;
EXECUTE addColsIfNotExists;
DEALLOCATE PREPARE addColsIfNotExists;
