# Estándar de Estructura y Convenciones de Nomenclatura — Classia

Este documento establece las convenciones de organización de archivos, estructura de carpetas y estándares de nombrado para el desarrollo de la plataforma **Classia**.

---

## 1. Estructura General del Proyecto

```text
Proyecto_TIS_Classia/
├── assets/                  # Recursos multimedia (imágenes, favicons, logotipos)
│   └── images/              # Archivos de imagen normalizados (kebab-case)
├── config/                  # Archivos de configuración global y PDO (database.php)
├── css/                     # Estilos CSS (style.css, animation.css)
├── docs/                    # Documentación técnica, diagramas UML y modelos
├── includes/                # Componentes comunes PHP (header.php, footer.php)
├── js/                      # Scripts Javascript de cliente (script.js)
├── php/                     # Lógica de negocio backend organizada por módulo (snake_case)
│   ├── auth/                # Sesiones y autenticación (session.php, logout.php)
│   ├── publicaciones/       # CRUD de publicaciones (crear_publicacion.php, etc.)
│   └── usuarios/            # Procesamiento de usuarios (registro.php, etc.)
├── scripts/                 # Scripts de utilidad CLI y diagnósticos de seguridad
├── sql/                     # Scripts DDL de base de datos (schema.sql)
└── views/                   # Vistas y páginas accesibles al usuario (.php en kebab-case)
```

---

## 2. Guía de Nomenclatura

| Tipo de Recurso | Convención | Regla / Explicación | Ejemplo |
| :--- | :--- | :--- | :--- |
| **Directorios** | `kebab-case` | Minúsculas separadas por guiones medios. | `assets/images/`, `php/auth/`, `views/` |
| **Vistas / Páginas web** | `kebab-case` | Archivos `.php` en `views/` con nombres descriptivos sin espacios ni Mayúsculas. | `panel-proveedor.php`, `crear-publicacion.php` |
| **Controladores PHP** | `snake_case` | Archivos backend en `php/*/` que procesan lógica. | `crear_publicacion.php`, `obtener_publicaciones.php` |
| **Imágenes / Iconos** | `kebab-case` | Guardadas en `assets/images/` sin caracteres especiales (`+`, `%20`, etc.). | `anitech-logo-negativo.png`, `logo-classia.png` |
| **Scripts CLI / Utility** | `kebab-case` | Herramientas de consola o shell ubicadas en `scripts/`. | `check-security-headers.sh` |
| **Variables / Funciones PHP** | `snake_case` | Minúsculas separadas por guiones bajos. | `$id_usuario`, `requerir_autenticacion()` |
| **Tablas / Campos SQL** | `snake_case` | Tablas en plural, columnas en singular con claves `id_entidad`. | `publicaciones`, `id_publicacion` |
| **Clases PHP** | `PascalCase` | Nombres de clases y tipos si aplica orientada a objetos. | `DatabaseConnection`, `UsuarioModel` |

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

2. **Formularios y Navegación**:
   - Las acciones de formulario que apuntan a vistas deben usar la URL en `kebab-case`:
     ```html
     <form action="crear-publicacion.php" method="POST">
     ```
   - Las redirecciones PHP de cabecera (`header("Location: ...")`) deben utilizar el nombre estandarizado:
     ```php
     header("Location: panel-proveedor.php?mensaje=creada");
     ```
