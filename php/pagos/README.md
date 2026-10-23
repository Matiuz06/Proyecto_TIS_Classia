# php/pagos/

Módulo de procesamiento y pasarela de cobro de servicios del proyecto integrador Classia.

## Componentes y Servicios

- `pasarela.php`: Carga y valida la contratación asociada al usuario autenticado (`id_contratacion`), verifica el monto pendiente y prepara la orden de cobro.
- `procesar_pago.php`: Valida tokens CSRF, contratación y datos de pago; actualiza el estado transaccional sin almacenar datos sensibles ni números de tarjeta en cumplimiento con PCI-DSS y RNF-11.
- **Servicio de Cobro MercadoPago**: Integración mediante SDK / API de MercadoPago para la generación de preferencias de cobro de cursos y contrataciones de servicios, recepción de notificaciones IPN / webhooks y soporte de reintegros automáticos según la política de reembolsos institucional.

## Política de Cancelaciones y Reembolsos (RN-14)

Conforme a la regla de negocio institucional **RN-14** (*"Las cancelaciones o reembolsos deberán ajustarse a la política de cancelación definida por AniTech para cada tipo de servicio"*), los pagos y reintegros se rigen por las siguientes directrices:

1. **Cursos y Talleres Virtuales**: Cancelaciones con más de 48 horas previas al inicio conllevan reintegro del 100%. Entre 24 y 48 horas previas conllevan 50% de reintegro. Menos de 24 horas previas o una vez iniciado el acceso al aula virtual, no aplican cancelaciones.
2. **Servicios Profesionales Personalizados**: Cancelaciones previas a la aceptación del presupuesto o inicio del trabajo conllevan 100% de devolución. Si el proveedor ya inició el desarrollo o incurrió en costos operativos (ej. insumos de impresión 3D), se retendrá la proporción proporcional calculada.
3. **Gestión de Reembolsos**: Los reintegros aprobados se procesan a través de la pasarela de MercadoPago acreditándose en el mismo medio de pago utilizado por el cliente.
4. **Transparencia**: Todo usuario puede consultar las condiciones completas en la vista pública [`views/politica-cancelaciones.php`](../../views/politica-cancelaciones.php) enlazada de forma permanente en el pie de página institucional.

## Seguridad y Cumplimiento

- **Control de Acceso**: Solo el usuario titular de la contratación o un administrador pueden acceder al detalle transaccional.
- **Consultas Preparadas**: Todas las consultas a base de datos utilizan PDO con sentencias preparadas para prevenir SQL Injection (`RNF-03`).
- **Idempotencia y Webhooks**: Las notificaciones de confirmación de pago operan de forma idempotente para evitar duplicación de matrículas.
- **Protección de Datos**: Cumplimiento estricto con la Ley N.º 18.331 de Protección de Datos Personales.
