# Integración docente/proveedor sobre testing

Esta versión toma `Proyecto_TIS_Classia-testing (7)` como base y conserva sus páginas visuales de curso, servicio, proveedor, panel y formulario personalizado. Se integraron sobre ellas la baja lógica real, contenido modular de cursos, perfiles profesionales, solicitudes persistentes y plantillas por tipo de servicio.

Para una base existente ejecutar una vez:

```powershell
podman exec -it classia_web php /var/www/html/scripts/migrate_provider_features.php
```

No es necesario borrar el volumen de MySQL.
