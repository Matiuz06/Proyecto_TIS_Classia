# Diagramas de Casos de Uso — Classia · Segunda Entrega (Sprint 3)

## 1. Diagrama General del Sistema

![Casos de Uso General](diagramas/casos_de_uso_general.png)

*Versión vectorial:* [casos_de_uso_general.svg](diagramas/casos_de_uso_general.svg) | *Código fuente:* [casos_de_uso_general.puml](diagramas/casos_de_uso_general.puml)

```plantuml
@startuml casos_de_uso_general
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

title Diagrama General de Casos de Uso — Classia (Sprint 3)

left to right direction

actor "Visitante" as Visitante
actor "Estudiante / Cliente" as Estudiante
actor "Docente / Proveedor" as Docente
actor "Administrador" as Admin

rectangle "Sistema Classia" {
  usecase "Registrarse en Plataforma" as UC_Reg
  usecase "Iniciar Sesión" as UC_Login
  usecase "Explorar Catálogo y Filtros" as UC_Cat
  usecase "Ver Detalle de Oferta" as UC_Det
  usecase "Solicitar Servicio a Medida" as UC_Sol
  usecase "Contratar y Pagar" as UC_Pay
  usecase "Calificar con Reseña" as UC_Val
  usecase "Publicar y Editar Cursos/Servicios" as UC_Pub
  usecase "Gestionar Contenidos y Supabase" as UC_Cont
  usecase "Responder Solicitudes y Chat" as UC_RespSol
  usecase "Gestionar Perfil Profesional" as UC_Perf
  usecase "Supervisar Usuarios y Métricas" as UC_Adm
  usecase "Aprobar Solicitudes Docentes" as UC_ApprDoc
}

Visitante --> UC_Reg
Visitante --> UC_Login
Visitante --> UC_Cat
Visitante --> UC_Det

Estudiante --|> Visitante
Estudiante --> UC_Sol
Estudiante --> UC_Pay
Estudiante --> UC_Val

Docente --|> Visitante
Docente --> UC_Pub
Docente --> UC_Cont
Docente --> UC_RespSol
Docente --> UC_Perf

Admin --|> Visitante
Admin --> UC_Adm
Admin --> UC_ApprDoc
@enduml

```

---

## 2. Casos de Uso por Rol

### A. Perfil Estudiante / Cliente
![Casos de Uso Estudiante](diagramas/casos_de_uso_estudiante.png)
*Versión vectorial:* [casos_de_uso_estudiante.svg](diagramas/casos_de_uso_estudiante.svg)

### B. Perfil Docente / Proveedor
![Casos de Uso Docente](diagramas/casos_de_uso_docente.png)
*Versión vectorial:* [casos_de_uso_docente.svg](diagramas/casos_de_uso_docente.svg)

### C. Perfil Administrador
![Casos de Uso Administrador](diagramas/casos_de_uso_admin.png)
*Versión vectorial:* [casos_de_uso_admin.svg](diagramas/casos_de_uso_admin.svg)
