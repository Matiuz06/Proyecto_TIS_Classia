<?php
/**
 * Google OAuth 2.0 — Callback
 * Recibe el code de Google, valida CSRF, intercambia por token,
 * obtiene el perfil y crea/inicia sesión del usuario en la BD.
 */

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/roles.php';
require_once __DIR__ . '/../../config/database.php';

iniciar_sesion();

// ── Si ya está autenticado, no hay nada que hacer ────────────────────────────
if (esta_autenticado()) {
    header('Location: ../../views/usuario.php');
    exit;
}

// ── Helper: redirigir con error ──────────────────────────────────────────────
function oauth_error(string $mensaje): never
{
    $_SESSION['login_error'] = $mensaje;
    header('Location: ../../views/login.php');
    exit;
}

// ── 1. Verificar que no haya un error de Google ──────────────────────────────
if (isset($_GET['error'])) {
    $google_error = htmlspecialchars($_GET['error']);
    error_log("Google OAuth error: {$google_error}");
    oauth_error('El inicio de sesión con Google fue cancelado o falló. Intentá de nuevo.');
}

// ── 2. Verificar presencia de code y state ───────────────────────────────────
if (empty($_GET['code']) || empty($_GET['state'])) {
    oauth_error('Respuesta de Google incompleta. Intentá de nuevo.');
}

// ── 3. Validar estado CSRF ───────────────────────────────────────────────────
$state_esperado = $_SESSION['google_oauth_state'] ?? '';
unset($_SESSION['google_oauth_state']);

if (!hash_equals($state_esperado, $_GET['state'])) {
    error_log('Google OAuth: state CSRF no coincide');
    oauth_error('Error de seguridad en el inicio de sesión. Intentá de nuevo.');
}

// ── 4. Leer credenciales ─────────────────────────────────────────────────────
$client_id     = getenv('GOOGLE_CLIENT_ID');
$client_secret = getenv('GOOGLE_CLIENT_SECRET');
$redirect_uri  = getenv('GOOGLE_REDIRECT_URI');

if (!$client_id || !$client_secret || !$redirect_uri) {
    error_log('Google OAuth: credenciales no configuradas en .env');
    oauth_error('El inicio de sesión con Google no está configurado correctamente.');
}

// ── 5. Intercambiar code por access_token ────────────────────────────────────
$token_url  = 'https://oauth2.googleapis.com/token';
$token_data = http_build_query([
    'code'          => $_GET['code'],
    'client_id'     => $client_id,
    'client_secret' => $client_secret,
    'redirect_uri'  => $redirect_uri,
    'grant_type'    => 'authorization_code',
]);

$context_token = stream_context_create([
    'http' => [
        'method'  => 'POST',
        'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
        'content' => $token_data,
        'timeout' => 10,
    ],
]);

$token_response = @file_get_contents($token_url, false, $context_token);

if ($token_response === false) {
    error_log('Google OAuth: no se pudo contactar a oauth2.googleapis.com/token');
    oauth_error('No se pudo conectar con Google. Verificá tu conexión e intentá de nuevo.');
}

$token_json = json_decode($token_response, true);

if (empty($token_json['access_token'])) {
    $desc = $token_json['error_description'] ?? $token_json['error'] ?? 'sin detalle';
    error_log("Google OAuth: token inválido — {$desc}");
    oauth_error('Google rechazó la solicitud de acceso. Intentá de nuevo.');
}

$access_token = $token_json['access_token'];

// ── 6. Obtener perfil del usuario ────────────────────────────────────────────
$userinfo_url = 'https://openidconnect.googleapis.com/v1/userinfo';
$context_info = stream_context_create([
    'http' => [
        'method'  => 'GET',
        'header'  => "Authorization: Bearer {$access_token}\r\n",
        'timeout' => 10,
    ],
]);

$userinfo_response = @file_get_contents($userinfo_url, false, $context_info);

if ($userinfo_response === false) {
    error_log('Google OAuth: no se pudo obtener userinfo');
    oauth_error('No se pudieron obtener los datos de tu cuenta Google.');
}

$perfil = json_decode($userinfo_response, true);

if (empty($perfil['email'])) {
    error_log('Google OAuth: userinfo sin email — ' . $userinfo_response);
    oauth_error('No se pudo obtener el email de tu cuenta Google. Asegurate de otorgar los permisos necesarios.');
}

$google_email  = strtolower(trim($perfil['email']));
$google_nombre = trim($perfil['given_name']  ?? '');
$google_apellido = trim($perfil['family_name'] ?? '');
$google_foto   = $perfil['picture'] ?? null;

// Nombre de fallback si Google no lo devuelve
if ($google_nombre === '') {
    $google_nombre = ucfirst(explode('@', $google_email)[0]);
}
if ($google_apellido === '') {
    $google_apellido = 'Google';
}

// ── 7. Buscar o crear el usuario en la BD ────────────────────────────────────
try {
    $stmt = $pdo->prepare("
        SELECT id_usuario, nombre, apellido, email, id_rol, foto_perfil
        FROM usuarios
        WHERE email = :email
        LIMIT 1
    ");
    $stmt->execute(['email' => $google_email]);
    $usuario = $stmt->fetch();

    if ($usuario) {
        // El usuario existe → login directo (nunca duplicar)
        // Actualizar foto si Google tiene una y el usuario no tiene
        if ($google_foto && empty($usuario['foto_perfil'])) {
            $pdo->prepare("UPDATE usuarios SET foto_perfil = :foto WHERE id_usuario = :id")
                ->execute(['foto' => $google_foto, 'id' => $usuario['id_usuario']]);
            $usuario['foto_perfil'] = $google_foto;
        }

        establecer_usuario_sesion(
            (int) $usuario['id_usuario'],
            $usuario['nombre'] . (!empty($usuario['apellido']) ? ' ' . $usuario['apellido'] : ''),
            $usuario['email'],
            (int) $usuario['id_rol'],
            $usuario['foto_perfil'] ?? null
        );
    } else {
        // Usuario nuevo → registrar con rol Estudiante
        $password_hash = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);

        $stmt_insert = $pdo->prepare("
            INSERT INTO usuarios (nombre, apellido, email, password_hash, id_rol, foto_perfil, fecha_registro)
            VALUES (:nombre, :apellido, :email, :pass, :rol, :foto, CURRENT_TIMESTAMP)
        ");
        $stmt_insert->execute([
            'nombre'   => $google_nombre,
            'apellido' => $google_apellido,
            'email'    => $google_email,
            'pass'     => $password_hash,
            'rol'      => ROL_ESTUDIANTE,
            'foto'     => $google_foto,
        ]);

        $nuevo_id = (int) $pdo->lastInsertId();

        establecer_usuario_sesion(
            $nuevo_id,
            $google_nombre . ' ' . $google_apellido,
            $google_email,
            ROL_ESTUDIANTE,
            $google_foto
        );
    }

    // ── 8. Redirigir según rol ───────────────────────────────────────────────
    $id_rol = (int) ($_SESSION['usuario']['id_rol'] ?? ROL_ESTUDIANTE);

    if ($id_rol === ROL_ADMIN) {
        header('Location: ../../views/panel-administrador.php');
    } elseif ($id_rol === ROL_DOCENTE) {
        header('Location: ../../views/panel-proveedor.php');
    } else {
        header('Location: ../../views/usuario.php');
    }
    exit;

} catch (PDOException $e) {
    error_log('Google OAuth — error de BD: ' . $e->getMessage());
    oauth_error('Error interno al procesar tu cuenta. Intentá de nuevo.');
}
