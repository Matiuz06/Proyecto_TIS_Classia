# Estructura PHP y Arquitectura Backend — Classia

Este documento define la arquitectura y organización técnica del backend PHP para la plataforma **Classia**.

---

## Estructura de carpetas y módulos

- `config/`: Configuración global del backend.
  - `config/database.php`: Conexión centralizada PDO a MariaDB/MySQL con manejo de variables de entorno y sentencias preparadas.
- `includes/`: Código PHP reutilizable y componentes compartidos de presentación.
  - `includes/header.php`: Barra de navegación modular con control de estado de sesión.
  - `includes/footer.php`: Pie de página institucional modular.
- `views/`: Vistas y páginas de la aplicación implementadas en PHP (`login.php`, `registro.php`, `panel-proveedor.php`, `panel-administrador.php`, `crear-publicacion.php`, `editar-publicacion.php`, etc.).
- `php/auth/`: Lógica de autenticación, control de acceso y sesiones.
  - `session.php`: Helper de funciones de sesión (`iniciar_sesion`, `requerir_autenticacion`, `establecer_usuario_sesion`, etc.).
  - `logout.php`: Procesamiento de cierre de sesión y destrucción de cookies.
- `php/usuarios/`: Lógica de usuarios.
  - `registro.php`: Procesamiento de registro de nuevos usuarios con validación de datos, verificación de unicidad de email y hash seguro (`password_hash`).
- `php/publicaciones/`: Lógica y controladores del ciclo de vida de cursos y servicios.
  - `crear_publicacion.php`: Alta de nuevas publicaciones vinculadas al usuario autenticado.
  - `editar_publicacion.php`: Edición de publicaciones existentes y alternancia de estados (`publicado`, `borrador`, `pausado`).
  - `obtener_publicaciones.php`: Funciones helper (`obtenerPublicacionesPorUsuario`) para alimentar vistas dinámicas.
- `php/solicitudes/`: Futura lógica relacionada con solicitudes de servicios personalizados.
- `php/contrataciones/`: Futura lógica de carrito y contrataciones.
- `php/pagos/`: Futura simulación y registro de pagos.
- `php/valoraciones/`: Futura lógica de valoraciones y cálculo de reseñas.
- `sql/`: Esquema relacional DDL (`schema.sql`), scripts y recursos de base de datos.
- `docs/`: Documentación técnica, modelos UML (casos de uso, clases, secuencia) y diseño relacional (MER).

---

## Vistas y páginas PHP

La plataforma completó la migración a PHP:
- El punto de entrada es `index.php` en la raíz.
- Las vistas residen en `views/*.php` y hacen uso de `require_once` para incorporar `includes/header.php` y `includes/footer.php`.
- Los formularios envían peticiones POST hacia las vistas o controladores en `php/` procesando de forma segura las operaciones.

## Base de datos y persistencia

La conexión PDO a la base de datos se centraliza en `config/database.php` y expone la variable `$pdo` para los scripts que la requieran.

La configuración de conexión se gestiona mediante variables de entorno en un archivo `.env` local (tomando como referencia `.env.example`).

El esquema DDL relacional está disponible en `sql/schema.sql` e implementa integridad referencial completa (Foreign Keys, restricciones UNIQUE y tablas en 3FN).

---

## Reutilización y rutas relativas con `__DIR__`

Para incluir configuración, helpers o componentes reutilizables se deben utilizar rutas absolutas basadas en `__DIR__`:

```php
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../auth/session.php";
```

---

## Manejo de sesiones y autenticación

El control de sesiones se gestiona en `php/auth/session.php`.

### Funciones disponibles:
- `iniciar_sesion()`: Inicia la sesión de forma segura únicamente si no existe una activa.
- `establecer_usuario_sesion($id_usuario, $nombre, $email, $id_rol)`: Regenera el ID de sesión (`session_regenerate_id(true)`) y almacena los datos mínimos del usuario.
- `esta_autenticado()`: Retorna `true` si existe `$_SESSION["usuario"]` con datos válidos.
- `usuario_actual()`: Devuelve el array con la información del usuario autenticado o `null`.
- `requerir_autenticacion($login_url)`: Redirige a `$login_url` si no hay sesión activa.
- `cerrar_sesion()`: Limpia `$_SESSION`, destruye la cookie de sesión y finaliza la sesión en el servidor.

### Estructura de datos en sesión:
```php
$_SESSION["usuario"] = [
    "id_usuario" => 1,
    "nombre" => "Nombre del Usuario",
    "email" => "usuario@classia.local",
    "id_rol" => 2, // 1: Administrador, 2: Proveedor/Docente, 3: Estudiante/Cliente
];
```

> ⚠️ **Política de seguridad de sesión:** Nunca deben almacenarse contraseñas, hashes, instancias de PDO ni tokens confidenciales dentro de `$_SESSION`.

---

## Convenciones de nomenclatura y desarrollo

- **Archivos PHP:** `camelCase` o `snake_case` según el contexto del módulo (`crearPublicacion.php`, `session.php`).
- **Tablas:** Plural y `snake_case` (`usuarios`, `roles`, `publicaciones`, `solicitudes`, `contrataciones`, `detalles_contratacion`, `pagos`, `valoraciones`).
- **Claves primarias y foráneas:** Denominaciones estandarizadas en `sql/schema.sql` (`id_usuario`, `id_publicacion`, `id_contratacion`, `id_solicitud`, `id_rol`, etc.).
- **Consultas SQL:** Obligatoriamente parametrizadas mediante Sentencias Preparadas PDO (`$pdo->prepare()`) para mitigar SQL Injection.
- **Credenciales:** Prohibido subir contraseñas o datos de producción al repositorio.

