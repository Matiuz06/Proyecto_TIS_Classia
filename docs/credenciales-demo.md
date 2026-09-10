# Credenciales demo

Estas cuentas son solo para desarrollo local y pruebas academicas.

| Rol | Email | Contrasena |
| --- | --- | --- |
| Estudiante | `estudiante@classia.com` | `EstudianteDemo1` |
| Docente | `docente@classia.com` | `DocenteDemo1` |
| Administrador | `admin@classia.com` | `AdminDemo1` |

Los valores guardados en `sql/schema.sql` son hashes generados con `password_hash($password, PASSWORD_DEFAULT)` y verificables con `password_verify()`.
