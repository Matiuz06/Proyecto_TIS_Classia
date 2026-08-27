# Diagramas de Secuencia UML - Classia

## Resumen del Documento
Este documento especifica los **Diagramas de Secuencia UML** para los flujos principales de la plataforma **Classia**. Detalla la interacción paso a paso entre el **Usuario (Frontend)**, la capa de aplicación **Backend (PHP)** y la capa de persistencia **Base de Datos (MariaDB/MySQL)**, contemplando respuestas exitosas y flujos alternativos de error.

---

## 1. Flujo: Registro de Usuario

```mermaid
sequenceDiagram
    autonumber
    actor usuario as Usuario
    participant frontend as Frontend (HTML/JS)
    participant backend as Backend (PHP)
    participant db as Base de Datos (SQL)

    usuario ->> frontend: Completa formulario y hace click en "Registrarse"
    frontend ->> backend: POST /api/registro.php (nombre, email, password, idRol)
    
    backend ->> backend: Validar formato de datos y campos obligatorios
    
    alt Datos inválidos
        backend -->> frontend: HTTP 400 Bad Request (Campos incompletos o formato incorrecto)
        frontend -->> usuario: Muestra mensaje de error en formulario
    else Datos válidos
        backend ->> db: SELECT idUsuario FROM USUARIO WHERE email = ?
        db -->> backend: Resultado (existe o no)
        
        alt Email ya registrado
            backend -->> frontend: HTTP 409 Conflict ("El email ya se encuentra registrado")
            frontend -->> usuario: Muestra alerta de email duplicado
        else Email disponible
            backend ->> backend: Hashear contraseña (passwordHash)
            backend ->> db: INSERT INTO USUARIO (nombre, apellido, email, passwordHash, idRol)
            db -->> backend: Registro guardado (idUsuario generado)
            backend -->> frontend: HTTP 201 Created ("Usuario registrado con éxito")
            frontend -->> usuario: Muestra mensaje de éxito y redirige a Login
        end
    end
```

---

## 2. Flujo: Inicio de Sesión (Login)

```mermaid
sequenceDiagram
    autonumber
    actor usuario as Usuario
    participant frontend as Frontend (HTML/JS)
    participant backend as Backend (PHP)
    participant db as Base de Datos (SQL)

    usuario ->> frontend: Ingresa email y contraseña, click en "Iniciar Sesión"
    frontend ->> backend: POST /api/login.php (email, password)
    
    backend ->> db: SELECT idUsuario, passwordHash, idRol FROM USUARIO WHERE email = ?
    db -->> backend: Retorna registro de usuario
    
    alt Usuario no encontrado
        backend -->> frontend: HTTP 401 Unauthorized ("Credenciales inválidas")
        frontend -->> usuario: Muestra error "Email o contraseña incorrectos"
    else Usuario encontrado
        backend ->> backend: Verificar hash de contraseña (passwordVerify)
        
        alt Contraseña incorrecta
            backend -->> frontend: HTTP 401 Unauthorized ("Credenciales inválidas")
            frontend -->> usuario: Muestra error "Email o contraseña incorrectos"
        else Contraseña válida
            backend ->> backend: Crear sesión PHP (sessionStart, $_SESSION)
            backend -->> frontend: HTTP 200 OK (Datos de usuario y sesión iniciada)
            frontend -->> usuario: Redirige al panel/catálogo según el rol
        end
    end
```

---

## 3. Flujo: Consulta de una Publicación (Ver Detalle)

```mermaid
sequenceDiagram
    autonumber
    actor usuario as Usuario
    participant frontend as Frontend (HTML/JS)
    participant backend as Backend (PHP)
    participant db as Base de Datos (SQL)

    usuario ->> frontend: Selecciona una publicación en el catálogo
    frontend ->> backend: GET /api/publicaciones.php?id=123
    
    backend ->> db: SELECT * FROM PUBLICACION p JOIN CATEGORIA c JOIN USUARIO u WHERE p.idPublicacion = 123
    db -->> backend: Datos de la publicación y autor
    
    alt Publicación no encontrada
        backend -->> frontend: HTTP 404 Not Found ("Publicación inexistente")
        frontend -->> usuario: Muestra vista de error "Publicación no encontrada"
    else Publicación encontrada
        backend ->> db: SELECT * FROM VALORACION WHERE idPublicacion = 123
        db -->> backend: Lista de valoraciones y reseñas
        
        backend ->> backend: Construir objeto JSON con detalle + valoraciones
        backend -->> frontend: HTTP 200 OK (JSON con detalle completo)
        frontend -->> usuario: Renderiza información en pantalla de detalle
    end
```

---

## 4. Flujo: Contratación de un Curso o Servicio

```mermaid
sequenceDiagram
    autonumber
    actor cliente as Cliente / Estudiante
    participant frontend as Frontend (HTML/JS)
    participant backend as Backend (PHP)
    participant db as Base de Datos (SQL)

    cliente ->> frontend: Selecciona método de pago y confirma contratación
    frontend ->> backend: POST /api/contrataciones.php (idPublicacion, monto, metodoPago)
    
    backend ->> backend: Verificar sesión activa de usuario
    
    alt Sesión no iniciada
        backend -->> frontend: HTTP 401 Unauthorized ("Sesión requerida")
        frontend -->> cliente: Redirige al Login
    else Sesión válida
        backend ->> db: Iniciar Transacción SQL (BEGIN TRANSACTION)
        backend ->> db: INSERT INTO CONTRATACION (fecha, montoTotal, estado, idUsuario)
        db -->> backend: idContratacion generado
        
        backend ->> db: INSERT INTO DETALLE_CONTRATACION (idContratacion, idPublicacion, subtotal)
        db -->> backend: Detalle guardado
        
        backend ->> db: INSERT INTO PAGO (monto, metodoPago, estadoPago, idContratacion)
        db -->> backend: Pago registrado
        
        alt Error en algún paso de la base de datos
            backend ->> db: ROLLBACK
            backend -->> frontend: HTTP 500 Internal Error ("Error al procesar la contratación")
            frontend -->> cliente: Muestra mensaje de error y reintento
        else Transacción exitosa
            backend ->> db: COMMIT
            backend -->> frontend: HTTP 201 Created (Comprobante y confirmación)
            frontend -->> cliente: Muestra pantalla de confirmación de compra exitosa
        end
    end
```

---

## 5. Flujo: Publicación de Curso o Servicio por un Proveedor

```mermaid
sequenceDiagram
    autonumber
    actor docente as Docente / Proveedor
    participant frontend as Frontend (HTML/JS)
    participant backend as Backend (PHP)
    participant db as Base de Datos (SQL)

    docente ->> frontend: Completa formulario de publicación y guarda
    frontend ->> backend: POST /api/publicaciones.php (titulo, descripcion, precio, tipo, idCategoria)
    
    backend ->> backend: Verificar sesión y validar rol ("Docente/Proveedor")
    
    alt Usuario no autorizado
        backend -->> frontend: HTTP 403 Forbidden ("Permisos insuficientes")
        frontend -->> docente: Muestra error de acceso denegado
    else Rol autorizado
        backend ->> backend: Validar campos (precio > 0, título no vacío)
        
        alt Validación de campos fallida
            backend -->> frontend: HTTP 400 Bad Request ("Datos inválidos")
            frontend -->> docente: Muestra errores en los campos del formulario
        else Validación correcta
            backend ->> db: INSERT INTO PUBLICACION (titulo, descripcion, precio, tipo, idUsuario, idCategoria)
            db -->> backend: idPublicacion generado
            backend -->> frontend: HTTP 201 Created ("Publicación creada con éxito")
            frontend -->> docente: Muestra mensaje de éxito y redirige a "Mis Publicaciones"
        end
    end
```

---

## Matriz de Coherencia con Casos de Uso y Backend

| Flujo de Secuencia | Caso de Uso Asociado | Tablas Afectadas en BD | Controlador Backend PHP Recomendado |
| :--- | :--- | :--- | :--- |
| **1. Registro** | `CU-02: Registrarse` | `USUARIO`, `ROL` | `RegistroController.php` |
| **2. Login** | `CU-01: Autenticarse` | `USUARIO` | `AuthController.php` |
| **3. Consulta Publicación** | `CU-04: Ver Detalle` | `PUBLICACION`, `CATEGORIA`, `VALORACION` | `PublicacionController.php` |
| **4. Contratación** | `CU-05: Contratar` + `CU-06: Pagar` | `CONTRATACION`, `DETALLE_CONTRATACION`, `PAGO` | `ContratacionController.php` |
| **5. Nueva Publicación** | `CU-09: Crear Publicación` | `PUBLICACION` | `PublicacionController.php` |
