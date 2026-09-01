# Classia

**Classia** es una plataforma web orientada a la **gestión educativa y contratación de servicios vinculados a la educación**, desarrollada por **AniTech** como proyecto académico integrador.

La propuesta centraliza en un único entorno digital a diferentes actores, recursos y servicios educativos, proporcionando una experiencia organizada, accesible, segura y moderna tanto para estudiantes como para docentes/proveedores y administradores.

---

## 🎯 Objetivo del proyecto

El objetivo principal de Classia es construir un Entorno Virtual de Aprendizaje (EVA) y catálogo de servicios educativos capaz de gestionar de forma integral el ciclo formativo y comercial:

- Acceso y registro seguro de usuarios con asignación de roles.
- Exploración de cursos y servicios con filtros y categorías.
- Inscripción en cursos y contratación de servicios especializados.
- Gestión de perfiles y paneles dedicados por rol (Estudiante, Proveedor/Docente, Administrador).
- Creación, edición, publicación y administración de publicaciones (cursos y servicios) con persistencia en base de datos.
- Gestión de solicitudes personalizadas.
- Flujo de carrito, pasarela de pago simulada y confirmación.
- Sistema de valoraciones y reseñas.
- Manejo seguro de sesiones y control de acceso (RBAC).

---

## 👥 Roles de la plataforma

Classia implementa un modelo de Control de Acceso Basado en Roles (RBAC):

### 🎓 Estudiantes / Clientes (Rol 3)
- Explorar el catálogo de cursos y servicios con filtros avanzados.
- Consultar detalles de publicaciones, programas formativos y paquetes.
- Solicitar servicios personalizados (e.g. impresión 3D, tutorías, robótica).
- Gestionar su carrito de compras y simular el proceso de pago.
- Visualizar sus cursos inscritos, historial de contrataciones y certificados en su perfil.
- Emitir valoraciones y comentarios sobre cursos y servicios completados.

### 👨‍🏫 Docentes / Proveedores (Rol 2)
- Acceder a su **Panel de Proveedor** con estadísticas y métricas de actividad.
- Crear nuevas publicaciones especificando título, descripción, precio, categoría y modalidad (curso o servicio).
- Editar publicaciones existentes y gestionar su estado (`publicado`, `borrador`, `pausado`, `archivado`).
- Consultar y gestionar en tiempo real el listado dinámico de sus cursos y servicios activos.
- Recibir y gestionar solicitudes de servicios personalizadas.
- Administrar el contenido de sus cursos y calificaciones.

### 🛡️ Administradores (Rol 1)
- Acceso al **Panel de Administrador** para la supervisión global de la plataforma.
- Gestión y moderación de usuarios, roles y permisos.
- Moderación de publicaciones, cursos y servicios ofertados.
- Administración de categorías institucionales y métricas globales.

---

## 🖥️ Estructura del proyecto

El proyecto cuenta con una arquitectura modular en **PHP**, separando la capa de presentación, la lógica de negocio, la configuración y el acceso a datos:

```text
Proyecto_TIS_Classia/
├── index.php                      # Punto de entrada principal de la plataforma
├── config/
│   └── database.php               # Conexión centralizada PDO a MariaDB / MySQL
├── includes/
│   ├── header.php                 # Encabezado modular con navegación y estado de sesión
│   └── footer.php                 # Pie de página institucional modular
├── html/                          # Vistas y páginas de la aplicación (.php)
│   ├── login.php                  # Inicio de sesión con feedback y redirecciones
│   ├── registro.php               # Formulario de registro con validaciones
│   ├── panelProveedor.php         # Panel del proveedor con listado dinámico de publicaciones
│   ├── panelAdministrador.php     # Panel de gestión y métricas administrativas
│   ├── crearPublicacion.php       # Interfaz de creación de cursos y servicios
│   ├── editarPublicacion.php      # Interfaz de edición y gestión de estado
│   ├── catalogo.php               # Catálogo interactivo de cursos y servicios
│   ├── curso.php                  # Vista detallada de contenido de un curso
│   ├── servicioDetalle.php        # Vista de detalle y paquetes de servicios
│   ├── formsSolicitarServicio.php # Formulario de solicitud personalizada
│   ├── solicitudImpresion3d.php   # Formulario especializado de impresión 3D
│   ├── carrito.php                # Carrito de compras
│   ├── pasarelaPago.php           # Simulación de pasarela de pago segura
│   ├── confirmacion.php           # Confirmación de contratación
│   ├── usuario.php                # Perfil del usuario estudiante
│   ├── valoracion.php             # Formulario de emisión de reseñas
│   ├── cambiarContrasena.php      # Cambio de credenciales de usuario
│   ├── restablecerContrasena.php  # Recuperación de contraseña
│   ├── primerosPasos.php          # Guía de onboarding para nuevos usuarios
│   └── politicaPrivacidad.php     # Política de privacidad (Ley 18.331 / URCDP)
├── php/                           # Lógica de backend organizada por módulo
│   ├── auth/
│   │   ├── session.php            # Helper de sesiones seguras y control de acceso
│   │   └── logout.php             # Cierre seguro de sesión y destrucción de cookies
│   ├── usuarios/
│   │   └── registro.php           # Procesamiento de registro, validaciones y hash seguro
│   ├── publicaciones/
│   │   ├── crearPublicacion.php   # Procesamiento de alta de cursos y servicios (PDO)
│   │   ├── editarPublicacion.php  # Procesamiento de edición y actualización de estado
│   │   └── obtenerPublicaciones.php # Helpers para consulta de publicaciones por usuario
│   ├── contrataciones/            # Lógica en desarrollo de contrataciones
│   ├── pagos/                     # Lógica en desarrollo de pagos
│   ├── solicitudes/               # Lógica en desarrollo de solicitudes
│   └── valoraciones/              # Lógica en desarrollo de valoraciones
├── sql/
│   └── schema.sql                 # Esquema relacional DDL (MariaDB / MySQL)
├── docs/                          # Documentación técnica, modelos y diagramas UML
│   ├── estructura_php.md          # Guía de arquitectura backend y sesiones
│   ├── mer_classia.md             # Modelo Entidad-Relación formal
│   ├── modelo_relacional_classia.md # Modelo relacional normalizado
│   ├── diagrama_de_clases_classia.md # Diagrama de clases de dominio UML
│   ├── casos_de_uso_classia.md    # Especificación y diagrama de casos de uso
│   └── diagramas_de_secuencia_classia.md # Diagramas de secuencia de flujos principales
├── css/
│   ├── style.css                  # Hoja de estilos principal (Design Tokens, variables, responsive)
│   └── animation.css              # Microinteracciones, keyframes y accesibilidad
├── js/
│   └── script.js                  # Interacciones dinámicas en el cliente
├── assets/                        # Recursos multimedia, imágenes e íconos
├── scripts/                       # Scripts auxiliares y validaciones de seguridad
├── .github/                       # CI/CD Workflows, templates de PR/Issue y linters
├── .env.example                   # Ejemplo de variables de entorno de base de datos
└── package.json                   # Herramientas de calidad de código y linters
```

---

## 🛠️ Tecnologías utilizadas

### Backend y Base de Datos
- **PHP 8.x**: Lenguaje de servidor para controladores, helpers de sesión y endpoints modulares.
- **PDO (PHP Data Objects)**: Capa de abstracción segura para persistencia con soporte de sentencias preparadas contra SQL Injection.
- **MariaDB / MySQL**: Motor de base de datos relacional estructurado en tercera forma normal (3FN) con integridad referencial completa.

### Frontend y Diseño
- **HTML5 semántico**: Estructura accesible con atributos ARIA y optimización SEO.
- **CSS3 / Vanilla CSS**: Sistema de diseño basado en 55+ Design Tokens, variables CSS, Flexbox/CSS Grid y soporte `@media (prefers-reduced-motion)` para accesibilidad WCAG AA.
- **JavaScript (Vanilla)**: Interacciones del cliente, validaciones dinámicas y componentes de interfaz.

### Calidad, CI/CD y Seguridad
- **Node.js & npm**: Entorno para herramientas de análisis estático (Stylelint, HTMLHint, ESLint, HTML Validate, Lighthouse CI).
- **GitHub Actions**: Integración continua con validación de seguridad (Gitleaks, Semgrep SAST, cabeceras CSP, verificación de referencias SRS).
- **Git & GitHub**: Flujo de trabajo basado en ramas de desarrollo (`Dev-*`, `Testing`, `main`) y Conventional Commits.

---

## 🚀 Instalación y ejecución local

### 1. Requisitos previos
- **PHP 8.0 o superior** con extensión `pdo_mysql` habilitada.
- **MariaDB 10.4+** o **MySQL 8.0+** (vía XAMPP, Laragon, Docker o servicio local).
- **Node.js 18+** y **npm** (opcional, para herramientas de análisis de código).

### 2. Configuración de la base de datos
1. Iniciar el servicio de base de datos (MariaDB/MySQL).
2. Crear la base de datos e importar el esquema DDL:
   ```bash
   mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS classia_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   mysql -u root -p classia_db < sql/schema.sql
   ```
3. Configurar las credenciales en caso de no usar los valores por defecto (`localhost:3306`, usuario `root`, contraseña vacía, base `classia_db`). Se pueden definir las variables de entorno `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER` y `DB_PASSWORD`.

### 3. Iniciar el servidor web local

**Opción A — Servidor integrado de PHP (Recomendado para desarrollo rápido):**
```bash
php -S localhost:8000
```
Abrir en el navegador: [http://localhost:8000](http://localhost:8000)

**Opción B — XAMPP / Laragon:**
Ubicar el proyecto dentro de la carpeta `htdocs` (o `www`) y acceder a través de: `http://localhost/Proyecto_TIS_Classia/`

---

## ✅ Calidad y validación del código

Para ejecutar las herramientas de análisis estático:

```bash
npm install
npm run lint
```

Para verificar reglas de estilo CSS de forma individual:
```bash
npm run lint:css
```

---

## 🔐 Seguridad y cumplimiento normativo

El proyecto implementa un enfoque **DevSecOps** documentado en [SECURITY.md](SECURITY.md):

- **Almacenamiento seguro de credenciales (`RNF-01`, `RNF-02`):** Las contraseñas se hashean utilizando `password_hash()` con algoritmo **bcrypt** nativo.
- **Prevención de inyecciones SQL (`RNF-03`):** Todas las consultas con parámetros utilizan sentencias preparadas PDO (`prepare()` + `execute()`).
- **Gestión de sesiones (`RNF-14`):** Identificadores de sesión regenerados tras autenticación (`session_regenerate_id(true)`), cookies `HttpOnly` y borrado total en logout.
- **Protección de datos (`RNF-10`, `RNF-11`):** Cumplimiento con la **Ley N.º 18.331** de Protección de Datos Personales de Uruguay y derechos ARCO.
- **SAST y Secret Detection:** Escaneo continuo en CI/CD con Semgrep y Gitleaks.

---

## 📌 Estado actual del proyecto

Actualmente el proyecto cuenta con:

- [x] **Arquitectura PHP modular** completada con vistas `.php`, `includes/header.php` y `includes/footer.php`.
- [x] **Base de datos relacional** normalizada e implementada en `sql/schema.sql`.
- [x] **Manejo seguro de sesiones** en `php/auth/session.php` y cierre de sesión en `php/auth/logout.php`.
- [x] **Registro de usuarios** funcional en `php/usuarios/registro.php` con validación de unicidad de email y hash de contraseñas.
- [x] **CRUD de publicaciones** (cursos y servicios) operativo en `php/publicaciones/` con creación, edición, cambio de estados y visualización dinámica en `panelProveedor.php`.
- [x] **Modelado técnico UML completo** en `docs/` (Casos de uso, clases, secuencia, MER y modelo relacional).
- [x] **Pipelines de CI/CD automatizados** para linting, seguridad y verificación de trazabilidad.

---

## 🔮 Próximos pasos

- Implementación del inicio de sesión con autenticación contra base de datos (`php/auth/login.php`).
- Módulo de contrataciones y persistencia de compras (`php/contrataciones/`).
- Módulo de gestión y respuesta a solicitudes de servicios personalizados (`php/solicitudes/`).
- Sistema dinámico de valoraciones y cálculo de promedios (`php/valoraciones/`).
- Panel de administración con métricas y moderación activa (`php/admin/`).

---

## 📚 Documentación técnica adicional

En el directorio [`docs/`](docs/) se encuentran disponibles los documentos de especificación técnica:
- [Arquitectura PHP](docs/estructura_php.md)
- [Diagrama de Casos de Uso](docs/casos_de_uso_classia.md)
- [Diagrama de Clases de Dominio](docs/diagrama_de_clases_classia.md)
- [Diagramas de Secuencia UML](docs/diagramas_de_secuencia_classia.md)
- [Modelo Entidad-Relación (MER)](docs/mer_classia.md)
- [Modelo Relacional de Base de Datos](docs/modelo_relacional_classia.md)
- [Guía de Contribución y Sprints](CONTRIBUTING.md)
- [Política de Seguridad](SECURITY.md)

