# Guía de Implementación: Separación Backend (php/) y Frontend (views/)

Guía técnica paso a paso para estructurar la autenticación de sesión y la carga dinámica de publicaciones respetando la separación entre la lógica de negocio/base de datos (`php/`) y las plantillas visuales (`views/`).

---

## Estructura de Módulos

```
├── config/
│   └── database.php                    # Conexión centralizada PDO
├── includes/
│   └── header.php                      # Encabezado modular con detección de sesión
├── php/                                # LÓGICA BACKEND (Consultas SQL, validación, sesión)
│   ├── auth/
│   │   ├── login.php                   # Procesador POST de autenticación
│   │   ├── logout.php                  # Destrucción de sesión
│   │   └── session.php                 # Helpers de sesión y cookies
│   ├── publicaciones/
│   │   ├── catalogo.php                # Consultas SQL para listado y filtros del catálogo
│   │   ├── detalle_curso.php           # Consulta SQL de publicación por ID
│   │   └── obtener_publicaciones.php   # Helpers de publicaciones para proveedor
│   └── usuarios/
│       ├── perfil.php                  # Consulta de perfil y contrataciones de usuario
│       └── registro.php                # Procesador POST de registro
└── views/                              # PRESENTACIÓN / VISTAS (HTML + renderizado)
    ├── catalogo.php                    # Requiere php/publicaciones/catalogo.php
    ├── curso.php                       # Requiere php/publicaciones/detalle_curso.php
    ├── login.php                       # Requiere php/auth/login.php
    ├── registro.php                    # Requiere php/usuarios/registro.php
    └── usuario.php                     # Requiere php/usuarios/perfil.php
```

---

## 1. Módulo de Autenticación y Sesión

### Backend: `php/auth/login.php`
Procesa el formulario, verifica la contraseña con `password_verify` y gestiona la redirección por rol:

```php
<?php

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../../config/database.php';

iniciar_sesion();

if (esta_autenticado()) {
    $usr = usuario_actual();
    if ($usr['id_rol'] === 3) {
        header("Location: panel-administrador.php");
    } elseif ($usr['id_rol'] === 2) {
        header("Location: panel-proveedor.php");
    } else {
        header("Location: usuario.php");
    }
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errores = [];
$correo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = strtolower(trim($_POST['correo'] ?? ''));
    $contrasena = $_POST['contrasena'] ?? ($_POST['contraseña'] ?? ($_POST['contrasenia'] ?? ''));

    if (empty($correo) || empty($contrasena)) {
        $errores[] = "Por favor, ingresá tu correo electrónico y contraseña.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id_usuario, nombre, apellido, email, password_hash, id_rol FROM usuarios WHERE email = :email LIMIT 1");
            $stmt->execute(['email' => $correo]);
            $usuario = $stmt->fetch();

            if ($usuario && password_verify($contrasena, $usuario['password_hash'])) {
                establecer_usuario_sesion(
                    (int)$usuario['id_usuario'],
                    $usuario['nombre'] . ' ' . $usuario['apellido'],
                    $usuario['email'],
                    (int)$usuario['id_rol']
                );
                $_SESSION['id_usuario'] = (int)$usuario['id_usuario'];

                if ((int)$usuario['id_rol'] === 3) {
                    header("Location: panel-administrador.php");
                } elseif ((int)$usuario['id_rol'] === 2) {
                    header("Location: panel-proveedor.php");
                } else {
                    header("Location: usuario.php");
                }
                exit;
            } else {
                $errores[] = "El correo electrónico o la contraseña ingresada son incorrectos.";
            }
        } catch (PDOException $e) {
            error_log("Error SQL en login: " . $e->getMessage());
            $errores[] = "Error al intentar iniciar sesión. Por favor, intentá nuevamente en unos momentos.";
        }
    }
}
```

### Backend: `php/auth/session.php`
Asegura la configuración de cookies y el guardado de `id_usuario`:

```php
function iniciar_sesion(): void
{
    if (session_status() !== PHP_SESSION_NONE) {
        return;
    }

    if (!headers_sent() && PHP_VERSION_ID >= 70300) {
        session_set_cookie_params([
            "lifetime" => 0,
            "path" => "/",
            "secure" => !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off",
            "httponly" => true,
            "samesite" => "Lax",
        ]);
    }

    if (!headers_sent() && session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function establecer_usuario_sesion(int $id_usuario, string $nombre, string $email, int $id_rol): void
{
    iniciar_sesion();
    session_regenerate_id(true);

    $_SESSION["usuario"] = [
        "id_usuario" => $id_usuario,
        "nombre" => $nombre,
        "email" => $email,
        "id_rol" => $id_rol,
    ];
    $_SESSION["id_usuario"] = $id_usuario;
}
```

### Vista: `views/login.php`
La vista únicamente importa `php/auth/login.php` y renderiza el formulario con los errores si existen:

```php
<?php
require_once '../php/auth/login.php';

$title      = 'Iniciar sesión';
$description = 'Inicio de sesión en Classia.';
$cssPrefix  = '..';
$bodyClass  = 'auth-page';
$activePage = 'cuenta';
include '../includes/header.php';
?>

  <main class="auth-shell">
    <section class="auth-intro" aria-labelledby="intro-login">
      <h1 id="intro-login">Entrá a tu espacio educativo</h1>
      <p>Accedé a tus cursos, solicitudes, publicaciones y herramientas de gestión desde una cuenta Classia.</p>
    </section>

    <section class="auth-card" aria-labelledby="titulo-login">
      <h2 id="titulo-login">Iniciar sesión</h2>
      <p>Usá tu correo y contraseña para continuar.</p>
      
      <?php if (isset($_GET['registro']) && $_GET['registro'] === 'exitoso'): ?>
        <div class="alert alert-success">Registro completado con éxito. Ahora podés iniciar sesión.</div>
      <?php endif; ?>

      <?php if (!empty($errores)): ?>
        <div class="alert alert-danger">
          <ul>
            <?php foreach ($errores as $error): ?>
              <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form action="login.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
        <p>
          <label for="correo">Correo electrónico</label>
          <input type="email" id="correo" name="correo" autocomplete="email" required
            value="<?php echo htmlspecialchars($correo ?? ''); ?>"
            placeholder="ejemplo@correo.com" />
        </p>
        <p>
          <label for="contrasena">Contraseña</label>
          <input type="password" id="contrasena" name="contrasena" autocomplete="current-password" required
            placeholder="Ingresa tu contraseña" />
        </p>
        <label>
          <input type="checkbox" name="terminos" required /> Acepto los términos y condiciones
        </label>
        <button type="submit">Iniciar sesión</button>
      </form>
    </section>
  </main>
<?php include '../includes/footer.php'; ?>
```

---

## 2. Módulo de Catálogo de Cursos y Servicios

### Backend: `php/publicaciones/catalogo.php`
Encargado de capturar parámetros `GET`, consultar la base de datos y filtrar listas:

```php
<?php

require_once __DIR__ . '/../../config/database.php';

$busqueda = trim($_GET['busqueda'] ?? '');
$tipo_filtro = trim($_GET['tipo'] ?? '');

$sql = "SELECT p.*, c.nombre_categoria, u.nombre AS autor_nombre, u.apellido AS autor_apellido 
        FROM publicaciones p 
        JOIN categorias c ON p.id_categoria = c.id_categoria 
        JOIN usuarios u ON p.id_usuario = u.id_usuario 
        WHERE p.estado = 'Activo'";
$params = [];

if ($busqueda !== '') {
    $sql .= " AND (p.titulo LIKE :busqueda OR p.descripcion LIKE :busqueda OR c.nombre_categoria LIKE :busqueda OR u.nombre LIKE :busqueda OR u.apellido LIKE :busqueda)";
    $params['busqueda'] = '%' . $busqueda . '%';
}

if ($tipo_filtro === 'curso') {
    $sql .= " AND p.tipo = 'Curso'";
} elseif ($tipo_filtro === 'servicio') {
    $sql .= " AND p.tipo = 'Servicio'";
}

$sql .= " ORDER BY p.fecha_creacion DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $publicaciones = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error al consultar catalogo: " . $e->getMessage());
    $publicaciones = [];
}

$cursos = array_filter($publicaciones, fn($p) => $p['tipo'] === 'Curso');
$servicios = array_filter($publicaciones, fn($p) => $p['tipo'] === 'Servicio');
```

### Vista: `views/catalogo.php`
Incluye `../php/publicaciones/catalogo.php` y renderiza el diseño sin incluir lógica SQL:

```php
<?php
require_once '../php/publicaciones/catalogo.php';

$title      = 'Catálogo de cursos y servicios';
$description = 'Catálogo de cursos y servicios educativos disponibles en Classia.';
$cssPrefix  = '..';
$activePage = 'catalogo';
include '../includes/header.php';
?>

    <main>
      <!-- Formulario de búsqueda -->
      <!-- Filtros de tipo -->
      <!-- Iteración de $cursos y $servicios -->
    </main>
<?php include '../includes/footer.php'; ?>
```

---

## 3. Módulo de Detalle de Curso

### Backend: `php/publicaciones/detalle_curso.php`
Recibe `$_GET['id']` y consulta la publicación en BD:

```php
<?php

require_once __DIR__ . '/../../config/database.php';

$id_curso = isset($_GET['id']) ? (int)$_GET['id'] : null;
$curso = null;

if ($id_curso) {
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, c.nombre_categoria, u.nombre AS autor_nombre, u.apellido AS autor_apellido 
            FROM publicaciones p 
            JOIN categorias c ON p.id_categoria = c.id_categoria 
            JOIN usuarios u ON p.id_usuario = u.id_usuario 
            WHERE p.id_publicacion = :id
        ");
        $stmt->execute(['id' => $id_curso]);
        $curso = $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error al consultar detalle del curso: " . $e->getMessage());
        $curso = null;
    }
}
```

### Vista: `views/curso.php`
Incluye `../php/publicaciones/detalle_curso.php` y renderiza los datos en la interfaz.

---

## 4. Módulo de Perfil de Usuario

### Backend: `php/usuarios/perfil.php`
Valida la sesión y consulta el perfil y las contrataciones del usuario autenticado:

```php
<?php

require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../../config/database.php';

iniciar_sesion();

if (!esta_autenticado()) {
    header('Location: login.php');
    exit;
}

$userSession = usuario_actual();
$id_usuario = (int)$userSession['id_usuario'];

try {
    $stmt_user = $pdo->prepare("SELECT u.*, r.nombre_rol FROM usuarios u JOIN roles r ON u.id_rol = r.id_rol WHERE u.id_usuario = :id");
    $stmt_user->execute(['id' => $id_usuario]);
    $userData = $stmt_user->fetch();

    if (!$userData) {
        cerrar_sesion();
        header('Location: login.php');
        exit;
    }

    $stmt_cursos = $pdo->prepare("
        SELECT p.id_publicacion, p.titulo, c.fecha_contratacion, c.estado 
        FROM contrataciones c
        JOIN detalles_contratacion d ON c.id_contratacion = d.id_contratacion
        JOIN publicaciones p ON d.id_publicacion = p.id_publicacion
        WHERE c.id_usuario = :id AND p.tipo = 'Curso'
        ORDER BY c.fecha_contratacion DESC
    ");
    $stmt_cursos->execute(['id' => $id_usuario]);
    $mis_cursos = $stmt_cursos->fetchAll();

    $stmt_servicios = $pdo->prepare("
        SELECT p.id_publicacion, p.titulo, c.fecha_contratacion, c.estado 
        FROM contrataciones c
        JOIN detalles_contratacion d ON c.id_contratacion = d.id_contratacion
        JOIN publicaciones p ON d.id_publicacion = p.id_publicacion
        WHERE c.id_usuario = :id AND p.tipo = 'Servicio'
        ORDER BY c.fecha_contratacion DESC
    ");
    $stmt_servicios->execute(['id' => $id_usuario]);
    $mis_servicios = $stmt_servicios->fetchAll();
} catch (PDOException $e) {
    error_log("Error al consultar perfil de usuario: " . $e->getMessage());
    $userData = [];
    $mis_cursos = [];
    $mis_servicios = [];
}
```

### Vista: `views/usuario.php`
Incluye `../php/usuarios/perfil.php` y renderiza la información personal y las listas de `$mis_cursos` y `$mis_servicios`.

---

## Credenciales de Prueba

* **Admin:** `admin@classia.com` / `12345678`
* **Docente:** `docente@classia.com` / `12345678`
* **Estudiante:** `estudiante@classia.com` / `12345678`
