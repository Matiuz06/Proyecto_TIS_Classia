# Actualización para Entrega Final — Classia (AniTech)
> **Fecha:** Octubre 2026 | **Sprint 4** | Preparado para integrar en el documento PDF de entrega

---

## 1. Resumen de cambios desde la Segunda Entrega

### Correcciones críticas aplicadas
| # | Problema | Corrección aplicada | Estado |
|---|----------|---------------------|--------|
| 1 | Cursos no cargaban (columna `porcentaje_minimo_aprobacion` ausente) | Migración aplicada vía `005_progreso_certificados.sql` | ✅ Resuelto |
| 2 | Tabla `notificaciones` no existía → error en acceso a curso | Migración `007_notificaciones.sql` aplicada | ✅ Resuelto |
| 3 | `schema.sql` duplicaba bloques de tablas (líneas 130–349) | Schema depurado; reemplazado por dump real de la BD | ✅ Resuelto |
| 4 | MER y Modelo Relacional no incluían 7 tablas de Sprint 3–4 | Documentos actualizados con `CURSO_TAREA`, `CERTIFICADO`, `NOTIFICACION`, etc. | ✅ Resuelto |

---

## 2. Pruebas Funcionales — Sprint 4

**Entorno de prueba:** Docker local — Apache 2.4.69 / PHP 8.3.36 / MySQL 8.0
**Fecha de ejecución:** 24/10/2026
**Resultado global:** ✅ **22/22 casos PASS (100%)**

### 2.1 Tabla de casos de prueba

| ID | Funcionalidad | Acción de prueba | Resultado esperado | Resultado |
|----|---------------|-----------------|--------------------|--------------------|
| RF-01a | Login Administrador | Login con admin@classia.com / 12345678 | Redirige a Panel Admin (302) | ✅ PASS |
| RF-01b | Login Docente | Login con docente@classia.com / 12345678 | Redirige a Panel Proveedor (302) | ✅ PASS |
| RF-01c | Login Estudiante | Login con estudiante@classia.com / 12345678 | Redirige a inicio autenticado (302) | ✅ PASS |
| RF-02 | Catálogo de cursos | GET /views/catalogo.php | Lista cursos activos (HTTP 200) | ✅ PASS |
| RF-03 | Detalle de curso | GET /views/curso.php?id=3 | Muestra datos del curso con módulos | ✅ PASS |
| RF-04 | Módulos visibles | Consulta curso_modulos por publicación | Devuelve módulos del curso | ✅ PASS |
| RF-05 | Recursos del curso | Consulta recursos por módulo/unidad | Devuelve archivos, videos, tareas, foros | ✅ PASS |
| RF-06 | Tareas configuradas | Consulta curso_tareas | 5 tareas activas con configuración | ✅ PASS |
| RF-07 | Sistema de entregas | Consulta curso_entregas | 5 entregas (4 calificadas, 1 pendiente) | ✅ PASS |
| RF-08 | Certificados emitidos | Consulta certificados | 5 certificados con código único | ✅ PASS |
| RF-09 | Verificar certificado | GET /views/verificar-certificado.php | Devuelve datos del certificado (200) | ✅ PASS |
| RF-10 | Notificaciones | Consulta notificaciones por usuario | Devuelve notificaciones con estado leida | ✅ PASS |
| RF-11 | Solicitudes de servicio | Consulta solicitudes | 10 solicitudes en distintos estados | ✅ PASS |
| RF-12 | Contrataciones | Consulta contrataciones | 13 contrataciones registradas | ✅ PASS |
| RF-13 | Pagos aprobados | Consulta pagos WHERE estado_pago=Aprobado | Pagos aprobados registrados | ✅ PASS |
| RF-14 | Valoraciones | Consulta valoraciones | 10 valoraciones (1–5 estrellas) | ✅ PASS |
| RF-15 | Noticias publicadas | GET /views/noticias.php | 5 noticias estado=Publicada (HTTP 200) | ✅ PASS |
| RF-16 | Eventos activos | GET /views/eventos.php | 5 eventos estado=Abierto (HTTP 200) | ✅ PASS |
| RF-17 | Solicitudes rol docente | Consulta solicitudes_docente | 10 solicitudes (5 pendientes, 5 aprobadas) | ✅ PASS |
| RF-18 | Control de acceso y sesiones | Acceso no autorizado a panel administrativo sin rol 3 | Redirección 302 / Denegado | ✅ PASS |
| RF-19 | Perfiles profesionales | Consulta perfiles_profesionales | 10 perfiles de docentes/proveedores | ✅ PASS |
| RF-20 | Foro del curso | Consulta curso_foro_mensajes | 5 mensajes de foro registrados | ✅ PASS |

### 2.2 Pruebas HTTP de páginas

| Página | URL | HTTP Status | Estado |
|--------|-----|-------------|--------|
| Inicio | / | 200 | ✅ OK |
| Catálogo | /views/catalogo.php | 200 | ✅ OK |
| Login | /views/login.php | 200 | ✅ OK |
| Registro | /views/registro.php | 200 | ✅ OK |
| Detalle curso | /views/curso.php?id=3 | 200 | ✅ OK |
| Noticias | /views/noticias.php | 200 | ✅ OK |
| Eventos | /views/eventos.php | 200 | ✅ OK |
| Verificar certificado | /views/verificar-certificado.php | 200 | ✅ OK |
| Notificaciones | /views/notificaciones.php | 200 | ✅ OK |
| Panel Admin | /views/panel-administrador.php | 302 → Login | ✅ OK |
| Panel Proveedor | /views/panel-proveedor.php | 302 → Login | ✅ OK |
| Gestionar contenido | /views/gestionar-contenido-curso.php | 302 → Login | ✅ OK |

### 2.3 Prueba de sintaxis PHP

Se ejecutó `php -l` en **todos los archivos .php** del proyecto.
**Resultado: 0 errores de sintaxis** en los 60+ archivos verificados.

---

## 3. Estado de Implementación de Requerimientos

### Funcionalidades completadas

| Módulo | Funcionalidad | Estado |
|--------|--------------|--------|
| Autenticación | Login/logout con sesión segura | ✅ Implementado |
| Autenticación | 2FA/TOTP | ✅ Implementado |
| Autenticación | Verificación de email | ✅ Implementado |
| Autenticación | Restablecimiento de contraseña | ✅ Implementado |
| Usuarios | Registro con validaciones | ✅ Implementado |
| Usuarios | RBAC (3 roles: Admin, Docente, Estudiante) | ✅ Implementado |
| Usuarios | Edición de perfil y foto | ✅ Implementado |
| Cursos | Catálogo con filtros y búsqueda | ✅ Implementado |
| Cursos | Detalle de curso con módulos y recursos | ✅ Implementado |
| Cursos | Creación y edición de publicaciones | ✅ Implementado |
| Cursos | Gestión de módulos, unidades y recursos | ✅ Implementado |
| Cursos | Visibilidad de recursos (ocultar/mostrar a alumnos) | ✅ Implementado |
| Cursos | Soporte de video (YouTube/Vimeo/Loom) | ✅ Implementado |
| Evaluación | Tareas con configuración flexible | ✅ Implementado |
| Evaluación | Entregas de alumnos con archivos | ✅ Implementado |
| Evaluación | Revisión y calificación por docente | ✅ Implementado |
| Progreso | Seguimiento de recursos completados | ✅ Implementado |
| Progreso | Barra de progreso por curso | ✅ Implementado |
| Certificados | Emisión automática al completar curso | ✅ Implementado |
| Certificados | Verificación pública por código | ✅ Implementado |
| Comunicación | Foro por recurso con respuestas anidadas | ✅ Implementado |
| Comunicación | Notificaciones en tiempo real | ✅ Implementado |
| Servicios | Catálogo de servicios | ✅ Implementado |
| Servicios | Solicitudes personalizadas con mensajería | ✅ Implementado |
| Servicios | Solicitud de impresión 3D | ✅ Implementado |
| Comercial | Carrito de compras | ✅ Implementado |
| Comercial | Pasarela de pago (simulada) | ✅ Implementado |
| Comercial | Comprobante descargable | ✅ Implementado |
| Valoraciones | Sistema de reseñas 1–5 estrellas | ✅ Implementado |
| Editorial | Noticias con moderación | ✅ Implementado |
| Editorial | Eventos con inscripciones | ✅ Implementado |
| Administración | Panel con métricas | ✅ Implementado |
| Administración | Moderación de usuarios y publicaciones | ✅ Implementado |
| Administración | Gestión de solicitudes de rol docente | ✅ Implementado |
| Seguridad | CSRF en todos los formularios | ✅ Implementado |
| Seguridad | Protección XSS (htmlspecialchars) | ✅ Implementado |
| Seguridad | Logs de eventos de sistema | ✅ Implementado |
| Seguridad | Sesiones seguras con regeneración de ID | ✅ Implementado |
| Perfil | Perfil profesional público (docentes) | ✅ Implementado |
| Accesibilidad | Políticas y páginas institucionales | ✅ Implementado |

### Matriz de Permisos (Rol × Función)

| Módulo / Funcionalidad | Visitante (Público) | Estudiante (Rol 1) | Docente (Rol 2) | Administrador (Rol 3) |
|------------------------|---------------------|--------------------|-----------------|----------------------|
| Explorar catálogo de cursos | ✅ Permitido | ✅ Permitido | ✅ Permitido | ✅ Permitido |
| Ver noticias y eventos públicos | ✅ Permitido | ✅ Permitido | ✅ Permitido | ✅ Permitido |
| Registro e inicio de sesión | ✅ Permitido | ✅ Permitido | ✅ Permitido | ✅ Permitido |
| Configurar autenticación 2FA | ❌ Requiere login | ✅ Permitido | ✅ Permitido | ✅ Permitido |
| Comprar / Inscribirse en cursos | ❌ Requiere login | ✅ Permitido | ✅ Permitido | ✅ Permitido |
| Acceder a clases y aula virtual | ❌ No accesible | ✅ Alumno inscripto | ✅ Titular del curso | ✅ Acceso total |
| Enviar entregas de tareas | ❌ No accesible | ✅ Permitido | ❌ No aplica | ❌ No aplica |
| Calificar tareas y feedback | ❌ No accesible | ❌ Denegado | ✅ Docente del curso | ✅ Acceso total |
| Emisión y descarga de certificados | ❌ No accesible | ✅ Alumno aprobado | ✅ Docente del curso | ✅ Acceso total |
| Participar en foro del curso | ❌ No accesible | ✅ Alumno inscripto | ✅ Docente del curso | ✅ Moderador |
| Crear y editar publicaciones | ❌ Denegado | ❌ Requiere rol 2 | ✅ Permitido | ✅ Acceso total |
| Gestionar módulos y unidades | ❌ Denegado | ❌ Denegado | ✅ Titular del curso | ✅ Acceso total |
| Postular a rol Docente/Proveedor | ❌ Requiere login | ✅ Permitido | ❌ Ya es docente | ❌ No aplica |
| Panel de administración | ❌ Denegado (302) | ❌ Denegado (302) | ❌ Denegado (302) | ✅ Acceso exclusivo |
| Moderar usuarios y solicitudes | ❌ Denegado (302) | ❌ Denegado (302) | ❌ Denegado (302) | ✅ Acceso exclusivo |

### Funcionalidades pendientes / limitaciones

| Funcionalidad | Justificación |
|--------------|---------------|
| Integración real con MercadoPago | Requiere cuenta de vendedor real; flujo simulado completo |
| Envío de correo en producción | Requiere SMTP real; funciona con Mailpit en local |
| Supabase en producción | Modo híbrido configurado; activo con credenciales reales |
| App móvil nativa | Fuera del alcance del proyecto integrador |

---

## 4. Cronograma actualizado — Sprint 4 (Octubre 2026)

| Semana | Fechas | Actividades realizadas |
|--------|--------|----------------------|
| Sprint 4 — Sem 1 | 01–07/Oct | Implementación de tareas, entregas y calificaciones |
| Sprint 4 — Sem 2 | 08–14/Oct | Certificados automáticos y verificación pública |
| Sprint 4 — Sem 3 | 15–21/Oct | Foro de cursos, notificaciones, pruebas de integración |
| Sprint 4 — Sem 4 | 22–24/Oct | Corrección de bugs, depuración schema.sql, docs actualizados |
| Entrega Final | 29/Oct | Demostración y defensa oral |

---

## 5. Base de datos — Estado actual

### Tablas y registros de prueba (Mínimo 5 tuplas cumplido en el 100% de tablas)

| Tabla | Registros | Descripción |
|-------|-----------|-------------|
| roles | 5 | Estudiante, Docente, Administrador, Institución, Moderador |
| usuarios | 14+ | 3 cuentas demo + usuarios de prueba |
| categorias | 11 | Tecnología, Diseño, Electrónica, etc. |
| publicaciones | 11 | Cursos y servicios activos |
| curso_modulos | 11 | Módulos de cursos |
| curso_unidades | 11 | Unidades dentro de módulos |
| curso_recursos | 19 | Archivos, videos, tareas y foros |
| curso_tareas | 5 | Tareas configuradas |
| curso_entregas | 5 | Entregas de alumnos |
| curso_entrega_archivos | 5 | Archivos adjuntos a entregas |
| curso_recursos_completados | 5 | Progreso de alumnos |
| certificados | 5 | Certificados con código único |
| curso_foro_mensajes | 5 | Mensajes de foro |
| solicitudes | 10 | Solicitudes de servicios |
| solicitud_mensajes | 10 | Mensajería de solicitudes |
| solicitudes_docente | 10 | Solicitudes de rol docente |
| contrataciones | 14 | Órdenes de compra |
| detalles_contratacion | 14 | Líneas de contratación |
| pagos | 13 | Pagos registrados |
| valoraciones | 10 | Reseñas 1–5 estrellas |
| perfiles_profesionales | 10 | Perfiles de docentes |
| noticias | 5 | Noticias institucionales |
| eventos | 5 | Eventos programados |
| notificaciones | 5 | Notificaciones de sistema |
| publicacion_visitas | 337 | Estadísticas de visitas |

### Credenciales para la demostración

| Rol | Email | Contraseña |
|-----|-------|------------|
| Administrador | admin@classia.com | 12345678 |
| Docente/Proveedor | docente@classia.com | 12345678 |
| Estudiante/Cliente | estudiante@classia.com | 12345678 |

---

## 6. Instrucciones de instalación

```bash
# 1. Clonar repositorio
git clone [URL_REPO]
cd Proyecto_TIS_Classia

# 2. Levantar contenedores
docker compose up -d

# 3. Esperar ~30 segundos a que MySQL inicialice

# 4. Acceder
# Plataforma: http://localhost:8080
# phpMyAdmin: http://localhost:8081
# Mailpit:    http://localhost:8025
```

---

## 7. Conclusión actualizada (Sprint 4)

Durante el Sprint 4 se consolidó Classia completando evaluación (tareas/entregas), progreso de aprendizaje, certificación automática, foro y notificaciones. Se corrigieron bugs críticos de acceso a cursos y consistencia del schema.

**22/22 pruebas funcionales PASS (100%). 0 errores de sintaxis PHP.**

La plataforma implementa MVC, RBAC con 3 roles, CSRF/XSS, 2FA, auditoría y soporte híbrido MySQL/Supabase, cubriendo el ciclo formativo completo desde el registro hasta la certificación.
