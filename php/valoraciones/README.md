# php/valoraciones/

Módulo de consulta y registro de valoraciones sobre contrataciones.

## Archivos

- `obtener_contrataciones_valorables.php`: carga contrataciones del usuario que cumplen las condiciones del formulario.
- `guardar_valoracion.php`: exige sesión y POST, valida CSRF, puntuación de 1 a 5, pertenencia de la contratación, estado permitido y unicidad de la valoración antes del INSERT.

El comentario es opcional, se guarda como `NULL` cuando está vacío y todos los accesos a la base usan PDO preparado. Los errores se almacenan temporalmente en sesión para conservar los datos del formulario después de redirigir.
