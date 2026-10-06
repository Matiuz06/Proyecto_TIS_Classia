# php/solicitudes/

Módulo de solicitudes de servicios y solicitudes para convertirse en docente.

## Archivos

- `solicitar_docente.php`: crea la tabla si es necesario, evita solicitudes pendientes duplicadas, valida CSRF y registra el motivo del usuario.
- `gestionar_solicitudes_docente.php`: permite al administrador listar y aprobar o rechazar solicitudes, comprobando rol, propiedad de la acción y token CSRF.
- `solicitudes_servicio.php`: gestiona el ciclo completo de solicitudes de servicios: crear, listar, actualizar estado (Pendiente, Aceptada, Rechazada, Contraoferta, En Proceso, Realizada), agregar mensajes de chat y manejar archivos adjuntos.

Las respuestas se devuelven como arrays con `exito`, `mensaje` y `error` para que las vistas decidan cómo presentar el resultado. Los errores SQL se registran en el log y no se exponen al usuario.
