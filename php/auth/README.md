# php/auth/

Backend de autenticación, autorización y ciclo de vida de la sesión.

## Archivos y responsabilidades

| Archivo | Responsabilidad |
|---|---|
| `sesion.php` | Inicia sesiones, configura cookies, regenera el ID al autenticar, expone helpers de usuario y destruye sesiones. |
| `procesar_login.php` | Valida POST, CSRF, correo, contraseña y correo confirmado; redirige según rol. |
| `login.php` | Backend alternativo de login/onboarding utilizado por algunos flujos heredados. |
| `registro.php` | Está en `php/usuarios/`; procesa alta, hash, verificación de correo y sesión pendiente. |
| `inicio_oauth_google.php` | Genera el estado CSRF y redirige a Google OAuth 2.0. |
| `callback_oauth_google.php` | Valida el estado, consulta OpenID UserInfo, crea o localiza el usuario y guarda su foto. |
| `procesar_restablecer_clave.php` | Solicita enlaces neutros de recuperación y consume tokens de un solo uso con expiración. |
| `guardia_onboarding.php` | Impide que estudiantes incompletos accedan al resto de la aplicación; permite políticas legales. |
| `roles.php` | Centraliza constantes y comprobaciones de rol. |
| `password_policy.php` | Reutiliza las reglas de complejidad de contraseña. |
| `logout.php` | Vacía la sesión, invalida la cookie y redirige al inicio. |

## Controles implementados

- Tokens CSRF generados con `random_bytes()` y comparados con `hash_equals()`.
- `password_hash()` y `password_verify()` para credenciales; nunca se guarda la contraseña original.
- `session_regenerate_id(true)` después del login para evitar fijación de sesión.
- Cookie `HttpOnly`, `SameSite=Lax` y `Secure` cuando la conexión usa HTTPS.
- Cookie de navegador fuera de entornos locales; en `localhost`, `127.0.0.1` y `::1` se permite una duración de desarrollo de 30 días.
- OAuth Google protegido con parámetro `state`; la imagen externa se conserva como URL HTTPS.
- Mensaje neutro en recuperación para no revelar si un correo existe.

`$_SESSION["usuario"]` contiene únicamente `id_usuario`, `nombre`, `email`, `id_rol` y, cuando existe, `foto_perfil`.

## Límites actuales

Google es autenticación federada, no autenticación en dos pasos. Todavía no existe un desafío OTP por correo o celular posterior al primer factor.

Ejemplo de protección:

```php
require_once __DIR__ . '/../auth/sesion.php';
requerir_autenticacion('../../views/login.php');
```
