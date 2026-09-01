# php/publicaciones/

Módulo de gestión del ciclo de vida de cursos y servicios educativos para la plataforma Classia.

---

## 📚 PHP-12: CRUD de Publicaciones (Cursos y Servicios)

Este módulo gestiona la creación, edición, consulta y control de estados de las publicaciones ofertadas por docentes y proveedores de AniTech.

### Componentes y scripts:

### 1. `crearPublicacion.php`
- Procesa el alta de nuevos cursos o servicios (`html/crearPublicacion.php`).
- **Validaciones:** Comprobación de token CSRF, campos requeridos (título, descripción, precio, tipo, categoría), tipos permitidos (`Curso`, `Servicio`), precios positivos y existencia previa de la categoría.
- **Persistencia:** Inserción en la tabla `publicaciones` mediante PDO vinculando automáticamente el `id_usuario` del autor autenticado.
- **Redirección:** Redirige al panel del proveedor con notificación (`panelProveedor.php?mensaje=creada`).

### 2. `editarPublicacion.php`
- Gestiona la modificación y actualización de publicaciones existentes (`html/editarPublicacion.php`).
- **Control de autorización y propiedad:** Valida que la publicación pertenezca al usuario en sesión (`WHERE id_publicacion = :id_pub AND id_usuario = :id_user`).
- **Gestión de estados:** Permite alternar entre `Activo`, `Inactivo` y `Pausado`.
- **Actualización segura:** Actualización de atributos (título, descripción, precio, tipo, categoría, estado) mediante sentencias preparadas PDO.

### 3. `obtenerPublicaciones.php`
- Helper para consultas de datos del catálogo del proveedor.
- Carga las categorías activas para los selectores de formularios.
- Ejecuta la consulta de publicaciones del usuario autenticado con JOIN a la tabla `categorias` para alimentar dinámicamente las tarjetas en `html/panelProveedor.php`.

