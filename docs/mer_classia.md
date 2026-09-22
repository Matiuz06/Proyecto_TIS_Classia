# Modelo Entidad-Relación (MER) — Classia · Segunda Entrega (Sprint 3)

## Resumen del Modelo
Este documento especifica el **Modelo Entidad-Relación (MER) actualizado** para la plataforma **Classia**, reflejando el estado real del sistema al cierre del **Sprint 3 (Segunda Entrega Funcional y Técnica)**. La nomenclatura de atributos coincide exactamente con el esquema físico definido en [`sql/schema.sql`](../sql/schema.sql) (convención `snake_case` de MariaDB/MySQL).

> **Última actualización:** Segunda entrega funcional y técnica (Sprint 3)  
> **Coherencia validada contra:** `sql/schema.sql`

---

## Diagrama Entidad-Relación (PlantUML / draw.io)

![Diagrama MER Classia](diagramas/mer_classia.png)

*Versión vectorial:* [mer_classia.svg](diagramas/mer_classia.svg) | *Código fuente:* [mer_classia.puml](diagramas/mer_classia.puml)

### Código Fuente PlantUML
```plantuml
@startuml mer_classia
!theme plain

skinparam backgroundColor #FFFFFF
skinparam shadowing true
skinparam roundcorner 8
skinparam defaultFontName 'Segoe UI', 'Helvetica', 'Arial', sans-serif
skinparam defaultFontSize 12
skinparam defaultFontColor #2D3748

skinparam class {
    BackgroundColor #FFFFFF
    ArrowColor #3182CE
    BorderColor #2B6CB0
    HeaderBackgroundColor #EBF8FF
}
skinparam entity {
    BackgroundColor #FFFFFF
    BorderColor #2B6CB0
}
skinparam sequence {
    ActorBorderColor #2B6CB0
    ActorBackgroundColor #EBF8FF
    ParticipantBorderColor #2B6CB0
    ParticipantBackgroundColor #EBF8FF
    LifeLineBorderColor #4A5568
    LifeLineBackgroundColor #EDF2F7
    ArrowColor #2B6CB0
}
skinparam usecase {
    BackgroundColor #FFFFFF
    BorderColor #2B6CB0
    ArrowColor #3182CE
    ActorBorderColor #2B6CB0
    ActorBackgroundColor #EBF8FF
}

title Modelo Entidad-Relación (MER) — Classia (Sprint 3)

entity "ROL" as roles {
  * id_rol : INT <<PK>>
  --
  nombre_rol : VARCHAR(50)
  descripcion : VARCHAR(255)
}

entity "USUARIO" as usuarios {
  * id_usuario : INT <<PK>>
  --
  nombre : VARCHAR(100)
  apellido : VARCHAR(100)
  email : VARCHAR(150)
  password_hash : VARCHAR(255)
  telefono : VARCHAR(30)
  fecha_registro : DATETIME
  * id_rol : INT <<FK>>
  foto_perfil : VARCHAR(255)
  dos_factores_activo : TINYINT(1)
  activo : TINYINT(1)
}

entity "PERFIL_PROFESIONAL" as perfiles {
  * id_perfil : INT <<PK>>
  --
  * id_usuario : INT <<FK, UNIQUE>>
  titulo_profesional : VARCHAR(180)
  presentacion : TEXT
  experiencia : TEXT
  formacion : TEXT
  habilidades : TEXT
  especialidades : TEXT
  ubicacion : VARCHAR(150)
  modalidad_trabajo : VARCHAR(150)
}

entity "CATEGORIA" as categorias {
  * id_categoria : INT <<PK>>
  --
  nombre_categoria : VARCHAR(100)
  descripcion : TEXT
  creada_por : INT <<FK>>
}

entity "PUBLICACION" as publicaciones {
  * id_publicacion : INT <<PK>>
  --
  titulo : VARCHAR(200)
  descripcion : TEXT
  precio : DECIMAL(10,2)
  tipo : ENUM('Curso', 'Servicio')
  modalidad : VARCHAR(30)
  duracion_horas : SMALLINT
  cupos : INT
  estado : ENUM('Activo', 'Inactivo', 'Pausado', 'Eliminado')
  imagen : VARCHAR(255)
  * id_usuario : INT <<FK>>
  * id_categoria : INT <<FK>>
}

entity "CURSO_MODULO" as modulos {
  * id_modulo : INT <<PK>>
  --
  * id_publicacion : INT <<FK>>
  titulo : VARCHAR(180)
  descripcion : TEXT
  orden : INT
}

entity "CURSO_UNIDAD" as unidades {
  * id_unidad : INT <<PK>>
  --
  * id_modulo : INT <<FK>>
  titulo : VARCHAR(180)
  descripcion : TEXT
  orden : INT
}

entity "CURSO_RECURSO" as recursos {
  * id_recurso : INT <<PK>>
  --
  * id_unidad : INT <<FK>>
  titulo : VARCHAR(180)
  tipo : ENUM('Archivo', 'PDF', 'Imagen', 'Video', 'Enlace')
  url : VARCHAR(500)
  archivo : VARCHAR(255)
  orden : INT
}

entity "SOLICITUD" as solicitudes {
  * id_solicitud : INT <<PK>>
  --
  titulo : VARCHAR(200)
  descripcion : TEXT
  estado : ENUM('Pendiente', 'Aceptada', 'Rechazada', 'Contraoferta', 'En Proceso', 'Realizada', 'Cancelada')
  precio_propuesto : DECIMAL(10,2)
  archivo_adjunto : VARCHAR(255)
  * id_usuario : INT <<FK>>
  id_publicacion : INT <<FK>>
}

entity "SOLICITUD_MENSAJE" as mensajes {
  * id_mensaje : INT <<PK>>
  --
  * id_solicitud : INT <<FK>>
  * id_usuario : INT <<FK>>
  mensaje : TEXT
  fecha_mensaje : DATETIME
}

entity "SOLICITUD_DOCENTE" as sol_doc {
  * id_solicitud_docente : INT <<PK>>
  --
  * id_usuario : INT <<FK>>
  estado : ENUM('Pendiente', 'Aprobada', 'Rechazada')
  motivo : TEXT
  fecha_solicitud : DATETIME
}

entity "CONTRATACION" as contrataciones {
  * id_contratacion : INT <<PK>>
  --
  fecha_contratacion : DATETIME
  monto_total : DECIMAL(10,2)
  estado : ENUM('Pendiente', 'En Proceso', 'Completada', 'Cancelada')
  * id_usuario : INT <<FK>>
}

entity "DETALLE_CONTRATACION" as detalles {
  * id_detalle : INT <<PK>>
  --
  cantidad : INT
  precio_unitario : DECIMAL(10,2)
  subtotal : DECIMAL(10,2)
  * id_contratacion : INT <<FK>>
  * id_publicacion : INT <<FK>>
}

entity "PAGO" as pagos {
  * id_pago : INT <<PK>>
  --
  monto : DECIMAL(10,2)
  metodo_pago : ENUM('Tarjeta', 'Transferencia', 'MercadoPago', 'Efectivo')
  estado_pago : ENUM('Pendiente', 'Aprobado', 'Rechazado')
  transaccion_ref : VARCHAR(100)
  * id_contratacion : INT <<FK>>
}

entity "VALORACION" as valoraciones {
  * id_valoracion : INT <<PK>>
  --
  puntuacion : INT
  comentario : TEXT
  * id_usuario : INT <<FK>>
  * id_publicacion : INT <<FK>>
  id_contratacion : INT <<FK>>
}

roles ||--o{ usuarios : "asigna rol"
usuarios ||--o| perfiles : "perfil profesional"
usuarios ||--o{ sol_doc : "solicita rol docente"
usuarios ||--o{ categorias : "crea categoría"
usuarios ||--o{ publicaciones : "publica"
categorias ||--o{ publicaciones : "clasifica"

publicaciones ||--o{ modulos : "contiene"
modulos ||--o{ unidades : "organiza"
unidades ||--o{ recursos : "incluye"

usuarios ||--o{ solicitudes : "envía solicitud"
publicaciones ||--o{ solicitudes : "referencia oferta"
solicitudes ||--o{ mensajes : "intercambia"
usuarios ||--o{ mensajes : "escribe"

usuarios ||--o{ contrataciones : "realiza orden"
contrataciones ||--|{ detalles : "desglosa"
publicaciones ||--o{ detalles : "ítem contratado"
contrataciones ||--o{ pagos : "abona"

usuarios ||--o{ valoraciones : "emite opinión"
publicaciones ||--o{ valoraciones : "recibe calificación"
contrataciones ||--o{ valoraciones : "respalda"
@enduml

```

---

## Diccionario de Entidades y Atributos

### 1. Entidad: ROL
Representa los perfiles de usuario permitidos en la plataforma (`roles`).
- `id_rol` (INT, PK, AUTO_INCREMENT)
- `nombre_rol` (VARCHAR(50), NOT NULL UNIQUE)
- `descripcion` (VARCHAR(255), NULL)

### 2. Entidad: USUARIO
Almacena las cuentas de usuario de la plataforma (`usuarios`).
- `id_usuario` (INT, PK, AUTO_INCREMENT)
- `nombre`, `apellido` (VARCHAR(100), NOT NULL)
- `email` (VARCHAR(150), NOT NULL UNIQUE)
- `password_hash` (VARCHAR(255), NOT NULL)
- `telefono` (VARCHAR(30), NULL)
- `id_rol` (INT, FK -> roles)
- `dos_factores_activo` (TINYINT(1), NOT NULL DEFAULT 0)
- `activo` (TINYINT(1), NOT NULL DEFAULT 1)

### 3. Entidad: PERFIL_PROFESIONAL
Perfil extendido para docentes y prestadores (`perfiles_profesionales`).
- `id_perfil` (INT, PK, AUTO_INCREMENT)
- `id_usuario` (INT, FK UNIQUE -> usuarios)
- `titulo_profesional` (VARCHAR(180))
- `presentacion`, `experiencia`, `formacion`, `certificaciones`, `habilidades`, `especialidades` (TEXT)
- `ubicacion`, `modalidad_trabajo` (VARCHAR(150))
- `portfolio_url`, `linkedin_url` (VARCHAR(255))

### 4. Entidad: CATEGORIA
Agrupa las áreas de cursos y servicios (`categorias`).
- `id_categoria` (INT, PK, AUTO_INCREMENT)
- `nombre_categoria` (VARCHAR(100), NOT NULL UNIQUE)
- `descripcion` (TEXT, NULL)
- `creada_por` (INT, FK -> usuarios)

### 5. Entidad: PUBLICACION
Oferta formativa o servicio a medida (`publicaciones`).
- `id_publicacion` (INT, PK, AUTO_INCREMENT)
- `titulo` (VARCHAR(200), NOT NULL)
- `descripcion` (TEXT, NOT NULL)
- `precio` (DECIMAL(10,2), NOT NULL)
- `tipo` (ENUM('Curso','Servicio'), NOT NULL)
- `modalidad` (VARCHAR(30))
- `duracion_horas` (SMALLINT)
- `cupos` (INT)
- `estado` (ENUM('Activo','Inactivo','Pausado','Eliminado'), NOT NULL DEFAULT 'Activo')
- `imagen` (VARCHAR(255))
- `id_usuario` (INT, FK -> usuarios)
- `id_categoria` (INT, FK -> categorias)

### 6. Entidades de Estructura de Curso: CURSO_MODULO, CURSO_UNIDAD, CURSO_RECURSO
Organización modular de lecciones y recursos con soporte Supabase Cloud (`curso_modulos`, `curso_unidades`, `curso_recursos`).

### 7. Entidades de Servicios y Negociación: SOLICITUD, SOLICITUD_MENSAJE, SOLICITUD_DOCENTE
Flujo interactivo de presupuestos y postulación docente (`solicitudes`, `solicitud_mensajes`, `solicitudes_docente`).

### 8. Entidades Comerciales: CONTRATACION, DETALLE_CONTRATACION, PAGO, VALORACION
Gestión de compras, pasarela de pago y opiniones con puntuación 1-5 (`contrataciones`, `detalles_contratacion`, `pagos`, `valoraciones`).
