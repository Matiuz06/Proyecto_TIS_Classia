# Estructura PHP

Este documento define la estructura inicial del backend PHP para Classia.

## Carpetas

- `config/`: configuracion global del backend.
- `config/database.php`: conexion PDO centralizada a la base de datos.
- `includes/`: codigo PHP reutilizable, como validaciones, helpers o componentes comunes.
- `php/auth/`: futura logica de login, registro y logout.
- `php/usuarios/`: futura logica relacionada con usuarios.
- `php/publicaciones/`: futura logica de cursos y servicios.
- `php/solicitudes/`: futura logica relacionada con solicitudes de servicios.
- `php/contrataciones/`: futura logica de contrataciones.
- `php/pagos/`: futura simulacion y registro de pagos.
- `php/valoraciones/`: futura logica de valoraciones.
- `sql/`: esquema, scripts y recursos relacionados con la base de datos.
- `docs/`: documentacion tecnica y modelos.

## Paginas y logica

Las paginas actuales deben mantenerse en `index.html` y `html/` hasta la tarea de migracion HTML a PHP.

La logica PHP debe ir dentro de `php/`, separada por responsabilidad. Por ejemplo:

- `php/auth/procesar_login.php`
- `php/publicaciones/crear_publicacion.php`
- `php/publicaciones/detalle_publicacion.php`

Los archivos reutilizables deben ir en `includes/`.

## Base de datos

La conexion PDO a la base de datos esta en `config/database.php` y deja disponible la variable `$pdo` para los archivos que la incluyan.

Valores locales predeterminados:

- `DB_HOST`: `localhost`
- `DB_PORT`: `3306`
- `DB_NAME`: `classia_db`
- `DB_USER`: `root`
- `DB_PASSWORD`: vacia

Estos valores pueden reemplazarse con variables de entorno del mismo nombre. El archivo `.env.example` muestra los nombres esperados, pero el proyecto no carga archivos `.env` automaticamente ni requiere paquetes externos.

Los scripts SQL deben guardarse en `sql/`. El archivo `sql/schema.sql` sera incorporado desde la rama que contiene el modelo de base de datos.

## Reutilizacion con require_once

Para incluir configuracion o archivos reutilizables se recomienda usar rutas basadas en `__DIR__`:

```php
require_once __DIR__ . "/ruta/al/archivo.php";
```

Ejemplo para reutilizar la conexion desde un archivo dentro de `php/usuarios/`:

```php
require_once __DIR__ . "/../../config/database.php";

$stmt = $pdo->prepare("SELECT 1");
```

## Sesiones PHP

La logica inicial de sesiones esta en `php/auth/session.php`.

Funciones disponibles:

- `iniciar_sesion()`: inicia la sesion solo si no existe una sesion activa.
- `establecer_usuario_sesion($id_usuario, $nombre, $email, $id_rol)`: regenera el ID de sesion y guarda los datos minimos del usuario autenticado.
- `esta_autenticado()`: devuelve `true` cuando existe `$_SESSION["usuario"]` con los datos minimos requeridos.
- `usuario_actual()`: devuelve el arreglo del usuario autenticado o `null`.
- `requerir_autenticacion($login_url)`: redirige a la URL de login indicada cuando no hay usuario autenticado.
- `cerrar_sesion()`: vacia `$_SESSION`, elimina la cookie de sesion cuando corresponde y destruye la sesion.

Estructura guardada en sesion:

```php
$_SESSION["usuario"] = [
    "id_usuario" => 1,
    "nombre" => "Usuario de prueba",
    "email" => "prueba@classia.local",
    "id_rol" => 1,
];
```

No se deben guardar `password`, `password_hash`, datos de PDO ni datos sensibles innecesarios.

Prueba manual de persistencia con XAMPP:

1. Abrir `scripts/session/iniciar_prueba.php` desde el navegador.
2. Abrir `scripts/session/verificar_prueba.php` y comprobar que el usuario ficticio persiste.
3. Abrir `scripts/session/cerrar_prueba.php`.
4. Volver a `scripts/session/verificar_prueba.php` y comprobar que ya no hay usuario autenticado.

Estos scripts son solo para desarrollo y no consultan la base de datos.

## Convenciones

- Archivos PHP: `snake_case`.
- Tablas: plural y `snake_case`, por ejemplo `usuarios`, `roles`, `publicaciones`, `contrataciones`, `valoraciones`.
- Claves primarias y foraneas: respetar las denominaciones establecidas en `sql/schema.sql`, por ejemplo `id_usuario`, `id_publicacion`, `id_contratacion`, `id_valoracion`, `id_solicitud`.
- No colocar credenciales reales en archivos del repositorio.
- No mezclar logica PHP nueva dentro de HTML hasta la tarea correspondiente.
