# Diagramas de Secuencia UML — Classia · Segunda Entrega

## Resumen del Documento
Este documento especifica los **Diagramas de Secuencia UML** para los flujos principales de la plataforma **Classia**. Detalla la interacción paso a paso entre el **Usuario (Frontend)**, la capa **Backend (PHP)** y la **Base de Datos (MariaDB/MySQL)**, contemplando respuestas exitosas y flujos alternativos de error.

> **Última actualización:** Segunda entrega funcional y técnica (Sprint 2)  
> **Leyenda:** ✅ Implementado | 🔄 Vista disponible, backend pendiente

---

## 1. Flujo: Registro de Usuario ✅

```mermaid
sequenceDiagram
    autonumber
    actor usuario as Usuario
    participant frontend as Frontend (HTML/JS)
    participant backend as Backend (PHP)
    participant db as Base de Datos (SQL)

    usuario ->> frontend: Completa formulario y hace click en "Registrarse"
    frontend ->> backend: POST php/usuarios/registro.php (nombre, apellido, correo, contrasenia, csrf_token)
    
    backend ->> backend: Validar token CSRF (hash_equals)
    
    alt Token CSRF inválido
        backend -->> frontend: Redirige a registro.php con error de sesión
        frontend -->> usuario: Muestra "La sesión del formulario expiró"
    else Token válido
        backend ->> backend: Validar campos obligatorios y formato de email
        
        alt Datos inválidos
            backend -->> frontend: Redirige a registro.php con $errores[]
            frontend -->> usuario: Muestra mensajes de error en formulario
        else Datos válidos
            backend ->> db: SELECT id_usuario FROM usuarios WHERE email = :email
            db -->> backend: Resultado (existe o no)
            
            alt Email ya registrado
                backend -->> frontend: Redirige con "No es posible registrar este correo"
                frontend -->> usuario: Muestra alerta de email duplicado
            else Email disponible
                backend ->> backend: password_hash($contrasena, PASSWORD_DEFAULT) → bcrypt
                backend ->> db: INSERT INTO usuarios (nombre, apellido, email, password_hash, id_rol=1)
                db -->> backend: Registro guardado (id_usuario generado)
                backend ->> backend: session_regenerate_id(true)
                backend -->> frontend: Location: login.php?registro=exitoso
                frontend -->> usuario: Muestra mensaje de éxito y redirige a Login
            end
        end
    end
```

**Archivos involucrados:** `views/registro.php` · `php/usuarios/registro.php` · `config/database.php` · tabla `usuarios`

---

## 2. Flujo: Inicio de Sesión (Login) 🔄

```mermaid
sequenceDiagram
    autonumber
    actor usuario as Usuario
    participant frontend as Frontend (HTML/JS)
    participant backend as Backend (PHP)
    participant db as Base de Datos (SQL)

    usuario ->> frontend: Ingresa email y contraseña, click en "Iniciar Sesión"
    frontend ->> backend: POST php/auth/login.php (email, password) [pendiente implementar]
    
    backend ->> db: SELECT id_usuario, password_hash, id_rol FROM usuarios WHERE email = :email
    db -->> backend: Retorna registro de usuario
    
    alt Usuario no encontrado
        backend -->> frontend: Redirige a login.php con error
        frontend -->> usuario: Muestra "Email o contraseña incorrectos"
    else Usuario encontrado
        backend ->> backend: password_verify($password, $password_hash)
        
        alt Contraseña incorrecta
            backend -->> frontend: Redirige a login.php con error
            frontend -->> usuario: Muestra "Email o contraseña incorrectos"
        else Contraseña válida
            backend ->> backend: establecer_usuario_sesion(id_usuario, nombre, email, id_rol)
            backend ->> backend: session_regenerate_id(true)
            
            alt id_rol = 1 (Cliente/Estudiante)
                backend -->> frontend: Location: views/usuario.php
            else id_rol = 2 (Docente/Proveedor)
                backend -->> frontend: Location: views/panel-proveedor.php
            else id_rol = 3 (Administrador)
                backend -->> frontend: Location: views/panel-administrador.php
            end
            
            frontend -->> usuario: Redirige al panel correspondiente por rol
        end
    end
```

**Archivos involucrados:** `views/login.php` · `php/auth/session.php` · `php/auth/login.php` (pendiente) · tabla `usuarios`

---

## 3. Flujo: Consulta de una Publicación (Ver Detalle) ✅

```mermaid
sequenceDiagram
    autonumber
    actor usuario as Usuario
    participant frontend as Frontend (HTML/JS)
    participant backend as Backend (PHP)
    participant db as Base de Datos (SQL)

    usuario ->> frontend: Selecciona una publicación en el catálogo
    frontend ->> backend: GET views/servicio-detalle.php?id=123
    
    backend ->> db: SELECT p.*, c.nombre_categoria, u.nombre, u.apellido FROM publicaciones p JOIN categorias c JOIN usuarios u WHERE p.id_publicacion = 123
    db -->> backend: Datos de la publicación y autor
    
    alt Publicación no encontrada
        backend -->> frontend: Muestra vista de error "Publicación no encontrada"
        frontend -->> usuario: Renderiza mensaje de error
    else Publicación encontrada
        backend ->> db: SELECT * FROM valoraciones WHERE id_publicacion = 123
        db -->> backend: Lista de valoraciones y reseñas
        
        backend ->> backend: Construir datos completos (publicación + valoraciones)
        backend -->> frontend: Renderiza HTML con detalle completo
        frontend -->> usuario: Muestra información completa de la publicación
    end
```

**Archivos involucrados:** `views/servicio-detalle.php` · `views/curso.php` · tablas `publicaciones`, `categorias`, `usuarios`, `valoraciones`

---

## 4. Flujo: Creación de Publicación por Docente ✅

```mermaid
sequenceDiagram
    autonumber
    actor docente as Docente / Proveedor
    participant frontend as Frontend (HTML/JS)
    participant backend as Backend (PHP)
    participant db as Base de Datos (SQL)

    docente ->> frontend: Completa formulario de publicación y guarda
    frontend ->> backend: POST php/publicaciones/crear_publicacion.php (titulo, descripcion, precio, tipo, id_categoria, csrf_token)
    
    backend ->> backend: Validar token CSRF
    backend ->> backend: Verificar sesión activa y rol Docente/Proveedor (id_rol=2)
    
    alt Sesión no activa o rol incorrecto
        backend -->> frontend: Redirige a login.php
        frontend -->> docente: Muestra pantalla de login
    else Autorizado
        backend ->> backend: Validar campos (título, descripción, precio > 0, tipo válido)
        
        alt Validación fallida
            backend -->> frontend: Redirige con $errores[]
            frontend -->> docente: Muestra errores en los campos del formulario
        else Validación correcta
            backend ->> db: SELECT id_categoria FROM categorias WHERE id_categoria = :id
            db -->> backend: Categoría existe
            
            backend ->> db: INSERT INTO publicaciones (titulo, descripcion, precio, tipo, estado='Activo', id_usuario, id_categoria)
            db -->> backend: id_publicacion generado
            backend -->> frontend: Location: panel-proveedor.php?mensaje=creada
            frontend -->> docente: Muestra "Publicación creada exitosamente"
        end
    end
```

**Archivos involucrados:** `views/crear-publicacion.php` · `php/publicaciones/crear_publicacion.php` · `config/database.php` · tabla `publicaciones`

---

## 5. Flujo: Edición / Cambio de Estado de Publicación ✅

```mermaid
sequenceDiagram
    autonumber
    actor docente as Docente / Proveedor
    participant frontend as Frontend (HTML/JS)
    participant backend as Backend (PHP)
    participant db as Base de Datos (SQL)

    docente ->> frontend: Selecciona publicación y modifica datos o cambia estado
    frontend ->> backend: POST php/publicaciones/editar_publicacion.php (id_publicacion, datos, csrf_token)
    
    backend ->> backend: Validar token CSRF
    backend ->> db: SELECT * FROM publicaciones WHERE id_publicacion = :id AND id_usuario = :id_usuario
    db -->> backend: Verifica propiedad de la publicación
    
    alt No es propietario
        backend -->> frontend: Redirige con "No tenés permisos para modificar esta publicación"
        frontend -->> docente: Muestra error de acceso
    else Es propietario
        alt Acción: cambiar_estado
            backend ->> backend: Validar estado (Activo | Inactivo | Pausado)
            backend ->> db: UPDATE publicaciones SET estado = :estado WHERE id_publicacion = :id AND id_usuario = :id_usuario
            db -->> backend: OK
            backend -->> frontend: Location: panel-proveedor.php?mensaje=estado_actualizado
        else Acción: editar datos completos
            backend ->> backend: Validar campos (título, precio > 0, tipo, estado válidos)
            backend ->> db: UPDATE publicaciones SET titulo, descripcion, precio, tipo, id_categoria, estado WHERE id_publicacion = :id AND id_usuario = :id_usuario
            db -->> backend: OK
            backend -->> frontend: Location: panel-proveedor.php?mensaje=actualizada
        end
        frontend -->> docente: Muestra confirmación de cambios guardados
    end
```

**Archivos involucrados:** `views/editar-publicacion.php` · `php/publicaciones/editar_publicacion.php` · tabla `publicaciones`

---

## 6. Flujo: Envío de Solicitud Personalizada (Solicitud Docente) 🔄

```mermaid
sequenceDiagram
    autonumber
    actor cliente as Cliente / Estudiante
    participant frontend as Frontend (HTML/JS)
    participant backend as Backend (PHP)
    participant db as Base de Datos (SQL)

    cliente ->> frontend: Navega al detalle del servicio y hace click en "Solicitar"
    frontend -->> cliente: Renderiza views/form-solicitar-servicio.php con datos del servicio seleccionado
    
    cliente ->> frontend: Completa formulario (datos de contacto, descripción, presupuesto estimado, adjuntos)
    frontend ->> backend: POST php/solicitudes/crear_solicitud.php (titulo, descripcion, id_publicacion, csrf_token) [pendiente]
    
    backend ->> backend: Validar token CSRF
    backend ->> backend: Verificar sesión activa (id_rol=1 o sin sesión permite consulta)
    
    alt Datos inválidos
        backend -->> frontend: Redirige con $errores[]
        frontend -->> cliente: Muestra errores en el formulario
    else Datos válidos
        backend ->> db: INSERT INTO solicitudes (titulo, descripcion, estado='Pendiente', id_usuario, id_publicacion)
        db -->> backend: id_solicitud generado
        
        backend -->> frontend: Location: confirmacion.php?tipo=solicitud
        frontend -->> cliente: Muestra confirmación "Tu solicitud fue enviada exitosamente"
        
        note over backend,db: El Docente/Proveedor podrá ver la solicitud<br/>en panel-proveedor.php (sección Solicitudes)<br/>y cambiar estado: Aceptada / Rechazada / Cancelada
    end
```

**Archivos involucrados:** `views/form-solicitar-servicio.php` · `views/solicitud-impresion-3d.php` · `php/solicitudes/crear_solicitud.php` (pendiente) · tabla `solicitudes`

---

## 7. Flujo: Contratación de un Curso o Servicio 🔄

```mermaid
sequenceDiagram
    autonumber
    actor cliente as Cliente / Estudiante
    participant frontend as Frontend (HTML/JS)
    participant backend as Backend (PHP)
    participant db as Base de Datos (SQL)

    cliente ->> frontend: Agrega al carrito y confirma contratación con método de pago
    frontend ->> backend: POST php/contrataciones/crear_contratacion.php (id_publicacion, monto, metodo_pago) [pendiente]
    
    backend ->> backend: Verificar sesión activa de usuario (id_rol=1)
    
    alt Sesión no iniciada
        backend -->> frontend: Location: login.php
        frontend -->> cliente: Redirige al Login
    else Sesión válida
        backend ->> db: BEGIN TRANSACTION
        backend ->> db: INSERT INTO contrataciones (fecha_contratacion, monto_total, estado='Pendiente', id_usuario)
        db -->> backend: id_contratacion generado
        
        backend ->> db: INSERT INTO detalles_contratacion (id_contratacion, id_publicacion, cantidad, precio_unitario, subtotal)
        db -->> backend: Detalle guardado
        
        backend ->> db: INSERT INTO pagos (monto, metodo_pago, estado_pago='Pendiente', id_contratacion)
        db -->> backend: Pago registrado
        
        alt Error en algún paso
            backend ->> db: ROLLBACK
            backend -->> frontend: Error al procesar la contratación
            frontend -->> cliente: Muestra mensaje de error y opción de reintento
        else Transacción exitosa
            backend ->> db: COMMIT
            backend -->> frontend: Location: confirmacion.php
            frontend -->> cliente: Muestra confirmación de compra exitosa
        end
    end
```

**Archivos involucrados:** `views/carrito.php` · `views/pasarela-pago.php` · `views/confirmacion.php` · `php/contrataciones/crear_contratacion.php` (pendiente) · tablas `contrataciones`, `detalles_contratacion`, `pagos`

---

## Matriz de Coherencia con Casos de Uso y Backend

| Flujo de Secuencia | Caso de Uso | Estado | Tablas Afectadas | Controlador PHP |
| :--- | :--- | :---: | :--- | :--- |
| **1. Registro** | CU-02: Registrarse | ✅ | `usuarios` | `php/usuarios/registro.php` |
| **2. Login** | CU-01: Autenticarse | 🔄 | `usuarios` | `php/auth/login.php` (pendiente) |
| **3. Ver Publicación** | CU-04: Ver Detalle | ✅ | `publicaciones`, `categorias`, `valoraciones` | vistas directas |
| **4. Crear Publicación** | CU-09: Crear Publicación | ✅ | `publicaciones` | `php/publicaciones/crear_publicacion.php` |
| **5. Editar Publicación** | CU-09 + CU-10 | ✅ | `publicaciones` | `php/publicaciones/editar_publicacion.php` |
| **6. Solicitud Docente** | CU-07: Enviar Solicitud | 🔄 | `solicitudes` | `php/solicitudes/crear_solicitud.php` (pendiente) |
| **7. Contratación** | CU-05 + CU-06 | 🔄 | `contrataciones`, `detalles_contratacion`, `pagos` | `php/contrataciones/` (pendiente) |
