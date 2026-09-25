# Estructura y Arquitectura Backend — Classia

Documento técnico que describe la organización del backend en PHP, la gestión de base de datos, los controladores y las reglas de seguridad del proyecto **Classia**.

---

## 1. Organización del Proyecto

```text
Proyecto_TIS_Classia/
├── index.php                         # Página principal de inicio
├── config/
│   ├── database.php                  # Conexión PDO compatible con MySQL y PostgreSQL/Supabase
│   └── supabase.php                  # Parámetros y utilidades para Supabase
├── includes/
│   ├── header.php                    # Encabezado común y navegación por rol
│   └── footer.php                    # Pie de página común
├── views/                            # Vistas del sistema (.php)
│   ├── login.php                     # Inicio de sesión y 2FA
│   ├── registro.php                  # Registro de usuarios
│   ├── panel-proveedor.php           # Panel para docentes y proveedores
│   ├── panel-administrador.php       # Panel de administración, supervisión y matriculación
│   ├── catalogo.php                  # Catálogo de cursos y servicios con filtros
│   ├── curso.php                     # Aula virtual: temario, entregas, foros y videos
│   ├── contenido-curso.php           # Gestión y ordenamiento del temario (docente/admin)
│   ├── crear-publicacion.php         # Alta de nuevos cursos y servicios
│   ├── editar-publicacion.php        # Edición de publicaciones
│   ├── servicio-detalle.php          # Vista de servicio y paquetes
│   ├── form-solicitar-servicio.php   # Formulario de solicitud personalizada
│   ├── solicitud-impresion-3d.php    # Formulario especializado de impresión 3D
│   ├── carrito.php                   # Carrito de compras
│   ├── pasarela-pago.php             # Simulación de pago
│   ├── confirmacion.php              # Confirmación de compra
│   ├── comprobante.php               # Comprobante de pago
│   ├── usuario.php                   # Perfil y cursos del estudiante
│   ├── perfil-profesional.php        # Perfil público del docente
│   ├── editar-perfil.php             # Edición de datos personales
│   ├── editar-perfil-profesional.php # Edición de datos docentes
│   ├── eventos.php                   # Lista y propuesta de eventos
│   ├── evento-detalle.php            # Detalle de evento
│   ├── noticias.php                  # Lista y propuesta de noticias
│   ├── noticia-detalle.php           # Detalle de noticia
│   ├── valoracion.php                # Emisión de valoraciones y reseñas
│   ├── primeros-pasos.php            # Onboarding para nuevos usuarios
│   └── reglamento.php                # Términos y reglamento institucional
├── php/                              # Lógica de negocio y controladores
│   ├── admin/
│   │   └── acciones_admin.php        # Estadísticas, bloqueo de usuarios, matriculación y moderación
│   ├── auth/
│   │   ├── sesion.php                # Manejo de sesiones y autenticación
│   │   ├── roles.php                 # Constantes de roles y guardias de acceso
│   │   ├── procesar_login.php        # Validación de credenciales y bloqueo de cuenta
│   │   ├── logout.php                # Cierre seguro de sesión
│   │   ├── activar_2fa.php           # Activación de TOTP / 2FA
│   │   └── confirmar_correo.php      # Verificación de correo electrónico
│   ├── publicaciones/
│   │   ├── ContenidoCursoRepository.php # Repositorio PDO de cursos, módulos, clases y recursos
│   │   ├── ElementoCurso.php         # Clase abstracta base
│   │   ├── Modulo.php                # Entidad de módulo
│   │   ├── Unidad.php                # Entidad de clase/unidad
│   │   ├── Recurso.php               # Entidad de recurso multimedia
│   │   ├── contenido_curso.php       # Procesamiento de temario y wrappers de compatibilidad
│   │   ├── detalle_curso.php         # Carga de aula virtual, foros y entregas
│   │   ├── crear_publicacion.php     # Creación de publicaciones
│   │   └── editar_publicacion.php    # Edición de publicaciones
│   ├── contrataciones/
│   │   └── crear_contratacion.php    # Generación de orden de compra
│   ├── pagos/
│   │   ├── pasarela.php              # Carga de orden de pago
│   │   ├── procesar_pago.php         # Procesamiento y emisión de recibo
│   │   └── comprobante_pago.php      # Generación de comprobante
│   ├── solicitudes/
│   │   ├── gestionar_solicitudes_docente.php # Aprobación de solicitudes para ser docente
│   │   └── solicitudes_servicio.php  # Mensajería y seguimiento de servicios
│   ├── noticias/
│   │   └── gestionar_noticias.php    # CRUD y moderación de noticias
│   ├── eventos/
│   │   └── gestionar_eventos.php     # CRUD y moderación de eventos
│   ├── valoraciones/
│   │   └── guardar_valoracion.php    # Guardado de puntuación y comentario
│   └── utils/
│       ├── file_upload_helper.php    # Manejo local de subida de archivos
│       ├── supabase_storage.php      # Manejo de subida a Supabase Storage
│       └── mailer.php                # Envío de correos electrónicos
├── sql/
│   ├── schema.sql                    # Esquema DDL para MySQL / MariaDB
│   └── schema_supabase.sql           # Esquema DDL para PostgreSQL / Supabase
└── docs/                             # Documentación del proyecto
```

---

## 2. Base de Datos y Conexión

La base de datos se conecta a través de PDO en `config/database.php`. El archivo detecta automáticamente si el entorno está usando MySQL o PostgreSQL (Supabase) según las variables de `.env`.

- **MySQL / MariaDB local:** puerto 3306.
- **Supabase / PostgreSQL:** puerto 5432 / 6543 o host `supabase.co`.

Todas las consultas utilizan sentencias preparadas (`$pdo->prepare()` y `$stmt->execute()`) para evitar inyecciones SQL.

---

## 3. Seguridad y Control de Acceso

- **Roles del sistema:**
  - `1`: Estudiante (acceso a compras, catálogo, aula virtual y solicitudes).
  - `2`: Docente / Proveedor (panel de publicaciones, gestión de temarios, atención de servicios y propuestas).
  - `3`: Administrador (supervisión global, métricas, moderación de contenidos, bloqueo de usuarios y matriculación).
- **Protección CSRF:** Todos los formularios POST envían y validan un token generado en `$_SESSION['csrf_token']` mediante `hash_equals()`.
- **Bloqueo de cuentas:** El administrador puede suspender usuarios indicando un motivo. Al intentar iniciar sesión, el sistema valida el estado y deniega el acceso a cuentas inactivas.
- **Subida de archivos:** Se validan extensiones y tipos MIME permitidos tanto en almacenamiento local como en Supabase Storage.
