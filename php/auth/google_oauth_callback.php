<?php


require_once __DIR__ . '/session.php';
require_once __DIR__ . '/roles.php';
require_once __DIR__ . '/../../config/database.php';

iniciar_sesion();

if (esta_autenticado()) {
    header('Location: ../../views/usuario.php');
    exit;
}

function oauth_error(string $mensaje): never
{
    $_SESSION['login_error'] = $mensaje;
    header('Location: ../../views/login.php');
    exit;
}

function generar_nombre_usuario_google(PDO $pdo, string $email): string
{
    $base = strtolower((string) preg_replace('/[^a-z0-9]+/i', '_', strstr($email, '@', true) ?: 'usuario'));
    $base = trim(substr($base, 0, 24), '_');
    $base = $base !== '' ? $base : 'usuario';
    $nombre_usuario = $base;
    $numero = 1;

    $stmt = $pdo->prepare('SELECT id_usuario FROM usuarios WHERE nombre_usuario = :nombre_usuario LIMIT 1');
    while (true) {
        $stmt->execute(['nombre_usuario' => $nombre_usuario]);
        if (!$stmt->fetch()) {
            return $nombre_usuario;
        }
        $sufijo = '_' . $numero++;
        $nombre_usuario = substr($base, 0, 30 - strlen($sufijo)) . $sufijo;
    }
}

if (isset($_GET['error'])) {
    $google_error = htmlspecialchars($_GET['error']);
    error_log("Google OAuth error: {$google_error}");
    oauth_error('El inicio de sesión con Google fue cancelado o falló. Intentá de nuevo.');
}

if (empty($_GET['code']) || empty($_GET['state'])) {
    oauth_error('Respuesta de Google incompleta. Intentá de nuevo.');
}

$state_esperado = $_SESSION['google_oauth_state'] ?? '';
unset($_SESSION['google_oauth_state']);

if (!hash_equals($state_esperado, $_GET['state'])) {
    error_log('Google OAuth: state CSRF no coincide');
    oauth_error('Error de seguridad en el inicio de sesión. Intentá de nuevo.');
}

$client_id     = getenv('GOOGLE_CLIENT_ID');
$client_secret = getenv('GOOGLE_CLIENT_SECRET');
$redirect_uri  = getenv('GOOGLE_REDIRECT_URI');

if (!$client_id || !$client_secret || !$redirect_uri) {
    error_log('Google OAuth: credenciales no configuradas en .env');
    oauth_error('El inicio de sesión con Google no está configurado correctamente.');
}

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

if ($google_nombre === '') {
    $google_nombre = ucfirst(explode('@', $google_email)[0]);
}
if ($google_apellido === '') {
    $google_apellido = 'Google';
}

try {
    $onboarding_step_usuario = 1;
    $stmt = $pdo->prepare("
        SELECT id_usuario, nombre, apellido, email, id_rol, foto_perfil, onboarding_step
        FROM usuarios
        WHERE email = :email
        LIMIT 1
    ");
    $stmt->execute(['email' => $google_email]);
    $usuario = $stmt->fetch();

    if ($usuario) {
        $onboarding_step_usuario = (int) ($usuario['onboarding_step'] ?? 1);
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
        $password_hash = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);

        $stmt_insert = $pdo->prepare("
            INSERT INTO usuarios (nombre, apellido, nombre_usuario, genero, email, password_hash, id_rol, foto_perfil, email_verificado, onboarding_step, fecha_registro)
            VALUES (:nombre, :apellido, :nombre_usuario, 'sin-especificar', :email, :pass, :rol, :foto, 1, 1, CURRENT_TIMESTAMP)
        ");
        $stmt_insert->execute([
            'nombre'   => $google_nombre,
            'apellido' => $google_apellido,
            'nombre_usuario' => generar_nombre_usuario_google($pdo, $google_email),
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

    $onboarding_pendiente = $id_rol === ROL_ESTUDIANTE && $onboarding_step_usuario <= 9;

    if ($onboarding_pendiente) {
        header('Location: ../../views/primeros-pasos.php');
    } elseif ($id_rol === ROL_ADMIN) {
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
