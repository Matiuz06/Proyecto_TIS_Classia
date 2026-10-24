-- =========================================================
-- Classia Platform - Schema Completo con Datos de Prueba
-- Equipo: AniTech | Proyecto Integrador - Entrega Final
-- Sprint 4 - Octubre 2026
-- =========================================================

-- INSTRUCCIONES DE INSTALACION:
-- 1. Tener MySQL/MariaDB corriendo (o usar Docker: docker compose up -d)
-- 2. Ejecutar: mysql -uroot -p < sql/schema.sql
-- 3. O bien dejar que Docker lo inicialice automáticamente al primer arranque


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

/*!40000 DROP DATABASE IF EXISTS `classia_db`*/;

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `classia_db` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;

USE `classia_db`;
DROP TABLE IF EXISTS `auditoria_seguridad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `auditoria_seguridad` (
  `id_auditoria` bigint NOT NULL AUTO_INCREMENT,
  `id_usuario` int DEFAULT NULL,
  `tipo_evento` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `direccion_ip` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nivel_severidad` enum('INFO','WARNING','CRITICAL') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'INFO',
  `datos_adicionales` json DEFAULT NULL,
  `fecha_evento` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_auditoria`),
  KEY `idx_auditoria_evento` (`tipo_evento`),
  KEY `idx_auditoria_fecha` (`fecha_evento`),
  KEY `idx_auditoria_usuario` (`id_usuario`),
  CONSTRAINT `fk_auditoria_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `categorias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categorias` (
  `id_categoria` int NOT NULL AUTO_INCREMENT,
  `nombre_categoria` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `creada_por` int DEFAULT NULL,
  PRIMARY KEY (`id_categoria`),
  UNIQUE KEY `nombre_categoria` (`nombre_categoria`),
  KEY `fk_categorias_creador` (`creada_por`),
  CONSTRAINT `fk_categorias_creador` FOREIGN KEY (`creada_por`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `certificados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `certificados` (
  `id_certificado` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `id_curso` int NOT NULL,
  `codigo_verificacion` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_emision` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `porcentaje_aprobacion` decimal(5,2) NOT NULL,
  PRIMARY KEY (`id_certificado`),
  UNIQUE KEY `uq_certificado_usuario_curso` (`id_usuario`,`id_curso`),
  UNIQUE KEY `uq_certificado_codigo` (`codigo_verificacion`),
  KEY `idx_certificados_curso` (`id_curso`),
  CONSTRAINT `fk_certificados_curso` FOREIGN KEY (`id_curso`) REFERENCES `publicaciones` (`id_publicacion`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_certificados_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `contrataciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contrataciones` (
  `id_contratacion` int NOT NULL AUTO_INCREMENT,
  `fecha_contratacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `monto_total` decimal(10,2) NOT NULL,
  `estado` enum('Pendiente','En Proceso','Completada','Cancelada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pendiente',
  `id_usuario` int NOT NULL,
  PRIMARY KEY (`id_contratacion`),
  KEY `idx_cont_usuario` (`id_usuario`),
  KEY `idx_cont_estado` (`estado`),
  KEY `idx_cont_fecha` (`fecha_contratacion`),
  CONSTRAINT `fk_contrataciones_usuarios` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `curso_entrega_archivos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `curso_entrega_archivos` (
  `id_archivo` int NOT NULL AUTO_INCREMENT,
  `id_entrega` int NOT NULL,
  `nombre_original` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ruta` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `extension` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tamano` bigint DEFAULT NULL,
  `fecha_subida` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_archivo`),
  KEY `idx_curso_entrega_archivos_entrega` (`id_entrega`),
  CONSTRAINT `fk_curso_entrega_archivos_entrega` FOREIGN KEY (`id_entrega`) REFERENCES `curso_entregas` (`id_entrega`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `curso_entregas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `curso_entregas` (
  `id_entrega` int NOT NULL AUTO_INCREMENT,
  `id_tarea` int NOT NULL,
  `id_recurso` int NOT NULL,
  `id_usuario` int NOT NULL,
  `archivo_entrega` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `texto_entrega` text COLLATE utf8mb4_unicode_ci,
  `enlace_entrega` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `comentario_entrega` text COLLATE utf8mb4_unicode_ci,
  `estado` enum('Entregada','Calificada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Entregada',
  `estado_entrega` enum('borrador','entregada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'entregada',
  `calificacion` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `feedback_docente` text COLLATE utf8mb4_unicode_ci,
  `archivo_feedback` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_calificacion` datetime DEFAULT NULL,
  `id_docente_calificador` int DEFAULT NULL,
  `fecha_entrega` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `fecha_actualizacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_entrega`),
  UNIQUE KEY `uk_recurso_usuario` (`id_recurso`,`id_usuario`),
  KEY `fk_curso_entregas_usuario` (`id_usuario`),
  CONSTRAINT `fk_curso_entregas_recurso` FOREIGN KEY (`id_recurso`) REFERENCES `curso_recursos` (`id_recurso`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_curso_entregas_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `curso_foro_mensajes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `curso_foro_mensajes` (
  `id_mensaje` int NOT NULL AUTO_INCREMENT,
  `id_recurso` int NOT NULL,
  `id_usuario` int NOT NULL,
  `mensaje` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_mensaje` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_mensaje`),
  KEY `fk_curso_foro_usuario` (`id_usuario`),
  KEY `idx_curso_foro_recurso_fecha` (`id_recurso`,`fecha_mensaje`),
  CONSTRAINT `fk_curso_foro_recurso` FOREIGN KEY (`id_recurso`) REFERENCES `curso_recursos` (`id_recurso`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_curso_foro_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `curso_modulos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `curso_modulos` (
  `id_modulo` int NOT NULL AUTO_INCREMENT,
  `id_publicacion` int NOT NULL,
  `titulo` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `orden` int NOT NULL DEFAULT '1',
  `visible_alumnos` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'REQ-CON-04: 0 = oculto para alumnos, 1 = visible',
  PRIMARY KEY (`id_modulo`),
  KEY `idx_curso_modulos_publicacion_orden` (`id_publicacion`,`orden`),
  KEY `idx_mod_pub` (`id_publicacion`,`orden`),
  CONSTRAINT `fk_curso_modulos_publicacion` FOREIGN KEY (`id_publicacion`) REFERENCES `publicaciones` (`id_publicacion`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `curso_recursos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `curso_recursos` (
  `id_recurso` int NOT NULL AUTO_INCREMENT,
  `id_unidad` int NOT NULL,
  `titulo` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('Archivo','Foro','Entrega de Tareas','Video','PDF','Imagen','Enlace') COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `archivo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `orden` int NOT NULL DEFAULT '1',
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `visible_alumnos` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'REQ-CON-04: 0 = oculto para alumnos, 1 = visible',
  PRIMARY KEY (`id_recurso`),
  KEY `idx_curso_recursos_unidad_orden` (`id_unidad`,`orden`),
  KEY `idx_rec_uni` (`id_unidad`,`orden`),
  CONSTRAINT `fk_curso_recursos_unidad` FOREIGN KEY (`id_unidad`) REFERENCES `curso_unidades` (`id_unidad`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `curso_recursos_completados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `curso_recursos_completados` (
  `id_completado` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `id_recurso` int NOT NULL,
  `fecha_completado` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_completado`),
  UNIQUE KEY `uq_recurso_completado_usuario` (`id_usuario`,`id_recurso`),
  KEY `idx_recursos_completados_recurso` (`id_recurso`),
  CONSTRAINT `fk_recursos_completados_recurso` FOREIGN KEY (`id_recurso`) REFERENCES `curso_recursos` (`id_recurso`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_recursos_completados_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `curso_tareas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `curso_tareas` (
  `id_tarea` int NOT NULL AUTO_INCREMENT,
  `id_recurso` int NOT NULL,
  `fecha_apertura` datetime DEFAULT NULL,
  `fecha_cierre` datetime DEFAULT NULL,
  `permite_archivos` tinyint(1) NOT NULL DEFAULT '1',
  `permite_texto` tinyint(1) NOT NULL DEFAULT '0',
  `permite_enlace` tinyint(1) NOT NULL DEFAULT '0',
  `max_archivos` int NOT NULL DEFAULT '1',
  `max_tamano_mb` int NOT NULL DEFAULT '20',
  `formatos_permitidos` json DEFAULT NULL,
  `requisito_entrega` enum('cualquiera','todos') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cualquiera',
  `puntaje_maximo` decimal(8,2) DEFAULT NULL,
  `tipo_calificacion` enum('numerica','aprobado_reprobado','sin_calificacion') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'numerica',
  `permite_feedback_archivo` tinyint(1) NOT NULL DEFAULT '0',
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_tarea`),
  UNIQUE KEY `uq_curso_tareas_recurso` (`id_recurso`),
  CONSTRAINT `fk_curso_tareas_recurso` FOREIGN KEY (`id_recurso`) REFERENCES `curso_recursos` (`id_recurso`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `curso_unidades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `curso_unidades` (
  `id_unidad` int NOT NULL AUTO_INCREMENT,
  `id_modulo` int NOT NULL,
  `titulo` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `orden` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_unidad`),
  KEY `idx_curso_unidades_modulo_orden` (`id_modulo`,`orden`),
  KEY `idx_uni_mod` (`id_modulo`,`orden`),
  CONSTRAINT `fk_curso_unidades_modulo` FOREIGN KEY (`id_modulo`) REFERENCES `curso_modulos` (`id_modulo`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `detalles_contratacion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detalles_contratacion` (
  `id_detalle` int NOT NULL AUTO_INCREMENT,
  `cantidad` int NOT NULL DEFAULT '1',
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `id_contratacion` int NOT NULL,
  `id_publicacion` int NOT NULL,
  PRIMARY KEY (`id_detalle`),
  KEY `idx_det_contratacion` (`id_contratacion`),
  KEY `idx_det_publicacion` (`id_publicacion`),
  CONSTRAINT `fk_detalles_contrataciones` FOREIGN KEY (`id_contratacion`) REFERENCES `contrataciones` (`id_contratacion`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_detalles_publicaciones` FOREIGN KEY (`id_publicacion`) REFERENCES `publicaciones` (`id_publicacion`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eventos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `eventos` (
  `id_evento` int NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'Webinar',
  `modalidad` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'Online',
  `fecha_evento` datetime NOT NULL,
  `ubicacion_enlace` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cupos` int DEFAULT '0',
  `imagen` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'Abierto',
  `id_usuario` int DEFAULT NULL,
  PRIMARY KEY (`id_evento`),
  KEY `idx_eventos_estado` (`estado`),
  KEY `idx_eventos_fecha` (`fecha_evento`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `noticias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `noticias` (
  `id_noticia` int NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subtitulo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cuerpo` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `imagen` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `categoria` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'Institucional',
  `autor` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'Equipo AniTech',
  `fecha_publicacion` datetime DEFAULT CURRENT_TIMESTAMP,
  `orden` int DEFAULT '0',
  `estado` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'Publicada',
  `id_usuario` int DEFAULT NULL,
  PRIMARY KEY (`id_noticia`),
  KEY `idx_noticias_estado` (`estado`),
  KEY `idx_noticias_fecha` (`fecha_publicacion`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `notificaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notificaciones` (
  `id_notificacion` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `categoria` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `titulo` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mensaje` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `url_destino` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo_entidad` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_entidad` int DEFAULT NULL,
  `leida` tinyint(1) NOT NULL DEFAULT '0',
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_leida` datetime DEFAULT NULL,
  `clave_idempotencia` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id_notificacion`),
  UNIQUE KEY `uq_notificaciones_usuario_idempotencia` (`id_usuario`,`clave_idempotencia`),
  KEY `idx_notificaciones_usuario_lectura_fecha` (`id_usuario`,`leida`,`fecha_creacion`),
  KEY `idx_notificaciones_usuario_fecha` (`id_usuario`,`fecha_creacion`),
  KEY `idx_notificaciones_entidad` (`tipo_entidad`,`id_entidad`),
  CONSTRAINT `fk_notificaciones_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pagos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pagos` (
  `id_pago` int NOT NULL AUTO_INCREMENT,
  `monto` decimal(10,2) NOT NULL,
  `metodo_pago` enum('Tarjeta','Transferencia','MercadoPago','Efectivo') COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado_pago` enum('Pendiente','Aprobado','Rechazado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pendiente',
  `fecha_pago` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `transaccion_ref` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_contratacion` int NOT NULL,
  PRIMARY KEY (`id_pago`),
  KEY `idx_pagos_contratacion` (`id_contratacion`),
  KEY `idx_pagos_estado` (`estado_pago`),
  CONSTRAINT `fk_pagos_contrataciones` FOREIGN KEY (`id_contratacion`) REFERENCES `contrataciones` (`id_contratacion`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `perfiles_profesionales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `perfiles_profesionales` (
  `id_perfil` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `titulo_profesional` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `presentacion` text COLLATE utf8mb4_unicode_ci,
  `experiencia` text COLLATE utf8mb4_unicode_ci,
  `formacion` text COLLATE utf8mb4_unicode_ci,
  `certificaciones` text COLLATE utf8mb4_unicode_ci,
  `habilidades` text COLLATE utf8mb4_unicode_ci,
  `especialidades` text COLLATE utf8mb4_unicode_ci,
  `idiomas` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ubicacion` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `modalidad_trabajo` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `portfolio_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `linkedin_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tiempo_respuesta` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `visibilidad` enum('Registrados','Relacionados') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Registrados',
  `actualizado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_perfil`),
  UNIQUE KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `fk_perfiles_profesionales_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `publicacion_visitas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `publicacion_visitas` (
  `id_visita` int NOT NULL AUTO_INCREMENT,
  `id_publicacion` int NOT NULL,
  `fecha_visita` date NOT NULL,
  `visitas` int unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_visita`),
  UNIQUE KEY `uq_pub_visita_fecha` (`id_publicacion`,`fecha_visita`),
  KEY `idx_visitas_pub` (`id_publicacion`),
  KEY `idx_visitas_fecha` (`fecha_visita`),
  CONSTRAINT `fk_visitas_publicaciones` FOREIGN KEY (`id_publicacion`) REFERENCES `publicaciones` (`id_publicacion`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=342 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `publicaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `publicaciones` (
  `id_publicacion` int NOT NULL AUTO_INCREMENT,
  `titulo` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `tipo` enum('Curso','Servicio') COLLATE utf8mb4_unicode_ci NOT NULL,
  `modalidad` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nivel_experiencia` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `duracion_horas` smallint unsigned DEFAULT NULL,
  `cupos` int DEFAULT NULL,
  `disponibilidad` text COLLATE utf8mb4_unicode_ci,
  `tipo_servicio` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` enum('Activo','Inactivo','Pausado','Eliminado','Archivado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Activo',
  `imagen` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `eliminado_en` datetime DEFAULT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `id_usuario` int NOT NULL,
  `id_categoria` int NOT NULL,
  `porcentaje_minimo_aprobacion` tinyint unsigned NOT NULL DEFAULT '70',
  PRIMARY KEY (`id_publicacion`),
  KEY `idx_pub_usuario` (`id_usuario`),
  KEY `idx_pub_categoria` (`id_categoria`),
  KEY `idx_pub_estado_tipo` (`estado`,`tipo`),
  KEY `idx_pub_fecha` (`fecha_creacion`),
  KEY `idx_pub_precio` (`precio`),
  KEY `idx_pub_tipo_precio` (`tipo`,`precio`),
  CONSTRAINT `fk_publicaciones_categorias` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_publicaciones_usuarios` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id_rol` int NOT NULL AUTO_INCREMENT,
  `nombre_rol` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_rol`),
  UNIQUE KEY `nombre_rol` (`nombre_rol`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `solicitud_mensajes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `solicitud_mensajes` (
  `id_mensaje` int NOT NULL AUTO_INCREMENT,
  `id_solicitud` int NOT NULL,
  `id_usuario` int NOT NULL,
  `mensaje` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_mensaje` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_mensaje`),
  KEY `fk_solicitud_mensajes_usuario` (`id_usuario`),
  KEY `idx_solicitud_mensajes_fecha` (`id_solicitud`,`fecha_mensaje`),
  CONSTRAINT `fk_solicitud_mensajes_solicitud` FOREIGN KEY (`id_solicitud`) REFERENCES `solicitudes` (`id_solicitud`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_solicitud_mensajes_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `solicitudes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `solicitudes` (
  `id_solicitud` int NOT NULL AUTO_INCREMENT,
  `titulo` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` enum('Pendiente','Aceptada','Rechazada','Contraoferta','En Proceso','Realizada','Cancelada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pendiente',
  `detalles_json` json DEFAULT NULL,
  `archivo_adjunto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `precio_propuesto` decimal(10,2) DEFAULT NULL,
  `fecha_hora_propuesta` datetime DEFAULT NULL,
  `respuesta_proveedor` text COLLATE utf8mb4_unicode_ci,
  `fecha_solicitud` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `id_usuario` int NOT NULL,
  `id_publicacion` int DEFAULT NULL,
  `id_contratacion` int DEFAULT NULL,
  PRIMARY KEY (`id_solicitud`),
  KEY `idx_sol_usuario` (`id_usuario`),
  KEY `idx_sol_publicacion` (`id_publicacion`),
  KEY `idx_sol_estado` (`estado`),
  KEY `idx_sol_contratacion` (`id_contratacion`),
  CONSTRAINT `fk_solicitudes_contratacion` FOREIGN KEY (`id_contratacion`) REFERENCES `contrataciones` (`id_contratacion`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_solicitudes_publicaciones` FOREIGN KEY (`id_publicacion`) REFERENCES `publicaciones` (`id_publicacion`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_solicitudes_usuarios` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `solicitudes_docente`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `solicitudes_docente` (
  `id_solicitud_docente` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `estado` enum('Pendiente','Aprobada','Rechazada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pendiente',
  `motivo` text COLLATE utf8mb4_unicode_ci,
  `documentacion_ruta` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `documentacion_nombre` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `documentacion_mime` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `documentacion_tamano` int unsigned DEFAULT NULL,
  `documentacion_fecha` datetime DEFAULT NULL,
  `fecha_solicitud` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_respuesta` datetime DEFAULT NULL,
  PRIMARY KEY (`id_solicitud_docente`),
  KEY `idx_solicitudes_docente_estado` (`estado`),
  KEY `idx_solicitudes_docente_usuario_estado` (`id_usuario`,`estado`),
  CONSTRAINT `fk_solicitudes_docente_usuarios` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id_usuario` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellido` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cedula_identidad` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cedula_hash` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_registro` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id_rol` int NOT NULL,
  `foto_perfil` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `github_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dos_factores_activo` tinyint(1) NOT NULL DEFAULT '0',
  `dos_factores_secreto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dos_factores_backup_codes` text COLLATE utf8mb4_unicode_ci,
  `nombre_usuario` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `genero` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sin-especificar',
  `email_verificado` tinyint(1) NOT NULL DEFAULT '1',
  `email_verificacion_token` char(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verificacion_expira` datetime DEFAULT NULL,
  `onboarding_step` tinyint unsigned NOT NULL DEFAULT '1',
  `onboarding_data` text COLLATE utf8mb4_unicode_ci,
  `fecha_nacimiento` date DEFAULT NULL,
  `datos_cambiados_en` datetime DEFAULT NULL,
  `nuevo_email_pendiente` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nuevo_email_token` char(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nuevo_email_expira` datetime DEFAULT NULL,
  `password_reset_token` char(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password_reset_expira` datetime DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `fecha_baja` datetime DEFAULT NULL,
  `motivo_bloqueo` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `nombre_usuario` (`nombre_usuario`),
  UNIQUE KEY `idx_usuarios_github` (`github_id`),
  UNIQUE KEY `idx_usuarios_ci_hash` (`cedula_hash`),
  KEY `idx_usuarios_verificacion_token` (`email_verificacion_token`),
  KEY `idx_usr_rol` (`id_rol`),
  KEY `idx_usr_activo` (`activo`),
  CONSTRAINT `fk_usuarios_roles` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `valoraciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `valoraciones` (
  `id_valoracion` int NOT NULL AUTO_INCREMENT,
  `puntuacion` int NOT NULL,
  `comentario` text COLLATE utf8mb4_unicode_ci,
  `fecha_valoracion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id_usuario` int NOT NULL,
  `id_publicacion` int NOT NULL,
  `id_contratacion` int DEFAULT NULL,
  PRIMARY KEY (`id_valoracion`),
  KEY `idx_val_publicacion` (`id_publicacion`),
  KEY `idx_val_usuario` (`id_usuario`),
  KEY `idx_val_contratacion` (`id_contratacion`),
  KEY `idx_val_pub_puntuacion` (`id_publicacion`,`puntuacion`),
  CONSTRAINT `fk_valoraciones_contrataciones` FOREIGN KEY (`id_contratacion`) REFERENCES `contrataciones` (`id_contratacion`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_valoraciones_publicaciones` FOREIGN KEY (`id_publicacion`) REFERENCES `publicaciones` (`id_publicacion`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_valoraciones_usuarios` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `valoraciones_chk_1` CHECK ((`puntuacion` between 1 and 5))
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;


-- =========================================================
-- Crear usuario de aplicación y permisos
-- =========================================================
CREATE USER IF NOT EXISTS 'classia_user'@'%' IDENTIFIED BY '1234';
GRANT SELECT, INSERT, UPDATE, DELETE ON classia_db.* TO 'classia_user'@'%';
FLUSH PRIVILEGES;

USE classia_db;

-- =========================================================
-- Datos de prueba (mínimo 5 tuplas por tabla)
-- =========================================================
SET FOREIGN_KEY_CHECKS=0;

LOCK TABLES `roles` WRITE;
INSERT INTO `roles` (`id_rol`, `nombre_rol`, `descripcion`) VALUES (1,'Cliente/Estudiante','Usuario consumidor de cursos y servicios'),(2,'Docente/Proveedor','Usuario creador y prestador de servicios educativos'),(3,'Administrador','Superusuario del sistema');
UNLOCK TABLES;
LOCK TABLES `categorias` WRITE;
INSERT INTO `categorias` (`id_categoria`, `nombre_categoria`, `descripcion`, `creada_por`) VALUES (1,'Programación y Desarrollo','Cursos de software, desarrollo web, móvil y backend',NULL),(2,'Robótica y Automatización','Proyectos de hardware, Arduino, electrónica y robótica aplicada',NULL),(3,'Diseño e Impresión 3D','Modelado 3D, prototipado e impresión digital técnica',NULL),(4,'Mentorías y Capacitación','Asesorías personalizadas y refuerzo escolar adaptado',NULL),(5,'Inteligencia Artificial y Datos','Ciencia de datos, aprendizaje automático y procesamiento de información',NULL),(6,'Electrónica y Microcontroladores','Circuitos analógicos y digitales, ESP32 y sistemas embebidos',NULL),(7,'Ciberseguridad y Redes','Fundamentos de seguridad informática, redes y protección de datos',NULL),(8,'Diseño Web y UX/UI','Interfaces de usuario, experiencia de usuario y maquetación web moderna',NULL),(9,'Idiomas y Comunicación Técnica','Inglés técnico para desarrolladores y redacción de documentación',NULL),(10,'Gestión de Proyectos Tecnológicos','Metodologías ágiles, Scrum y dirección de proyectos de software',NULL),(11,'Testing','Estamos testeando',2);
UNLOCK TABLES;
LOCK TABLES `usuarios` WRITE;
INSERT INTO `usuarios` (`id_usuario`, `nombre`, `apellido`, `cedula_identidad`, `cedula_hash`, `email`, `password_hash`, `telefono`, `fecha_registro`, `id_rol`, `foto_perfil`, `github_id`, `dos_factores_activo`, `dos_factores_secreto`, `dos_factores_backup_codes`, `nombre_usuario`, `genero`, `email_verificado`, `email_verificacion_token`, `email_verificacion_expira`, `onboarding_step`, `onboarding_data`, `fecha_nacimiento`, `datos_cambiados_en`, `nuevo_email_pendiente`, `nuevo_email_token`, `nuevo_email_expira`, `password_reset_token`, `password_reset_expira`, `activo`, `fecha_baja`, `motivo_bloqueo`) VALUES (1,'Carlos','Admin',NULL,NULL,'admin@classia.com','$2y$10$or6PqVokbem4qppNUvDzYuilSguKG9E07zXjVWTd0bd1dxsDt3ZWW','1155443322','2026-01-10 09:00:00',3,NULL,NULL,0,NULL,NULL,'usuario_1','sin-especificar',1,NULL,NULL,3,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL),(2,'María','Docente',NULL,NULL,'docente@classia.com','$2y$10$uqECVu4oBAg4uMhQWLh/neVSbgdmZ8/LB8TODoEPoZrPlyB3byIWS','1199887766','2026-01-15 10:30:00',2,NULL,NULL,0,NULL,NULL,'usuario_2','sin-especificar',1,NULL,NULL,3,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL),(3,'Roberto','Gómez',NULL,NULL,'roberto.gomez@classia.com','$2y$10$9uPkDJAuukGbxcGBBGLpgeCX56917CuzrIshRboLRguw7DhDt8jNS','1144332211','2026-01-20 11:15:00',2,NULL,NULL,0,NULL,NULL,'usuario_3','sin-especificar',1,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL),(4,'Lucía','Fernández',NULL,NULL,'lucia.fernandez@classia.com','$2y$10$9uPkDJAuukGbxcGBBGLpgeCX56917CuzrIshRboLRguw7DhDt8jNS','1133221100','2026-02-01 14:00:00',2,NULL,NULL,0,NULL,NULL,'usuario_4','sin-especificar',1,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL),(5,'Gonzalo','Martínez',NULL,NULL,'gonzalo.martinez@classia.com','$2y$10$9uPkDJAuukGbxcGBBGLpgeCX56917CuzrIshRboLRguw7DhDt8jNS','1122110099','2026-02-05 16:45:00',2,NULL,NULL,0,NULL,NULL,'usuario_5','sin-especificar',1,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL),(6,'Juan','Pérez',NULL,NULL,'estudiante@classia.com','$2y$10$HP/EvH2rM5iE5Il2Rbm5fO4dDWWOf5hsvUxkPMvbsAR7JQAvkuhHy','1122334455','2026-02-10 12:00:00',1,NULL,NULL,0,NULL,NULL,'usuario_6','sin-especificar',1,NULL,NULL,10,'{\"categorias\":[],\"modalidades\":[],\"nivel_experiencia\":\"inicial\",\"presupuesto\":\"\",\"duracion_preferida\":\"\",\"preferencias_contratacion\":[\"mostrar-servicios-locales\"],\"notificaciones\":[\"recomendaciones\",\"mensajes\",\"contrataciones\"],\"quiere_publicar\":\"\",\"tipo_proveedor\":\"\",\"sitio_web\":\"\",\"descripcion_profesional\":\"\",\"acepta_personalizacion\":\"si\",\"acepta_privacidad\":\"si\"}',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL),(7,'Ana','Silva',NULL,NULL,'ana.silva@classia.com','$2y$10$9uPkDJAuukGbxcGBBGLpgeCX56917CuzrIshRboLRguw7DhDt8jNS','1166778899','2026-02-12 13:20:00',1,NULL,NULL,0,NULL,NULL,'usuario_7','sin-especificar',1,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL),(8,'Diego','López',NULL,NULL,'diego.lopez@classia.com','$2y$10$9uPkDJAuukGbxcGBBGLpgeCX56917CuzrIshRboLRguw7DhDt8jNS','1177889900','2026-02-15 15:10:00',1,NULL,NULL,0,NULL,NULL,'usuario_8','sin-especificar',1,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL),(9,'Sofía','Rodríguez',NULL,NULL,'sofia.rodriguez@classia.com','$2y$10$9uPkDJAuukGbxcGBBGLpgeCX56917CuzrIshRboLRguw7DhDt8jNS','1188990011','2026-02-18 17:30:00',1,NULL,NULL,0,NULL,NULL,'usuario_9','sin-especificar',1,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL),(10,'Martín','Benítez',NULL,NULL,'martin.benitez@classia.com','$2y$10$9uPkDJAuukGbxcGBBGLpgeCX56917CuzrIshRboLRguw7DhDt8jNS','1199001122','2026-02-20 09:45:00',1,NULL,NULL,0,NULL,NULL,'usuario_10','sin-especificar',1,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL),(14,'Federico','Garcia',NULL,NULL,'alumno_1789043682@classia.com','$2y$10$FNtsayilEez6AT1T/PNDs.n3DkvTzrZAml/5PRtKXPm8Zo0kGSkmu',NULL,'2026-09-10 12:34:42',1,NULL,NULL,0,NULL,NULL,'usuario_14','sin-especificar',1,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL),(35,'Thiago','Sosa','enc_v1:73fdouWgfYzhY2ol:SXnzWB4+oYt7lO4lGHVIzw==:V454xWAHMPs=','4bd839dc7fb055bbd80c3b8838d0df6ce902c31e734f52b84000cc62c780deda','retr0sys@users.noreply.github.com','$2y$10$fHxePn8Gh5.DoP8Q0eiih.6HFvszBEw79/nt65whYKOMZ6K3/O7A2','099177918','2026-09-15 03:26:17',1,'https://avatars.githubusercontent.com/u/84207498?v=4','84207498',0,NULL,NULL,'retr0sys','sin-especificar',1,NULL,NULL,10,'{\"otra_profesion\":\"\",\"nombre\":\"Thiago\",\"apellido\":\"Sosa\",\"fecha_nacimiento\":\"2005-04-22\",\"cedula_identidad\":\"55543106\",\"profesion\":\"docente\",\"institucion\":\"CeRP Litoral\",\"pais\":\"Uruguay\",\"departamento\":\"Salto\",\"uso_plataforma\":[\"explorar-servicios\",\"contratar-institucion\",\"publicar-servicios\"],\"categorias\":[\"cursos\",\"proyectos-educativos\",\"diseno-impresion-3d\"],\"modalidades\":[\"virtual\",\"presencial\"],\"nivel_experiencia\":\"avanzado\",\"presupuesto\":\"entre-3000-y-10000\",\"duracion_preferida\":\"entre-10-y-20-horas\",\"preferencias_contratacion\":[\"mostrar-gratuitos\"],\"notificaciones\":[\"recomendaciones\",\"mensajes\",\"contrataciones\"],\"quiere_publicar\":\"\",\"tipo_proveedor\":\"\",\"sitio_web\":\"\",\"descripcion_profesional\":\"\",\"acepta_personalizacion\":\"si\",\"acepta_privacidad\":\"si\"}','2005-04-22','2026-09-15 03:29:22',NULL,NULL,NULL,NULL,NULL,1,NULL,NULL),(36,'Rafael','Sosa','enc_v1:vFMhzeeoMnGPSZtR:NG5XSgERyfbAldJapYG65g==:qssCmFympF8=','9b042e8787994091a4cc0ab1158f893b54f4ffc4f5338c3abcca85db04f53077','sosat279@gmail.com','$2y$10$FG27orn3/gw/wnMKowX0ielRzumoj2f11DFrAyx7fo393lBjCn5HS',NULL,'2026-09-17 12:54:01',1,'https://lh3.googleusercontent.com/a/ACg8ocLgLSVn-961Q-5G1GKNBhZG8J9S5qRr_Bpi9wgUVYa7ESI8yYms=s96-c',NULL,0,NULL,NULL,'sosat279','sin-especificar',1,NULL,NULL,2,'{\"otra_profesion\":\"\",\"nombre\":\"Rafael\",\"apellido\":\"Sosa\",\"fecha_nacimiento\":\"2005-04-22\",\"cedula_identidad\":\"57358163\",\"profesion\":\"estudiante\",\"institucion\":\"CeRP Litoral\",\"pais\":\"Uruguay\",\"departamento\":\"Salto\"}','2005-04-22',NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL),(37,'Testeando','LaPag','enc_v1:YQlsMHaX8BNQpjTi:iH73UZLUdjn4gh1wDEUb9g==:GNdao9tk7iE=','5518b9864c3ff387b4610296e683c7e1511ed3dfc16528f1c1b3e9adf13dd5f6','eltesting@gmail.com','$2y$10$IdddfqBQ32zx5RZc4LEfoevODe9CY3OHrrp.5U1rWJLCTPfTlIgsa',NULL,'2026-10-01 14:30:48',1,NULL,NULL,0,NULL,NULL,'ElTestingPa','sin-especificar',1,NULL,NULL,2,'{\"otra_profesion\":\"\",\"nombre\":\"Testeando\",\"apellido\":\"LaPag\",\"fecha_nacimiento\":\"2005-04-22\",\"profesion\":\"estudiante\",\"institucion\":\"CeRP Litoral\",\"pais\":\"Uruguay\",\"departamento\":\"Salto\"}','2005-04-22',NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL);
UNLOCK TABLES;
LOCK TABLES `publicaciones` WRITE;
INSERT INTO `publicaciones` (`id_publicacion`, `titulo`, `descripcion`, `precio`, `tipo`, `modalidad`, `nivel_experiencia`, `duracion_horas`, `cupos`, `disponibilidad`, `tipo_servicio`, `estado`, `imagen`, `eliminado_en`, `fecha_creacion`, `fecha_actualizacion`, `id_usuario`, `id_categoria`, `porcentaje_minimo_aprobacion`) VALUES (1,'Curso Completo de PHP y MySQL','Aprende backend desde cero hasta crear sistemas dinámicos seguros con PDO.',15000.00,'Curso',NULL,NULL,NULL,NULL,NULL,NULL,'Activo',NULL,NULL,'2026-02-21 10:00:00','2026-09-15 01:19:11',2,1,70),(2,'Servicio de Prototipado e Impresión 3D','Impresión de piezas técnicas y maquetas didácticas en PLA/PETG con alta precisión.',8500.00,'Servicio',NULL,NULL,NULL,NULL,NULL,'impresion_3d','Activo',NULL,NULL,'2026-02-22 11:30:00','2026-09-15 01:19:12',2,3,70),(3,'Taller Introductorio de Arduino y Robótica','Construcción de circuitos básicos, sensores y programación de microcontroladores.',12000.00,'Curso',NULL,NULL,NULL,NULL,NULL,NULL,'Activo',NULL,NULL,'2026-02-23 14:15:00','2026-09-15 01:19:11',3,2,70),(4,'Mentoría Personalizada en JavaScript y React','Asesoría uno a uno para resolver dudas de proyectos web front-end.',9500.00,'Servicio',NULL,NULL,NULL,NULL,NULL,'mentoria','Activo',NULL,NULL,'2026-02-24 16:00:00','2026-09-15 01:19:12',4,4,70),(5,'Introducción a Python y Ciencia de Datos','Fundamentos de Python, Numpy, Pandas y visualización de datos.',18000.00,'Curso',NULL,NULL,NULL,NULL,NULL,NULL,'Activo',NULL,NULL,'2026-02-25 09:20:00','2026-09-15 01:19:11',5,5,70),(6,'Diseño de PCB y Circuitos Electrónicos','Curso práctico de diseño de placas impresas con KiCAD.',14000.00,'Curso',NULL,NULL,NULL,NULL,NULL,NULL,'Activo',NULL,NULL,'2026-02-26 12:45:00','2026-09-15 01:19:11',3,6,70),(7,'Auditoría y Diagnóstico de Ciberseguridad Web','Evaluación de vulnerabilidades y buenas prácticas OWASP para aplicaciones web.',25000.00,'Servicio',NULL,NULL,NULL,NULL,NULL,NULL,'Activo',NULL,NULL,'2026-02-27 15:10:00','2026-09-15 01:19:11',5,7,70),(8,'Taller de Diseño de Interfaces UX/UI en Figma','Creación de prototipos interactivos, wireframes y sistemas de diseño.',11000.00,'Curso',NULL,NULL,NULL,NULL,NULL,NULL,'Activo',NULL,NULL,'2026-02-28 17:00:00','2026-09-15 01:19:11',4,8,70),(9,'Clases de Inglés Técnico para Programadores','Capacitación orientada a entrevistas de trabajo y lectura de documentación en inglés.',10000.00,'Curso',NULL,NULL,NULL,NULL,NULL,NULL,'Activo',NULL,NULL,'2026-03-01 08:30:00','2026-09-15 01:19:11',2,9,70),(10,'Consultoría en Implementación de Metodologías Ágiles','Asesoría para equipos de desarrollo en adopción de Scrum y Kanban.',30000.00,'Servicio',NULL,NULL,NULL,NULL,NULL,'formacion_institucional','Activo',NULL,NULL,'2026-03-01 13:00:00','2026-09-15 01:19:12',5,10,70),(11,'Testeando','Este curso está el propio',1.00,'Curso','virtual','avanzado',20,NULL,NULL,NULL,'Activo','assets/uploads/publicaciones/publicaciones_c8d5c5d384645aba_1789693933.jpg',NULL,'2026-09-10 14:51:01','2026-09-18 01:12:13',2,11,70);
UNLOCK TABLES;
LOCK TABLES `solicitudes` WRITE;
INSERT INTO `solicitudes` (`id_solicitud`, `titulo`, `descripcion`, `estado`, `detalles_json`, `archivo_adjunto`, `precio_propuesto`, `fecha_hora_propuesta`, `respuesta_proveedor`, `fecha_solicitud`, `fecha_actualizacion`, `id_usuario`, `id_publicacion`, `id_contratacion`) VALUES (1,'Solicitud de Maqueta 3D para Proyecto Final','Requiero la impresión 3D de un engranaje y carcasa en material PLA negro.','Aceptada',NULL,NULL,NULL,NULL,NULL,'2026-03-02 09:15:00','2026-09-15 01:19:12',6,2,NULL),(2,'Refuerzo Personalizado en SQL Complejo','Busco tutoría de 2 horas para entender JOINs complejos y subconsultas en MySQL.','Aceptada',NULL,NULL,NULL,NULL,NULL,'2026-03-02 11:40:00','2026-09-15 01:19:12',7,1,NULL),(3,'Asesoría en Prototipo Robótico con ESP32','Necesito ayuda para integrar un módulo WiFi ESP32 con sensores ultrasónicos.','Pendiente',NULL,NULL,NULL,NULL,NULL,'2026-03-03 14:00:00','2026-09-15 01:19:12',8,3,NULL),(4,'Revisión de Código React Native','Solicito revisión de arquitectura y corrección de bugs en aplicación móvil.','Aceptada',NULL,NULL,NULL,NULL,NULL,'2026-03-04 10:20:00','2026-09-15 01:19:12',9,4,NULL),(5,'Modelado e Impresión de Soporte para Cámara','Diseño e impresión a medida de un soporte ajustable para cámara web.','Aceptada',NULL,NULL,NULL,NULL,NULL,'2026-03-05 16:50:00','2026-09-15 01:19:12',10,2,NULL),(6,'Capacitación Grupal en Git y GitHub Flow','Taller de 3 horas para equipo universitario sobre control de versiones.','Pendiente',NULL,NULL,NULL,NULL,NULL,'2026-03-06 12:30:00','2026-09-15 01:19:12',6,NULL,NULL),(7,'Diagnóstico de Seguridad para Servidor VPS','Solicitud de escaneo y hardening de servidor Linux centrado en Nginx.','Rechazada',NULL,NULL,NULL,NULL,NULL,'2026-03-07 15:15:00','2026-09-15 01:19:12',7,7,NULL),(8,'Rediseño UX para Plataforma Educativa','Diseño de prototipo de alta fidelidad para sistema de notas estudiantiles.','Pendiente',NULL,NULL,NULL,NULL,NULL,'2026-03-08 18:00:00','2026-09-15 01:19:12',8,8,NULL),(9,'Traducción de Documentación de API','Traducción de manual técnico de español a inglés para publicación open-source.','Cancelada',NULL,NULL,NULL,NULL,NULL,'2026-03-09 09:30:00','2026-09-15 01:19:12',9,9,NULL),(10,'Asesoría en Selección de Microcontroladores','Consulta técnica sobre costo y eficiencia entre Arduino Nano y STM32.','Aceptada',NULL,NULL,NULL,NULL,NULL,'2026-03-10 14:10:00','2026-09-15 01:19:12',10,6,NULL);
UNLOCK TABLES;
LOCK TABLES `contrataciones` WRITE;
INSERT INTO `contrataciones` (`id_contratacion`, `fecha_contratacion`, `monto_total`, `estado`, `id_usuario`) VALUES (1,'2026-08-10 10:15:00',15000.00,'Completada',6),(2,'2026-08-12 14:30:00',8500.00,'Completada',7),(3,'2026-08-15 09:00:00',12000.00,'En Proceso',8),(4,'2026-08-18 16:45:00',9500.00,'Completada',9),(5,'2026-08-20 11:20:00',18000.00,'Pendiente',10),(6,'2026-08-22 15:10:00',14000.00,'En Proceso',6),(7,'2026-08-25 18:00:00',25000.00,'Completada',7),(8,'2026-08-28 13:25:00',11000.00,'Pendiente',8),(9,'2026-08-30 17:40:00',10000.00,'Cancelada',9),(10,'2026-09-01 08:50:00',30000.00,'En Proceso',10),(14,'2026-09-18 01:52:54',1.00,'En Proceso',35),(15,'2026-09-18 17:04:35',15000.00,'Completada',35),(16,'2026-10-15 13:57:26',10000.00,'En Proceso',6);
UNLOCK TABLES;
LOCK TABLES `detalles_contratacion` WRITE;
INSERT INTO `detalles_contratacion` (`id_detalle`, `cantidad`, `precio_unitario`, `subtotal`, `id_contratacion`, `id_publicacion`) VALUES (1,1,15000.00,15000.00,1,1),(2,1,8500.00,8500.00,2,2),(3,1,12000.00,12000.00,3,3),(4,1,9500.00,9500.00,4,4),(5,1,18000.00,18000.00,5,5),(6,1,14000.00,14000.00,6,6),(7,1,25000.00,25000.00,7,7),(8,1,11000.00,11000.00,8,8),(9,1,10000.00,10000.00,9,9),(10,1,30000.00,30000.00,10,10),(14,1,1.00,1.00,14,11),(15,1,15000.00,15000.00,15,1),(16,1,10000.00,10000.00,16,9);
UNLOCK TABLES;
LOCK TABLES `pagos` WRITE;
INSERT INTO `pagos` (`id_pago`, `monto`, `metodo_pago`, `estado_pago`, `fecha_pago`, `transaccion_ref`, `id_contratacion`) VALUES (1,15000.00,'MercadoPago','Aprobado','2026-08-10 10:18:00','MP-TRX-998877',1),(2,8500.00,'Tarjeta','Aprobado','2026-08-12 14:32:00','VISA-AUTH-4411',2),(3,12000.00,'Transferencia','Aprobado','2026-08-15 09:15:00','TRF-BANCO-88221',3),(4,9500.00,'MercadoPago','Aprobado','2026-08-18 16:48:00','MP-TRX-554433',4),(5,18000.00,'Tarjeta','Pendiente','2026-08-20 11:20:00','VISA-PEND-1029',5),(6,14000.00,'Efectivo','Aprobado','2026-08-22 15:15:00','REC-EF-33211',6),(7,25000.00,'Transferencia','Aprobado','2026-08-25 18:10:00','TRF-BANCO-99001',7),(8,11000.00,'MercadoPago','Pendiente','2026-08-28 13:25:00','MP-TRX-112233',8),(9,10000.00,'Tarjeta','Rechazado','2026-08-30 17:42:00','VISA-DECL-8822',9),(10,30000.00,'Transferencia','Aprobado','2026-09-01 09:00:00','TRF-BANCO-77112',10),(13,1.00,'Tarjeta','Aprobado','2026-09-18 01:53:17','CARD-AUTH-****-2132-14E6C1',14),(14,15000.00,'Tarjeta','Aprobado','2026-09-18 17:05:14','CARD-AUTH-****-1232-91D9B1',15),(15,10000.00,'Tarjeta','Aprobado','2026-10-15 13:57:57','CARD-AUTH-****-3456-E1696E',16);
UNLOCK TABLES;
LOCK TABLES `valoraciones` WRITE;
INSERT INTO `valoraciones` (`id_valoracion`, `puntuacion`, `comentario`, `fecha_valoracion`, `id_usuario`, `id_publicacion`, `id_contratacion`) VALUES (1,5,'Excelente curso, muy claro y práctico. Recomendado 100%.','2026-08-11 12:00:00',6,1,1),(2,5,'La impresión de las piezas quedó impecable y la entrega fue muy rápida.','2026-08-13 16:20:00',7,2,2),(3,4,'Buen material práctico y explicaciones claras sobre Arduino.','2026-08-16 11:30:00',8,3,3),(4,5,'La mentoría me ayudó a resolver un problema en React que me tenía estancado.','2026-08-19 18:15:00',9,4,4),(5,4,'Excelente introducción a Python y manejo de librerías de ciencia de datos.','2026-08-21 14:00:00',10,5,5),(6,5,'Muy buenas explicaciones sobre ruteo y diseño de PCB en KiCAD.','2026-08-23 10:45:00',6,6,6),(7,5,'Informe de auditoría web exhaustivo con recomendaciones muy valiosas.','2026-08-26 19:30:00',7,7,7),(8,4,'Contenido didáctico y muy buenos ejercicios prácticos en Figma.','2026-08-29 15:10:00',8,8,8),(9,3,'El contenido es bueno aunque me hubiera gustado profundizar más en vocabulario específico.','2026-08-31 09:20:00',9,9,9),(10,5,'Gran asesoría en Scrum, ayudó a organizar la dinámica de nuestro equipo.','2026-09-01 11:00:00',10,10,10);
UNLOCK TABLES;
LOCK TABLES `solicitudes_docente` WRITE;
INSERT INTO `solicitudes_docente` (`id_solicitud_docente`, `id_usuario`, `estado`, `motivo`, `documentacion_ruta`, `documentacion_nombre`, `documentacion_mime`, `documentacion_tamano`, `documentacion_fecha`, `fecha_solicitud`, `fecha_respuesta`) VALUES (1,6,'Aprobada','Poseo 4 años de experiencia como desarrollador backend y deseo dictar cursos de Laravel y API REST.',NULL,NULL,NULL,NULL,NULL,'2026-02-15 09:00:00','2026-02-16 14:30:00'),(2,7,'Pendiente','Especialista en robótica educativa con proyectos Arduino en nivel secundario y terciario.',NULL,NULL,NULL,NULL,NULL,'2026-02-18 10:20:00',NULL),(3,8,'Rechazada','Interés en dictar cursos básicos sin adjuntar certificaciones ni experiencia comprobable.',NULL,NULL,NULL,NULL,NULL,'2026-02-20 11:45:00','2026-02-21 16:00:00'),(4,9,'Aprobada','Diseñadora UX/UI con certificación Figma y experiencia liderando proyectos digitales.',NULL,NULL,NULL,NULL,NULL,'2026-02-22 15:10:00','2026-02-23 10:00:00'),(5,10,'Pendiente','Ingeniero electrónico con trayectoria en diseño de hardware libre y microcontroladores ESP32.',NULL,NULL,NULL,NULL,NULL,'2026-02-25 12:00:00',NULL),(6,6,'Aprobada','Actualización de solicitud para habilitación de servicios de consultoría técnica.',NULL,NULL,NULL,NULL,NULL,'2026-03-01 08:30:00','2026-03-02 09:15:00'),(7,7,'Pendiente','Postulación para dictado de talleres de modelado paramétrico e impresión 3D.',NULL,NULL,NULL,NULL,NULL,'2026-03-03 14:00:00',NULL),(8,8,'Pendiente','Especialista en seguridad informática enfocado en auditorías web OWASP.',NULL,NULL,NULL,NULL,NULL,'2026-03-05 16:20:00',NULL),(9,9,'Aprobada','Docente de inglés técnico con experiencia en capacitación a equipos de ingeniería.',NULL,NULL,NULL,NULL,NULL,'2026-03-07 10:10:00','2026-03-08 11:30:00'),(10,10,'Pendiente','Scrum Master certificado buscando impartir mentorías de agilidad y gestión de software.',NULL,NULL,NULL,NULL,NULL,'2026-03-09 17:40:00',NULL);
UNLOCK TABLES;
LOCK TABLES `perfiles_profesionales` WRITE;
INSERT INTO `perfiles_profesionales` (`id_perfil`, `id_usuario`, `titulo_profesional`, `presentacion`, `experiencia`, `formacion`, `certificaciones`, `habilidades`, `especialidades`, `idiomas`, `ubicacion`, `modalidad_trabajo`, `portfolio_url`, `linkedin_url`, `tiempo_respuesta`, `visibilidad`, `actualizado_en`) VALUES (1,1,'Administrador de Plataforma y DevOps','Coordinador técnico de la infraestructura y seguridad en Classia.','8 años en administración de sistemas Linux, bases de datos MariaDB y orquestación con Docker.','Licenciado en Sistemas de Información','LPIC-2, AWS Certified Solutions Architect','Linux, Docker, MySQL, Ciberseguridad, PHP','Infraestructura educativa y DevOps','Español (Nativo), Inglés (Avanzado)','Montevideo, Uruguay','Remoto / Híbrido','https://github.com/classia-admin','https://linkedin.com/in/carlos-admin-classia','Menos de 2 horas','Registrados','2026-09-18 00:47:55'),(2,2,'Desarrolladora Web Full Stack y Docente TI','Apasionada por la enseñanza de tecnologías web modernas y arquitecturas de software limpias.','6 años como docente y desarrolladora backend en entornos LAMP y stacks modernos.','Ingeniería en Computación (UdelaR)','Zend Certified PHP Engineer, Scrum Master','PHP 8, MySQL, JavaScript, HTML5, CSS3, Docker','Desarrollo Backend, Arquitectura MVC, REST APIs','Español, Inglés B2','Salto, Uruguay','Virtual / Remoto','https://github.com/maria-docente','https://linkedin.com/in/maria-docente','Menos de 4 horas','Registrados','2026-09-18 00:47:55'),(3,3,'Especialista en Hardware, IoT y Robótica','Instructor de robótica aplicada y circuitos electrónicos para proyectos de automatización.','5 años capacitando a estudiantes en electrónica digital, sensores y microcontroladores.','Tecnólogo en Mecatrónica','Certificación Arduino Oficial, IPC-A-610','Arduino, ESP32, KiCad, Impresión 3D, C/C++','Sistemas Embebidos e Internet de las Cosas','Español, Portugués','Paysandú, Uruguay','Presencial / Híbrida','https://portfolio.robertogomez.tech','https://linkedin.com/in/roberto-gomez-iot','Menos de 6 horas','Registrados','2026-09-18 00:47:55'),(4,4,'Diseñadora UX/UI y Desarrolladora Frontend','Diseño de experiencias centradas en el usuario con foco en accesibilidad e interacción moderna.','4 años creando prototipos de alta fidelidad y guiando cursos prácticos de diseño visual.','Diseño Gráfico Digital y Multimedia','NN/g UX Master, Interaction Design Foundation','Figma, Design Systems, CSS Grid, React, Wireframing','Interfaces Educativas y Accesibilidad Web','Español, Inglés','Montevideo, Uruguay','Remoto','https://dribbble.com/luciafernandez-ux','https://linkedin.com/in/lucia-fernandez-ux','Menos de 3 horas','Registrados','2026-09-18 00:47:55'),(5,5,'Científico de Datos y Consultor de Ciberseguridad','Docente e investigador en análisis de datos, modelos de IA y auditorías de vulnerabilidades.','7 años en consultoría de seguridad informática y entrenamiento de modelos predictivos.','Máster en Ciencia de Datos y Seguridad Informática','CompTIA Security+, Certified Ethical Hacker (CEH)','Python, Pandas, Scikit-Learn, Pentesting, OWASP','Data Science, Machine Learning y Pentesting Web','Español, Inglés C1','Colonia, Uruguay','Virtual / Remoto','https://gonzalomartinez.dev','https://linkedin.com/in/gonzalo-martinez-sec','Menos de 5 horas','Registrados','2026-09-18 00:47:55'),(6,6,'Estudiante Avanzado y Desarrollador Junior','Interesado en participar en proyectos colaborativos y reforzar conocimientos en frontend.','2 años de prácticas formativas y proyectos open-source.','Estudiante de Profesorado de Informática (CeRP)','Curso Frontend Developer','HTML, CSS, JavaScript, Git','Maquetación Web y Control de Versiones','Español','Salto, Uruguay','Híbrida','https://github.com/juanperez-dev','https://linkedin.com/in/juan-perez-student','Menos de 24 horas','Registrados','2026-09-18 00:47:55'),(7,7,'Diseñadora 3D y Prototipista Técnica','Apasionada por la fabricación digital, impresión 3D y diseño de productos personalizados.','3 años operando impresoras FDM y diseñando piezas en Blender y Fusion 360.','Técnica en Fabricación Digital','Certificado Autodesk Fusion 360','Fusion 360, Blender, Cura, PrusaSlicer, PLA/PETG','Modelado 3D para Prototipos Didácticos','Español, Portugués','Rivera, Uruguay','Presencial','https://anasilva3d.artstation.com','https://linkedin.com/in/ana-silva-3d','Menos de 12 horas','Registrados','2026-09-18 00:47:55'),(8,8,'Técnico en Redes y Soporte Informático','Especialista en cableado estructurado, configuración de routers y seguridad de red local.','3 años en soporte informático corporativo y mantenimiento de servidores.','Técnico en Redes y Telecomunicaciones','Cisco CCNA 1 y 2','TCP/IP, Routing, Switching, Linux Server, Hardening','Infraestructura de Redes y Diagnóstico','Español, Inglés Técnico','Salto, Uruguay','Presencial / Remoto','https://diegolopez.net/soporte','https://linkedin.com/in/diego-lopez-redes','Menos de 8 horas','Registrados','2026-09-18 00:47:55'),(9,9,'Traductora Técnica y Diseñadora Visual','Especialista en traducción inglés-español de contenidos tecnológicos y localización de software.','4 años trabajando con comunidades de software libre y redacción de especificaciones.','Traductorado Técnico-Científico en Inglés','Cambridge C2 Proficiency, Technical Writing Certification','Technical English, Redacción de APIs, Markdown, Git','Documentación de Software y Localización','Español, Inglés Nativo','Montevideo, Uruguay','Remoto','https://sofiarodriguez-translations.com','https://linkedin.com/in/sofia-rodriguez-tr','Menos de 4 horas','Registrados','2026-09-18 00:47:55'),(10,10,'Agile Coach y Gestor de Proyectos de Software','Facilitador de dinámicas de equipo, Scrum, Kanban y mejora continua en proyectos IT.','5 años acompañando startups y equipos universitarios en metodologías ágiles.','Licenciado en Administración y Gestión Tecnológica','PSM I (Professional Scrum Master), PMI-ACP','Scrum, Kanban, Jira, Trello, Métricas Ágiles, Retrospectivas','Transformación Ágil y Coordinación de Equipos','Español, Inglés B2','Maldonado, Uruguay','Remoto / Híbrido','https://martinbenitez-agile.com','https://linkedin.com/in/martin-benitez-scrum','Menos de 6 horas','Registrados','2026-09-18 00:47:55');
UNLOCK TABLES;
LOCK TABLES `curso_modulos` WRITE;
INSERT INTO `curso_modulos` (`id_modulo`, `id_publicacion`, `titulo`, `descripcion`, `orden`, `visible_alumnos`) VALUES (1,1,'Fundamentos de PHP Moderno','Sintaxis básica, estructuras de control, funciones y manejo de tipos estrictos en PHP 8.',1,1),(2,1,'Programación Orientada a Objetos y PDO','Clases, interfaces, herencia, conexión a bases de datos y sentencias preparadas contra inyecciones SQL.',2,1),(3,1,'Arquitectura MVC y Seguridad Web','Separación de vistas, lógica de negocio, sesiones seguras, CSRF y hash de contraseñas.',3,1),(4,3,'Introducción a la Electrónica y Arduino','Conceptos de voltaje, corriente, ley de Ohm y estructura del microcontrolador ATmega328P.',1,1),(5,3,'Sensores, Actuadores y Comunicación Serie','Lectura de sensores analógicos/digitales, control de servomotores y pantallas LCD.',2,1),(6,5,'Fundamentos de Python y Estructuras de Datos','Variables, listas, tuplas, diccionarios y funciones lambda en Python 3.',1,1),(7,5,'Manipulación y Visualización con NumPy y Pandas','DataFrames, limpieza de valores nulos, agregaciones y gráficos interactivos con Matplotlib/Seaborn.',2,1),(8,6,'Fundamentos de Circuitos y Simulación','Diseño de diagramas esquemáticos con KiCad y simulación de circuitos analógicos.',1,1),(9,8,'Fundamentos de UX y Arquitectura de la Información','Research con usuarios, mapas de empatía, user journeys y card sorting.',1,1),(10,8,'Prototipado y Design Systems en Figma','Componentes reutilizables, auto-layout, variantes e interacciones animadas.',2,1),(11,11,'El primero',NULL,1,1);
UNLOCK TABLES;
LOCK TABLES `curso_unidades` WRITE;
INSERT INTO `curso_unidades` (`id_unidad`, `id_modulo`, `titulo`, `descripcion`, `orden`) VALUES (1,1,'Instalación del Entorno y Sintaxis Básica','Configuración de PHP 8, Apache y variables de entorno.',1),(2,1,'Estructuras de Control y Arreglos Asociativos','Uso de bucles foreach, match, funciones de array y validaciones.',2),(3,2,'Conexión Segura con PDO y Manejo de Errores','Instanciación de PDO, opciones de atributos y captura de PDOException.',1),(4,2,'Consultas Preparadas (CRUD Completo)','Operaciones de alta, baja, modificación y consulta utilizando marcadores de posición.',2),(5,3,'Control de Sesiones y Protección contra CSRF','session_start seguro, tokens anti-CSRF y expiración por inactividad.',1),(6,4,'Componentes Básicos y Entorno IDE','Instalación de Arduino IDE, placas compatibles y primer sketch Blink.',1),(7,5,'Sensores Ultrasónicos y Servomotores','Medición de distancia con HC-SR04 y control de servomotores SG90.',1),(8,6,'Sintaxis de Python y Tipos de Datos','Variables, tipos primitivos, listas y comprensiones.',1),(9,7,'Carga y Análisis de Datasets con Pandas','Lectura de archivos CSV, filtros booleanos y cálculo de estadísticas descriptivas.',1),(10,10,'Creación de Componentes y Variantes en Figma','Estructuración de botones, inputs y tarjetas con diseño responsive en Figma.',1),(13,11,'Primera clase',NULL,1);
UNLOCK TABLES;
LOCK TABLES `curso_recursos` WRITE;
INSERT INTO `curso_recursos` (`id_recurso`, `id_unidad`, `titulo`, `tipo`, `url`, `archivo`, `descripcion`, `orden`, `fecha_creacion`, `visible_alumnos`) VALUES (1,1,'Guía de Instalación de PHP 8 y Docker','PDF',NULL,'assets/uploads/cursos/guia_php_docker.pdf','Documento paso a paso para levantar el entorno local de desarrollo.',1,'2026-02-21 10:30:00',1),(2,1,'Repositorio de Ejemplos Básicos en GitHub','Enlace','https://github.com/classia-demo/php-fundamentos',NULL,'Código fuente de los ejemplos vistos en la clase inaugural.',2,'2026-02-21 11:00:00',1),(3,2,'Cheat Sheet: Funciones de Arreglos en PHP','PDF',NULL,'assets/uploads/cursos/cheatsheet_arrays_php.pdf','Resumen imprimible de funciones útiles como array_map y array_filter.',1,'2026-02-21 11:45:00',1),(4,3,'Diagrama de Arquitectura de Conexión PDO','Imagen',NULL,'assets/uploads/cursos/diagrama_pdo.png','Esquema visual del patrón de conexión segura con MySQL/MariaDB.',1,'2026-02-22 09:00:00',1),(5,4,'Script de Prueba de Sentencias Preparadas','Archivo',NULL,'assets/uploads/cursos/ejemplo_pdo_crud.zip','Código de ejemplo con consultas SELECT, INSERT y UPDATE comentadas.',1,'2026-02-22 14:20:00',1),(6,5,'Video Explicativo: Ataques CSRF y Mitigación','Video','https://www.youtube.com/watch?v=dQw4w9WgXcQ',NULL,'Demostración práctica de cómo proteger formularios contra falsificación de petición.',1,'2026-02-23 10:15:00',1),(7,6,'Manual Oficial de Pinout Arduino Uno R3','PDF',NULL,'assets/uploads/cursos/pinout_arduino_uno.pdf','Diagrama esquemático con los pines de entrada y salida digital y analógica.',1,'2026-02-23 15:00:00',1),(8,7,'Código Sketch: Radar Ultrasónico','Archivo',NULL,'assets/uploads/cursos/sketch_radar_hcsr04.ino','Sketch de Arduino para leer distancia y accionar una señal lumínica.',1,'2026-02-24 09:30:00',1),(9,9,'Dataset de Prueba: Ventas y Estadísticas (.csv)','Archivo',NULL,'assets/uploads/cursos/dataset_ventas_2026.csv','Conjunto de datos estructurados con 500 filas para ejercicios de análisis.',1,'2026-02-25 11:00:00',1),(10,10,'Kit de UI y Design System en Figma Community','Enlace','https://www.figma.com/community/file/classia-ui-kit',NULL,'Archivo base de Figma con componentes atómicos y paleta de colores.',1,'2026-02-28 17:30:00',1),(11,4,'Tarea PrÃ¡ctica: ImplementaciÃ³n de CRUD con PDO','Entrega de Tareas',NULL,NULL,'Desarrolla las 4 operaciones bÃ¡sicas con sentencias preparadas y manejo de excepciones.',2,'2026-02-22 15:00:00',1),(12,5,'Trabajo Final: Mini Sistema MVC Seguro','Entrega de Tareas',NULL,NULL,'Proyecto integrador del mÃ³dulo 3 aplicando sesiones seguras y token CSRF.',2,'2026-02-23 11:00:00',1),(13,7,'PrÃ¡ctica de Laboratorio: Radar con HC-SR04','Entrega de Tareas',NULL,NULL,'EnvÃ­a el sketch de Arduino y un video o reporte de simulaciÃ³n en Tinkercad/Wokwi.',2,'2026-02-24 10:00:00',1),(14,9,'DesafÃ­o: AnÃ¡lisis Exploratorio de Datos con Pandas','Entrega de Tareas',NULL,NULL,'Carga el dataset de ventas, calcula mÃ©tricas descriptivas y exporta los resultados en notebook.',2,'2026-02-25 12:00:00',1),(15,10,'Entrega: Prototipo Interactivo de Alta Fidelidad','Entrega de Tareas',NULL,NULL,'Presenta el enlace o archivo de Figma con el flujo completo de navegaciÃ³n validado.',2,'2026-02-28 18:00:00',1),(16,1,'Foro de Debate: PHP 8 y Nuevas CaracterÃ­sticas','Foro',NULL,NULL,'Espacio de discusiÃ³n sobre JIT, tipos de uniÃ³n y mejoras de sintaxis en PHP 8.',3,'2026-02-21 12:00:00',1),(17,13,'Video introductorio de PHP','Video','https://youtu.be/nPCJAx5c1uE',NULL,NULL,1,'2026-09-18 16:51:07',1),(18,13,'Teorico PHP','Archivo',NULL,'supabase:cursos/11/84db35828731930f_1789750589.pdf',NULL,2,'2026-09-18 16:56:31',1),(19,13,'Actividad 1: Preparando el entorno','Entrega de Tareas',NULL,NULL,'Se le solicita actualizar todo su S.O para luego instalar el IDE de programación junto al lenguaje PHP en su dispositivo.',3,'2026-09-18 16:57:44',1);
UNLOCK TABLES;
LOCK TABLES `solicitud_mensajes` WRITE;
INSERT INTO `solicitud_mensajes` (`id_mensaje`, `id_solicitud`, `id_usuario`, `mensaje`, `fecha_mensaje`) VALUES (1,1,6,'Hola María, te comparto las medidas requeridas: 45mm de diámetro exterior y 12 dientes en el engranaje.','2026-03-02 09:20:00'),(2,1,2,'Recibido Juan. Lo imprimiré con altura de capa de 0.2mm en PETG para máxima resistencia mecánica.','2026-03-02 09:45:00'),(3,2,7,'Hola, tengo dudas puntuales sobre LEFT JOIN múltiples y cláusulas HAVING en reportes.','2026-03-02 11:45:00'),(4,2,2,'¡Perfecto Ana! Coordinamos una sesión de 2 horas con ejercicios prácticos basados en tu esquema.','2026-03-02 12:10:00'),(5,3,8,'Hola Roberto, ¿el taller incluye la configuración de la librería WiFi y cliente MQTT?','2026-03-03 14:15:00'),(6,4,9,'Hola Lucía, te adjunto el link al repositorio para que puedas revisar el flujo de navegación.','2026-03-04 10:35:00'),(7,4,4,'Excelente Sofía, revisé el repo y veo áreas claras de mejora en el renderizado condicional.','2026-03-04 11:20:00'),(8,5,10,'Hola, necesitaría que el soporte tenga orificios para tornillos métricos M3.','2026-03-05 17:00:00'),(9,5,2,'Anotado Martín, adapto el modelo CAD y te envío un render antes de imprimir.','2026-03-05 17:30:00'),(10,6,6,'Queremos subcontratar 3 módulos del sistema de asistencia. Podemos empezar la semana próxima.','2026-03-06 09:00:00');
UNLOCK TABLES;
LOCK TABLES `noticias` WRITE;
INSERT INTO `noticias` (`id_noticia`, `titulo`, `subtitulo`, `cuerpo`, `imagen`, `categoria`, `autor`, `fecha_publicacion`, `orden`, `estado`, `id_usuario`) VALUES (1,'Lanzamiento de Classia 2.0 y Nuevos Entornos de Aprendizaje','Una plataforma renovada para fortalecer la educaciÃ³n tÃ©cnica y el intercambio profesional.','Nos complace presentar Classia 2.0, una evoluciÃ³n integral diseÃ±ada para conectar a estudiantes, docentes y entusiastas de la tecnologÃ­a. Con mÃ³dulos de cursos estructurados, visor de documentos integrado y un sistema Ã¡gil de solicitud de servicios personalizados, la plataforma refuerza el compromiso de democratizar el acceso a la educaciÃ³n tecnolÃ³gica de calidad.','assets/images/cybersecurity_lab.jpg','Institucional','Equipo AniTech','2026-09-01 10:00:00',1,'Publicada',NULL),(2,'Taller de Ciberseguridad y AuditorÃ­a Web en CeRP del Suroeste','CapacitaciÃ³n prÃ¡ctica en buenas prÃ¡cticas OWASP y defensas activas en servidores.','Se llevÃ³ a cabo con gran concurrencia el taller presencial de ciberseguridad aplicada en el Laboratorio del CeRP del Suroeste. Durante la jornada se abordaron tÃ©cnicas de auditorÃ­a de vulnerabilidades, protecciÃ³n contra inyecciones SQL y mitigaciÃ³n de ataques CSRF en arquitecturas modernas.','assets/images/network_security_center.jpg','Ciberseguridad','Gonzalo MartÃ­nez','2026-09-08 14:30:00',2,'Publicada',NULL),(3,'Nueva Convocatoria para Docentes y Creadores de Contenido TI','Sumate a la red de instructores y compartÃ­ tus cursos y servicios especializados.','Abrimos la convocatoria para docentes, profesionales independientes y tÃ©cnicos que deseen publicar sus propios cursos y ofrecer servicios de consultorÃ­a o fabricaciÃ³n tÃ©cnica dentro del ecosistema Classia.','assets/images/cloud_server_facility.jpg','AcadÃ©mico','Carlos Admin','2026-09-15 09:15:00',3,'Publicada',NULL),(4,'Apertura de Nuevos Cursos de RobÃ³tica en CFE CeRP','Alianza acadÃ©mica para fortalecer competencias digitales y programaciÃ³n en microcontroladores.','Con el inicio del nuevo semestre se habilitaron inscripciones para los cursos de microcontroladores y electrÃ³nica aplicada dictados por profesionales del Centro Regional de Profesores.','assets/images/printing3d_lab.jpg','Institucional','Carlos Admin','2026-09-20 11:00:00',4,'Publicada',NULL),(5,'Resultados del Hackathon Anual de InnovaciÃ³n Educativa','Estudiantes de profesorado presentaron prototipos de software accesibles y seguros.','El equipo de AniTech obtuvo una menciÃ³n especial por el desarrollo de arquitecturas accesibles bajo pautas WCAG 2.1 aplicadas a plataformas educativas.','assets/images/cybersecurity_lab.jpg','Comunidad','LucÃ­a FernÃ¡ndez','2026-09-28 16:30:00',5,'Publicada',NULL);
UNLOCK TABLES;
LOCK TABLES `eventos` WRITE;
INSERT INTO `eventos` (`id_evento`, `titulo`, `descripcion`, `tipo`, `modalidad`, `fecha_evento`, `ubicacion_enlace`, `cupos`, `imagen`, `estado`, `id_usuario`) VALUES (1,'Webinar: Seguridad en el Desarrollo Web Moderno (OWASP Top 10)','Aprende a identificar y mitigar las vulnerabilidades mÃ¡s crÃ­ticas en aplicaciones web: inyecciones, CSRF, autenticaciÃ³n rota y configuraciones de seguridad esenciales.','Webinar','Online','2026-10-15 19:00:00','https://meet.google.com/classia-security',120,'assets/images/cybersecurity_lab.jpg','Abierto',NULL),(2,'Taller PrÃ¡ctico: DiseÃ±o y FabricaciÃ³n Digital con ImpresiÃ³n 3D','Jornada intensiva para aprender calibraciÃ³n de impresoras FDM, modelado en Fusion 360 y optimizaciÃ³n de parÃ¡metros de laminado con PLA y PETG.','Taller','Presencial','2026-10-22 14:30:00','Laboratorio CeRP del Suroeste (Colonia)',30,'assets/images/network_security_center.jpg','Abierto',NULL),(3,'Mesa Redonda: DesafÃ­os de la EducaciÃ³n TÃ©cnica y el Software Libre','Intercambio abierto entre docentes y estudiantes sobre la adopciÃ³n de herramientas libres y metodologÃ­as activas en la formaciÃ³n tecnolÃ³gica.','Conferencia','HÃ­brido','2026-11-05 18:00:00','SalÃ³n de Actos CeRP & TransmisiÃ³n en Vivo',80,'assets/images/cloud_server_facility.jpg','Abierto',NULL),(4,'Workshop: Control de Versiones con Git y Flujo Trunk-Based','Buenas prÃ¡cticas de branching, resoluciÃ³n de conflictos y automatizaciÃ³n de despliegues.','Taller','Online','2026-11-12 19:00:00','https://meet.google.com/classia-git',60,'assets/images/cloud_server_facility.jpg','Abierto',NULL),(5,'Seminario: IntegraciÃ³n de Sistemas y Arquitectura POO en PHP 8','Patrones de repositorio, inyecciÃ³n de dependencias y desacoplamiento de capas en entornos educativos.','Seminario','HÃ­brido','2026-11-20 18:30:00','SalÃ³n CeRP & Streaming',90,'assets/images/cybersecurity_lab.jpg','Abierto',NULL);
UNLOCK TABLES;
LOCK TABLES `auditoria_seguridad` WRITE;
INSERT INTO `auditoria_seguridad` (`id_auditoria`, `id_usuario`, `tipo_evento`, `descripcion`, `direccion_ip`, `user_agent`, `nivel_severidad`, `datos_adicionales`, `fecha_evento`) VALUES (1,1,'LOGIN_EXITOSO','Inicio de sesiÃ³n de administrador mediante credenciales vÃ¡lidas','192.168.1.10','Mozilla/5.0 (X11; Linux x86_64)','INFO',NULL,'2026-09-01 08:00:00'),(2,2,'LOGIN_EXITOSO','Inicio de sesiÃ³n de docente en aula virtual','192.168.1.15','Mozilla/5.0 (Windows NT 10.0; Win64; x64)','INFO',NULL,'2026-09-01 08:30:00'),(3,6,'LOGIN_EXITOSO','Inicio de sesiÃ³n de estudiante verificado','192.168.1.20','Mozilla/5.0 (X11; Linux x86_64)','INFO',NULL,'2026-09-01 09:00:00'),(4,1,'MODERACION_PUBLICACION','Administrador revisÃ³ y confirmÃ³ estado activo de publicaciÃ³n 1','192.168.1.10','Mozilla/5.0 (X11; Linux x86_64)','INFO',NULL,'2026-09-02 10:15:00'),(5,8,'INTENTO_FALLIDO_AUTH','Intento de login con contraseÃ±a errÃ³nea para cuenta diego.lopez@classia.com','192.168.1.45','Mozilla/5.0 (Windows NT 10.0; Win64; x64)','WARNING',NULL,'2026-09-03 14:00:00');
UNLOCK TABLES;
LOCK TABLES `curso_tareas` WRITE;
INSERT INTO `curso_tareas` (`id_tarea`, `id_recurso`, `fecha_apertura`, `fecha_cierre`, `permite_archivos`, `permite_texto`, `permite_enlace`, `max_archivos`, `max_tamano_mb`, `formatos_permitidos`, `requisito_entrega`, `puntaje_maximo`, `tipo_calificacion`, `permite_feedback_archivo`, `fecha_creacion`, `fecha_actualizacion`) VALUES (1,11,'2026-02-22 00:00:00','2026-12-31 23:59:59',1,1,1,2,20,'[\"zip\", \"pdf\", \"php\"]','cualquiera',100.00,'numerica',0,'2026-10-24 16:03:48','2026-10-24 16:03:48'),(2,12,'2026-02-23 00:00:00','2026-12-31 23:59:59',1,0,1,1,30,'[\"zip\", \"tar\"]','cualquiera',100.00,'numerica',0,'2026-10-24 16:03:48','2026-10-24 16:03:48'),(3,13,'2026-02-24 00:00:00','2026-12-31 23:59:59',1,1,1,2,15,'[\"zip\", \"ino\", \"pdf\"]','cualquiera',10.00,'numerica',0,'2026-10-24 16:03:48','2026-10-24 16:03:48'),(4,14,'2026-02-25 00:00:00','2026-12-31 23:59:59',1,1,1,1,25,'[\"ipynb\", \"zip\", \"py\"]','cualquiera',100.00,'numerica',0,'2026-10-24 16:03:48','2026-10-24 16:03:48'),(5,15,'2026-02-28 00:00:00','2026-12-31 23:59:59',1,0,1,1,50,'[\"fig\", \"pdf\", \"zip\"]','cualquiera',1.00,'aprobado_reprobado',0,'2026-10-24 16:03:48','2026-10-24 16:03:48');
UNLOCK TABLES;
LOCK TABLES `curso_entregas` WRITE;
INSERT INTO `curso_entregas` (`id_entrega`, `id_tarea`, `id_recurso`, `id_usuario`, `archivo_entrega`, `texto_entrega`, `enlace_entrega`, `comentario_entrega`, `estado`, `estado_entrega`, `calificacion`, `feedback_docente`, `archivo_feedback`, `fecha_calificacion`, `id_docente_calificador`, `fecha_entrega`, `fecha_actualizacion`) VALUES (1,1,11,6,'entregas/tarea_1/crud_pdo.zip','Se implementaron sentencias preparadas y transacciones con manejo de excepciones PDO.','https://github.com/juanperez-dev/pdo-crud','Entrega en fecha.','Calificada','entregada','95','Excelente estructuraciÃ³n de cÃ³digo y control estricto de tipos.',NULL,'2026-03-01 10:00:00',2,'2026-02-26 15:30:00','2026-10-24 16:05:32'),(2,2,12,6,'entregas/tarea_2/sistema_mvc.zip','Estructura MVC limpia con protecciÃ³n anti-CSRF y verificaciÃ³n de roles.','https://github.com/juanperez-dev/mini-mvc','Listo para revisiÃ³n.','Calificada','entregada','90','Muy buen patrÃ³n y modularizaciÃ³n de controladores.',NULL,'2026-03-05 14:20:00',2,'2026-03-02 18:10:00','2026-10-24 16:05:32'),(3,3,13,8,'entregas/tarea_3/radar_arduino.zip','Sketch probado en simulaciÃ³n con sensor HC-SR04 y servomotor SG90.','https://wokwi.com/projects/classia-radar-demo','SimulaciÃ³n adjunta.','Calificada','entregada','9.5','El circuito funciona adecuadamente y el cÃ¡lculo de distancia es preciso.',NULL,'2026-03-06 11:30:00',3,'2026-03-04 12:45:00','2026-10-24 16:05:32'),(4,4,14,10,'entregas/tarea_4/analisis_ventas.ipynb','Notebook con carga de dataset, grÃ¡ficos de dispersiÃ³n y estadÃ­sticas descriptivas.',NULL,'Se adjunta archivo Jupyter.','Entregada','entregada',NULL,NULL,NULL,NULL,NULL,'2026-03-08 09:15:00','2026-10-24 16:05:32'),(5,5,15,9,'entregas/tarea_5/prototipo_ux.pdf',NULL,'https://www.figma.com/design/demo-classia-ux','Prototipo interactivo navegable.','Calificada','entregada','aprobado','DiseÃ±o accesible, excelente contraste y componentes reutilizables.',NULL,'2026-03-10 16:00:00',4,'2026-03-09 17:00:00','2026-10-24 16:05:32');
UNLOCK TABLES;
LOCK TABLES `curso_entrega_archivos` WRITE;
INSERT INTO `curso_entrega_archivos` (`id_archivo`, `id_entrega`, `nombre_original`, `ruta`, `mime_type`, `extension`, `tamano`, `fecha_subida`) VALUES (1,1,'crud_pdo_juan_perez.zip','entregas/tarea_1/crud_pdo.zip','application/zip','zip',1048576,'2026-02-26 15:30:00'),(2,2,'sistema_mvc_final.zip','entregas/tarea_2/sistema_mvc.zip','application/zip','zip',2097152,'2026-03-02 18:10:00'),(3,3,'sketch_radar_diego.ino','entregas/tarea_3/radar_arduino.zip','application/zip','zip',45000,'2026-03-04 12:45:00'),(4,4,'notebook_analisis_martin.ipynb','entregas/tarea_4/analisis_ventas.ipynb','application/json','ipynb',150000,'2026-03-08 09:15:00'),(5,5,'prototipo_figma_sofia.pdf','entregas/tarea_5/prototipo_ux.pdf','application/pdf','pdf',3500000,'2026-03-09 17:00:00');
UNLOCK TABLES;
LOCK TABLES `curso_recursos_completados` WRITE;
INSERT INTO `curso_recursos_completados` (`id_completado`, `id_usuario`, `id_recurso`, `fecha_completado`) VALUES (1,6,1,'2026-02-21 11:00:00'),(2,6,2,'2026-02-21 11:30:00'),(3,6,3,'2026-02-21 12:00:00'),(4,6,4,'2026-02-22 10:00:00'),(5,8,7,'2026-02-23 16:00:00');
UNLOCK TABLES;
LOCK TABLES `certificados` WRITE;
INSERT INTO `certificados` (`id_certificado`, `id_usuario`, `id_curso`, `codigo_verificacion`, `fecha_emision`, `porcentaje_aprobacion`) VALUES (1,6,1,'CERT-PHP-2026-JP6','2026-03-06 10:00:00',95.00),(2,7,3,'CERT-ARD-2026-AS7','2026-03-08 14:00:00',90.00),(3,8,5,'CERT-PY-2026-DL8','2026-03-10 11:30:00',88.50),(4,9,8,'CERT-UX-2026-SR9','2026-03-12 16:45:00',98.00),(5,10,1,'CERT-PHP-2026-MB10','2026-03-15 09:15:00',92.00);
UNLOCK TABLES;
LOCK TABLES `curso_foro_mensajes` WRITE;
INSERT INTO `curso_foro_mensajes` (`id_mensaje`, `id_recurso`, `id_usuario`, `mensaje`, `fecha_mensaje`) VALUES (1,16,6,'Â¿CuÃ¡l es la principal ventaja de rendimiento de PHP 8 con JIT en comparaciÃ³n con PHP 7.4?','2026-02-21 12:30:00'),(2,16,2,'Hola Juan, JIT compila ciertas partes crÃ­ticas de bytecode a cÃ³digo mÃ¡quina nativo. En tareas CPU-intensivas o matemÃ¡ticas se percibe una mejora sustancial.','2026-02-21 13:00:00'),(3,16,7,'TambiÃ©n las uniones de tipos (Union Types) y atributos nativos hacen que el cÃ³digo sea mucho mÃ¡s limpio y auto-documentado.','2026-02-21 14:15:00'),(4,17,8,'Â¿Recomiendan alimentar el servomotor directamente desde el pin de 5V de Arduino o usar fuente externa?','2026-02-23 16:30:00'),(5,17,3,'Siempre se aconseja fuente externa compartiendo la tierra (GND). Los picos de corriente del motor pueden reiniciar el microcontrolador.','2026-02-23 17:00:00');
UNLOCK TABLES;
LOCK TABLES `notificaciones` WRITE;
INSERT INTO `notificaciones` (`id_notificacion`, `id_usuario`, `categoria`, `titulo`, `mensaje`, `url_destino`, `tipo_entidad`, `id_entidad`, `leida`, `fecha_creacion`, `fecha_leida`, `clave_idempotencia`) VALUES (1,6,'tareas','Tarea calificada','Tu entrega para \"Tarea PrÃ¡ctica: ImplementaciÃ³n de CRUD con PDO\" fue calificada con 95 / 100.','views/curso.php?id=1&recurso=11','entrega',1,0,'2026-10-24 16:05:32',NULL,'notif_seed_1'),(2,2,'foro','Nueva pregunta en el foro','Juan PÃ©rez publicÃ³ una pregunta en \"Foro de Debate: PHP 8 y Nuevas CaracterÃ­sticas\".','views/curso.php?id=1&recurso=16','foro_mensaje',1,1,'2026-10-24 16:05:32',NULL,'notif_seed_2'),(3,6,'certificados','Â¡Certificado emitido!','Completaste con Ã©xito el curso \"Curso Completo de PHP y MySQL\". Ya puedes verificar tu certificado.','views/curso.php?id=1','certificado',1,0,'2026-10-24 16:05:32',NULL,'notif_seed_3'),(4,8,'tareas','Nueva tarea disponible','El docente habilitÃ³ la tarea \"PrÃ¡ctica de Laboratorio: Radar con HC-SR04\".','views/curso.php?id=3&recurso=13','tarea',3,0,'2026-10-24 16:05:32',NULL,'notif_seed_4'),(5,7,'sistema','Solicitud docente en revisiÃ³n','Tu postulaciÃ³n para dictado de cursos se encuentra en revisiÃ³n por el equipo administrativo.','views/usuario.php','solicitud_docente',2,1,'2026-10-24 16:05:32',NULL,'notif_seed_5');
UNLOCK TABLES;

SET FOREIGN_KEY_CHECKS=1;
