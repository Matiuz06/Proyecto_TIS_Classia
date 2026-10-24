-- Issue: comprobante obligatorio para solicitudes docentes (MySQL)
ALTER TABLE solicitudes_docente
    ADD COLUMN documentacion_ruta VARCHAR(500) NULL AFTER motivo,
    ADD COLUMN documentacion_nombre VARCHAR(255) NULL AFTER documentacion_ruta,
    ADD COLUMN documentacion_mime VARCHAR(100) NULL AFTER documentacion_nombre,
    ADD COLUMN documentacion_tamano INT UNSIGNED NULL AFTER documentacion_mime,
    ADD COLUMN documentacion_fecha DATETIME NULL AFTER documentacion_tamano;
