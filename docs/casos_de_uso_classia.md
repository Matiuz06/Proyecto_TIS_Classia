# Diagrama de Casos de Uso General - Classia

## Resumen del Documento
Este documento especifica el **Diagrama de Casos de Uso General** para la plataforma **Classia**, representando de forma formal y visual cómo interactúan los distintos actores con el sistema según las funcionalidades del SRS y las interfaces HTML.

---

## Diagrama UML de Casos de Uso (Mermaid)

```mermaid
graph LR
    %% Estilos CSS para actores y casos de uso
    classDef actorStyle fill:#e1f5fe,stroke:#0288d1,stroke-width:2px,color:#01579b;
    classDef ucStyle fill:#ffffff,stroke:#37474f,stroke-width:1.5px,color:#263238;

    %% Actores del sistema (camelCase)
    subgraph actores["Actores del Sistema"]
        clienteEstudiante["Cliente / Estudiante"]
        docenteProveedor["Docente / Proveedor"]
        administrador["Administrador"]
    end

    %% Límite del Sistema Classia (camelCase)
    subgraph sistemaClassia["Sistema Classia"]
        
        %% Autenticación
        ucAutenticarse("(CU-01: Autenticarse / Iniciar Sesión)")
        ucRegistrarse("(CU-02: Registrarse en la Plataforma)")
        
        %% Cliente / Estudiante
        ucExplorarCatalogo("(CU-03: Explorar Catálogo de Cursos y Servicios)")
        ucVerDetalle("(CU-04: Ver Detalle de Publicación)")
        ucContratarServicio("(CU-05: Contratar Curso o Servicio)")
        ucRealizarPago("(CU-06: Realizar Pago)")
        ucEnviarSolicitud("(CU-07: Enviar Solicitud Personalizada)")
        ucEmitirValoracion("(CU-08: Emitir Valoración y Reseña)")

        %% Docente / Proveedor
        ucCrearPublicacion("(CU-09: Crear / Editar Publicación)")
        ucGestionarPublicaciones("(CU-10: Gestionar Mis Publicaciones)")
        ucResponderSolicitud("(CU-11: Responder Solicitudes Recibidas)")
        ucConsultarVentas("(CU-12: Consultar Ventas y Contrataciones)")

        %% Administrador
        ucGestionarUsuarios("(CU-13: Gestionar Usuarios y Roles)")
        ucModerarPublicaciones("(CU-14: Moderar Publicaciones)")
        ucAdministrarCategorias("(CU-15: Administrar Categorías)")
    end

    %% Interacciones Cliente / Estudiante
    clienteEstudiante --> ucRegistrarse
    clienteEstudiante --> ucAutenticarse
    clienteEstudiante --> ucExplorarCatalogo
    clienteEstudiante --> ucVerDetalle
    clienteEstudiante --> ucContratarServicio
    clienteEstudiante --> ucEnviarSolicitud
    clienteEstudiante --> ucEmitirValoracion

    %% Relaciones UML (include / extend)
    ucContratarServicio -.->|include| ucRealizarPago
    ucContratarServicio -.->|include| ucAutenticarse
    ucEmitirValoracion -.->|extend| ucVerDetalle

    %% Interacciones Docente / Proveedor
    docenteProveedor --> ucAutenticarse
    docenteProveedor --> ucCrearPublicacion
    docenteProveedor --> ucGestionarPublicaciones
    docenteProveedor --> ucResponderSolicitud
    docenteProveedor --> ucConsultarVentas
    
    ucCrearPublicacion -.->|include| ucAutenticarse

    %% Interacciones Administrador
    administrador --> ucAutenticarse
    administrador --> ucGestionarUsuarios
    administrador --> ucModerarPublicaciones
    administrador --> ucAdministrarCategorias

    ucGestionarUsuarios -.->|include| ucAutenticarse

    %% Aplicación de estilos visuales
    class clienteEstudiante,docenteProveedor,administrador actorStyle;
    class ucAutenticarse,ucRegistrarse,ucExplorarCatalogo,ucVerDetalle,ucContratarServicio,ucRealizarPago,ucEnviarSolicitud,ucEmitirValoracion,ucCrearPublicacion,ucGestionarPublicaciones,ucResponderSolicitud,ucConsultarVentas,ucGestionarUsuarios,ucModerarPublicaciones,ucAdministrarCategorias ucStyle;
```

---

## Especificación de Actores

| Actor | Tipo | Descripción |
| :--- | :--- | :--- |
| **Cliente / Estudiante** | Principal | Usuario que busca, explora, contrata cursos/servicios, realiza pagos y emite opiniones o solicitudes personalizadas. |
| **Docente / Proveedor** | Principal | Usuario capacitador o profesional que crea, oferta y gestiona cursos y servicios educativos, respondiendo a contrataciones y solicitudes. |
| **Administrador** | Principal | Usuario responsable de la gestión global del sistema, moderación de contenidos, administración de categorías y control de usuarios. |

---

## Matriz de Casos de Uso y Actores

| ID | Caso de Uso | Cliente / Estudiante | Docente / Proveedor | Administrador | Relaciones (`include` / `extend`) |
| :---: | :--- | :---: | :---: | :---: | :--- |
| **CU-01** | Autenticarse / Iniciar Sesión | X | X | X | Requerido por la mayoría de las acciones. |
| **CU-02** | Registrarse en la Plataforma | X | X | | Permite la creación inicial de cuenta. |
| **CU-03** | Explorar Catálogo | X | X | X | Acceso público/general. |
| **CU-04** | Ver Detalle de Publicación | X | X | X | Muestra información de cursos y servicios. |
| **CU-05** | Contratar Curso o Servicio | X | | | `include` -> **CU-06** (Realizar Pago) y **CU-01**. |
| **CU-06** | Realizar Pago | X | | | Ejecutado durante el proceso de contratación. |
| **CU-07** | Enviar Solicitud Personalizada | X | | | Permite pedir presupuestos/servicios a medida. |
| **CU-08** | Emitir Valoración y Reseña | X | | | `extend` -> **CU-04** (opcional tras contratar). |
| **CU-09** | Crear / Editar Publicación | | X | | `include` -> **CU-01**. Permite publicar ofertas. |
| **CU-10** | Gestionar Mis Publicaciones | | X | | Permite pausar, activar o editar cursos. |
| **CU-11** | Responder Solicitudes | | X | | Atención a pedidos a medida de estudiantes. |
| **CU-12** | Consultar Ventas | | X | | Historial de contrataciones recibidas. |
| **CU-13** | Gestionar Usuarios y Roles | | | X | Modificación de datos y asignación de roles. |
| **CU-14** | Moderar Publicaciones | | | X | Control de calidad y baja de contenidos. |
| **CU-15** | Administrar Categorías | | | X | Alta, baja y modificación de categorías. |
