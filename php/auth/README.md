# php/auth/

Backend de autenticación, autorización y ciclo de vida de la sesión (Sprint 3 — Segunda Entrega).

## Archivos y responsabilidades

| Archivo | Responsabilidad |
|---|---|
| `sesion.php` | Inicia sesiones, configura cookies seguras (HttpOnly, SameSite), regenera ID al autenticar y expone helpers de control de rol. |
| `procesar_login.php` | Valida POST, CSRF, correo, hash bcrypt (`password_verify`) y bifurca al flujo de 2FA si está activo. |
| `totp_helper.php` | Implementación nativa de TOTP (RFC 6238 / Google Authenticator) y generación de códigos QR de enrolamiento. |
| `verificar_2fa.php` | Validación del código de 6 dígitos durante el login o consumo de código de respaldo único. |
| `configurar_2fa.php` | Activación, verificación y generación de códigos de backup de dos factores. |
| `inicio_oauth_google.php` / `callback_oauth_google.php` | Flujo OAuth 2.0 con Google. |
| `inicio_oauth_github.php` / `callback_oauth_github.php` | Flujo OAuth 2.0 con GitHub. |
| `procesar_restablecer_clave.php` | Recuperación de contraseñas mediante tokens seguros con expiración. |
| `roles.php` | Constantes y comprobaciones de permisos por rol (`Estudiante`, `Docente`, `Administrador`). |
| `logout.php` | Cierre seguro de sesión e invalidación de cookies de autenticación. |
