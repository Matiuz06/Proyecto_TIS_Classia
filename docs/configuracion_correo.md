# Configuración de correo

## Desarrollo local con Docker

El proyecto usa Mailpit para capturar correos sin enviarlos a Internet.

1. Levantar el entorno:

   ```bash
   docker compose up -d
   ```

2. Abrir `http://localhost:8025` para ver los mensajes.
3. Mantener estas variables en `.env`:

   ```env
   MAIL_DRIVER=smtp
   MAIL_HOST=mailpit
   MAIL_PORT=1025
   MAIL_USERNAME=
   MAIL_PASSWORD=
   MAIL_ENCRYPTION=none
   MAIL_FROM=no-reply@classia.local
   MAIL_FROM_NAME=Classia
   MAIL_TO=anitechsa2026@gmail.com
   APP_URL=http://localhost:8080
   ```

El enlace de confirmación usa `APP_URL`, por eso debe coincidir con la URL desde la que se prueba la aplicación.

## Producción con Brevo e InfinityFree

No subir el `.env` al repositorio. Crear el archivo en el servidor con los valores reales y usar una dirección del dominio alojado como remitente.

Primera opción, si el hosting habilita la función PHP `mail()`:

```env
MAIL_DRIVER=mail
MAIL_FROM=cuenta@tudominio.com
MAIL_FROM_NAME=Classia
MAIL_TO=cuenta-receptora@tudominio.com
APP_URL=https://tudominio.com
```

Para producción se recomienda usar Brevo como proveedor SMTP. En Brevo hay que crear la cuenta, verificar el remitente o dominio y copiar la clave SMTP generada en el panel. Configurar:

```env
MAIL_DRIVER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=USUARIO_SMTP_DE_BREVO
MAIL_PASSWORD=CLAVE_SMTP_DE_BREVO
MAIL_ENCRYPTION=tls
MAIL_FROM=cuenta@tudominio.com
MAIL_FROM_NAME=Classia
MAIL_TO=cuenta-receptora@tudominio.com
APP_URL=https://tudominio.com
```

InfinityFree puede aplicar restricciones a conexiones SMTP externas según la cuenta y el plan. Después de publicar, probar primero el registro y el formulario de contacto desde una cuenta real. Si InfinityFree bloquea conexiones SMTP salientes, habrá que usar el mecanismo de correo habilitado por el proveedor o consultar su soporte.

No usar `no-reply@classia.local` en producción: ese dominio solo sirve para pruebas locales.
