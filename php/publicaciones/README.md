# php/publicaciones/

Módulo de gestión del ciclo de vida de cursos y servicios educativos para la plataforma Classia (Sprint 3 — Segunda Entrega).

---

## 📚 CRUD y Gestión de Publicaciones

Este módulo gestiona la creación, edición, consulta, control de estados y contenidos modulares ofertados por docentes y proveedores en Classia.

### Componentes y scripts:

### 1. `crear_publicacion.php`
- Procesa el alta de nuevos cursos o servicios (`views/crear-publicacion.php`).
- **Validaciones:** Comprobación de token CSRF, campos requeridos (título, descripción, precio, tipo), precios positivos, y **regla de exclusividad de categoría**: sólo se permite crear una categoría nueva si la categoría existente está deseleccionada.
- **Subida de portada:** Validación MIME y almacenamiento seguro en `assets/uploads/publicaciones/` con `.htaccess` de protección.
- **Persistencia:** Inserción en la tabla `publicaciones` mediante PDO vinculando el `id_usuario` del docente autenticado.

### 2. `editar_publicacion.php`
- Modificación y actualización de publicaciones existentes (`views/editar-publicacion.php`).
- Soporte para actualización o eliminación de imagen de portada y sincronización de categorías.

### 3. `cambiar_estado.php`
- Transición entre estados del ciclo de vida (`Activo`, `Pausado`, `Inactivo`, `Eliminado`).

### 4. `contenido_curso.php` & `php/utils/supabase_storage.php`
- Gestión jerárquica de módulos (`curso_modulos`), unidades (`curso_unidades`) y recursos (`curso_recursos`).
- Soporte para almacenamiento en la nube mediante **Supabase Storage** con generación de URLs firmadas temporales para descargas protegidas.

### 5. `publicacion_helpers.php`
- Helpers centralizados para resolución de categorías con exclusividad (`resolver_categoria_publicacion`) y validación de tipos, modalidades y plantillas de servicio.
