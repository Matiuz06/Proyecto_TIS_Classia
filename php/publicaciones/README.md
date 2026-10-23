# Módulo de publicaciones — PHP

Responsable de toda la lógica de negocio relacionada con publicaciones, cursos, servicios y su contenido estructurado.

---

## Arquitectura POO del contenido de cursos

### Jerarquía de composición

```
Curso (publicaciones)
 └── Modulo          (curso_modulos)
      └── Unidad     (curso_unidades)
           └── Recurso (curso_recursos)
```

### Clases de dominio (sin PDO)

| Clase | Archivo | Responsabilidad |
|-------|---------|-----------------|
| `Modulo` | `Modulo.php` | Representa un módulo; compone `Unidad[]` |
| `Unidad` | `Unidad.php` | Representa una unidad/clase; compone `Recurso[]` |
| `Recurso` | `Recurso.php` | Representa un recurso; contiene lógica de presentación (iconos, embed, visualización) |

Las clases de dominio **no tienen dependencias de PDO**. Son objetos puros que encapsulan datos y lógica de presentación.

### Repositorio

| Clase | Archivo | Responsabilidad |
|-------|---------|-----------------|
| `ContenidoCursoRepository` | `ContenidoCursoRepository.php` | Único punto de acceso a BD para contenido de cursos. Construye los objetos de dominio. Realiza todas las operaciones CRUD. |

### Wrappers de compatibilidad (`contenido_curso.php`)

Las funciones libres (globales) de `contenido_curso.php` son **wrappers delgados** que delegan al repositorio. Permiten que las vistas existentes sigan funcionando sin cambios de firma. Las funciones marcadas como `@deprecated` deben migrase a usar directamente la clase `Recurso`.

### Cómo usar el repositorio directamente

```php
// Cargar módulos como objetos
$repo    = new ContenidoCursoRepository($pdo);
$modulos = $repo->obtenerPorCurso($id_curso); // Modulo[]

foreach ($modulos as $modulo) {
    echo $modulo->getTitulo();
    foreach ($modulo->getUnidades() as $unidad) {
        echo $unidad->getTitulo();
        foreach ($unidad->getRecursos() as $recurso) {
            echo $recurso->getEmojiIcono() . ' ' . $recurso->getTitulo();
            echo $recurso->getVideoEmbedUrl(); // null si no es video
        }
    }
}

// Para plantillas que esperan arrays:
$contenido = $repo->obtenerPorCursoComoArray($id_curso);
// equivalente a: array_map(fn($m) => $m->toArray(), $modulos)
```

### Separación de responsabilidades

- **Consultas SQL** → solo en `ContenidoCursoRepository`
- **Lógica de presentación** (iconos, embed) → en `Recurso`
- **Composición y conteo** → en `Modulo` y `Unidad`
- **Validación y orquestación de POST** → en `procesar_contenido_curso()` (delega al repositorio)
- **Subida de archivos** → en `file_upload_helper.php` y `supabase_storage.php`

---

## Otros archivos del módulo

| Archivo | Responsabilidad |
|---------|-----------------|
| `catalogo.php` | Listado y filtrado de publicaciones |
| `crear_publicacion.php` | Alta de publicaciones |
| `editar_publicacion.php` | Modificación de publicaciones |
| `detalle_curso.php` | Carga de datos del curso para la vista |
| `detalle_servicio.php` | Carga de datos del servicio para la vista |
| `obtener_publicaciones.php` | Consultas de publicaciones por usuario |
| `publicacion_helpers.php` | Validaciones y utilidades compartidas |
