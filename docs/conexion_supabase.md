# Guía de Conexión de Base de Datos con Supabase (Classia)

Classia soporta una arquitectura de base de datos **híbrida y flexible**:
1. **Supabase (PostgreSQL 15+)**: Para entornos remotos, pruebas en la nube y producción.
2. **Local (MySQL / MariaDB)**: Para desarrollo offline o entornos locales clásicos.

El sistema detecta automáticamente el motor adecuado a partir de las variables de entorno en el archivo `.env`.

---

## 1. Configuración de Supabase (Paso a Paso)

### Paso 1: Crear / Seleccionar Proyecto en Supabase
1. Ingresa a tu consola en [supabase.com/dashboard](https://supabase.com/dashboard).
2. Selecciona o crea un nuevo proyecto (ej. `classia-production`).

### Paso 2: Ejecutar el Esquema DDL en Supabase
1. En el menú lateral izquierdo de Supabase, entra al **SQL Editor**.
2. Abre el archivo [`sql/schema_supabase.sql`](../sql/schema_supabase.sql) de este repositorio.
3. Copia todo su contenido, pégalo en el editor SQL de Supabase y presiona **Run**.
4. Esto creará:
   - Todas las tablas del sistema (`usuarios`, `publicaciones`, `solicitudes`, `contrataciones`, `curso_modulos`, etc.).
   - Triggers automáticos de actualización para `fecha_actualizacion` y `actualizado_en`.
   - Índices y claves foráneas en cascada.
   - Datos semilla iniciales (roles, categorías, usuario administrador y cursos de muestra).

### Paso 3: Obtener las Credenciales de Conexión
1. En Supabase, ve a **Project Settings** (ícono de engranaje) → **Database**.
2. En la sección **Connection parameters**, copia los datos:
   - **Host:** ej. `aws-0-sa-east-1.pooler.supabase.com` (o `db.[tu-ref].supabase.co`)
   - **Port:** `6543` (para Transaction Pooler / Concurrencia) o `5432` (Directo)
   - **Database Name:** `postgres`
   - **User:** ej. `postgres.tu_id_de_proyecto`
   - **Password:** La contraseña que definiste al crear el proyecto.

---

## 2. Configuración en `.env`

### Opción A: Conexión Activa a Supabase
En tu archivo `.env` local o en el servidor de hosting:

```env
# Conexión a Base de Datos Supabase (PostgreSQL)
DB_DRIVER=pgsql
DB_HOST=aws-0-sa-east-1.pooler.supabase.com
DB_PORT=6543
DB_NAME=postgres
DB_USER=postgres.tu_id_de_proyecto
DB_PASSWORD=tu_password_segura_de_supabase
DB_SSLMODE=require

# Supabase Storage (Almacenamiento de archivos y cursos)
SUPABASE_URL=https://tu-proyecto.supabase.co
SUPABASE_ANON_KEY=tu-clave-anon-publica
SUPABASE_SERVICE_KEY=tu-clave-service-role-secreta
SUPABASE_BUCKET=recursos-cursos
SUPABASE_ACTIVO=true
```

### Opción B: Conexión a Base de Datos Local (MySQL)
Si deseas trabajar de forma local con MySQL / XAMPP / Docker:

```env
# Conexión a Base de Datos Local (MySQL)
DB_DRIVER=mysql
DB_HOST=localhost
DB_PORT=3306
DB_NAME=classia_db
DB_USER=classia_user
DB_PASSWORD=CONTRASENA_LOCAL

# Storage puede permanecer local o apuntar a Supabase según preferencia
SUPABASE_ACTIVO=false
```

---

## 3. Comprobación y Diagnóstico

Para verificar que la conexión esté funcionando correctamente, ejecuta desde la terminal:

```bash
php scripts/test_database_connection.php
```

El script reportará:
- El motor de base de datos detectado (`PostgreSQL (Supabase)` o `MySQL / MariaDB (Local)`).
- La versión del servidor.
- La cantidad de tablas encontradas y sus nombres.
- El estado de los buckets de Supabase Storage.
