# php/pagos/

Backend de la pasarela de pago simulada del proyecto integrador.

## Archivos

- `pasarela.php`: exige autenticación, carga una contratación propia por `id_contratacion`, obtiene sus detalles y calcula el importe que se muestra.
- `procesar_pago.php`: valida CSRF, contratación y datos mínimos del formulario; actualiza el estado simulado sin almacenar números completos de tarjeta.

## Alcance y seguridad

El flujo es educativo y no representa una integración financiera real. No deben incorporarse datos de tarjeta reales ni credenciales de producción. El total se obtiene desde la base de datos, las consultas usan PDO preparado y el acceso se limita al usuario dueño de la contratación.

Para una integración real se debe delegar el pago a un proveedor certificado, verificar webhooks firmados, registrar estados idempotentes y mantener el alcance PCI DSS fuera de Classia.
