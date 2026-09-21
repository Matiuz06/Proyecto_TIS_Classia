# Classia

**Classia** es una plataforma web orientada a la **gestión educativa y contratación de servicios vinculados a la educación**, desarrollada por **AniTech** como proyecto académico integrador del Taller Integrador de Sistemas.

La propuesta centraliza en un único entorno digital a diferentes actores, recursos y servicios educativos, proporcionando una experiencia organizada, accesible, segura y moderna tanto para estudiantes como para docentes/proveedores y administradores.

---

## Objetivo del proyecto

El objetivo principal de Classia es construir un Entorno Virtual de Aprendizaje (EVA) y catálogo de servicios educativos capaz de gestionar de forma integral el ciclo formativo y comercial:

- Acceso y registro seguro de usuarios con asignación de roles y autenticación de dos factores (2FA/TOTP).
- Exploración de cursos y servicios con filtros y categorías.
- Inscripción en cursos con control de acceso por pago aprobado y contratación de servicios especializados.
- Gestión de perfiles y paneles dedicados por rol (Estudiante, Proveedor/Docente, Administrador).
- Creación, edición, publicación y administración de publicaciones (cursos y servicios) con persistencia en base de datos.
- Gestión de solicitudes personalizadas con mensajería y seguimiento.
- Flujo de carrito, pasarela de pago simulada, confirmación y comprobante.
- Sistema de valoraciones y reseñas.
- Publicación y moderación de Noticias y Eventos por parte de docentes y administradores.
- Soporte de embebido de videos (YouTube, Vimeo, Dailymotion, Loom) y archivos en el aula virtual.
- Manejo seguro de sesiones, control de acceso RBAC y tokens CSRF en todos los formularios.
- Conexión híbrida a MySQL/Docker y PostgreSQL/Supabase con detección automática de driver.

---

## Roles de la plataforma

Classia implementa un modelo de Control de Acceso Basado en Roles (RBAC):

### Estudiantes / Clientes (id_rol = 1)
- Explorar el catálogo de cursos y servicios con filtros avanzados.
- Consultar detalles de publicaciones, programas formativos y paquetes.
- Solicitar servicios personalizados (e.g. impresión 3D, tutorías, robótica).
- Gestionar su carrito de compras y procesar el pago.
- Visualizar sus cursos inscritos (solo con pago aprobado), historial de contrataciones y certificados en su perfil.
- Cursos con pago pendiente aparecen marcados con opción de completar el pago.
- Emitir valoraciones y comentarios sobre cursos y servicios completados.

### Docentes / Proveedores (id_rol = 2)
- Acceder a su **Panel de Proveedor** con estadísticas y métricas de actividad.
- Crear nuevas publicaciones especificando título, descripción, precio, categoría, modalidad, duración, cupos y disponibilidad (curso o servicio).
- Editar publicaciones existentes y gestionar su estado (`Activo`, `Inactivo`, `Pausado`).
- Administrar el contenido multimedia de sus cursos: módulos, unidades, archivos, PDFs, imágenes, videos (subidos o embebidos) y enlaces.
- Recibir, gestionar y responder solicitudes de servicios personalizadas.
- **Proponer Noticias y Eventos** que quedan en estado `Pendiente` hasta su aprobación por el administrador.
- Administrar el contenido de sus cursos y calificaciones de entregas.

### Administradores (id_rol = 3)
- Acceso al **Panel de Administrador** para la supervisión global de la plataforma.
- Gestión y moderación de usuarios, roles y permisos.
- Moderación de publicaciones, cursos y servicios ofertados.
- **Aprobar, rechazar o eliminar Noticias y Eventos** propuestos por docentes o propios.
- Administración de categorías institucionales y métricas globales.

---

## Estructura del proyecto

El proyecto cuenta con una arquitectura modular en **PHP**, separando la capa de presentación (vistas en `views/`), la lógica de negocio (`php/`), los estilos (`css/`) y el acceso a datos:

```text
Proyecto_TIS_Classia/
├── index.php                          # Punto de entrada principal de la plataforma
├── config/
│   ├── database.php                   # Conexión centralizada PDO — MySQL o PostgreSQL (auto-detect)
│   └── supabase.php                   # Configuración adicional para Supabase
├── includes/
│   ├── header.php                     # Encabezado modular con navegación por rol y estado de sesión
│   └── footer.php                     # Pie de página institucional modular
├── views/                             # Vistas y páginas de la aplicación (kebab-case .php)
│   ├── login.php                      # Inicio de sesión con 2FA/TOTP
│   ├── registro.php                   # Registro con validaciones y verificación de email
│   ├── panel-proveedor.php            # Panel del docente/proveedor con métricas y gestión
│   ├── panel-administrador.php        # Panel de gestión y supervisión administrativa
│   ├── crear-publicacion.php          # Creación de cursos y servicios
│   ├── editar-publicacion.php         # Edición, cambio de estado y gestión de publicaciones
│   ├── gestionar-contenido-curso.php  # Gestión de módulos, unidades y recursos de un curso
│   ├── catalogo.php                   # Catálogo interactivo con filtros avanzados
│   ├── curso.php                      # Aula virtual: contenido, entregas, foros y videos embebidos
│   ├── servicio-detalle.php           # Detalle y paquetes de servicios
│   ├── form-solicitar-servicio.php    # Formulario de solicitud personalizada de servicios
│   ├── solicitud-impresion-3d.php     # Formulario especializado de impresión 3D
│   ├── carrito.php                    # Carrito de compras
│   ├── pasarela-pago.php              # Pasarela de pago (acceso bloqueado sin carrito activo)
│   ├── confirmacion.php               # Confirmación de contratación y resumen
│   ├── comprobante.php                # Comprobante de pago descargable
│   ├── usuario.php                    # Perfil del usuario con cursos, servicios y estado de pago
│   ├── eventos.php                    # Listado de eventos — propuesta y moderación
│   ├── evento-detalle.php             # Detalle de evento con compartir / Google Calendar / invitar
│   ├── noticias.php                   # Listado de noticias — propuesta y moderación
│   ├── noticia-detalle.php            # Detalle de noticia con compartir
│   ├── valoracion.php                 # Formulario de valoraciones y reseñas
│   ├── editar-perfil.php              # Edición de datos personales
│   ├── editar-perfil-profesional.php  # Edición del perfil profesional del docente
│   ├── perfil-profesional.php         # Perfil público del docente/proveedor
│   ├── cambiar-contrasena.php         # Cambio de credenciales
│   ├── restablecer-contrasena.php     # Recuperación de contraseña por email
│   ├── configurar-2fa.php             # Configuración de autenticación en dos pasos (TOTP)
│   ├── verificar-2fa.php              # Verificación de código OTP en el inicio de sesión
│   ├── primeros-pasos.php             # Guía de onboarding para nuevos usuarios
│   ├── solicitudes-docente.php        # Gestión de solicitudes de rol docente (admin)
│   ├── solicitudes-servicios.php      # Gestión de solicitudes de servicios (proveedor)
│   ├── mis-solicitudes-servicios.php  # Mis solicitudes de servicios (estudiante)
│   ├── institucional.php              # Página institucional / Sobre Nosotros
│   └── politica-privacidad.php        # Política de privacidad (Ley 18.331 / URCDP)
├── php/                               # Lógica de backend organizada por módulo (snake_case)
│   ├── auth/
│   │   ├── sesion.php                 # Helper canónico de sesiones seguras y CSRF
│   │   ├── roles.php                  # Funciones y constantes de control de roles (RBAC)
│   │   └── logout.php                 # Cierre seguro de sesión
│   ├── usuarios/
│   │   ├── registro.php               # Procesamiento de registro con hash bcrypt
│   │   ├── perfil.php                 # Carga de datos del perfil de usuario
│   │   ├── perfil_profesional.php     # Gestión del perfil profesional del docente
│   │   └── foto_perfil.php            # Subida y eliminación de foto de perfil
│   ├── publicaciones/
│   │   ├── crear_publicacion.php      # Alta de cursos y servicios (PDO + CSRF)
│   │   ├── editar_publicacion.php     # Edición y cambio de estado de publicaciones
│   │   ├── obtener_publicaciones.php  # Helpers SELECT para vistas dinámicas
│   │   ├── contenido_curso.php        # CRUD de módulos, unidades y recursos con soporte de video embebido
│   │   └── detalle_curso.php          # Acceso al aula virtual condicionado a pago aprobado
│   ├── eventos/
│   │   └── gestionar_eventos.php      # CRUD de eventos con moderación (Pendiente → Abierto / Rechazado)
│   ├── noticias/
│   │   └── gestionar_noticias.php     # CRUD de noticias con moderación (Pendiente → Publicada / Rechazada)
│   ├── pagos/
│   │   ├── procesar_pago.php          # Procesamiento de pagos con validación de orden activa
│   │   └── comprobante_pago.php       # Generación de comprobante de pago
│   ├── solicitudes/
│   │   └── solicitudes_servicio.php   # Gestión de solicitudes personalizadas de servicios
│   ├── contrataciones/                # Lógica de órdenes y flujo de contratación
│   ├── valoraciones/                  # Reseñas y cálculo de promedios
│   ├── inicio/
│   │   └── obtener_datos_home.php     # Carga de datos para la página de inicio (publicaciones, eventos, noticias)
│   ├── utils/
│   │   ├── i18n.php                   # Internacionalización y helper de traducción __t()
│   │   └── cedula_uy.php              # Validación y enmascaramiento de cédula uruguaya
│   └── lang/
│       └── es.php                     # Traducciones en español de la plataforma
├── sql/
│   ├── schema.sql                     # Esquema DDL completo para MySQL/MariaDB
│   └── schema_supabase.sql            # Esquema DDL adaptado para PostgreSQL/Supabase
├── docs/                              # Documentación técnica, modelos y diagramas UML
│   ├── estructura_php.md
│   ├── estilo_y_nomenclatura.md
│   ├── mer_classia.md
│   ├── modelo_relacional_classia.md
│   ├── diagrama_de_clases_classia.md
│   ├── casos_de_uso_classia.md
│   ├── diagramas_de_secuencia_classia.md
│   ├── credenciales-demo.md           # Cuentas de prueba para desarrollo local
│   └── diagramas/                     # Diagramas UML exportados (PNG, SVG, PlantUML)
├── css/
│   ├── style.css                      # Sistema de diseño completo: Design Tokens, Flexbox/Grid, responsive
│   └── animation.css                  # Microinteracciones, keyframes y accesibilidad (prefers-reduced-motion)
├── js/
│   └── script.js                      # Interacciones dinámicas en el cliente
├── assets/
│   └── images/                        # Imágenes de marca, avatares, portadas de eventos y noticias
├── .github/                           # CI/CD Workflows, linters y configuración de calidad
├── .env.example                       # Ejemplo de variables de entorno de base de datos
└── docker-compose.yml                 # Stack Docker: PHP 8.3 + MySQL + phpMyAdmin + Mailpit
```

---

## Tecnologías utilizadas

### Backend y Base de Datos
- **PHP 8.3**: Lenguaje de servidor para controladores, helpers de sesión y endpoints modulares.
- **PDO (PHP Data Objects)**: Capa de abstracción segura con sentencias preparadas y detección automática de driver (MySQL / PostgreSQL).
- **MySQL 8.0 / MariaDB** (Docker local): Motor de base de datos relacional en tercera forma normal (3FN).
- **PostgreSQL / Supabase** (producción): Soporte completo con consultas compatibles (sin sintaxis MySQL-específica).

### Frontend y Diseño
- **HTML5 semántico**: Estructura accesible con atributos ARIA, breadcrumbs y optimización SEO.
- **CSS3 / Vanilla CSS**: Sistema de diseño basado en Design Tokens, variables CSS, Flexbox/CSS Grid, soporte `@media (prefers-reduced-motion)` para accesibilidad WCAG AA.
- **JavaScript (Vanilla)**: Interacciones del cliente, validaciones dinámicas, modales nativos (`<dialog>`) y portapapeles.

### Infraestructura y Herramientas
- **Docker Compose**: Stack de desarrollo con `classia_web` (PHP+Apache), `classia_db` (MySQL), `classia_phpmyadmin` y `classia_mailpit`.
- **Node.js & npm**: Herramientas de análisis estático (Stylelint, HTMLHint, ESLint, Lighthouse CI).
- **GitHub Actions**: CI/CD con validación de seguridad (Gitleaks, Semgrep SAST, cabeceras CSP).
- **Git & GitHub**: Flujo de ramas `Dev-*` → `testing` → `main` con Conventional Commits.

---

## Instalación y ejecución local

### 1. Requisitos previos
- **Docker** y **Docker Compose** (recomendado).
- O bien **PHP 8.0+** con extensión `pdo_mysql`, y **MySQL 8.0+** de forma local.

### 2. Configuración con Docker (recomendado)

```bash
# Clonar el repositorio y posicionarse en la raíz
cp .env.example .env        # Configurar variables de entorno
docker compose up -d        # Levantar contenedores
```

Acceder en el navegador: [http://localhost:8080](http://localhost:8080)  
phpMyAdmin disponible en: [http://localhost:8081](http://localhost:8081)  
Mailpit (emails de prueba): [http://localhost:8025](http://localhost:8025)

### 3. Base de datos ya existente (migración)

Si el volumen MySQL ya existe, ejecutar la migración de provider features:
```bash
docker exec classia_web php scripts/migrate_provider_features.php
```

### 4. Cuentas de prueba

| Rol | Email | Contraseña |
|-----|-------|------------|
| Estudiante | `estudiante@classia.com` | `12345678` |
| Docente | `docente@classia.com` | `12345678` |
| Administrador | `admin@classia.com` | `12345678` |

> Ver [`docs/credenciales-demo.md`](docs/credenciales-demo.md) para más detalles.

---

## Seguridad y cumplimiento normativo

El proyecto implementa un enfoque **DevSecOps** documentado en [SECURITY.md](SECURITY.md):

- **Contraseñas seguras (`RNF-01`, `RNF-02`):** Hash con `password_hash()` / bcrypt nativo. Sin contraseñas en texto plano.
- **SQL Injection (`RNF-03`):** Todas las consultas usan sentencias preparadas PDO.
- **Tokens CSRF:** Validados con `hash_equals()` en todos los formularios POST.
- **Autenticación en dos pasos (`2FA/TOTP`):** Implementada con Google/Microsoft Authenticator y Authy. El secreto TOTP se protege con cifrado AES-256-GCM.
- **Sesiones seguras (`RNF-14`):** `session_regenerate_id(true)` tras autenticación, cookies `HttpOnly`, destrucción total en logout.
- **Control de acceso al contenido:** El acceso al aula virtual está condicionado estrictamente a que el pago tenga `estado_pago = 'Aprobado'`. Salir de la pasarela sin pagar no otorga acceso.
- **Protección de datos (`RNF-10`, `RNF-11`):** Cumplimiento con la **Ley N.º 18.331** (Uruguay) y derechos ARCO. Cédula enmascarada en la interfaz.
- **SAST y Secret Detection:** Escaneo continuo en CI/CD con Semgrep y Gitleaks.

---

## 📌 Estado actual del proyecto

### ✅ Completado — Segunda Entrega

- [x] **Arquitectura PHP modular** con más de 50 vistas, includes reutilizables y backend organizado por dominio.
- [x] **Base de datos relacional** en `sql/schema.sql` (MySQL) y `sql/schema_supabase.sql` (PostgreSQL), con 12+ tablas, 3FN e integridad referencial completa.
- [x] **Conexión híbrida MySQL/PostgreSQL**: auto-detección de driver en `config/database.php`, compatible con Docker y Supabase.
- [x] **Autenticación completa**: registro, login, logout, 2FA/TOTP (AES-256-GCM), recuperación de contraseña por email y verificación de correo.
- [x] **CRUD de publicaciones** operativo: creación, edición, cambio de estado y visualización dinámica.
- [x] **Aula Virtual (`views/curso.php`)**: módulos, unidades, entregas, foro, videos embebidos (YouTube, Vimeo, Dailymotion, Loom) y apertura en nueva pestaña.
- [x] **Control de acceso al curso**: solo con `estado_pago = 'Aprobado'`. Cursos pendientes de pago aparecen en el perfil con enlace `Completar pago →`.
- [x] **Pasarela de pago** con bloqueo de acceso sin orden activa: no se puede acceder directamente sin haber iniciado el flujo de compra.
- [x] **Noticias y Eventos**: flujo completo de propuesta por docente (estado `Pendiente`) y aprobación/rechazo/eliminación por administrador.
- [x] **Compartir e Invitar en Eventos**: WhatsApp, Twitter/X, Google Calendar y copiar enlace.
- [x] **Formularios CSS externos**: eliminado CSS inline de `form-solicitar-servicio.php` y `confirmacion.php`; clases en `css/style.css`.
- [x] **Panel de Proveedor** con métricas, solicitudes, valoraciones, contenido de cursos y accesos directos a eventos/noticias.
- [x] **Panel de Administrador** con tarjetas para gestión de solicitudes docentes, eventos y noticias.
- [x] **Navegación por rol**: docentes y admins tienen accesos directos a Eventos y Noticias en la barra de navegación y en el menú desplegable de usuario.
- [x] **Perfil profesional** del docente: información pública, visibilidad configurable.
- [x] **Foto de perfil**: subida, visualización y eliminación.
- [x] **Sistema de valoraciones y reseñas** con cálculo de promedio.
- [x] **Solicitudes de servicios** con mensajería, contraoferta y seguimiento de estado.
- [x] **Gestión de contenido de cursos**: módulos, unidades, archivos (PDF, imágenes, videos), validación de tokens CSRF.
- [x] **i18n (Internacionalización)**: sistema de traducciones `__t()` con archivo `php/lang/es.php`.
- [x] **Modelado técnico UML completo** en `docs/diagramas/` (PNG, SVG, PlantUML): casos de uso, diagrama de clases, MER, modelo relacional, secuencia.
- [x] **CI/CD automatizado**: linting (Stylelint, HTMLHint, ESLint), seguridad (Gitleaks, Semgrep SAST), verificación de trazabilidad SRS.

---

## 📚 Documentación técnica adicional

En el directorio [`docs/`](docs/) se encuentran disponibles los documentos de especificación técnica:

- [Arquitectura PHP y Conexión BD](docs/estructura_php.md)
- [Estilo y Nomenclatura](docs/estilo_y_nomenclatura.md)
- [Diagrama de Casos de Uso](docs/casos_de_uso_classia.md)
- [Diagrama de Clases de Dominio](docs/diagrama_de_clases_classia.md)
- [Diagramas de Secuencia UML](docs/diagramas_de_secuencia_classia.md)
- [Modelo Entidad-Relación (MER)](docs/mer_classia.md)
- [Modelo Relacional de Base de Datos](docs/modelo_relacional_classia.md)
- [Conexión a Supabase](docs/conexion_supabase.md)
- [Credenciales Demo](docs/credenciales-demo.md)
- [Política de Seguridad](SECURITY.md)
