# Modelo Relacional y Diccionario de Datos - Classia

## Resumen del Documento
Este documento especifica la **transformación del Modelo Entidad-Relación (MER) al Modelo Relacional Físico** para la base de datos de **Classia** (MariaDB/MySQL). Incluye la definición de tablas, claves primarias, claves foráneas, restricciones de integridad, resolución de relaciones N:M, diccionario inicial de datos y verificación de normalización (3FN).

---

## Esquema de Tablas Físicas

### 1. `roles`
- **`idRol`** `INT AUTO_INCREMENT` `PRIMARY KEY`
- **`nombreRol`** `VARCHAR(50)` `NOT NULL` `UNIQUE`
- **`descripcion`** `VARCHAR(255)` `NULL`

### 2. `usuarios`
- **`idUsuario`** `INT AUTO_INCREMENT` `PRIMARY KEY`
- **`nombre`** `VARCHAR(100)` `NOT NULL`
- **`apellido`** `VARCHAR(100)` `NOT NULL`
- **`email`** `VARCHAR(150)` `NOT NULL` `UNIQUE`
- **`passwordHash`** `VARCHAR(255)` `NOT NULL`
- **`telefono`** `VARCHAR(30)` `NULL`
- **`fechaRegistro`** `DATETIME` `NOT NULL` `DEFAULT CURRENT_TIMESTAMP`
- **`idRol`** `INT` `NOT NULL`
  - `FOREIGN KEY (idRol) REFERENCES roles(idRol)`

### 3. `categorias`
- **`idCategoria`** `INT AUTO_INCREMENT` `PRIMARY KEY`
- **`nombreCategoria`** `VARCHAR(100)` `NOT NULL` `UNIQUE`
- **`descripcion`** `TEXT` `NULL`

### 4. `publicaciones`
- **`idPublicacion`** `INT AUTO_INCREMENT` `PRIMARY KEY`
- **`titulo`** `VARCHAR(200)` `NOT NULL`
- **`descripcion`** `TEXT` `NOT NULL`
- **`precio`** `DECIMAL(10,2)` `NOT NULL`
- **`tipo`** `ENUM('Curso', 'Servicio')` `NOT NULL`
- **`estado`** `ENUM('Activo', 'Inactivo', 'Pausado')` `NOT NULL` `DEFAULT 'Activo'`
- **`fechaCreacion`** `DATETIME` `NOT NULL` `DEFAULT CURRENT_TIMESTAMP`
- **`idUsuario`** `INT` `NOT NULL`
  - `FOREIGN KEY (idUsuario) REFERENCES usuarios(idUsuario)`
- **`idCategoria`** `INT` `NOT NULL`
  - `FOREIGN KEY (idCategoria) REFERENCES categorias(idCategoria)`

### 5. `solicitudes`
- **`idSolicitud`** `INT AUTO_INCREMENT` `PRIMARY KEY`
- **`titulo`** `VARCHAR(200)` `NOT NULL`
- **`descripcion`** `TEXT` `NOT NULL`
- **`estado`** `ENUM('Pendiente', 'Aceptada', 'Rechazada', 'Cancelada')` `NOT NULL` `DEFAULT 'Pendiente'`
- **`fechaSolicitud`** `DATETIME` `NOT NULL` `DEFAULT CURRENT_TIMESTAMP`
- **`idUsuario`** `INT` `NOT NULL`
  - `FOREIGN KEY (idUsuario) REFERENCES usuarios(idUsuario)`
- **`idPublicacion`** `INT` `NULL`
  - `FOREIGN KEY (idPublicacion) REFERENCES publicaciones(idPublicacion)`

### 6. `contrataciones`
- **`idContratacion`** `INT AUTO_INCREMENT` `PRIMARY KEY`
- **`fechaContratacion`** `DATETIME` `NOT NULL` `DEFAULT CURRENT_TIMESTAMP`
- **`montoTotal`** `DECIMAL(10,2)` `NOT NULL`
- **`estado`** `ENUM('Pendiente', 'En Proceso', 'Completada', 'Cancelada')` `NOT NULL` `DEFAULT 'Pendiente'`
- **`idUsuario`** `INT` `NOT NULL`
  - `FOREIGN KEY (idUsuario) REFERENCES usuarios(idUsuario)`

### 7. `detallesContratacion` *(Tabla Intermedia que resuelve relación N:M)*
- **`idDetalle`** `INT AUTO_INCREMENT` `PRIMARY KEY`
- **`cantidad`** `INT` `NOT NULL` `DEFAULT 1`
- **`precioUnitario`** `DECIMAL(10,2)` `NOT NULL`
- **`subtotal`** `DECIMAL(10,2)` `NOT NULL`
- **`idContratacion`** `INT` `NOT NULL`
  - `FOREIGN KEY (idContratacion) REFERENCES contrataciones(idContratacion)`
- **`idPublicacion`** `INT` `NOT NULL`
  - `FOREIGN KEY (idPublicacion) REFERENCES publicaciones(idPublicacion)`

### 8. `pagos`
- **`idPago`** `INT AUTO_INCREMENT` `PRIMARY KEY`
- **`monto`** `DECIMAL(10,2)` `NOT NULL`
- **`metodoPago`** `ENUM('Tarjeta', 'Transferencia', 'MercadoPago', 'Efectivo')` `NOT NULL`
- **`estadoPago`** `ENUM('Pendiente', 'Aprobado', 'Rechazado')` `NOT NULL` `DEFAULT 'Pendiente'`
- **`fechaPago`** `DATETIME` `NOT NULL` `DEFAULT CURRENT_TIMESTAMP`
- **`transaccionRef`** `VARCHAR(100)` `NULL`
- **`idContratacion`** `INT` `NOT NULL`
  - `FOREIGN KEY (idContratacion) REFERENCES contrataciones(idContratacion)`

### 9. `valoraciones`
- **`idValoracion`** `INT AUTO_INCREMENT` `PRIMARY KEY`
- **`puntuacion`** `INT` `NOT NULL` *(CHECK puntuacion BETWEEN 1 AND 5)*
- **`comentario`** `TEXT` `NULL`
- **`fechaValoracion`** `DATETIME` `NOT NULL` `DEFAULT CURRENT_TIMESTAMP`
- **`idUsuario`** `INT` `NOT NULL`
  - `FOREIGN KEY (idUsuario) REFERENCES usuarios(idUsuario)`
- **`idPublicacion`** `INT` `NOT NULL`
  - `FOREIGN KEY (idPublicacion) REFERENCES publicaciones(idPublicacion)`
- **`idContratacion`** `INT` `NULL`
  - `FOREIGN KEY (idContratacion) REFERENCES contrataciones(idContratacion)`

---

## Resolución de Relaciones N:M

La relación entre **`contrataciones`** y **`publicaciones`** es de tipo **Muchos a Muchos (N:M)**, ya que una contratación puede incluir múltiples publicaciones (cursos/servicios), y una publicación puede estar presente en múltiples contrataciones realizadas por distintos usuarios.

- **Solución implementada:** Se creó la tabla intermedia **`detallesContratacion`**, descomponiendo la relación N:M en dos relaciones 1:N:
  1. `contrataciones` (1) $\rightarrow$ (N) `detallesContratacion`
  2. `publicaciones` (1) $\rightarrow$ (N) `detallesContratacion`

---

## Verificación de Normalización (Tercera Forma Normal - 3FN)

1. **1FN (Primera Forma Normal):** Todos los atributos contienen valores atómicos (sin listas ni conjuntos en una celda) y cada tabla posee una clave primaria bien definida.
2. **2FN (Segunda Forma Normal):** Todos los atributos no clave dependen funcionalmente de la totalidad de la clave primaria.
3. **3FN (Tercera Forma Normal):** No existen dependencias transitivas entre atributos no clave. Todos los campos no clave dependen directamente únicamente de la clave primaria.

---

## Archivo SQL Físico

El script DDL listo para ejecución en MySQL/MariaDB se encuentra en:
👉 [`sql/schema.sql`]