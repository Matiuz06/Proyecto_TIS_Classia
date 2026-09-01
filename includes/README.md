# includes/

Directorio de componentes PHP reutilizables y plantillas compartidas para la plataforma Classia.

---

## Componentes disponibles:

### 1. `header.php`
Encabezado global HTML5 con metadatos dinámicos y barra de navegación institucional.
- **Variables configurables:**
  - `$title`: Título de la página (por defecto `'Classia'`).
  - `$description`: Metaetiqueta de descripción para SEO.
  - `$cssPrefix`: Prefijo de rutas relativas para CSS y assets (`'.'` desde la raíz o `'..'` desde subcarpetas).
  - `$activePage`: Identificador de la sección actual para el atributo de accesibilidad `aria-current="page"` (`'inicio'`, `'catalogo'`, `'carrito'`, `'cuenta'`).
  - `$bodyClass`: Clase CSS opcional para el elemento `<body>`.

### 2. `footer.php`
Pie de página institucional con enlaces rápidos a políticas de privacidad (Ley 18.331), términos, ayuda e información corporativa de **AniTech**, incluyendo el cierre de etiquetas HTML y carga de scripts.

