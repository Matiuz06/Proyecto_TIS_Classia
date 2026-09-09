# Modelo Relacional y Diccionario de Datos — Classia · Segunda Entrega

## Resumen del Documento
Este documento especifica la **transformación del MER al Modelo Relacional Físico** para la base de datos de **Classia** (MariaDB/MySQL). Incluye la definición de tablas, claves primarias, claves foráneas, restricciones de integridad, resolución de relaciones N:M, diccionario de datos, roles del sistema y verificación de normalización (3FN).

> **Última actualización:** Segunda entrega funcional y técnica (Sprint 2)  
> **Coherencia validada contra:** [`sql/schema.sql`](../sql/schema.sql)  
> **Convención de nombres:** `snake_case` para tablas y columnas, conforme al DDL físico.

---

## Esquema de Tablas Físicas

### 1. `roles`
| Columna | Tipo | Restricciones |
|:---|:---|:---|
| `id_rol` | `INT AUTO_INCREMENT` | `PRIMARY KEY` |
| `nombre_rol` | `VARCHAR(50)` | `NOT NULL UNIQUE` |
| `descripcion` | `VARCHAR(255)` | `NULL` |

**Datos semilla:**
| `id_rol` | `nombre_rol` | `descripcion` |
|:---:|:---|:---|
| 1 | `Cliente/Estudiante` | Usuario consumidor de cursos y servicios |
| 2 | `Docente/Proveedor` | Usuario creador y prestador de servicios educativos |
| 3 | `Administrador` | Superusuario del sistema |

---

### 2. `usuarios`
| Columna | Tipo | Restricciones |
|:---|:---|:---|
| `id_usuario` | `INT AUTO_INCREMENT` | `PRIMARY KEY` |
| `nombre` | `VARCHAR(100)` | `NOT NULL` |
| `apellido` | `VARCHAR(100)` | `NOT NULL` |
| `email` | `VARCHAR(150)` | `NOT NULL UNIQUE` |
| `password_hash` | `VARCHAR(255)` | `NOT NULL` |
| `telefono` | `VARCHAR(30)` | `NULL` |
| `fecha_registro` | `DATETIME` | `NOT NULL DEFAULT CURRENT_TIMESTAMP` |
| `id_rol` | `INT` | `NOT NULL` · `FK → roles(id_rol)` ON DELETE RESTRICT |

---

### 3. `categorias`
| Columna | Tipo | Restricciones |
|:---|:---|:---|
| `id_categoria` | `INT AUTO_INCREMENT` | `PRIMARY KEY` |
| `nombre_categoria` | `VARCHAR(100)` | `NOT NULL UNIQUE` |
| `descripcion` | `TEXT` | `NULL` |

**10 categorías semilla:** Programación y Desarrollo, Robótica y Automatización, Diseño e Impresión 3D, Mentorías y Capacitación, Inteligencia Artificial y Datos, Electrónica y Microcontroladores, Ciberseguridad y Redes, Diseño Web y UX/UI, Idiomas y Comunicación Técnica, Gestión de Proyectos Tecnológicos.

---

### 4. `publicaciones`
| Columna | Tipo | Restricciones |
|:---|:---|:---|
| `id_publicacion` | `INT AUTO_INCREMENT` | `PRIMARY KEY` |
| `titulo` | `VARCHAR(200)` | `NOT NULL` |
| `descripcion` | `TEXT` | `NOT NULL` |
| `precio` | `DECIMAL(10,2)` | `NOT NULL` |
| `tipo` | `ENUM('Curso','Servicio')` | `NOT NULL` |
| `estado` | `ENUM('Activo','Inactivo','Pausado')` | `NOT NULL DEFAULT 'Activo'` |
| `fecha_creacion` | `DATETIME` | `NOT NULL DEFAULT CURRENT_TIMESTAMP` |
| `id_usuario` | `INT` | `NOT NULL` · `FK → usuarios(id_usuario)` ON DELETE CASCADE |
| `id_categoria` | `INT` | `NOT NULL` · `FK → categorias(id_categoria)` ON DELETE RESTRICT |

---

### 5. `solicitudes`
Implementa el flujo de **solicitud personalizada de servicios educativos** (solicitud docente), accesible mediante `views/form-solicitar-servicio.php` y `views/solicitud-impresion-3d.php`. La lógica backend (`php/solicitudes/`) está planificada para la siguiente entrega.

| Columna | Tipo | Restricciones |
|:---|:---|:---|
| `id_solicitud` | `INT AUTO_INCREMENT` | `PRIMARY KEY` |
| `titulo` | `VARCHAR(200)` | `NOT NULL` |
| `descripcion` | `TEXT` | `NOT NULL` |
| `estado` | `ENUM('Pendiente','Aceptada','Rechazada','Cancelada')` | `NOT NULL DEFAULT 'Pendiente'` |
| `fecha_solicitud` | `DATETIME` | `NOT NULL DEFAULT CURRENT_TIMESTAMP` |
| `id_usuario` | `INT` | `NOT NULL` · `FK → usuarios(id_usuario)` ON DELETE CASCADE |
| `id_publicacion` | `INT` | `NULL` · `FK → publicaciones(id_publicacion)` ON DELETE SET NULL |

---

### 6. `contrataciones`
| Columna | Tipo | Restricciones |
|:---|:---|:---|
| `id_contratacion` | `INT AUTO_INCREMENT` | `PRIMARY KEY` |
| `fecha_contratacion` | `DATETIME` | `NOT NULL DEFAULT CURRENT_TIMESTAMP` |
| `monto_total` | `DECIMAL(10,2)` | `NOT NULL` |
| `estado` | `ENUM('Pendiente','En Proceso','Completada','Cancelada')` | `NOT NULL DEFAULT 'Pendiente'` |
| `id_usuario` | `INT` | `NOT NULL` · `FK → usuarios(id_usuario)` ON DELETE CASCADE |

---

### 7. `detalles_contratacion` *(tabla intermedia que resuelve relación N:M)*
| Columna | Tipo | Restricciones |
|:---|:---|:---|
| `id_detalle` | `INT AUTO_INCREMENT` | `PRIMARY KEY` |
| `cantidad` | `INT` | `NOT NULL DEFAULT 1` |
| `precio_unitario` | `DECIMAL(10,2)` | `NOT NULL` |
| `subtotal` | `DECIMAL(10,2)` | `NOT NULL` |
| `id_contratacion` | `INT` | `NOT NULL` · `FK → contrataciones(id_contratacion)` ON DELETE CASCADE |
| `id_publicacion` | `INT` | `NOT NULL` · `FK → publicaciones(id_publicacion)` ON DELETE RESTRICT |

---

### 8. `pagos`
| Columna | Tipo | Restricciones |
|:---|:---|:---|
| `id_pago` | `INT AUTO_INCREMENT` | `PRIMARY KEY` |
| `monto` | `DECIMAL(10,2)` | `NOT NULL` |
| `metodo_pago` | `ENUM('Tarjeta','Transferencia','MercadoPago','Efectivo')` | `NOT NULL` |
| `estado_pago` | `ENUM('Pendiente','Aprobado','Rechazado')` | `NOT NULL DEFAULT 'Pendiente'` |
| `fecha_pago` | `DATETIME` | `NOT NULL DEFAULT CURRENT_TIMESTAMP` |
| `transaccion_ref` | `VARCHAR(100)` | `NULL` |
| `id_contratacion` | `INT` | `NOT NULL` · `FK → contrataciones(id_contratacion)` ON DELETE CASCADE |

---

### 9. `valoraciones`
| Columna | Tipo | Restricciones |
|:---|:---|:---|
| `id_valoracion` | `INT AUTO_INCREMENT` | `PRIMARY KEY` |
| `puntuacion` | `INT` | `NOT NULL CHECK (puntuacion BETWEEN 1 AND 5)` |
| `comentario` | `TEXT` | `NULL` |
| `fecha_valoracion` | `DATETIME` | `NOT NULL DEFAULT CURRENT_TIMESTAMP` |
| `id_usuario` | `INT` | `NOT NULL` · `FK → usuarios(id_usuario)` ON DELETE CASCADE |
| `id_publicacion` | `INT` | `NOT NULL` · `FK → publicaciones(id_publicacion)` ON DELETE CASCADE |
| `id_contratacion` | `INT` | `NULL` · `FK → contrataciones(id_contratacion)` ON DELETE SET NULL |

---

## Resolución de Relaciones N:M

La relación entre **`contrataciones`** y **`publicaciones`** es de tipo **Muchos a Muchos (N:M)**, ya que una contratación puede incluir múltiples publicaciones y una publicación puede estar presente en múltiples contrataciones.

**Solución implementada:** Tabla intermedia **`detalles_contratacion`** que descompone la relación N:M en dos relaciones 1:N:
1. `contrataciones` (1) → (N) `detalles_contratacion`
2. `publicaciones` (1) → (N) `detalles_contratacion`

---

## Roles y Permisos del Sistema

El sistema implementa **Control de Acceso Basado en Roles (RBAC)** mediante la tabla `roles` y el campo `id_rol` en `usuarios`.

| Rol | `id_rol` | Permisos principales |
|:---|:---:|:---|
| **Cliente / Estudiante** | 1 | Explorar catálogo, contratar servicios, enviar solicitudes, emitir valoraciones |
| **Docente / Proveedor** | 2 | Crear/editar/gestionar publicaciones, ver solicitudes recibidas, consultar contrataciones |
| **Administrador** | 3 | Gestión global de usuarios, moderación de publicaciones, administración de categorías |

La sesión PHP almacena `id_rol` para control de acceso en vistas y controladores:

```php
// php/auth/session.php
$_SESSION["usuario"] = [
    "id_usuario" => $id,
    "nombre"     => $nombre,
    "email"      => $email,
    "id_rol"     => $id_rol, // 1=Cliente, 2=Docente, 3=Admin
];
```

---

## Verificación de Normalización (Tercera Forma Normal — 3FN)

1. **1FN:** Todos los atributos contienen valores atómicos. Cada tabla posee una clave primaria bien definida. Los valores multi-estado se expresan como `ENUM`.
2. **2FN:** Todos los atributos no clave dependen funcionalmente de la totalidad de la clave primaria.
3. **3FN:** No existen dependencias transitivas entre atributos no clave. Todos los campos no clave dependen directamente únicamente de la clave primaria.

---

## Archivo SQL Físico

El script DDL listo para ejecución en MySQL/MariaDB se encuentra en:
👉 [`sql/schema.sql`](../sql/schema.sql)

Para importarlo:
```bash
mysql -u root -p classia_db < sql/schema.sql
```