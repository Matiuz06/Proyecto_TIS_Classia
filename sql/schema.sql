CREATE DATABASE IF NOT EXISTS classia_db
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE classia_db;


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
(1, 'Programación y Desarrollo', 'Cursos de software, desarrollo web y móvil'),
(2, 'Robótica y Automatización', 'Proyectos de hardware, Arduino, electrónica y robótica'),
(3, 'Diseño e Impresión 3D', 'Modelado 3D, prototipado e impresión digital'),
(4, 'Mentorías y Capacitación', 'Asesorías personalizadas y refuerzo escolar');

INSERT IGNORE INTO usuarios (id_usuario, nombre, apellido, email, password_hash, telefono, id_rol) VALUES
(1, 'Juan', 'Pérez', 'estudiante@classia.com', '$2y$10$e.1234567890123456789012345678901234567890123456789012', '1122334455', 1),
(2, 'María', 'Docente', 'docente@classia.com', '$2y$10$e.1234567890123456789012345678901234567890123456789012', '1199887766', 2),
(3, 'Carlos', 'Admin', 'admin@classia.com', '$2y$10$e.1234567890123456789012345678901234567890123456789012', '1155443322', 3);


INSERT IGNORE INTO publicaciones (id_publicacion, titulo, descripcion, precio, tipo, estado, id_usuario, id_categoria) VALUES
(1, 'Curso Completo de PHP y MySQL', 'Aprende backend desde cero hasta crear sistemas dinámicos.', 15000.00, 'Curso', 'Activo', 2, 1),
(2, 'Servicio de Prototipado e Impresión 3D', 'Impresión de piezas técnicas y maquetas didácticas en PLA/PETG.', 8500.00, 'Servicio', 'Activo', 2, 3),
(3, 'Taller Introductorio de Arduino y Robótica', 'Construcción de circuitos básicos y programación de microcontroladores.', 12000.00, 'Curso', 'Activo', 2, 2);

INSERT IGNORE INTO contrataciones (id_contratacion, monto_total, estado, id_usuario) VALUES
(1, 15000.00, 'Completada', 1);

INSERT IGNORE INTO detalles_contratacion (id_detalle, cantidad, precio_unitario, subtotal, id_contratacion, id_publicacion) VALUES
(1, 1, 15000.00, 15000.00, 1, 1);

INSERT IGNORE INTO pagos (id_pago, monto, metodo_pago, estado_pago, transaccion_ref, id_contratacion) VALUES
(1, 15000.00, 'MercadoPago', 'Aprobado', 'MP-TRX-998877', 1);

INSERT IGNORE INTO valoraciones (id_valoracion, puntuacion, comentario, id_usuario, id_publicacion, id_contratacion) VALUES
(1, 5, 'Excelente curso, muy claro y práctico.', 1, 1, 1);

