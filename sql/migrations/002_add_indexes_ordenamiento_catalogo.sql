-- Migration 002: Índices de soporte para ordenamiento dinámico del catálogo
-- Fecha: 2026-09-30
-- Descripción: Agrega índices en publicaciones.precio, valoraciones.puntuacion y
--              detalles_contratacion.id_publicacion para soportar el ORDER BY dinámico
--              introducido por REQ-SER-06 sin degradar el rendimiento en catálogos extensos.
-- Compatible con: MySQL 8.0+

-- Índice en precio para ORDER BY precio ASC / DESC
SET @dbname = DATABASE();
SET @tablename = "publicaciones";
SET @indexname = "idx_pub_precio";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(1) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE table_schema = @dbname
      AND table_name = @tablename
      AND index_name = @indexname
  ) > 0,
  "SELECT 1",
  CONCAT("CREATE INDEX ", @indexname, " ON ", @tablename, " (precio)")
));
PREPARE createIndexIfNotExists FROM @preparedStatement;
EXECUTE createIndexIfNotExists;
DEALLOCATE PREPARE createIndexIfNotExists;

-- Índice compuesto tipo + precio
SET @indexname = "idx_pub_tipo_precio";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(1) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE table_schema = @dbname
      AND table_name = @tablename
      AND index_name = @indexname
  ) > 0,
  "SELECT 1",
  CONCAT("CREATE INDEX ", @indexname, " ON ", @tablename, " (tipo, precio)")
));
PREPARE createIndexIfNotExists FROM @preparedStatement;
EXECUTE createIndexIfNotExists;
DEALLOCATE PREPARE createIndexIfNotExists;

-- Índice compuesto en valoraciones para acelerar AVG(v.puntuacion)
SET @tablename = "valoraciones";
SET @indexname = "idx_val_pub_puntuacion";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(1) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE table_schema = @dbname
      AND table_name = @tablename
      AND index_name = @indexname
  ) > 0,
  "SELECT 1",
  CONCAT("CREATE INDEX ", @indexname, " ON ", @tablename, " (id_publicacion, puntuacion)")
));
PREPARE createIndexIfNotExists FROM @preparedStatement;
EXECUTE createIndexIfNotExists;
DEALLOCATE PREPARE createIndexIfNotExists;
