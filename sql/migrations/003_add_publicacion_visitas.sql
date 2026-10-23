-- Migration 003: Tabla de registro y estadísticas de visitas por publicación y fecha
-- Requerimientos asociados: REQ-ADM-02, REQ-ADM-04
-- Fecha: 2026-09-30
-- Compatible con: MySQL 8.0+

CREATE TABLE IF NOT EXISTS publicacion_visitas (
    id_visita INT AUTO_INCREMENT PRIMARY KEY,
    id_publicacion INT NOT NULL,
    fecha_visita DATE NOT NULL,
    visitas INT UNSIGNED NOT NULL DEFAULT 1,
    CONSTRAINT fk_visitas_publicaciones
        FOREIGN KEY (id_publicacion) REFERENCES publicaciones(id_publicacion)
        ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY uq_pub_visita_fecha (id_publicacion, fecha_visita),
    INDEX idx_visitas_pub (id_publicacion),
    INDEX idx_visitas_fecha (fecha_visita)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Datos semilla iniciales de visitas para publicaciones existentes en los últimos 30 días
INSERT INTO publicacion_visitas (id_publicacion, fecha_visita, visitas)
SELECT p.id_publicacion, CURDATE() - INTERVAL d.dia DAY, 
       FLOOR(5 + (RAND() * 25)) AS visitas
FROM publicaciones p
CROSS JOIN (
    SELECT 0 AS dia UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4
    UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9
    UNION SELECT 10 UNION SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14
    UNION SELECT 15 UNION SELECT 16 UNION SELECT 17 UNION SELECT 18 UNION SELECT 19
    UNION SELECT 20 UNION SELECT 21 UNION SELECT 22 UNION SELECT 23 UNION SELECT 24
    UNION SELECT 25 UNION SELECT 26 UNION SELECT 27 UNION SELECT 28 UNION SELECT 29
) d
WHERE p.estado = 'Activo'
ON DUPLICATE KEY UPDATE visitas = VALUES(visitas);
