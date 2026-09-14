# php/contrataciones/

Módulo de carrito, creación y confirmación de contrataciones.

## Archivos

- `carrito.php`: agrega, quita o vacía publicaciones en `$_SESSION['carrito_publicaciones']`. Requiere autenticación para agregar, valida CSRF, evita duplicados y carga únicamente publicaciones activas.
- `crear_contratacion.php`: convierte el carrito en una contratación persistida junto con sus detalles, validando sesión, token y publicaciones disponibles.
- `confirmacion.php`: consulta la contratación perteneciente al usuario actual y prepara el resumen posterior a la operación.

## Flujo

1. El catálogo envía `accion=agregar`, `id_publicacion` y `csrf_token`.
2. El carrito valida el token y conserva solo identificadores de publicaciones.
3. El backend vuelve a consultar precios y estados en la base; nunca confía en totales enviados por el navegador.
4. `crear_contratacion.php` persiste cabecera y detalles mediante PDO.
5. La vista de confirmación muestra la contratación del usuario autenticado.

Los errores se guardan temporalmente en sesión para mostrarlos después de la redirección. Las URLs de retorno están limitadas a destinos internos para evitar redirecciones abiertas.
