# Estándar de Estructura y Convenciones de Nomenclatura — Classia · Segunda Entrega

Este documento establece las convenciones de organización de archivos, estructura de carpetas y estándares de nombrado para el desarrollo de la plataforma **Classia**.

> **Última actualización:** Segunda entrega funcional y técnica (Sprint 2)

---

## 1. Estructura General del Proyecto

```text
Proyecto_TIS_Classia/
├── index.php                        # Punto de entrada principal
├── assets/                          # Recursos multimedia (imágenes, favicons, logotipos)
│   └── images/                      # Archivos de imagen normalizados (kebab-case)
├── config/                          # Archivos de configuración global y PDO
│   └── database.php                 # Conexión centralizada PDO + variables de entorno
├── css/                             # Estilos CSS
│   ├── style.css                    # Hoja de estilos principal (Design Tokens, responsive)
│   └── animation.css                # Microinteracciones y keyframes
├── docs/                            # Documentación técnica, diagramas UML y modelos
│   ├── estructura_php.md            # Arquitectura backend y sesiones
│   ├── estilo_y_nomenclatura.md     # Convenciones oficiales de nombrado y estructura
│   ├── mer_classia.md               # Modelo Entidad-Relación formal
│   ├── modelo_relacional_classia.md # Modelo relacional normalizado
│   ├── diagrama_de_clases_classia.md# Diagrama de clases de dominio UML
│   ├── casos_de_uso_classia.md      # Especificación y diagrama de casos de uso
│   ├── diagramas_de_secuencia_classia.md # Diagramas de secuencia de flujos
│   └── pruebas_classia.md           # Registro de pruebas funcionales realizadas
├── includes/                        # Componentes comunes PHP
│   ├── header.php                   # Encabezado modular con navegación y sesión
│   └── footer.php                   # Pie de página institucional modular
├── js/                              # Scripts JavaScript del cliente
│   └── script.js                    # Interacciones dinámicas y validaciones
├── php/                             # Lógica de negocio backend organizada por módulo (snake_case)
│   ├── auth/                        # ✅ Sesiones y autenticación
│   │   ├── session.php              # Helper de funciones de sesión seguras
│   │   └── logout.php               # Cierre seguro de sesión
│   ├── publicaciones/               # ✅ CRUD de publicaciones
│   │   ├── crear_publicacion.php    # Alta de publicaciones (INSERT PDO + CSRF)
│   │   ├── editar_publicacion.php   # Edición y cambio de estado (UPDATE PDO + CSRF)
│   │   └── obtener_publicaciones.php# Helper SELECT para vistas dinámicas
│   ├── usuarios/                    # ✅ Procesamiento de usuarios
│   │   └── registro.php             # Registro con validaciones, unicidad, bcrypt
│   ├── solicitudes/                 # 🔄 En desarrollo — lógica de solicitudes personalizadas
│   ├── contrataciones/              # 🔄 En desarrollo — lógica de órdenes de compra
│   ├── pagos/                       # 🔄 En desarrollo — simulación y registro de pagos
│   └── valoraciones/                # 🔄 En desarrollo — reseñas y cálculo de promedios
├── scripts/                         # Scripts de utilidad CLI y diagnósticos de seguridad
├── sql/                             # Scripts DDL de base de datos
│   └── schema.sql                   # Esquema relacional completo (9 tablas, 3FN, seeds)
└── views/                           # Vistas y páginas accesibles al usuario (kebab-case .php)
    ├── login.php
    ├── registro.php
    ├── panel-proveedor.php
    ├── panel-administrador.php
    ├── crear-publicacion.php
    ├── editar-publicacion.php
    ├── catalogo.php
    ├── curso.php
    ├── servicio-detalle.php
    ├── form-solicitar-servicio.php
    ├── solicitud-impresion-3d.php
    ├── carrito.php
    ├── pasarela-pago.php
    ├── confirmacion.php
    ├── usuario.php
    ├── valoracion.php
    ├── cambiar-contrasena.php
    ├── restablecer-contrasena.php
    ├── primeros-pasos.php
    └── politica-privacidad.php
```

---

## 2. Guía de Nomenclatura

| Tipo de Recurso | Convención | Regla / Explicación | Ejemplo |
| :--- | :--- | :--- | :--- |
| **Directorios** | `kebab-case` | Minúsculas separadas por guiones medios. | `assets/images/`, `php/auth/`, `views/` |
| **Vistas / Páginas web** | `kebab-case` | Archivos `.php` en `views/` con nombres descriptivos sin espacios ni mayúsculas. | `panel-proveedor.php`, `crear-publicacion.php`, `form-solicitar-servicio.php` |
| **Controladores PHP** | `snake_case` | Archivos backend en `php/*/` que procesan lógica. | `crear_publicacion.php`, `obtener_publicaciones.php` |
| **Imágenes / Iconos** | `kebab-case` | Guardadas en `assets/images/` sin caracteres especiales. | `anitech-logo-negativo.png`, `logo-classia.png` |
| **Scripts CLI / Utility** | `kebab-case` | Herramientas de consola o shell en `scripts/`. | `check-security-headers.sh` |
| **Variables / Funciones PHP** | `snake_case` | Minúsculas separadas por guiones bajos. | `$id_usuario`, `requerir_autenticacion()` |
| **Tablas SQL** | `snake_case` plural | Tablas en plural, columnas en singular. | `publicaciones`, `detalles_contratacion` |
| **Columnas / Claves SQL** | `snake_case` | Formato `id_entidad` para PKs y FKs. | `id_usuario`, `id_publicacion`, `password_hash` |
| **Clases PHP** | `PascalCase` | Solo si se implementan clases orientadas a objetos. | `DatabaseConnection`, `UsuarioModel` |

---

## 3. Principios de Rutas e Inclusiones

1. **Vistas (`views/*.php`)**:
   - Deben incluir componentes mediante rutas relativas o basadas en `__DIR__`:
     ```php
     include '../includes/header.php';
     require_once '../php/publicaciones/obtener_publicaciones.php';
     ```
   - Deben referenciar imágenes en `assets/images/`:
     ```html
     <img src="../assets/images/logo-classia.png" alt="Classia" />
     ```

2. **Controladores (`php/*/*.php`)**:
   - Deben referenciar configuración y helpers con rutas absolutas:
     ```php
     require_once __DIR__ . '/../../config/database.php';
     require_once __DIR__ . '/../auth/session.php';
     ```

3. **Formularios y Navegación**:
   - Las acciones de formulario usan la URL en `kebab-case`:
     ```html
     <form action="../php/publicaciones/crear_publicacion.php" method="POST">
     ```
   - Las redirecciones PHP usan el nombre estandarizado:
     ```php
     header("Location: ../views/panel-proveedor.php?mensaje=creada");
     ```
