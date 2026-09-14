# Credenciales demo

Estas cuentas son solo para desarrollo local y pruebas académicas.

| Rol | Email | Contraseña |
| --- | --- | --- |
| Estudiante | `estudiante@classia.com` | `12345678` |
| Docente | `docente@classia.com` | `12345678` |
| Administrador | `admin@classia.com` | `12345678` |

Las tres cuentas demo quedan con correo verificado y onboarding completo para que se puedan usar inmediatamente.

Los valores almacenados en `usuarios.password_hash` se verifican con `password_verify()`. Para una base ya existente, ejecutar `scripts/repair_demo_login.php`.
