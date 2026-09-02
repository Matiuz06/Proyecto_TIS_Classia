# php/auth/

Logica de autenticacion y sesiones.

## PHP-03: sesiones

- `session.php`: helper reutilizable para iniciar sesion, guardar el usuario autenticado, verificar autenticacion, obtener el usuario actual, exigir autenticacion y cerrar sesion.
- `logout.php`: cierre minimo de sesion y redireccion a `../../index.php`.

`$_SESSION["usuario"]` guarda solo:

- `id_usuario`
- `nombre`
- `email`
- `id_rol`

No guarda `password`, `password_hash`, datos de PDO ni otros datos sensibles.

Ejemplo para proteger una pagina futura:

```php
require_once __DIR__ . "/../auth/session.php";

requerir_autenticacion("../../html/login.php");
```

PHP-03 no implementa login real, registro ni consultas a la base de datos.
