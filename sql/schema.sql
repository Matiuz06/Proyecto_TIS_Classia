CREATE DATABASE IF NOT EXISTS classia_db
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE classia_db;


CREATE USER IF NOT EXISTS 'classia_user'@'localhost' IDENTIFIED BY 'CONTRASENA_LOCAL';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, ALTER, INDEX ON classia_db.* TO 'classia_user'@'localhost';
FLUSH PRIVILEGES;


CREATE TABLE IF NOT EXISTS roles (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,
    nombre_rol VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    telefono VARCHAR(30) NULL,
    fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    id_rol INT NOT NULL,
    CONSTRAINT fk_usuarios_roles
        FOREIGN KEY (id_rol) REFERENCES roles(id_rol)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS categorias (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre_categoria VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS publicaciones (
    id_publicacion INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    tipo ENUM('Curso', 'Servicio') NOT NULL,
    estado ENUM('Activo', 'Inactivo', 'Pausado') NOT NULL DEFAULT 'Activo',
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    id_usuario INT NOT NULL,
    id_categoria INT NOT NULL,
    CONSTRAINT fk_publicaciones_usuarios
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_publicaciones_categorias
        FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS solicitudes (
    id_solicitud INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT NOT NULL,
    estado ENUM('Pendiente', 'Aceptada', 'Rechazada', 'Cancelada') NOT NULL DEFAULT 'Pendiente',
    fecha_solicitud DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    id_usuario INT NOT NULL,
    id_publicacion INT NULL,
    CONSTRAINT fk_solicitudes_usuarios
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_solicitudes_publicaciones
        FOREIGN KEY (id_publicacion) REFERENCES publicaciones(id_publicacion)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS contrataciones (
    id_contratacion INT AUTO_INCREMENT PRIMARY KEY,
    fecha_contratacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    monto_total DECIMAL(10,2) NOT NULL,
    estado ENUM('Pendiente', 'En Proceso', 'Completada', 'Cancelada') NOT NULL DEFAULT 'Pendiente',
    id_usuario INT NOT NULL,
    CONSTRAINT fk_contrataciones_usuarios
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS detalles_contratacion (
    id_detalle INT AUTO_INCREMENT PRIMARY KEY,
    cantidad INT NOT NULL DEFAULT 1,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    id_contratacion INT NOT NULL,
    id_publicacion INT NOT NULL,
    CONSTRAINT fk_detalles_contrataciones
        FOREIGN KEY (id_contratacion) REFERENCES contrataciones(id_contratacion)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_detalles_publicaciones
        FOREIGN KEY (id_publicacion) REFERENCES publicaciones(id_publicacion)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pagos (
    id_pago INT AUTO_INCREMENT PRIMARY KEY,
    monto DECIMAL(10,2) NOT NULL,
    metodo_pago ENUM('Tarjeta', 'Transferencia', 'MercadoPago', 'Efectivo') NOT NULL,
    estado_pago ENUM('Pendiente', 'Aprobado', 'Rechazado') NOT NULL DEFAULT 'Pendiente',
    fecha_pago DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    transaccion_ref VARCHAR(100) NULL,
    id_contratacion INT NOT NULL,
    CONSTRAINT fk_pagos_contrataciones
        FOREIGN KEY (id_contratacion) REFERENCES contrataciones(id_contratacion)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS valoraciones (
    id_valoracion INT AUTO_INCREMENT PRIMARY KEY,
    puntuacion INT NOT NULL CHECK (puntuacion BETWEEN 1 AND 5),
    comentario TEXT NULL,
    fecha_valoracion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    id_usuario INT NOT NULL,
    id_publicacion INT NOT NULL,
    id_contratacion INT NULL,
    CONSTRAINT fk_valoraciones_usuarios
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_valoraciones_publicaciones
        FOREIGN KEY (id_publicacion) REFERENCES publicaciones(id_publicacion)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_valoraciones_contrataciones
        FOREIGN KEY (id_contratacion) REFERENCES contrataciones(id_contratacion)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO roles (id_rol, nombre_rol, descripcion) VALUES
(1, 'Cliente/Estudiante', 'Usuario consumidor de cursos y servicios'),
(2, 'Docente/Proveedor', 'Usuario creador y prestador de servicios educativos'),
(3, 'Administrador', 'Superusuario del sistema');

INSERT IGNORE INTO categorias (id_categoria, nombre_categoria, descripcion) VALUES
(1, 'Programación y Desarrollo', 'Cursos de software, desarrollo web, móvil y backend'),
(2, 'Robótica y Automatización', 'Proyectos de hardware, Arduino, electrónica y robótica aplicada'),
(3, 'Diseño e Impresión 3D', 'Modelado 3D, prototipado e impresión digital técnica'),
(4, 'Mentorías y Capacitación', 'Asesorías personalizadas y refuerzo escolar adaptado'),
(5, 'Inteligencia Artificial y Datos', 'Ciencia de datos, aprendizaje automático y procesamiento de información'),
(6, 'Electrónica y Microcontroladores', 'Circuitos analógicos y digitales, ESP32 y sistemas embebidos'),
(7, 'Ciberseguridad y Redes', 'Fundamentos de seguridad informática, redes y protección de datos'),
(8, 'Diseño Web y UX/UI', 'Interfaces de usuario, experiencia de usuario y maquetación web moderna'),
(9, 'Idiomas y Comunicación Técnica', 'Inglés técnico para desarrolladores y redacción de documentación'),
(10, 'Gestión de Proyectos Tecnológicos', 'Metodologías ágiles, Scrum y dirección de proyectos de software');

INSERT IGNORE INTO usuarios (id_usuario, nombre, apellido, email, password_hash, telefono, fecha_registro, id_rol) VALUES
(1, 'Carlos', 'Admin', 'admin@classia.com', '$2y$10$e.1234567890123456789012345678901234567890123456789012', '1155443322', '2026-01-10 09:00:00', 3),
(2, 'María', 'Docente', 'docente@classia.com', '$2y$10$e.1234567890123456789012345678901234567890123456789012', '1199887766', '2026-01-15 10:30:00', 2),
(3, 'Roberto', 'Gómez', 'roberto.gomez@classia.com', '$2y$10$e.1234567890123456789012345678901234567890123456789012', '1144332211', '2026-01-20 11:15:00', 2),
(4, 'Lucía', 'Fernández', 'lucia.fernandez@classia.com', '$2y$10$e.1234567890123456789012345678901234567890123456789012', '1133221100', '2026-02-01 14:00:00', 2),
(5, 'Gonzalo', 'Martínez', 'gonzalo.martinez@classia.com', '$2y$10$e.1234567890123456789012345678901234567890123456789012', '1122110099', '2026-02-05 16:45:00', 2),
(6, 'Juan', 'Pérez', 'estudiante@classia.com', '$2y$10$e.1234567890123456789012345678901234567890123456789012', '1122334455', '2026-02-10 12:00:00', 1),
(7, 'Ana', 'Silva', 'ana.silva@classia.com', '$2y$10$e.1234567890123456789012345678901234567890123456789012', '1166778899', '2026-02-12 13:20:00', 1),
(8, 'Diego', 'López', 'diego.lopez@classia.com', '$2y$10$e.1234567890123456789012345678901234567890123456789012', '1177889900', '2026-02-15 15:10:00', 1),
(9, 'Sofía', 'Rodríguez', 'sofia.rodriguez@classia.com', '$2y$10$e.1234567890123456789012345678901234567890123456789012', '1188990011', '2026-02-18 17:30:00', 1),
(10, 'Martín', 'Benítez', 'martin.benitez@classia.com', '$2y$10$e.1234567890123456789012345678901234567890123456789012', '1199001122', '2026-02-20 09:45:00', 1);

INSERT IGNORE INTO publicaciones (id_publicacion, titulo, descripcion, precio, tipo, estado, fecha_creacion, id_usuario, id_categoria) VALUES
(1, 'Curso Completo de PHP y MySQL', 'Aprende backend desde cero hasta crear sistemas dinámicos seguros con PDO.', 15000.00, 'Curso', 'Activo', '2026-02-21 10:00:00', 2, 1),
(2, 'Servicio de Prototipado e Impresión 3D', 'Impresión de piezas técnicas y maquetas didácticas en PLA/PETG con alta precisión.', 8500.00, 'Servicio', 'Activo', '2026-02-22 11:30:00', 2, 3),
(3, 'Taller Introductorio de Arduino y Robótica', 'Construcción de circuitos básicos, sensores y programación de microcontroladores.', 12000.00, 'Curso', 'Activo', '2026-02-23 14:15:00', 3, 2),
(4, 'Mentoría Personalizada en JavaScript y React', 'Asesoría uno a uno para resolver dudas de proyectos web front-end.', 9500.00, 'Servicio', 'Activo', '2026-02-24 16:00:00', 4, 4),
(5, 'Introducción a Python y Ciencia de Datos', 'Fundamentos de Python, Numpy, Pandas y visualización de datos.', 18000.00, 'Curso', 'Activo', '2026-02-25 09:20:00', 5, 5),
(6, 'Diseño de PCB y Circuitos Electrónicos', 'Curso práctico de diseño de placas impresas con KiCAD.', 14000.00, 'Curso', 'Activo', '2026-02-26 12:45:00', 3, 6),
(7, 'Auditoría y Diagnóstico de Ciberseguridad Web', 'Evaluación de vulnerabilidades y buenas prácticas OWASP para aplicaciones web.', 25000.00, 'Servicio', 'Activo', '2026-02-27 15:10:00', 5, 7),
(8, 'Taller de Diseño de Interfaces UX/UI en Figma', 'Creación de prototipos interactivos, wireframes y sistemas de diseño.', 11000.00, 'Curso', 'Activo', '2026-02-28 17:00:00', 4, 8),
(9, 'Clases de Inglés Técnico para Programadores', 'Capacitación orientada a entrevistas de trabajo y lectura de documentación en inglés.', 10000.00, 'Curso', 'Activo', '2026-03-01 08:30:00', 2, 9),
(10, 'Consultoría en Implementación de Metodologías Ágiles', 'Asesoría para equipos de desarrollo en adopción de Scrum y Kanban.', 30000.00, 'Servicio', 'Activo', '2026-03-01 13:00:00', 5, 10);

INSERT IGNORE INTO solicitudes (id_solicitud, titulo, descripcion, estado, fecha_solicitud, id_usuario, id_publicacion) VALUES
(1, 'Solicitud de Maqueta 3D para Proyecto Final', 'Requiero la impresión 3D de un engranaje y carcasa en material PLA negro.', 'Aceptada', '2026-03-02 09:15:00', 6, 2),
(2, 'Refuerzo Personalizado en SQL Complejo', 'Busco tutoría de 2 horas para entender JOINs complejos y subconsultas en MySQL.', 'Aceptada', '2026-03-02 11:40:00', 7, 1),
(3, 'Asesoría en Prototipo Robótico con ESP32', 'Necesito ayuda para integrar un módulo WiFi ESP32 con sensores ultrasónicos.', 'Pendiente', '2026-03-03 14:00:00', 8, 3),
(4, 'Revisión de Código React Native', 'Solicito revisión de arquitectura y corrección de bugs en aplicación móvil.', 'Aceptada', '2026-03-04 10:20:00', 9, 4),
(5, 'Modelado e Impresión de Soporte para Cámara', 'Diseño e impresión a medida de un soporte ajustable para cámara web.', 'Aceptada', '2026-03-05 16:50:00', 10, 2),
(6, 'Capacitación Grupal en Git y GitHub Flow', 'Taller de 3 horas para equipo universitario sobre control de versiones.', 'Pendiente', '2026-03-06 12:30:00', 6, NULL),
(7, 'Diagnóstico de Seguridad para Servidor VPS', 'Solicitud de escaneo y hardening de servidor Linux centrado en Nginx.', 'Rechazada', '2026-03-07 15:15:00', 7, 7),
(8, 'Rediseño UX para Plataforma Educativa', 'Diseño de prototipo de alta fidelidad para sistema de notas estudiantiles.', 'Pendiente', '2026-03-08 18:00:00', 8, 8),
(9, 'Traducción de Documentación de API', 'Traducción de manual técnico de español a inglés para publicación open-source.', 'Cancelada', '2026-03-09 09:30:00', 9, 9),
(10, 'Asesoría en Selección de Microcontroladores', 'Consulta técnica sobre costo y eficiencia entre Arduino Nano y STM32.', 'Aceptada', '2026-03-10 14:10:00', 10, 6);

INSERT IGNORE INTO contrataciones (id_contratacion, fecha_contratacion, monto_total, estado, id_usuario) VALUES
(1, '2026-08-10 10:15:00', 15000.00, 'Completada', 6),
(2, '2026-08-12 14:30:00', 8500.00, 'Completada', 7),
(3, '2026-08-15 09:00:00', 12000.00, 'En Proceso', 8),
(4, '2026-08-18 16:45:00', 9500.00, 'Completada', 9),
(5, '2026-08-20 11:20:00', 18000.00, 'Pendiente', 10),
(6, '2026-08-22 15:10:00', 14000.00, 'En Proceso', 6),
(7, '2026-08-25 18:00:00', 25000.00, 'Completada', 7),
(8, '2026-08-28 13:25:00', 11000.00, 'Pendiente', 8),
(9, '2026-08-30 17:40:00', 10000.00, 'Cancelada', 9),
(10, '2026-09-01 08:50:00', 30000.00, 'En Proceso', 10);

INSERT IGNORE INTO detalles_contratacion (id_detalle, cantidad, precio_unitario, subtotal, id_contratacion, id_publicacion) VALUES
(1, 1, 15000.00, 15000.00, 1, 1),
(2, 1, 8500.00, 8500.00, 2, 2),
(3, 1, 12000.00, 12000.00, 3, 3),
(4, 1, 9500.00, 9500.00, 4, 4),
(5, 1, 18000.00, 18000.00, 5, 5),
(6, 1, 14000.00, 14000.00, 6, 6),
(7, 1, 25000.00, 25000.00, 7, 7),
(8, 1, 11000.00, 11000.00, 8, 8),
(9, 1, 10000.00, 10000.00, 9, 9),
(10, 1, 30000.00, 30000.00, 10, 10);

INSERT IGNORE INTO pagos (id_pago, monto, metodo_pago, estado_pago, fecha_pago, transaccion_ref, id_contratacion) VALUES
(1, 15000.00, 'MercadoPago', 'Aprobado', '2026-08-10 10:18:00', 'MP-TRX-998877', 1),
(2, 8500.00, 'Tarjeta', 'Aprobado', '2026-08-12 14:32:00', 'VISA-AUTH-4411', 2),
(3, 12000.00, 'Transferencia', 'Aprobado', '2026-08-15 09:15:00', 'TRF-BANCO-88221', 3),
(4, 9500.00, 'MercadoPago', 'Aprobado', '2026-08-18 16:48:00', 'MP-TRX-554433', 4),
(5, 18000.00, 'Tarjeta', 'Pendiente', '2026-08-20 11:20:00', 'VISA-PEND-1029', 5),
(6, 14000.00, 'Efectivo', 'Aprobado', '2026-08-22 15:15:00', 'REC-EF-33211', 6),
(7, 25000.00, 'Transferencia', 'Aprobado', '2026-08-25 18:10:00', 'TRF-BANCO-99001', 7),
(8, 11000.00, 'MercadoPago', 'Pendiente', '2026-08-28 13:25:00', 'MP-TRX-112233', 8),
(9, 10000.00, 'Tarjeta', 'Rechazado', '2026-08-30 17:42:00', 'VISA-DECL-8822', 9),
(10, 30000.00, 'Transferencia', 'Aprobado', '2026-09-01 09:00:00', 'TRF-BANCO-77112', 10);

INSERT IGNORE INTO valoraciones (id_valoracion, puntuacion, comentario, fecha_valoracion, id_usuario, id_publicacion, id_contratacion) VALUES
(1, 5, 'Excelente curso, muy claro y práctico. Recomendado 100%.', '2026-08-11 12:00:00', 6, 1, 1),
(2, 5, 'La impresión de las piezas quedó impecable y la entrega fue muy rápida.', '2026-08-13 16:20:00', 7, 2, 2),
(3, 4, 'Buen material práctico y explicaciones claras sobre Arduino.', '2026-08-16 11:30:00', 8, 3, 3),
(4, 5, 'La mentoría me ayudó a resolver un problema en React que me tenía estancado.', '2026-08-19 18:15:00', 9, 4, 4),
(5, 4, 'Excelente introducción a Python y manejo de librerías de ciencia de datos.', '2026-08-21 14:00:00', 10, 5, 5),
(6, 5, 'Muy buenas explicaciones sobre ruteo y diseño de PCB en KiCAD.', '2026-08-23 10:45:00', 6, 6, 6),
(7, 5, 'Informe de auditoría web exhaustivo con recomendaciones muy valiosas.', '2026-08-26 19:30:00', 7, 7, 7),
(8, 4, 'Contenido didáctico y muy buenos ejercicios prácticos en Figma.', '2026-08-29 15:10:00', 8, 8, 8),
(9, 3, 'El contenido es bueno aunque me hubiera gustado profundizar más en vocabulario específico.', '2026-08-31 09:20:00', 9, 9, 9),
(10, 5, 'Gran asesoría en Scrum, ayudó a organizar la dinámica de nuestro equipo.', '2026-09-01 11:00:00', 10, 10, 10);
