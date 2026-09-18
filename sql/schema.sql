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
    cedula_identidad VARCHAR(255) NULL,
    cedula_hash VARCHAR(64) NULL UNIQUE,
    nombre_usuario VARCHAR(30) NULL UNIQUE,
    genero VARCHAR(30) NOT NULL DEFAULT 'sin-especificar',
    fecha_nacimiento DATE NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    telefono VARCHAR(30) NULL,
    fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    id_rol INT NOT NULL,
    foto_perfil VARCHAR(255) NULL DEFAULT NULL,
    github_id VARCHAR(100) NULL UNIQUE,
    dos_factores_activo TINYINT(1) NOT NULL DEFAULT 0,
    dos_factores_secreto VARCHAR(255) NULL,
    dos_factores_backup_codes TEXT NULL,
    email_verificado TINYINT(1) NOT NULL DEFAULT 1,
    email_verificacion_token CHAR(64) NULL,
    email_verificacion_expira DATETIME NULL,
    nuevo_email_pendiente VARCHAR(150) NULL,
    nuevo_email_token CHAR(64) NULL,
    nuevo_email_expira DATETIME NULL,
    datos_cambiados_en DATETIME NULL,
    password_reset_token CHAR(64) NULL,
    password_reset_expira DATETIME NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    fecha_baja DATETIME NULL,
    onboarding_step TINYINT UNSIGNED NOT NULL DEFAULT 1,
    onboarding_data TEXT NULL,
    CONSTRAINT fk_usuarios_roles
        FOREIGN KEY (id_rol) REFERENCES roles(id_rol)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS categorias (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre_categoria VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT NULL,
    creada_por INT NULL,
    CONSTRAINT fk_categorias_creador
        FOREIGN KEY (creada_por) REFERENCES usuarios(id_usuario)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS publicaciones (
    id_publicacion INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    tipo ENUM('Curso', 'Servicio') NOT NULL,
    modalidad VARCHAR(30) NULL,
    nivel_experiencia VARCHAR(30) NULL,
    duracion_horas SMALLINT UNSIGNED NULL,
    cupos INT NULL,
    disponibilidad TEXT NULL,
    tipo_servicio VARCHAR(60) NULL,
    estado ENUM('Activo', 'Inactivo', 'Pausado', 'Eliminado') NOT NULL DEFAULT 'Activo',
    imagen VARCHAR(255) NULL DEFAULT NULL,
    eliminado_en DATETIME NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    id_usuario INT NOT NULL,
    id_categoria INT NOT NULL,
    CONSTRAINT fk_publicaciones_usuarios FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_publicaciones_categorias FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS solicitudes (
    id_solicitud INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT NOT NULL,
    estado ENUM('Pendiente', 'Aceptada', 'Rechazada', 'Contraoferta', 'En Proceso', 'Realizada', 'Cancelada') NOT NULL DEFAULT 'Pendiente',
    detalles_json JSON NULL,
    archivo_adjunto VARCHAR(255) NULL,
    precio_propuesto DECIMAL(10,2) NULL,
    fecha_hora_propuesta DATETIME NULL,
    respuesta_proveedor TEXT NULL,
    fecha_solicitud DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    id_usuario INT NOT NULL,
    id_publicacion INT NULL,
    id_contratacion INT NULL,
    CONSTRAINT fk_solicitudes_usuarios FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_solicitudes_publicaciones FOREIGN KEY (id_publicacion) REFERENCES publicaciones(id_publicacion) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS solicitudes_docente (
    id_solicitud_docente INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    estado ENUM('Pendiente', 'Aprobada', 'Rechazada') NOT NULL DEFAULT 'Pendiente',
    motivo TEXT NULL,
    fecha_solicitud DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_respuesta DATETIME NULL,
    CONSTRAINT fk_solicitudes_docente_usuarios
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_solicitudes_docente_estado (estado),
    INDEX idx_solicitudes_docente_usuario_estado (id_usuario, estado)
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
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT uq_valoraciones_contratacion_usuario
        UNIQUE (id_contratacion, id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE IF NOT EXISTS perfiles_profesionales (
    id_perfil INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL UNIQUE,
    titulo_profesional VARCHAR(180) NULL,
    presentacion TEXT NULL,
    experiencia TEXT NULL,
    formacion TEXT NULL,
    certificaciones TEXT NULL,
    habilidades TEXT NULL,
    especialidades TEXT NULL,
    idiomas VARCHAR(255) NULL,
    ubicacion VARCHAR(150) NULL,
    modalidad_trabajo VARCHAR(150) NULL,
    portfolio_url VARCHAR(255) NULL,
    linkedin_url VARCHAR(255) NULL,
    tiempo_respuesta VARCHAR(100) NULL,
    visibilidad ENUM('Registrados','Relacionados') NOT NULL DEFAULT 'Registrados',
    actualizado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_perfiles_profesionales_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS curso_modulos (
    id_modulo INT AUTO_INCREMENT PRIMARY KEY,
    id_publicacion INT NOT NULL,
    titulo VARCHAR(180) NOT NULL,
    descripcion TEXT NULL,
    orden INT NOT NULL DEFAULT 1,
    CONSTRAINT fk_curso_modulos_publicacion FOREIGN KEY (id_publicacion) REFERENCES publicaciones(id_publicacion) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_curso_modulos_publicacion_orden (id_publicacion,orden)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS curso_unidades (
    id_unidad INT AUTO_INCREMENT PRIMARY KEY,
    id_modulo INT NOT NULL,
    titulo VARCHAR(180) NOT NULL,
    descripcion TEXT NULL,
    orden INT NOT NULL DEFAULT 1,
    CONSTRAINT fk_curso_unidades_modulo FOREIGN KEY (id_modulo) REFERENCES curso_modulos(id_modulo) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_curso_unidades_modulo_orden (id_modulo,orden)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS curso_recursos (
    id_recurso INT AUTO_INCREMENT PRIMARY KEY,
    id_unidad INT NOT NULL,
    titulo VARCHAR(180) NOT NULL,
    tipo ENUM('Archivo','Foro','Entrega de Tareas','Video','PDF','Imagen','Enlace') NOT NULL,
    url VARCHAR(500) NULL,
    archivo VARCHAR(255) NULL,
    descripcion TEXT NULL,
    orden INT NOT NULL DEFAULT 1,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_curso_recursos_unidad FOREIGN KEY (id_unidad) REFERENCES curso_unidades(id_unidad) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_curso_recursos_unidad_orden (id_unidad,orden)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS curso_entregas (
    id_entrega INT AUTO_INCREMENT PRIMARY KEY,
    id_recurso INT NOT NULL,
    id_usuario INT NOT NULL,
    archivo_entrega VARCHAR(500) NULL,
    comentario_entrega TEXT NULL,
    estado ENUM('Entregada', 'Calificada') NOT NULL DEFAULT 'Entregada',
    calificacion DECIMAL(4,2) NULL,
    fecha_entrega DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_curso_entregas_recurso FOREIGN KEY (id_recurso) REFERENCES curso_recursos(id_recurso) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_curso_entregas_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY uk_recurso_usuario (id_recurso, id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS curso_foro_mensajes (
    id_mensaje INT AUTO_INCREMENT PRIMARY KEY,
    id_recurso INT NOT NULL,
    id_usuario INT NOT NULL,
    mensaje TEXT NOT NULL,
    fecha_mensaje DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_curso_foro_recurso FOREIGN KEY (id_recurso) REFERENCES curso_recursos(id_recurso) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_curso_foro_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_curso_foro_recurso_fecha (id_recurso, fecha_mensaje)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS solicitud_mensajes (
    id_mensaje INT AUTO_INCREMENT PRIMARY KEY,
    id_solicitud INT NOT NULL,
    id_usuario INT NOT NULL,
    mensaje TEXT NOT NULL,
    fecha_mensaje DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_solicitud_mensajes_solicitud FOREIGN KEY (id_solicitud) REFERENCES solicitudes(id_solicitud) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_solicitud_mensajes_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_solicitud_mensajes_fecha (id_solicitud,fecha_mensaje)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE solicitudes
    ADD CONSTRAINT fk_solicitudes_contratacion
    FOREIGN KEY (id_contratacion) REFERENCES contrataciones(id_contratacion)
    ON DELETE SET NULL ON UPDATE CASCADE;

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

INSERT IGNORE INTO usuarios (id_usuario, nombre, apellido, email, password_hash, telefono, fecha_registro, id_rol, email_verificado, onboarding_step) VALUES
(1, 'Carlos', 'Admin', 'admin@classia.com', '$2y$10$5sRoonQ8BOFLS7jHfFKCmucev28F2JqZyeysT1lpNiYxbP9caKfPe', '1155443322', '2026-01-10 09:00:00', 3, 1, 10),
(2, 'María', 'Docente', 'docente@classia.com', '$2y$10$5sRoonQ8BOFLS7jHfFKCmucev28F2JqZyeysT1lpNiYxbP9caKfPe', '1199887766', '2026-01-15 10:30:00', 2, 1, 10),
(3, 'Roberto', 'Gómez', 'roberto.gomez@classia.com', '$2y$10$5sRoonQ8BOFLS7jHfFKCmucev28F2JqZyeysT1lpNiYxbP9caKfPe', '1144332211', '2026-01-20 11:15:00', 2, 1, 10),
(4, 'Lucía', 'Fernández', 'lucia.fernandez@classia.com', '$2y$10$5sRoonQ8BOFLS7jHfFKCmucev28F2JqZyeysT1lpNiYxbP9caKfPe', '1133221100', '2026-02-01 14:00:00', 2, 1, 10),
(5, 'Gonzalo', 'Martínez', 'gonzalo.martinez@classia.com', '$2y$10$5sRoonQ8BOFLS7jHfFKCmucev28F2JqZyeysT1lpNiYxbP9caKfPe', '1122110099', '2026-02-05 16:45:00', 2, 1, 10),
(6, 'Juan', 'Pérez', 'estudiante@classia.com', '$2y$10$5sRoonQ8BOFLS7jHfFKCmucev28F2JqZyeysT1lpNiYxbP9caKfPe', '1122334455', '2026-02-10 12:00:00', 1, 1, 10),
(7, 'Ana', 'Silva', 'ana.silva@classia.com', '$2y$10$5sRoonQ8BOFLS7jHfFKCmucev28F2JqZyeysT1lpNiYxbP9caKfPe', '1166778899', '2026-02-12 13:20:00', 1, 1, 10),
(8, 'Diego', 'López', 'diego.lopez@classia.com', '$2y$10$5sRoonQ8BOFLS7jHfFKCmucev28F2JqZyeysT1lpNiYxbP9caKfPe', '1177889900', '2026-02-15 15:10:00', 1, 1, 10),
(9, 'Sofía', 'Rodríguez', 'sofia.rodriguez@classia.com', '$2y$10$5sRoonQ8BOFLS7jHfFKCmucev28F2JqZyeysT1lpNiYxbP9caKfPe', '1188990011', '2026-02-18 17:30:00', 1, 1, 10),
(10, 'Martín', 'Benítez', 'martin.benitez@classia.com', '$2y$10$5sRoonQ8BOFLS7jHfFKCmucev28F2JqZyeysT1lpNiYxbP9caKfPe', '1199001122', '2026-02-20 09:45:00', 1, 1, 10);

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

UPDATE publicaciones SET tipo_servicio='impresion_3d' WHERE id_publicacion=2 AND tipo='Servicio' AND tipo_servicio IS NULL;
UPDATE publicaciones SET tipo_servicio='mentoria' WHERE id_publicacion=4 AND tipo='Servicio' AND tipo_servicio IS NULL;
UPDATE publicaciones SET tipo_servicio='formacion_institucional' WHERE id_publicacion=10 AND tipo='Servicio' AND tipo_servicio IS NULL;


INSERT IGNORE INTO solicitudes_docente (id_solicitud_docente, id_usuario, estado, motivo, fecha_solicitud, fecha_respuesta) VALUES
(1, 6, 'Aprobada', 'Poseo 4 años de experiencia como desarrollador backend y deseo dictar cursos de Laravel y API REST.', '2026-02-15 09:00:00', '2026-02-16 14:30:00'),
(2, 7, 'Pendiente', 'Especialista en robótica educativa con proyectos Arduino en nivel secundario y terciario.', '2026-02-18 10:20:00', NULL),
(3, 8, 'Rechazada', 'Interés en dictar cursos básicos sin adjuntar certificaciones ni experiencia comprobable.', '2026-02-20 11:45:00', '2026-02-21 16:00:00'),
(4, 9, 'Aprobada', 'Diseñadora UX/UI con certificación Figma y experiencia liderando proyectos digitales.', '2026-02-22 15:10:00', '2026-02-23 10:00:00'),
(5, 10, 'Pendiente', 'Ingeniero electrónico con trayectoria en diseño de hardware libre y microcontroladores ESP32.', '2026-02-25 12:00:00', NULL),
(6, 6, 'Aprobada', 'Actualización de solicitud para habilitación de servicios de consultoría técnica.', '2026-03-01 08:30:00', '2026-03-02 09:15:00'),
(7, 7, 'Pendiente', 'Postulación para dictado de talleres de modelado paramétrico e impresión 3D.', '2026-03-03 14:00:00', NULL),
(8, 8, 'Pendiente', 'Especialista en seguridad informática enfocado en auditorías web OWASP.', '2026-03-05 16:20:00', NULL),
(9, 9, 'Aprobada', 'Docente de inglés técnico con experiencia en capacitación a equipos de ingeniería.', '2026-03-07 10:10:00', '2026-03-08 11:30:00'),
(10, 10, 'Pendiente', 'Scrum Master certificado buscando impartir mentorías de agilidad y gestión de software.', '2026-03-09 17:40:00', NULL);

INSERT IGNORE INTO perfiles_profesionales (id_perfil, id_usuario, titulo_profesional, presentacion, experiencia, formacion, certificaciones, habilidades, especialidades, idiomas, ubicacion, modalidad_trabajo, portfolio_url, linkedin_url, tiempo_respuesta, visibilidad) VALUES
(1, 1, 'Administrador de Plataforma y DevOps', 'Coordinador técnico de la infraestructura y seguridad en Classia.', '8 años en administración de sistemas Linux, bases de datos MariaDB y orquestación con Docker.', 'Licenciado en Sistemas de Información', 'LPIC-2, AWS Certified Solutions Architect', 'Linux, Docker, MySQL, Ciberseguridad, PHP', 'Infraestructura educativa y DevOps', 'Español (Nativo), Inglés (Avanzado)', 'Montevideo, Uruguay', 'Remoto / Híbrido', 'https://github.com/classia-admin', 'https://linkedin.com/in/carlos-admin-classia', 'Menos de 2 horas', 'Registrados'),
(2, 2, 'Desarrolladora Web Full Stack & Docente TI', 'Apasionada por la enseñanza de tecnologías web modernas y arquitecturas de software limpias.', '6 años como docente y desarrolladora backend en entornos LAMP y stacks modernos.', 'Ingeniería en Computación (UdelaR)', 'Zend Certified PHP Engineer, Scrum Master', 'PHP 8, MySQL, JavaScript, HTML5, CSS3, Docker', 'Desarrollo Backend, Arquitectura MVC, REST APIs', 'Español, Inglés B2', 'Salto, Uruguay', 'Virtual / Remoto', 'https://github.com/maria-docente', 'https://linkedin.com/in/maria-docente', 'Menos de 4 horas', 'Registrados'),
(3, 3, 'Especialista en Hardware, IoT y Robótica', 'Instructor de robótica aplicada y circuitos electrónicos para proyectos de automatización.', '5 años capacitando a estudiantes en electrónica digital, sensores y microcontroladores.', 'Tecnólogo en Mecatrónica', 'Certificación Arduino Oficial, IPC-A-610', 'Arduino, ESP32, KiCad, Impresión 3D, C/C++', 'Sistemas Embebidos e Internet de las Cosas', 'Español, Portugués', 'Paysandú, Uruguay', 'Presencial / Híbrida', 'https://portfolio.robertogomez.tech', 'https://linkedin.com/in/roberto-gomez-iot', 'Menos de 6 horas', 'Registrados'),
(4, 4, 'Diseñadora UX/UI & Desarrolladora Frontend', 'Diseño de experiencias centradas en el usuario con foco en accesibilidad e interacción moderna.', '4 años creando prototipos de alta fidelidad y guiando cursos prácticos de diseño visual.', 'Diseño Gráfico Digital y Multimedia', 'NN/g UX Master, Interaction Design Foundation', 'Figma, Design Systems, CSS Grid, React, Wireframing', 'Interfaces Educativas y Accesibilidad Web', 'Español, Inglés', 'Montevideo, Uruguay', 'Remoto', 'https://dribbble.com/luciafernandez-ux', 'https://linkedin.com/in/lucia-fernandez-ux', 'Menos de 3 horas', 'Registrados'),
(5, 5, 'Científico de Datos & Consultor de Ciberseguridad', 'Docente e investigador en análisis de datos, modelos de IA y auditorías de vulnerabilidades.', '7 años en consultoría de seguridad informática y entrenamiento de modelos predictivos.', 'Máster en Ciencia de Datos y Seguridad Informática', 'CompTIA Security+, Certified Ethical Hacker (CEH)', 'Python, Pandas, Scikit-Learn, Pentesting, OWASP', 'Data Science, Machine Learning y Pentesting Web', 'Español, Inglés C1', 'Colonia, Uruguay', 'Virtual / Remoto', 'https://gonzalomartinez.dev', 'https://linkedin.com/in/gonzalo-martinez-sec', 'Menos de 5 horas', 'Registrados'),
(6, 6, 'Estudiante Avanzado & Desarrollador Junior', 'Interesado en participar en proyectos colaborativos y reforzar conocimientos en frontend.', '2 años de prácticas formativas y proyectos open-source.', 'Estudiante de Profesorado de Informática (CeRP)', 'Curso Frontend Developer', 'HTML, CSS, JavaScript, Git', 'Maquetación Web y Control de Versiones', 'Español', 'Salto, Uruguay', 'Híbrida', 'https://github.com/juanperez-dev', 'https://linkedin.com/in/juan-perez-student', 'Menos de 24 horas', 'Registrados'),
(7, 7, 'Diseñadora 3D & Prototipista Técnica', 'Apasionada por la fabricación digital, impresión 3D y diseño de productos personalizados.', '3 años operando impresoras FDM y diseñando piezas en Blender y Fusion 360.', 'Técnica en Fabricación Digital', 'Certificado Autodesk Fusion 360', 'Fusion 360, Blender, Cura, PrusaSlicer, PLA/PETG', 'Modelado 3D para Prototipos Didácticos', 'Español, Portugués', 'Rivera, Uruguay', 'Presencial', 'https://anasilva3d.artstation.com', 'https://linkedin.com/in/ana-silva-3d', 'Menos de 12 horas', 'Registrados'),
(8, 8, 'Técnico en Redes y Soporte Informático', 'Especialista en cableado estructurado, configuración de routers y seguridad de red local.', '3 años en soporte informático corporativo y mantenimiento de servidores.', 'Técnico en Redes y Telecomunicaciones', 'Cisco CCNA 1 y 2', 'TCP/IP, Routing, Switching, Linux Server, Hardening', 'Infraestructura de Redes y Diagnóstico', 'Español, Inglés Técnico', 'Salto, Uruguay', 'Presencial / Remoto', 'https://diegolopez.net/soporte', 'https://linkedin.com/in/diego-lopez-redes', 'Menos de 8 horas', 'Registrados'),
(9, 9, 'Traductora Técnica & Diseñadora Visual', 'Especialista en traducción inglés-español de contenidos tecnológicos y localización de software.', '4 años trabajando con comunidades de software libre y redacción de especificaciones.', 'Traductorado Técnico-Científico en Inglés', 'Cambridge C2 Proficiency, Technical Writing Certification', 'Technical English, Redacción de APIs, Markdown, Git', 'Documentación de Software y Localización', 'Español, Inglés Nativo', 'Montevideo, Uruguay', 'Remoto', 'https://sofiarodriguez-translations.com', 'https://linkedin.com/in/sofia-rodriguez-tr', 'Menos de 4 horas', 'Registrados'),
(10, 10, 'Agile Coach & Gestor de Proyectos de Software', 'Facilitador de dinámicas de equipo, Scrum, Kanban y mejora continua en proyectos IT.', '5 años acompañando startups y equipos universitarios en metodologías ágiles.', 'Licenciado en Administración y Gestión Tecnológica', 'PSM I (Professional Scrum Master), PMI-ACP', 'Scrum, Kanban, Jira, Trello, Métricas Ágiles, Retrospectivas', 'Transformación Ágil y Coordinación de Equipos', 'Español, Inglés B2', 'Maldonado, Uruguay', 'Remoto / Híbrido', 'https://martinbenitez-agile.com', 'https://linkedin.com/in/martin-benitez-scrum', 'Menos de 6 horas', 'Registrados');

INSERT IGNORE INTO curso_modulos (id_modulo, id_publicacion, titulo, descripcion, orden) VALUES
(1, 1, 'Módulo 1: Fundamentos de PHP Moderno', 'Sintaxis básica, estructuras de control, funciones y manejo de tipos estrictos en PHP 8.', 1),
(2, 1, 'Módulo 2: Programación Orientada a Objetos y PDO', 'Clases, interfaces, herencia, conexión a bases de datos y sentencias preparadas contra inyecciones SQL.', 2),
(3, 1, 'Módulo 3: Arquitectura MVC y Seguridad Web', 'Separación de vistas, lógica de negocio, sesiones seguras, CSRF y hash de contraseñas.', 3),
(4, 3, 'Módulo 1: Introducción a la Electrónica y Arduino', 'Conceptos de voltaje, corriente, ley de Ohm y estructura del microcontrolador ATmega328P.', 1),
(5, 3, 'Módulo 2: Sensores, Actuadores y Comunicación Serie', 'Lectura de sensores analógicos/digitales, control de servomotores y pantallas LCD.', 2),
(6, 5, 'Módulo 1: Fundamentos de Python y Estructuras de Datos', 'Variables, listas, tuplas, diccionarios y funciones lambda en Python 3.', 1),
(7, 5, 'Módulo 2: Manipulación y Visualización con NumPy y Pandas', 'DataFrames, limpieza de valores nulos, agregaciones y gráficos interactivos con Matplotlib/Seaborn.', 2),
(8, 6, 'Módulo 1: Fundamentos de Circuitos y Simulación', 'Diseño de diagramas esquemáticos con KiCad y simulación de circuitos analógicos.', 1),
(9, 8, 'Módulo 1: Fundamentos de UX y Arquitectura de la Información', 'Research con usuarios, mapas de empatía, user journeys y card sorting.', 1),
(10, 8, 'Módulo 2: Prototipado y Design Systems en Figma', 'Componentes reutilizables, auto-layout, variantes e interacciones animadas.', 2);

INSERT IGNORE INTO curso_unidades (id_unidad, id_modulo, titulo, descripcion, orden) VALUES
(1, 1, 'Unidad 1.1: Instalación del Entorno y Sintaxis Básica', 'Configuración de PHP 8, Apache y variables de entorno.', 1),
(2, 1, 'Unidad 1.2: Estructuras de Control y Arreglos Asociativos', 'Uso de bucles foreach, match, funciones de array y validaciones.', 2),
(3, 2, 'Unidad 2.1: Conexión Segura con PDO y Manejo de Errores', 'Instanciación de PDO, opciones de atributos y captura de PDOException.', 1),
(4, 2, 'Unidad 2.2: Consultas Preparadas (CRUD Completo)', 'Operaciones de alta, baja, modificación y consulta utilizando marcadores de posición.', 2),
(5, 3, 'Unidad 3.1: Control de Sesiones y Protección contra CSRF', 'session_start seguro, tokens anti-CSRF y expiración por inactividad.', 1),
(6, 4, 'Unidad 1.1: Componentes Básicos y Entorno IDE', 'Instalación de Arduino IDE, placas compatibles y primer sketch Blink.', 1),
(7, 5, 'Unidad 2.1: Sensores Ultrasónicos y Servomotores', 'Medición de distancia con HC-SR04 y control de servomotores SG90.', 1),
(8, 6, 'Unidad 1.1: Sintaxis de Python y Tipos de Datos', 'Variables, tipos primitivos, listas y comprensiones.', 1),
(9, 7, 'Unidad 2.1: Carga y Análisis de Datasets con Pandas', 'Lectura de archivos CSV, filtros booleanos y cálculo de estadísticas descriptivas.', 1),
(10, 10, 'Unidad 2.1: Creación de Componentes y Variantes en Figma', 'Estructuración de botones, inputs y tarjetas con diseño responsive en Figma.', 1);

INSERT IGNORE INTO curso_recursos (id_recurso, id_unidad, titulo, tipo, url, archivo, descripcion, orden, fecha_creacion) VALUES
(1, 1, 'Guía de Instalación de PHP 8 y Docker', 'PDF', NULL, 'assets/uploads/cursos/guia_php_docker.pdf', 'Documento paso a paso para levantar el entorno local de desarrollo.', 1, '2026-02-21 10:30:00'),
(2, 1, 'Repositorio de Ejemplos Básicos en GitHub', 'Enlace', 'https://github.com/classia-demo/php-fundamentos', NULL, 'Código fuente de los ejemplos vistos en la clase inaugural.', 2, '2026-02-21 11:00:00'),
(3, 2, 'Cheat Sheet: Funciones de Arreglos en PHP', 'PDF', NULL, 'assets/uploads/cursos/cheatsheet_arrays_php.pdf', 'Resumen imprimible de funciones útiles como array_map y array_filter.', 1, '2026-02-21 11:45:00'),
(4, 3, 'Diagrama de Arquitectura de Conexión PDO', 'Imagen', NULL, 'assets/uploads/cursos/diagrama_pdo.png', 'Esquema visual del patrón de conexión segura con MySQL/MariaDB.', 1, '2026-02-22 09:00:00'),
(5, 4, 'Script de Prueba de Sentencias Preparadas', 'Archivo', NULL, 'assets/uploads/cursos/ejemplo_pdo_crud.zip', 'Código de ejemplo con consultas SELECT, INSERT y UPDATE comentadas.', 1, '2026-02-22 14:20:00'),
(6, 5, 'Video Explicativo: Ataques CSRF y Mitigación', 'Video', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', NULL, 'Demostración práctica de cómo proteger formularios contra falsificación de petición.', 1, '2026-02-23 10:15:00'),
(7, 6, 'Manual Oficial de Pinout Arduino Uno R3', 'PDF', NULL, 'assets/uploads/cursos/pinout_arduino_uno.pdf', 'Diagrama esquemático con los pines de entrada y salida digital y analógica.', 1, '2026-02-23 15:00:00'),
(8, 7, 'Código Sketch: Radar Ultrasónico', 'Archivo', NULL, 'assets/uploads/cursos/sketch_radar_hcsr04.ino', 'Sketch de Arduino para leer distancia y accionar una señal lumínica.', 1, '2026-02-24 09:30:00'),
(9, 9, 'Dataset de Prueba: Ventas y Estadísticas (.csv)', 'Archivo', NULL, 'assets/uploads/cursos/dataset_ventas_2026.csv', 'Conjunto de datos estructurados con 500 filas para ejercicios de análisis.', 1, '2026-02-25 11:00:00'),
(10, 10, 'Kit de UI y Design System en Figma Community', 'Enlace', 'https://www.figma.com/community/file/classia-ui-kit', NULL, 'Archivo base de Figma con componentes atómicos y paleta de colores.', 1, '2026-02-28 17:30:00');

INSERT IGNORE INTO solicitud_mensajes (id_mensaje, id_solicitud, id_usuario, mensaje, fecha_mensaje) VALUES
(1, 1, 6, 'Hola María, te comparto las medidas requeridas: 45mm de diámetro exterior y 12 dientes en el engranaje.', '2026-03-02 09:20:00'),
(2, 1, 2, 'Recibido Juan. Lo imprimiré con altura de capa de 0.2mm en PETG para máxima resistencia mecánica.', '2026-03-02 09:45:00'),
(3, 2, 7, 'Hola, tengo dudas puntuales sobre LEFT JOIN múltiples y cláusulas HAVING en reportes.', '2026-03-02 11:45:00'),
(4, 2, 2, '¡Perfecto Ana! Coordinamos una sesión de 2 horas con ejercicios prácticos basados en tu esquema.', '2026-03-02 12:10:00'),
(5, 3, 8, 'Hola Roberto, ¿el taller incluye la configuración de la librería WiFi y cliente MQTT?', '2026-03-03 14:15:00'),
(6, 4, 9, 'Hola Lucía, te adjunto el link al repositorio para que puedas revisar el flujo de navegación.', '2026-03-04 10:35:00'),
(7, 4, 4, 'Excelente Sofía, revisé el repo y veo áreas claras de mejora en el renderizado condicional.', '2026-03-04 11:20:00'),
(8, 5, 10, 'Hola, necesitaría que el soporte tenga orificios para tornillos métricos M3.', '2026-03-05 17:00:00'),
(9, 5, 2, 'Anotado Martín, adapto el modelo CAD y te envío un render antes de imprimir.', '2026-03-05 17:30:00'),
(10, 10, 10, 'Gonzalo, gracias por aceptar la consulta. ¿Podemos enfocarnos en la comparativa de consumo energético?', '2026-03-10 14:25:00');