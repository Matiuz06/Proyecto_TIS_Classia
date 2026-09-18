# Diagramas de Secuencia — Classia · Segunda Entrega (Sprint 3)

Este documento contiene los diagramas de secuencia representativos de los principales flujos del sistema en el **Sprint 3**.

---

## 1. Flujo de Autenticación y Verificación en Dos Pasos (2FA)

![Secuencia Login y 2FA](diagramas/secuencia_autenticacion_2fa.png)

*Versión vectorial:* [secuencia_autenticacion_2fa.svg](diagramas/secuencia_autenticacion_2fa.svg)

```plantuml
@startuml secuencia_autenticacion_2fa
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

title Diagrama de Secuencia: Inicio de Sesión y Verificación 2FA

autonumber
actor "Usuario" as User
boundary "Vista: login.php" as ViewLogin
boundary "Vista: verificar-2fa.php" as View2FA
control "php/auth/procesar_login.php" as C_Login
control "php/auth/totp_helper.php" as C_TOTP
entity "Base de Datos (MySQL)" as DB

User -> ViewLogin: Ingresa email y contraseña
ViewLogin -> C_Login: POST (email, password)
activate C_Login

C_Login -> DB: SELECT * FROM usuarios WHERE email = :email
DB --> C_Login: Registro de usuario

alt Credenciales inválidas
  C_Login --> ViewLogin: Redirige con error ("Credenciales incorrectas")
  ViewLogin --> User: Muestra banner de error
else Credenciales válidas
  C_Login -> C_Login: password_verify(password, hash)
  
  alt 2FA está deshabilitado
    C_Login -> C_Login: session_regenerate_id() & inicializar sesión
    C_Login --> ViewLogin: Redirige a catálogo / panel
    ViewLogin --> User: Acceso concedido
  else 2FA activo
    C_Login -> C_Login: Guardar 2FA_PENDING en sesión
    C_Login --> View2FA: Redirige a verificación 2FA
    deactivate C_Login
    
    User -> View2FA: Ingresa código 6 dígitos
    View2FA -> C_TOTP: POST verificar código TOTP
    activate C_TOTP
    C_TOTP -> C_TOTP: Validar TOTP(secreto, codigo)
    
    alt Código correcto
      C_TOTP -> C_TOTP: session_regenerate_id() & autenticar
      C_TOTP --> User: Redirige al panel correspondiente
    else Código incorrecto
      C_TOTP --> View2FA: Retorna error de token inválido
      deactivate C_TOTP
    end
  end
end
@enduml

```

---

## 2. Flujo de Creación de Publicación y Exclusividad de Categoría

![Secuencia Crear Publicación](diagramas/secuencia_crear_publicacion.png)

*Versión vectorial:* [secuencia_crear_publicacion.svg](diagramas/secuencia_crear_publicacion.svg)

```plantuml
@startuml secuencia_crear_publicacion
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

title Diagrama de Secuencia: Crear Publicación y Exclusividad de Categoría

autonumber
actor "Docente / Proveedor" as Docente
boundary "Vista: crear-publicacion.php" as View
control "JS: sincronizarCategorias()" as JS
control "php/publicaciones/crear_publicacion.php" as C_Pub
control "php/utils/upload_helper.php" as Uploader
entity "Base de Datos (MySQL)" as DB

Docente -> View: Abre formulario de publicación
View -> JS: Inicializa sincronización

alt Docente selecciona categoría existente
  Docente -> View: Selecciona "Programación y Desarrollo"
  View -> JS: Evento change (id_categoria != '')
  JS -> View: Deshabilita campos de "Nueva categoría" y limpia texto
else Docente desea crear nueva categoría
  Docente -> View: Deja "-- Selecciona --" (id_categoria == '')
  JS -> View: Habilita input "nueva_categoria" y "descripcion_categoria"
  Docente -> View: Escribe "Computación Cuántica"
end

Docente -> View: Completa título, descripción, precio, portada
Docente -> View: Clic en "Guardar publicación"
View -> C_Pub: POST (datos del formulario, archivo portada)
activate C_Pub

C_Pub -> C_Pub: validar_datos_publicacion(POST)
alt Categoría existente seleccionada Y texto de nueva categoría presente
  C_Pub --> View: Error ("Para crear nueva categoría deselecciona la existente")
else Datos válidos
  opt Sube imagen de portada
    C_Pub -> Uploader: guardar_imagen_subida(FILE, 'publicaciones')
    activate Uploader
    Uploader -> Uploader: Validar MIME y crear carpeta con permisos 0777
    Uploader --> C_Pub: Ruta relativa segura
    deactivate Uploader
  end

  C_Pub -> DB: INSERT INTO categorias (si era nueva)
  C_Pub -> DB: INSERT INTO publicaciones (...)
  DB --> C_Pub: id_publicacion creado
  C_Pub --> View: Redirige con mensaje de éxito a panel-proveedor.php
  deactivate C_Pub
  View --> Docente: Muestra publicación activa en el catálogo
end
@enduml

```

---

## 3. Flujo de Solicitud de Servicio a Medida y Negociación

![Secuencia Solicitud Servicio](diagramas/secuencia_solicitud_servicio.png)

*Versión vectorial:* [secuencia_solicitud_servicio.svg](diagramas/secuencia_solicitud_servicio.svg)

```plantuml
@startuml secuencia_solicitud_servicio
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

title Diagrama de Secuencia: Solicitud de Servicio a Medida y Chat

autonumber
actor "Estudiante / Cliente" as Cliente
actor "Docente / Proveedor" as Docente
boundary "views/form-solicitar-servicio.php" as ViewForm
boundary "views/solicitud-servicio-detalle.php" as ViewChat
control "php/solicitudes/crear_solicitud.php" as C_Sol
control "php/solicitudes/mensajes_solicitud.php" as C_Msg
entity "Base de Datos (MySQL)" as DB

Cliente -> ViewForm: Completa formulario de requerimiento (título, descripción, presupuesto, adjunto)
ViewForm -> C_Sol: POST /crear_solicitud.php
activate C_Sol
C_Sol -> DB: INSERT INTO solicitudes (estado='Pendiente', ...)
DB --> C_Sol: id_solicitud
C_Sol --> ViewForm: Redirige a "Mis Solicitudes" con confirmación
deactivate C_Sol

Docente -> ViewChat: Accede al detalle de la solicitud recibida
Docente -> DB: Consulta datos de solicitud y mensajes
DB --> ViewChat: Carga historial de conversación

Docente -> ViewChat: Escribe contraoferta / mensaje de aclaración
ViewChat -> C_Msg: POST enviar mensaje
activate C_Msg
C_Msg -> DB: INSERT INTO solicitud_mensajes (...)
C_Msg -> DB: UPDATE solicitudes SET estado='Contraoferta'
DB --> C_Msg: OK
C_Msg --> ViewChat: Actualiza hilo de chat en tiempo real
deactivate C_Msg

Cliente -> ViewChat: Lee propuesta y acepta términos
ViewChat -> C_Sol: POST aceptar solicitud
activate C_Sol
C_Sol -> DB: UPDATE solicitudes SET estado='Aceptada'
C_Sol -> DB: INSERT INTO contrataciones (...)
deactivate C_Sol
ViewChat --> Cliente: Redirige a pasarela de pago para formalizar
@enduml

```

---

## 4. Flujo de Gestión de Contenido de Cursos y Supabase Storage

![Secuencia Gestión Cursos](diagramas/secuencia_gestion_curso_supabase.png)

*Versión vectorial:* [secuencia_gestion_curso_supabase.svg](diagramas/secuencia_gestion_curso_supabase.svg)

```plantuml
@startuml secuencia_gestion_curso_supabase
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

title Diagrama de Secuencia: Gestión Modular de Cursos y Supabase Storage

autonumber
actor "Docente" as Docente
boundary "views/gestionar-contenido-curso.php" as ViewCurso
control "php/publicaciones/contenido_curso.php" as C_Curso
control "php/utils/supabase_storage.php" as C_Supa
entity "Base de Datos (MySQL)" as DB
database "Supabase Storage (Cloud)" as SupaCloud

Docente -> ViewCurso: Crea Módulo ("Módulo 1: POO")
ViewCurso -> C_Curso: POST accion='crear_modulo'
activate C_Curso
C_Curso -> DB: INSERT INTO curso_modulos (...)
DB --> C_Curso: id_modulo
C_Curso --> ViewCurso: Renderiza módulo en acordeón
deactivate C_Curso

Docente -> ViewCurso: Agrega Unidad ("Unidad 1.1: Clases y Objetos")
ViewCurso -> C_Curso: POST accion='crear_unidad'
activate C_Curso
C_Curso -> DB: INSERT INTO curso_unidades (...)
DB --> C_Curso: id_unidad
C_Curso --> ViewCurso: Unidad disponible en módulo
deactivate C_Curso

Docente -> ViewCurso: Sube recurso PDF/Video ("guia_poo.pdf")
ViewCurso -> C_Curso: POST accion='crear_recurso' (archivo_recurso)
activate C_Curso
C_Curso -> C_Supa: supabase_subir_archivo(tmp_file, 'cursos/guia_poo.pdf')
activate C_Supa
C_Supa -> SupaCloud: PUT /storage/v1/object/recursos-cursos/...
SupaCloud --> C_Supa: 200 OK (Public/Signed Path)
C_Supa --> C_Curso: 'supabase:cursos/guia_poo.pdf'
deactivate C_Supa

C_Curso -> DB: INSERT INTO curso_recursos (tipo='PDF', archivo='supabase:...')
DB --> C_Curso: id_recurso registrado
C_Curso --> ViewCurso: Recurso visible con enlace protegido
deactivate C_Curso
@enduml

```
