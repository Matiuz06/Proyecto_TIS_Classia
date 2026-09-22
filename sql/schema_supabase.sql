-- ============================================================================
-- CLASSIA — ESQUEMA DE BASE DE DATOS PARA SUPABASE (POSTGRESQL 15 / 16)
-- ============================================================================
-- Este archivo contiene la definición completa de tablas, restricciones,
-- triggers de actualización automática y datos semilla iniciales para Supabase.
-- ============================================================================

-- Función utilitaria para actualizar automáticamente las columnas de timestamp
CREATE OR REPLACE FUNCTION trigger_set_timestamp()
RETURNS TRIGGER AS $$
BEGIN
  NEW.fecha_actualizacion = CURRENT_TIMESTAMP;
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION trigger_set_timestamp_actualizado_en()
RETURNS TRIGGER AS $$
BEGIN
  NEW.actualizado_en = CURRENT_TIMESTAMP;
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- ----------------------------------------------------------------------------
-- 1. TABLA: roles
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS roles (
    id_rol SERIAL PRIMARY KEY,
    nombre_rol VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(255) NULL
);

-- ----------------------------------------------------------------------------
-- 2. TABLA: usuarios
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario SERIAL PRIMARY KEY,
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
    fecha_registro TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    id_rol INT NOT NULL REFERENCES roles(id_rol) ON DELETE RESTRICT ON UPDATE CASCADE,
    foto_perfil VARCHAR(255) NULL DEFAULT NULL,
    github_id VARCHAR(100) NULL UNIQUE,
    dos_factores_activo SMALLINT NOT NULL DEFAULT 0,
    dos_factores_secreto VARCHAR(255) NULL,
    dos_factores_backup_codes TEXT NULL,
    email_verificado SMALLINT NOT NULL DEFAULT 1,
    email_verificacion_token CHAR(64) NULL,
    email_verificacion_expira TIMESTAMPTZ NULL,
    nuevo_email_pendiente VARCHAR(150) NULL,
    nuevo_email_token CHAR(64) NULL,
    nuevo_email_expira TIMESTAMPTZ NULL,
    datos_cambiados_en TIMESTAMPTZ NULL,
    password_reset_token CHAR(64) NULL,
    password_reset_expira TIMESTAMPTZ NULL,
    activo SMALLINT NOT NULL DEFAULT 1,
    fecha_baja TIMESTAMPTZ NULL,
    onboarding_step SMALLINT NOT NULL DEFAULT 1,
    onboarding_data TEXT NULL
);

-- ----------------------------------------------------------------------------
-- 3. TABLA: categorias
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS categorias (
    id_categoria SERIAL PRIMARY KEY,
    nombre_categoria VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT NULL,
    creada_por INT NULL REFERENCES usuarios(id_usuario) ON DELETE SET NULL ON UPDATE CASCADE
);

-- ----------------------------------------------------------------------------
-- 4. TABLA: publicaciones (Cursos y Servicios)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS publicaciones (
    id_publicacion SERIAL PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT NOT NULL,
    precio NUMERIC(10,2) NOT NULL,
    tipo VARCHAR(20) NOT NULL CHECK (tipo IN ('Curso', 'Servicio')),
    modalidad VARCHAR(30) NULL,
    nivel_experiencia VARCHAR(30) NULL,
    duracion_horas SMALLINT NULL,
    cupos INT NULL,
    disponibilidad TEXT NULL,
    tipo_servicio VARCHAR(60) NULL,
    estado VARCHAR(20) NOT NULL DEFAULT 'Activo' CHECK (estado IN ('Activo', 'Inactivo', 'Pausado', 'Eliminado')),
    imagen VARCHAR(255) NULL DEFAULT NULL,
    eliminado_en TIMESTAMPTZ NULL,
    fecha_creacion TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    id_usuario INT NOT NULL REFERENCES usuarios(id_usuario) ON DELETE CASCADE ON UPDATE CASCADE,
    id_categoria INT NOT NULL REFERENCES categorias(id_categoria) ON DELETE RESTRICT ON UPDATE CASCADE
);

DROP TRIGGER IF EXISTS set_timestamp_publicaciones ON publicaciones;
CREATE TRIGGER set_timestamp_publicaciones
BEFORE UPDATE ON publicaciones
FOR EACH ROW
EXECUTE FUNCTION trigger_set_timestamp();

-- ----------------------------------------------------------------------------
-- 5. TABLA: contrataciones
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS contrataciones (
    id_contratacion SERIAL PRIMARY KEY,
    fecha_contratacion TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    monto_total NUMERIC(10,2) NOT NULL,
    estado VARCHAR(30) NOT NULL DEFAULT 'Pendiente' CHECK (estado IN ('Pendiente', 'En Proceso', 'Completada', 'Cancelada')),
    id_usuario INT NOT NULL REFERENCES usuarios(id_usuario) ON DELETE CASCADE ON UPDATE CASCADE
);

-- ----------------------------------------------------------------------------
-- 6. TABLA: solicitudes
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS solicitudes (
    id_solicitud SERIAL PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT NOT NULL,
    estado VARCHAR(30) NOT NULL DEFAULT 'Pendiente' CHECK (estado IN ('Pendiente', 'Aceptada', 'Rechazada', 'Contraoferta', 'En Proceso', 'Realizada', 'Cancelada')),
    detalles_json JSONB NULL,
    archivo_adjunto VARCHAR(255) NULL,
    precio_propuesto NUMERIC(10,2) NULL,
    fecha_hora_propuesta TIMESTAMPTZ NULL,
    respuesta_proveedor TEXT NULL,
    fecha_solicitud TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    id_usuario INT NOT NULL REFERENCES usuarios(id_usuario) ON DELETE CASCADE ON UPDATE CASCADE,
    id_publicacion INT NULL REFERENCES publicaciones(id_publicacion) ON DELETE SET NULL ON UPDATE CASCADE,
    id_contratacion INT NULL REFERENCES contrataciones(id_contratacion) ON DELETE SET NULL ON UPDATE CASCADE
);

DROP TRIGGER IF EXISTS set_timestamp_solicitudes ON solicitudes;
CREATE TRIGGER set_timestamp_solicitudes
BEFORE UPDATE ON solicitudes
FOR EACH ROW
EXECUTE FUNCTION trigger_set_timestamp();

-- ----------------------------------------------------------------------------
-- 7. TABLA: solicitudes_docente
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS solicitudes_docente (
    id_solicitud_docente SERIAL PRIMARY KEY,
    id_usuario INT NOT NULL REFERENCES usuarios(id_usuario) ON DELETE CASCADE ON UPDATE CASCADE,
    estado VARCHAR(20) NOT NULL DEFAULT 'Pendiente' CHECK (estado IN ('Pendiente', 'Aprobada', 'Rechazada')),
    motivo TEXT NULL,
    fecha_solicitud TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_respuesta TIMESTAMPTZ NULL
);

CREATE INDEX IF NOT EXISTS idx_solicitudes_docente_estado ON solicitudes_docente (estado);
CREATE INDEX IF NOT EXISTS idx_solicitudes_docente_usuario_estado ON solicitudes_docente (id_usuario, estado);

-- ----------------------------------------------------------------------------
-- 8. TABLA: detalles_contratacion
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS detalles_contratacion (
    id_detalle SERIAL PRIMARY KEY,
    cantidad INT NOT NULL DEFAULT 1,
    precio_unitario NUMERIC(10,2) NOT NULL,
    subtotal NUMERIC(10,2) NOT NULL,
    id_contratacion INT NOT NULL REFERENCES contrataciones(id_contratacion) ON DELETE CASCADE ON UPDATE CASCADE,
    id_publicacion INT NOT NULL REFERENCES publicaciones(id_publicacion) ON DELETE RESTRICT ON UPDATE CASCADE
);

-- ----------------------------------------------------------------------------
-- 9. TABLA: pagos
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS pagos (
    id_pago SERIAL PRIMARY KEY,
    monto NUMERIC(10,2) NOT NULL,
    metodo_pago VARCHAR(30) NOT NULL CHECK (metodo_pago IN ('Tarjeta', 'Transferencia', 'MercadoPago', 'Efectivo')),
    estado_pago VARCHAR(30) NOT NULL DEFAULT 'Pendiente' CHECK (estado_pago IN ('Pendiente', 'Aprobado', 'Rechazado')),
    fecha_pago TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    transaccion_ref VARCHAR(100) NULL,
    id_contratacion INT NOT NULL REFERENCES contrataciones(id_contratacion) ON DELETE CASCADE ON UPDATE CASCADE
);

-- ----------------------------------------------------------------------------
-- 10. TABLA: valoraciones
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS valoraciones (
    id_valoracion SERIAL PRIMARY KEY,
    puntuacion INT NOT NULL CHECK (puntuacion BETWEEN 1 AND 5),
    comentario TEXT NULL,
    fecha_valoracion TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    id_usuario INT NOT NULL REFERENCES usuarios(id_usuario) ON DELETE CASCADE ON UPDATE CASCADE,
    id_publicacion INT NOT NULL REFERENCES publicaciones(id_publicacion) ON DELETE CASCADE ON UPDATE CASCADE,
    id_contratacion INT NULL REFERENCES contrataciones(id_contratacion) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT uq_valoraciones_contratacion_usuario UNIQUE (id_contratacion, id_usuario)
);

-- ----------------------------------------------------------------------------
-- 11. TABLA: perfiles_profesionales
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS perfiles_profesionales (
    id_perfil SERIAL PRIMARY KEY,
    id_usuario INT NOT NULL UNIQUE REFERENCES usuarios(id_usuario) ON DELETE CASCADE ON UPDATE CASCADE,
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
    visibilidad VARCHAR(30) NOT NULL DEFAULT 'Registrados' CHECK (visibilidad IN ('Registrados', 'Relacionados')),
    actualizado_en TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

DROP TRIGGER IF EXISTS set_timestamp_perfiles ON perfiles_profesionales;
CREATE TRIGGER set_timestamp_perfiles
BEFORE UPDATE ON perfiles_profesionales
FOR EACH ROW
EXECUTE FUNCTION trigger_set_timestamp_actualizado_en();

-- ----------------------------------------------------------------------------
-- 12. TABLAS: Estructura de Cursos (Módulos, Unidades, Recursos, Entregas, Foro)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS curso_modulos (
    id_modulo SERIAL PRIMARY KEY,
    id_publicacion INT NOT NULL REFERENCES publicaciones(id_publicacion) ON DELETE CASCADE ON UPDATE CASCADE,
    titulo VARCHAR(180) NOT NULL,
    descripcion TEXT NULL,
    orden INT NOT NULL DEFAULT 1
);
CREATE INDEX IF NOT EXISTS idx_curso_modulos_publicacion_orden ON curso_modulos (id_publicacion, orden);

CREATE TABLE IF NOT EXISTS curso_unidades (
    id_unidad SERIAL PRIMARY KEY,
    id_modulo INT NOT NULL REFERENCES curso_modulos(id_modulo) ON DELETE CASCADE ON UPDATE CASCADE,
    titulo VARCHAR(180) NOT NULL,
    descripcion TEXT NULL,
    orden INT NOT NULL DEFAULT 1
);
CREATE INDEX IF NOT EXISTS idx_curso_unidades_modulo_orden ON curso_unidades (id_modulo, orden);

CREATE TABLE IF NOT EXISTS curso_recursos (
    id_recurso SERIAL PRIMARY KEY,
    id_unidad INT NOT NULL REFERENCES curso_unidades(id_unidad) ON DELETE CASCADE ON UPDATE CASCADE,
    titulo VARCHAR(180) NOT NULL,
    tipo VARCHAR(30) NOT NULL CHECK (tipo IN ('Archivo','Foro','Entrega de Tareas','Video','PDF','Imagen','Enlace')),
    url VARCHAR(500) NULL,
    archivo VARCHAR(255) NULL,
    descripcion TEXT NULL,
    orden INT NOT NULL DEFAULT 1,
    fecha_creacion TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_curso_recursos_unidad_orden ON curso_recursos (id_unidad, orden);

CREATE TABLE IF NOT EXISTS curso_entregas (
    id_entrega SERIAL PRIMARY KEY,
    id_recurso INT NOT NULL REFERENCES curso_recursos(id_recurso) ON DELETE CASCADE ON UPDATE CASCADE,
    id_usuario INT NOT NULL REFERENCES usuarios(id_usuario) ON DELETE CASCADE ON UPDATE CASCADE,
    archivo_entrega VARCHAR(500) NULL,
    comentario_entrega TEXT NULL,
    estado VARCHAR(30) NOT NULL DEFAULT 'Entregada' CHECK (estado IN ('Entregada', 'Calificada')),
    calificacion NUMERIC(4,2) NULL,
    fecha_entrega TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uk_recurso_usuario UNIQUE (id_recurso, id_usuario)
);

CREATE TABLE IF NOT EXISTS curso_foro_mensajes (
    id_mensaje SERIAL PRIMARY KEY,
    id_recurso INT NOT NULL REFERENCES curso_recursos(id_recurso) ON DELETE CASCADE ON UPDATE CASCADE,
    id_usuario INT NOT NULL REFERENCES usuarios(id_usuario) ON DELETE CASCADE ON UPDATE CASCADE,
    mensaje TEXT NOT NULL,
    fecha_mensaje TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_curso_foro_recurso_fecha ON curso_foro_mensajes (id_recurso, fecha_mensaje);

CREATE TABLE IF NOT EXISTS solicitud_mensajes (
    id_mensaje SERIAL PRIMARY KEY,
    id_solicitud INT NOT NULL REFERENCES solicitudes(id_solicitud) ON DELETE CASCADE ON UPDATE CASCADE,
    id_usuario INT NOT NULL REFERENCES usuarios(id_usuario) ON DELETE CASCADE ON UPDATE CASCADE,
    mensaje TEXT NOT NULL,
    fecha_mensaje TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_solicitud_mensajes_fecha ON solicitud_mensajes (id_solicitud, fecha_mensaje);

-- Índices de optimización para consultas frecuentes
CREATE INDEX IF NOT EXISTS idx_pub_usuario ON publicaciones (id_usuario);
CREATE INDEX IF NOT EXISTS idx_pub_categoria ON publicaciones (id_categoria);
CREATE INDEX IF NOT EXISTS idx_pub_estado_tipo ON publicaciones (estado, tipo);
CREATE INDEX IF NOT EXISTS idx_pub_fecha ON publicaciones (fecha_creacion);

CREATE INDEX IF NOT EXISTS idx_sol_usuario ON solicitudes (id_usuario);
CREATE INDEX IF NOT EXISTS idx_sol_publicacion ON solicitudes (id_publicacion);
CREATE INDEX IF NOT EXISTS idx_sol_estado ON solicitudes (estado);
CREATE INDEX IF NOT EXISTS idx_sol_contratacion ON solicitudes (id_contratacion);

CREATE INDEX IF NOT EXISTS idx_cont_usuario ON contrataciones (id_usuario);
CREATE INDEX IF NOT EXISTS idx_cont_estado ON contrataciones (estado);
CREATE INDEX IF NOT EXISTS idx_cont_fecha ON contrataciones (fecha_contratacion);

CREATE INDEX IF NOT EXISTS idx_det_contratacion ON detalles_contratacion (id_contratacion);
CREATE INDEX IF NOT EXISTS idx_det_publicacion ON detalles_contratacion (id_publicacion);

CREATE INDEX IF NOT EXISTS idx_pagos_contratacion ON pagos (id_contratacion);
CREATE INDEX IF NOT EXISTS idx_pagos_estado ON pagos (estado_pago);

CREATE INDEX IF NOT EXISTS idx_val_publicacion ON valoraciones (id_publicacion);
CREATE INDEX IF NOT EXISTS idx_val_usuario ON valoraciones (id_usuario);
CREATE INDEX IF NOT EXISTS idx_val_contratacion ON valoraciones (id_contratacion);

-- ============================================================================
-- INSERCIÓN DE DATOS SEMILLA INICIALES
-- ============================================================================

INSERT INTO roles (id_rol, nombre_rol, descripcion) VALUES
(1, 'Cliente/Estudiante', 'Usuario consumidor de cursos y servicios'),
(2, 'Docente/Proveedor', 'Usuario creador y prestador de servicios educativos'),
(3, 'Administrador', 'Superusuario del sistema')
ON CONFLICT (id_rol) DO NOTHING;

INSERT INTO categorias (id_categoria, nombre_categoria, descripcion) VALUES
(1, 'Programación y Desarrollo', 'Cursos de software, desarrollo web, móvil y backend'),
(2, 'Robótica y Automatización', 'Proyectos de hardware, Arduino, electrónica y robótica aplicada'),
(3, 'Diseño e Impresión 3D', 'Modelado 3D, prototipado e impresión digital técnica'),
(4, 'Mentorías y Capacitación', 'Asesorías personalizadas y refuerzo escolar adaptado'),
(5, 'Inteligencia Artificial y Datos', 'Ciencia de datos, aprendizaje automático y procesamiento de información'),
(6, 'Electrónica y Microcontroladores', 'Circuitos analógicos y digitales, ESP32 y sistemas embebidos'),
(7, 'Ciberseguridad y Redes', 'Fundamentos de seguridad informática, redes y protección de datos'),
(8, 'Diseño Web y UX/UI', 'Interfaces de usuario, experiencia de usuario y maquetación web moderna'),
(9, 'Idiomas y Comunicación Técnica', 'Inglés técnico para desarrolladores y redacción de documentación'),
(10, 'Gestión de Proyectos Tecnológicos', 'Metodologías ágiles, Scrum y dirección de proyectos de software')
ON CONFLICT (id_categoria) DO NOTHING;

INSERT INTO usuarios (id_usuario, nombre, apellido, email, password_hash, telefono, fecha_registro, id_rol, email_verificado, onboarding_step) VALUES
(1, 'Carlos', 'Admin', 'admin@classia.com', '$2y$10$5sRoonQ8BOFLS7jHfFKCmucev28F2JqZyeysT1lpNiYxbP9caKfPe', '1155443322', '2026-01-10 09:00:00+00', 3, 1, 10),
(2, 'María', 'Docente', 'docente@classia.com', '$2y$10$5sRoonQ8BOFLS7jHfFKCmucev28F2JqZyeysT1lpNiYxbP9caKfPe', '1199887766', '2026-01-15 10:30:00+00', 2, 1, 10),
(3, 'Roberto', 'Gómez', 'roberto.gomez@classia.com', '$2y$10$5sRoonQ8BOFLS7jHfFKCmucev28F2JqZyeysT1lpNiYxbP9caKfPe', '1144332211', '2026-01-20 11:15:00+00', 2, 1, 10),
(4, 'Lucía', 'Fernández', 'lucia.fernandez@classia.com', '$2y$10$5sRoonQ8BOFLS7jHfFKCmucev28F2JqZyeysT1lpNiYxbP9caKfPe', '1133221100', '2026-02-01 14:00:00+00', 2, 1, 10),
(5, 'Gonzalo', 'Martínez', 'gonzalo.martinez@classia.com', '$2y$10$5sRoonQ8BOFLS7jHfFKCmucev28F2JqZyeysT1lpNiYxbP9caKfPe', '1122110099', '2026-02-05 16:45:00+00', 2, 1, 10),
(6, 'Juan', 'Pérez', 'estudiante@classia.com', '$2y$10$5sRoonQ8BOFLS7jHfFKCmucev28F2JqZyeysT1lpNiYxbP9caKfPe', '1122334455', '2026-02-10 12:00:00+00', 1, 1, 10),
(7, 'Ana', 'Silva', 'ana.silva@classia.com', '$2y$10$5sRoonQ8BOFLS7jHfFKCmucev28F2JqZyeysT1lpNiYxbP9caKfPe', '1166778899', '2026-02-12 13:20:00+00', 1, 1, 10),
(8, 'Diego', 'López', 'diego.lopez@classia.com', '$2y$10$5sRoonQ8BOFLS7jHfFKCmucev28F2JqZyeysT1lpNiYxbP9caKfPe', '1177889900', '2026-02-15 15:10:00+00', 1, 1, 10),
(9, 'Sofía', 'Rodríguez', 'sofia.rodriguez@classia.com', '$2y$10$5sRoonQ8BOFLS7jHfFKCmucev28F2JqZyeysT1lpNiYxbP9caKfPe', '1188990011', '2026-02-18 17:30:00+00', 1, 1, 10),
(10, 'Martín', 'Benítez', 'martin.benitez@classia.com', '$2y$10$5sRoonQ8BOFLS7jHfFKCmucev28F2JqZyeysT1lpNiYxbP9caKfPe', '1199001122', '2026-02-20 09:45:00+00', 1, 1, 10)
ON CONFLICT (id_usuario) DO NOTHING;

INSERT INTO publicaciones (id_publicacion, titulo, descripcion, precio, tipo, tipo_servicio, estado, fecha_creacion, id_usuario, id_categoria) VALUES
(1, 'Curso Completo de PHP y MySQL', 'Aprende backend desde cero hasta crear sistemas dinámicos seguros con PDO.', 15000.00, 'Curso', NULL, 'Activo', '2026-02-21 10:00:00+00', 2, 1),
(2, 'Servicio de Prototipado e Impresión 3D', 'Impresión de piezas técnicas y maquetas didácticas en PLA/PETG con alta precisión.', 8500.00, 'Servicio', 'impresion_3d', 'Activo', '2026-02-22 11:30:00+00', 2, 3),
(3, 'Taller Introductorio de Arduino y Robótica', 'Construcción de circuitos básicos, sensores y programación de microcontroladores.', 12000.00, 'Curso', NULL, 'Activo', '2026-02-23 14:15:00+00', 3, 2),
(4, 'Mentoría Personalizada en JavaScript y React', 'Asesoría uno a uno para resolver dudas de proyectos web front-end.', 9500.00, 'Servicio', 'mentoria', 'Activo', '2026-02-24 16:00:00+00', 4, 4),
(5, 'Introducción a Python y Ciencia de Datos', 'Fundamentos de Python, Numpy, Pandas y visualización de datos.', 18000.00, 'Curso', NULL, 'Activo', '2026-02-25 09:20:00+00', 5, 5),
(6, 'Diseño de PCB y Circuitos Electrónicos', 'Curso práctico de diseño de placas impresas con KiCAD.', 14000.00, 'Curso', NULL, 'Activo', '2026-02-26 12:45:00+00', 3, 6),
(7, 'Auditoría y Diagnóstico de Ciberseguridad Web', 'Evaluación de vulnerabilidades y buenas prácticas OWASP para aplicaciones web.', 25000.00, 'Servicio', NULL, 'Activo', '2026-02-27 15:10:00+00', 5, 7),
(8, 'Taller de Diseño de Interfaces UX/UI en Figma', 'Creación de prototipos interactivos, wireframes y sistemas de diseño.', 11000.00, 'Curso', NULL, 'Activo', '2026-02-28 17:00:00+00', 4, 8),
(9, 'Clases de Inglés Técnico para Programadores', 'Capacitación orientada a entrevistas de trabajo y lectura de documentación en inglés.', 10000.00, 'Curso', NULL, 'Activo', '2026-03-01 08:30:00+00', 2, 9),
(10, 'Consultoría en Implementación de Metodologías Ágiles', 'Asesoría para equipos de desarrollo en adopción de Scrum y Kanban.', 30000.00, 'Servicio', 'formacion_institucional', 'Activo', '2026-03-01 13:00:00+00', 5, 10)
ON CONFLICT (id_publicacion) DO NOTHING;

-- ----------------------------------------------------------------------------
-- 17. TABLA: noticias
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS noticias (
    id_noticia SERIAL PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    subtitulo VARCHAR(255) NULL,
    cuerpo TEXT NOT NULL,
    imagen VARCHAR(255) NULL,
    categoria VARCHAR(100) DEFAULT 'Institucional',
    autor VARCHAR(100) DEFAULT 'Equipo AniTech',
    id_usuario INT REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
    fecha_publicacion TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    orden INT DEFAULT 0,
    estado VARCHAR(50) DEFAULT 'Publicada'
);

-- ----------------------------------------------------------------------------
-- 18. TABLA: eventos
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS eventos (
    id_evento SERIAL PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT NOT NULL,
    tipo VARCHAR(100) DEFAULT 'Webinar',
    modalidad VARCHAR(50) DEFAULT 'Online',
    fecha_evento TIMESTAMPTZ NOT NULL,
    ubicacion_enlace VARCHAR(255) NULL,
    cupos INT DEFAULT 0,
    imagen VARCHAR(255) NULL,
    id_usuario INT REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
    estado VARCHAR(50) DEFAULT 'Abierto'
);

CREATE INDEX IF NOT EXISTS idx_noticias_estado ON noticias (estado);
CREATE INDEX IF NOT EXISTS idx_noticias_fecha ON noticias (fecha_publicacion);
CREATE INDEX IF NOT EXISTS idx_noticias_usuario ON noticias (id_usuario);
CREATE INDEX IF NOT EXISTS idx_eventos_estado ON eventos (estado);
CREATE INDEX IF NOT EXISTS idx_eventos_fecha ON eventos (fecha_evento);
CREATE INDEX IF NOT EXISTS idx_eventos_usuario ON eventos (id_usuario);

INSERT INTO noticias (id_noticia, titulo, subtitulo, cuerpo, imagen, categoria, autor, fecha_publicacion, orden, estado) VALUES
(1, 'Lanzamiento de Classia 2.0 y Nuevos Entornos de Aprendizaje', 'Una plataforma renovada para fortalecer la educación técnica y el intercambio profesional.', 'Nos complace presentar Classia 2.0, una evolución integral diseñada para conectar a estudiantes, docentes y entusiastas de la tecnología. Con módulos de cursos estructurados, visor de documentos integrado y un sistema ágil de solicitud de servicios personalizados, la plataforma refuerza el compromiso de democratizar el acceso a la educación tecnológica de calidad.', 'assets/images/cybersecurity_lab.jpg', 'Institucional', 'Equipo AniTech', '2026-09-01 10:00:00+00', 1, 'Publicada'),
(2, 'Taller de Ciberseguridad y Auditoría Web en CeRP del Suroeste', 'Capacitación práctica en buenas prácticas OWASP y defensas activas en servidores.', 'Se llevó a cabo con gran concurrencia el taller presencial de ciberseguridad aplicada en el Laboratorio del CeRP del Suroeste. Durante la jornada se abordaron técnicas de auditoría de vulnerabilidades, protección contra inyecciones SQL y mitigación de ataques CSRF en arquitecturas modernas.', 'assets/images/network_security_center.jpg', 'Ciberseguridad', 'Gonzalo Martínez', '2026-09-08 14:30:00+00', 2, 'Publicada'),
(3, 'Nueva Convocatoria para Docentes y Creadores de Contenido TI', 'Sumate a la red de instructores y compartí tus cursos y servicios especializados.', 'Abrimos la convocatoria para docentes, profesionales independientes y técnicos que deseen publicar sus propios cursos y ofrecer servicios de consultoría o fabricación técnica dentro del ecosistema Classia.', 'assets/images/cloud_server_facility.jpg', 'Académico', 'Carlos Admin', '2026-09-15 09:15:00+00', 3, 'Publicada')
ON CONFLICT (id_noticia) DO NOTHING;

INSERT INTO eventos (id_evento, titulo, descripcion, tipo, modalidad, fecha_evento, ubicacion_enlace, cupos, imagen, estado) VALUES
(1, 'Webinar: Seguridad en el Desarrollo Web Moderno (OWASP Top 10)', 'Aprende a identificar y mitigar las vulnerabilidades más críticas en aplicaciones web: inyecciones, CSRF, autenticación rota y configuraciones de seguridad esenciales.', 'Webinar', 'Online', '2026-10-15 19:00:00+00', 'https://meet.google.com/classia-security', 120, 'assets/images/cybersecurity_lab.jpg', 'Abierto'),
(2, 'Taller Práctico: Diseño y Fabricación Digital con Impresión 3D', 'Jornada intensiva para aprender calibración de impresoras FDM, modelado en Fusion 360 y optimización de parámetros de laminado con PLA y PETG.', 'Taller', 'Presencial', '2026-10-22 14:30:00+00', 'Laboratorio CeRP del Suroeste (Colonia)', 30, 'assets/images/network_security_center.jpg', 'Abierto'),
(3, 'Mesa Redonda: Desafíos de la Educación Técnica y el Software Libre', 'Intercambio abierto entre docentes y estudiantes sobre la adopción de herramientas libres y metodologías activas en la formación tecnológica.', 'Conferencia', 'Híbrido', '2026-11-05 18:00:00+00', 'Salón de Actos CeRP & Transmisión en Vivo', 80, 'assets/images/cloud_server_facility.jpg', 'Abierto')
ON CONFLICT (id_evento) DO NOTHING;

-- Sincronizar secuencias serial con los registros insertados
SELECT setval(pg_get_serial_sequence('roles', 'id_rol'), coalesce(max(id_rol), 1)) FROM roles;
SELECT setval(pg_get_serial_sequence('categorias', 'id_categoria'), coalesce(max(id_categoria), 1)) FROM categorias;
SELECT setval(pg_get_serial_sequence('usuarios', 'id_usuario'), coalesce(max(id_usuario), 1)) FROM usuarios;
SELECT setval(pg_get_serial_sequence('publicaciones', 'id_publicacion'), coalesce(max(id_publicacion), 1)) FROM publicaciones;
SELECT setval(pg_get_serial_sequence('noticias', 'id_noticia'), coalesce(max(id_noticia), 1)) FROM noticias;
SELECT setval(pg_get_serial_sequence('eventos', 'id_evento'), coalesce(max(id_evento), 1)) FROM eventos;

