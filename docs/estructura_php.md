# Estructura PHP

Este documento define la estructura inicial del backend PHP para Classia.

## Carpetas

- `config/`: configuracion global del backend.
- `config/database.php`: futura conexion PDO a la base de datos. Se implementara en PHP-02.
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

La conexion a la base de datos estara en `config/database.php`.

Los scripts SQL deben guardarse en `sql/`. El archivo `sql/schema.sql` sera incorporado desde la rama que contiene el modelo de base de datos.

## Reutilizacion con require_once

Para incluir configuracion o archivos reutilizables se recomienda usar rutas basadas en `__DIR__`:

```php
require_once __DIR__ . "/ruta/al/archivo.php";
```

## Convenciones

- Archivos PHP: `snake_case`.
- Tablas: plural y `snake_case`, por ejemplo `usuarios`, `roles`, `publicaciones`, `contrataciones`, `valoraciones`.
- Claves primarias y foraneas: respetar las denominaciones establecidas en `sql/schema.sql`, por ejemplo `id_usuario`, `id_publicacion`, `id_contratacion`, `id_valoracion`, `id_solicitud`.
- No colocar credenciales reales en archivos del repositorio.
- No mezclar logica PHP nueva dentro de HTML hasta la tarea correspondiente.
