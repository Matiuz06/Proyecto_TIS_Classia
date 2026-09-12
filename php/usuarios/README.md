# php/usuarios/

Módulo de gestión y procesamiento de usuarios para la plataforma Classia.

---

## PHP-10: Registro de usuarios (`registro.php`)

El script `php/usuarios/registro.php` implementa el procesamiento seguro del formulario de registro de nuevos usuarios (`views/registro.php`).

### Flujo y controles implementados:
1. **Protección CSRF:** Generación y validación de tokens CSRF en sesión (`$_SESSION['csrf_token']`) con `hash_equals()`.
2. **Validación de campos obligatorios:** Verifica la presencia de nombre, apellido, correo electrónico y contraseña.
3. **Validación de formato de correo (`REQ-AUT-02`):** Validación estricta mediante `filter_var($correo, FILTER_VALIDATE_EMAIL)`.
4. **Política de contraseñas seguras (`RNF-01`, `RNF-02`):**
   - Longitud mínima de 8 caracteres.
   - Requisito de incluir al menos una letra y un número (`preg_match`).
   - Verificación de coincidencia entre contraseña y confirmación.
5. **Verificación de unicidad de correo (`REQ-USR-06`):**
   - Consulta preparada PDO: `SELECT id_usuario FROM usuarios WHERE email = :email`.
   - Prevención de duplicados sin revelar información sensible.
6. **Almacenamiento criptográfico seguro (`RNF-01`, `RNF-02`):**
   - Uso de `password_hash($contrasena, PASSWORD_DEFAULT)` (bcrypt nativo de PHP).
7. **Persistencia e inserción:**
   - Inserción en la tabla `usuarios` mediante consultas preparadas PDO vinculando el rol correspondiente.
8. **Seguridad de sesión y redirección:**
   - Impide abrir o enviar el registro si ya existe una sesión autenticada.
   - Crea una sesión pendiente y exige confirmar el correo antes del acceso completo.

## Otros procesos

- `onboarding.php`: guarda paso actual y preferencias en JSON, valida CSRF y controla que el correo esté confirmado.
- `perfil.php` y `perfil_proveedor.php`: cargan datos de cuenta, contrataciones y perfiles públicos con consultas preparadas.
- `actualizar_datos.php`: valida CSRF y actualiza datos editables del perfil.
- `foto_perfil.php`: valida CSRF, procesa imágenes mediante `upload_helper.php`, permite reemplazar/eliminar la foto y actualiza la sesión.
- `procesar_cambio_clave.php`: exige autenticación, contraseña actual, política de complejidad y CSRF.

Las imágenes subidas se almacenan bajo `assets/uploads/perfiles/`; las imágenes externas de Google se mantienen como URL HTTPS y se muestran sin anteponer una ruta local.

