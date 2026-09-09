# Estructura PHP y Arquitectura Backend — Classia · Segunda Entrega

Este documento define la arquitectura y organización técnica del backend PHP para la plataforma **Classia**, actualizado al cierre de la segunda entrega funcional y técnica.

> **Última actualización:** Segunda entrega funcional y técnica (Sprint 2)

---

## Estructura de carpetas y módulos

```text
Proyecto_TIS_Classia/
├── index.php                        # Punto de entrada principal de la plataforma
├── config/
│   └── database.php                 # Conexión centralizada PDO a MariaDB/MySQL (.env)
├── includes/
│   ├── header.php                   # Barra de navegación modular con control de sesión
│   └── footer.php                   # Pie de página institucional modular
├── views/                           # Vistas y páginas accesibles al usuario (.php, kebab-case)
│   ├── login.php                    # Inicio de sesión con feedback y redirección por rol
│   ├── registro.php                 # Formulario de registro con validaciones PHP + CSRF
│   ├── panel-proveedor.php          # Panel del docente: listado dinámico de publicaciones PDO
│   ├── panel-administrador.php      # Panel de administración y métricas institucionales
│   ├── crear-publicacion.php        # Interfaz de alta de cursos y servicios
│   ├── editar-publicacion.php       # Interfaz de edición y cambio de estado de publicaciones
│   ├── catalogo.php                 # Catálogo interactivo de cursos y servicios
│   ├── curso.php                    # Vista de detalle de un curso
│   ├── servicio-detalle.php         # Vista de detalle y paquetes de un servicio
│   ├── form-solicitar-servicio.php  # Formulario de solicitud personalizada de servicio (solicitud docente)
│   ├── solicitud-impresion-3d.php   # Formulario especializado de solicitud de impresión 3D
│   ├── carrito.php                  # Carrito de compras (simulado)
│   ├── pasarela-pago.php            # Simulación de pasarela de pago segura
│   ├── confirmacion.php             # Confirmación de contratación exitosa
│   ├── usuario.php                  # Perfil del usuario / estudiante
│   ├── valoracion.php               # Formulario de emisión de reseñas y valoraciones
│   ├── cambiar-contrasena.php       # Cambio de credenciales de usuario
│   ├── restablecer-contrasena.php   # Recuperación de contraseña
│   ├── primeros-pasos.php           # Guía de onboarding para nuevos usuarios
│   └── politica-privacidad.php      # Política de privacidad (Ley 18.331 / URCDP)
├── php/                             # Lógica de negocio backend organizada por módulo
│   ├── auth/                        # ✅ IMPLEMENTADO
│   │   ├── session.php              # Helper de sesiones seguras y control de acceso
│   │   └── logout.php               # Cierre seguro de sesión y destrucción de cookies
│   ├── usuarios/                    # ✅ IMPLEMENTADO
│   │   └── registro.php             # Registro con validaciones, unicidad de email, bcrypt
│   ├── publicaciones/               # ✅ IMPLEMENTADO
│   │   ├── crear_publicacion.php    # Alta de publicaciones (INSERT PDO con CSRF)
│   │   ├── editar_publicacion.php   # Edición y cambio de estado (UPDATE PDO con CSRF)
│   │   └── obtener_publicaciones.php # Helper para consulta dinámica de publicaciones
│   ├── solicitudes/                 # 🔄 EN DESARROLLO — vista disponible, backend pendiente
│   ├── contrataciones/              # 🔄 EN DESARROLLO — vista disponible, backend pendiente
│   ├── pagos/                       # 🔄 EN DESARROLLO — vista disponible, backend pendiente
│   └── valoraciones/                # 🔄 EN DESARROLLO — vista disponible, backend pendiente
├── sql/
│   └── schema.sql                   # Esquema relacional DDL completo (9 tablas, 3FN)
└── docs/                            # Documentación técnica y diagramas UML
```

---

## Estado de implementación por módulo

| Módulo | Vista frontend | Lógica backend PHP | Persistencia BD |
|:---|:---:|:---:|:---:|
| Autenticación / Sesiones | ✅ `login.php` | ✅ `php/auth/session.php` | ✅ tabla `usuarios` |
| Registro de usuarios | ✅ `registro.php` | ✅ `php/usuarios/registro.php` | ✅ INSERT `usuarios` |
| Crear publicación | ✅ `crear-publicacion.php` | ✅ `php/publicaciones/crear_publicacion.php` | ✅ INSERT `publicaciones` |
| Editar publicación | ✅ `editar-publicacion.php` | ✅ `php/publicaciones/editar_publicacion.php` | ✅ UPDATE `publicaciones` |
| Panel proveedor | ✅ `panel-proveedor.php` | ✅ `php/publicaciones/obtener_publicaciones.php` | ✅ SELECT dinámico |
| Solicitud de servicio | ✅ `form-solicitar-servicio.php` | 🔄 `php/solicitudes/` — pendiente | 🔄 tabla `solicitudes` lista |
| Solicitud impresión 3D | ✅ `solicitud-impresion-3d.php` | 🔄 pendiente | 🔄 tabla `solicitudes` lista |
| Carrito / Contratación | ✅ `carrito.php`, `confirmacion.php` | 🔄 `php/contrataciones/` — pendiente | 🔄 tablas listas |
| Pasarela de pago | ✅ `pasarela-pago.php` | 🔄 `php/pagos/` — pendiente | 🔄 tabla `pagos` lista |
| Valoraciones | ✅ `valoracion.php` | 🔄 `php/valoraciones/` — pendiente | 🔄 tabla `valoraciones` lista |
| Panel administrador | ✅ `panel-administrador.php` | 🔄 `php/admin/` — pendiente | — |
| Login autenticado BD | ✅ `login.php` (vista) | 🔄 `php/auth/login.php` — pendiente | 🔄 SELECT `usuarios` |

---

## Conexión PHP / MySQL (PDO)

La conexión a la base de datos se centraliza en `config/database.php` mediante **PDO (PHP Data Objects)**, que expone la variable `$pdo` para todos los scripts que la requieran mediante `require_once`.

### Configuración mediante variables de entorno

Las credenciales se gestionan a través de un archivo `.env` local (tomar como referencia `.env.example`), evitando subir datos sensibles al repositorio:

```
DB_HOST=localhost
DB_PORT=3306
DB_NAME=classia_db
DB_USER=classia_user
DB_PASSWORD=tu_contrasena_local
```

### Implementación de `config/database.php`

```php
$dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";

$pdo = new PDO($dsn, $user, $password, [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,   // sentencias preparadas nativas
]);
```

### Uso en controladores (ejemplo)

```php
require_once __DIR__ . '/../../config/database.php';

// Consulta parametrizada (previene SQL Injection)
$stmt = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE email = :email");
$stmt->execute(['email' => $correo]);
$usuario = $stmt->fetch();
```

---

## Manejo de Sesiones y Autenticación

El control de sesiones se gestiona en `php/auth/session.php`.

### Funciones disponibles

| Función | Descripción |
|:---|:---|
| `iniciar_sesion()` | Inicia la sesión de forma segura si no existe una activa. Configura cookie `HttpOnly`, `SameSite=Lax`. |
| `establecer_usuario_sesion($id, $nombre, $email, $id_rol)` | Regenera el ID de sesión y almacena datos del usuario. |
| `esta_autenticado()` | Retorna `true` si existe `$_SESSION["usuario"]` con datos válidos. |
| `usuario_actual()` | Devuelve el array del usuario autenticado o `null`. |
| `requerir_autenticacion($login_url)` | Redirige a `$login_url` si no hay sesión activa. |
| `cerrar_sesion()` | Limpia `$_SESSION`, destruye la cookie de sesión y finaliza la sesión. |

### Estructura de datos en sesión

```php
$_SESSION["usuario"] = [
    "id_usuario" => 1,
    "nombre"     => "Nombre del Usuario",
    "email"      => "usuario@classia.local",
    "id_rol"     => 2,  // 1=Cliente/Estudiante, 2=Docente/Proveedor, 3=Administrador
];
```

> ⚠️ **Política de seguridad:** Nunca almacenar contraseñas, hashes, instancias de PDO ni tokens confidenciales dentro de `$_SESSION`.

---

## Roles y Permisos (RBAC)

| `id_rol` | Nombre | Panel de acceso | Acciones habilitadas |
|:---:|:---|:---|:---|
| 1 | `Cliente/Estudiante` | `views/usuario.php` | Explorar catálogo, contratar, solicitar servicios, valorar |
| 2 | `Docente/Proveedor` | `views/panel-proveedor.php` | Crear/editar publicaciones, ver solicitudes, ver contrataciones |
| 3 | `Administrador` | `views/panel-administrador.php` | Gestión de usuarios, moderación, administración de categorías |

El `id_rol` se verifica en cada controlador antes de procesar acciones sensibles. Ejemplo:

```php
// Verificar que el usuario tiene rol Docente/Proveedor (id_rol=2)
if (!esta_autenticado() || usuario_actual()['id_rol'] !== 2) {
    header("Location: ../views/login.php");
    exit;
}
```

---

## Protección CSRF

Todos los formularios que realizan escrituras en la base de datos implementan tokens CSRF:

```php
// Generación del token (en el controlador, antes de renderizar el formulario)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Validación al recibir POST
if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
    $errores[] = "La sesión del formulario expiró.";
}
```

Formularios con protección CSRF activa: `registro.php`, `crear_publicacion.php`, `editar_publicacion.php`.

---

## Reutilización y rutas relativas con `__DIR__`

Para incluir configuración, helpers o componentes reutilizables se utilizan rutas absolutas basadas en `__DIR__`:

```php
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../auth/session.php";
```

---

## Convenciones de nomenclatura y desarrollo

| Recurso | Convención | Ejemplo |
|:---|:---|:---|
| Archivos PHP de vista | `kebab-case` | `panel-proveedor.php`, `crear-publicacion.php` |
| Archivos PHP de controlador | `snake_case` | `crear_publicacion.php`, `session.php` |
| Tablas SQL | `snake_case` plural | `usuarios`, `publicaciones`, `detalles_contratacion` |
| Claves primarias | `snake_case`: `id_entidad` | `id_usuario`, `id_publicacion`, `id_rol` |
| Variables / funciones PHP | `snake_case` | `$id_usuario`, `requerir_autenticacion()` |
| Consultas SQL | PDO preparadas (`prepare()` + `execute()`) | Obligatorio; previene SQL Injection |
| Credenciales | Solo en `.env` local; nunca al repositorio | Ver `.env.example` |
