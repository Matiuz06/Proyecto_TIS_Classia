# Actualización del rol Docente / Proveedor

Esta actualización agrega:

- Gestión ampliada de publicaciones: modalidad, duración, cupos, disponibilidad, vista previa y estado.
- Eliminación lógica mediante el estado `Eliminado`; no se borra la publicación de la base de datos.
- Categorías personalizadas creadas por el docente al publicar o editar.
- Gestión de contenido interno de cursos mediante módulos, unidades/clases y recursos (archivos, PDF, imágenes, videos y enlaces).
- Plantillas predeterminadas de solicitud para proyectos educativos, formación institucional, diseño/impresión 3D, robótica/automatización y mentorías.
- Gestión de solicitudes por parte del proveedor: aceptar, rechazar, contraofertar precio/horario, marcar en proceso o realizada y mantener conversación con el solicitante.
- Seguimiento y mensajería de solicitudes para el cliente.
- Perfil profesional completo con control de visibilidad: usuarios registrados o solamente usuarios relacionados mediante cursos/servicios.

## Base de datos existente

Si el volumen de MySQL ya existe, `schema.sql` no vuelve a ejecutarse automáticamente. Aplicá la migración después de levantar los contenedores:

```powershell
podman exec -it classia_web php /var/www/html/scripts/migrate_provider_features.php
```

El script verifica columnas y tablas antes de crearlas y puede ejecutarse nuevamente.

Para una instalación nueva o si eliminás el volumen de la base de datos, `sql/schema.sql` ya contiene la estructura actualizada y no necesitás ejecutar el script manualmente.

## Flujo recomendado de prueba

1. Entrar como docente.
2. Completar el perfil profesional desde el panel.
3. Crear un curso y agregar módulos, unidades y recursos.
4. Crear un servicio seleccionando una plantilla.
5. Entrar como estudiante y enviar una solicitud desde el servicio.
6. Volver al docente, abrir la solicitud, responder y proponer precio/horario.
7. Entrar nuevamente como estudiante y comprobar la conversación.
8. Probar pausa, activación y eliminación lógica de una publicación.
