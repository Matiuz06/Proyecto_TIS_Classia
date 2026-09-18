# Diagrama de Clases — Classia · Segunda Entrega (Sprint 3)

## Arquitectura de Clases y Servicios
El diagrama de clases modela la arquitectura de entidades, controladores y servicios auxiliares en PHP 8.

---

## Diagrama de Clases (PlantUML / draw.io)

![Diagrama de Clases](diagramas/diagrama_de_clases_classia.png)

*Versión vectorial:* [diagrama_de_clases_classia.svg](diagramas/diagrama_de_clases_classia.svg) | *Código fuente:* [diagrama_de_clases_classia.puml](diagramas/diagrama_de_clases_classia.puml)

### Código Fuente PlantUML
```plantuml
@startuml diagrama_de_clases_classia
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

title Diagrama de Clases del Sistema — Classia (Arquitectura PHP)

package "Modelos / Entidades" {
  class Usuario {
    - id_usuario: int
    - nombre: string
    - apellido: string
    - email: string
    - password_hash: string
    - id_rol: int
    - dos_factores_activo: bool
    + autenticar(password: string): bool
    + tieneRol(rol: string): bool
    + activar2FA(secreto: string): bool
  }

  class PerfilProfesional {
    - id_perfil: int
    - id_usuario: int
    - titulo_profesional: string
    - presentacion: string
    - habilidades: string
    + guardar(): bool
    + obtenerPorUsuario(id_usuario: int): PerfilProfesional
  }

  class Publicacion {
    - id_publicacion: int
    - titulo: string
    - descripcion: string
    - precio: float
    - tipo: string
    - estado: string
    - imagen: string
    + guardar(): bool
    + cambiarEstado(nuevo_estado: string): bool
    + obtenerModulos(): List<CursoModulo>
  }

  class Categoria {
    - id_categoria: int
    - nombre_categoria: string
    - descripcion: string
    + resolver(id: int, nombre: string): int
  }

  class CursoModulo {
    - id_modulo: int
    - id_publicacion: int
    - titulo: string
    - orden: int
    + agregarUnidad(u: CursoUnidad): bool
  }

  class CursoUnidad {
    - id_unidad: int
    - id_modulo: int
    - titulo: string
    - orden: int
    + agregarRecurso(r: CursoRecurso): bool
  }

  class CursoRecurso {
    - id_recurso: int
    - id_unidad: int
    - titulo: string
    - tipo: string
    - url: string
    - archivo: string
    + descargar(): Stream
  }

  class Solicitud {
    - id_solicitud: int
    - titulo: string
    - descripcion: string
    - estado: string
    - precio_propuesto: float
    + responder(nuevo_estado: string, respuesta: string): bool
    + enviarMensaje(id_usuario: int, texto: string): bool
  }

  class Contratacion {
    - id_contratacion: int
    - fecha: DateTime
    - monto_total: float
    - estado: string
    + registrarPago(pago: Pago): bool
  }

  class Pago {
    - id_pago: int
    - monto: float
    - metodo_pago: string
    - estado_pago: string
    - transaccion_ref: string
    + procesar(): bool
  }

  class Valoracion {
    - id_valoracion: int
    - puntuacion: int
    - comentario: string
    + registrar(): bool
  }
}

package "Servicios y Helpers" {
  class AuthHelper {
    + iniciarSesion(usuario: Usuario): void
    + cerrarSesion(): void
    + verificar2FA(codigo: string): bool
  }

  class UploadHelper {
    + guardarImagen(archivo: array, subcarpeta: string): array
    + eliminarImagen(ruta: string): bool
  }

  class SupabaseStorageService {
    + subirArchivo(rutaLocal: string, destino: string): array
    + obtenerUrlFirmada(destino: string): string
    + eliminarArchivo(destino: string): bool
  }
}

Usuario "1" *-- "0..1" PerfilProfesional
Usuario "1" o-- "0..*" Publicacion : publica
Categoria "1" o-- "0..*" Publicacion : clasifica
Publicacion "1" *-- "0..*" CursoModulo
CursoModulo "1" *-- "0..*" CursoUnidad
CursoUnidad "1" *-- "0..*" CursoRecurso

Usuario "1" o-- "0..*" Solicitud : emite
Publicacion "0..1" o-- "0..*" Solicitud : referencia
Usuario "1" o-- "0..*" Contratacion : compra
Contratacion "1" *-- "1..*" Publicacion : contiene
Contratacion "1" *-- "0..*" Pago : liquida
Usuario "1" o-- "0..*" Valoracion : emite
Publicacion "1" o-- "0..*" Valoracion : calificada

Publicacion ..> UploadHelper : usa
CursoRecurso ..> SupabaseStorageService : almacena
Usuario ..> AuthHelper : gestiona
@enduml

```
