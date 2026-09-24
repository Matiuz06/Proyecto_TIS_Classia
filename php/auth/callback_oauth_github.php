<?php

/**
 * Responsabilidad: Completa el flujo OAuth de GitHub y crea o actualiza la sesión local.
 */

require_once __DIR__ . '/sesion.php';
require_once __DIR__ . '/roles.php';
require_once __DIR__ . '/../../config/database.php';

iniciar_sesion();

if (esta_autenticado()) {
    header('Location: ../../views/usuario.php');
    exit;
}

function github_oauth_error(string $mensaje): never
{
    $_SESSION['login_error'] = $mensaje;
    header('Location: ../../views/login.php');
    exit;
}

function generar_nombre_usuario_github(PDO $pdo, string $base_nombre): string
{
    $base = strtolower((string) preg_replace('/[^a-z0-9]+/i', '_', $base_nombre ?: 'usuario'));
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
    $err = htmlspecialchars($_GET['error_description'] ?? $_GET['error']);
    error_log("GitHub OAuth error: {$err}");
    github_oauth_error('El inicio de sesión con GitHub fue cancelado o falló.');
}

if (empty($_GET['code']) || empty($_GET['state'])) {
    github_oauth_error('Respuesta de GitHub incompleta. Intentá de nuevo.');
}

$state_esperado = $_SESSION['github_oauth_state'] ?? '';
unset($_SESSION['github_oauth_state']);

if (!hash_equals($state_esperado, $_GET['state'])) {
    error_log('GitHub OAuth: state CSRF mismatch');
    github_oauth_error('Error de seguridad en la autenticación con GitHub (token no coincide).');
}

$client_id     = getenv('GITHUB_CLIENT_ID');
$client_secret = getenv('GITHUB_CLIENT_SECRET');
$redirect_uri  = getenv('GITHUB_REDIRECT_URI');

if (!$client_id || !$client_secret) {
    github_oauth_error('Configuración de GitHub OAuth incompleta en el servidor.');
}

$ch = curl_init('https://github.com/login/oauth/access_token');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => http_build_query([
        'client_id'     => $client_id,
        'client_secret' => $client_secret,
        'code'          => $_GET['code'],
        'redirect_uri'  => $redirect_uri,
    ]),
    CURLOPT_HTTPHEADER     => [
        'Accept: application/json',
        'User-Agent: Classia-App',
    ],
    CURLOPT_TIMEOUT        => 10,
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_err  = curl_error($ch);
curl_close($ch);

if ($response === false || $http_code !== 200) {
    error_log("GitHub OAuth: error al canjear token: {$curl_err} (HTTP {$http_code})");
    github_oauth_error('No se pudo conectar con los servidores de GitHub.');
}

$token_data = json_decode($response, true);
$access_token = $token_data['access_token'] ?? null;

if (!$access_token) {
    error_log('GitHub OAuth: respuesta sin access_token: ' . $response);
    github_oauth_error('GitHub no otorgó el token de acceso.');
}

$ch_user = curl_init('https://api.github.com/user');
curl_setopt_array($ch_user, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => [
        'Authorization: Bearer ' . $access_token,
        'User-Agent: Classia-App',
        'Accept: application/vnd.github.v3+json, application/json',
    ],
    CURLOPT_TIMEOUT        => 10,
    CURLOPT_SSL_VERIFYPEER => false,
]);
$user_resp = curl_exec($ch_user);
curl_close($ch_user);

$gh_user = json_decode((string) $user_resp, true);
$github_id = isset($gh_user['id']) ? (string) $gh_user['id'] : null;

if (!$github_id) {
    error_log('GitHub OAuth: respuesta de usuario inválida: ' . (string) $user_resp);
    github_oauth_error('No se pudo obtener la información de perfil de GitHub.');
}

$login_gh = $gh_user['login'] ?? 'usuario_' . $github_id;
$email = !empty($gh_user['email']) ? $gh_user['email'] : null;

if (!$email) {
    $ch_emails = curl_init('https://api.github.com/user/emails');
    curl_setopt_array($ch_emails, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $access_token,
            'User-Agent: Classia-App',
            'Accept: application/vnd.github.v3+json, application/json',
        ],
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $emails_resp = curl_exec($ch_emails);
    curl_close($ch_emails);

    $emails_data = json_decode((string) $emails_resp, true);
    if (is_array($emails_data)) {
        // 1. Primario y verificado
        foreach ($emails_data as $e) {
            if (!empty($e['email']) && !empty($e['primary']) && !empty($e['verified'])) {
                $email = $e['email'];
                break;
            }
        }
        // 2. Cualquier correo verificado
        if (!$email) {
            foreach ($emails_data as $e) {
                if (!empty($e['email']) && !empty($e['verified'])) {
                    $email = $e['email'];
                    break;
                }
            }
        }
        // 3. Cualquier correo primario
        if (!$email) {
            foreach ($emails_data as $e) {
                if (!empty($e['email']) && !empty($e['primary'])) {
                    $email = $e['email'];
                    break;
                }
            }
        }
        // 4. Cualquier correo disponible en la lista
        if (!$email) {
            foreach ($emails_data as $e) {
                if (!empty($e['email'])) {
                    $email = $e['email'];
                    break;
                }
            }
        }
    }
}

// 5. Fallback infalible si el usuario tiene todos sus correos 100% privados en GitHub
if (!$email) {
    $email = $login_gh . '@users.noreply.github.com';
}

$email = strtolower(trim($email));
$nombre_completo = trim($gh_user['name'] ?? $gh_user['login'] ?? 'Usuario');
$partes_nombre = explode(' ', $nombre_completo, 2);
$nombre   = $partes_nombre[0];
$apellido = $partes_nombre[1] ?? 'GitHub';
$foto_perfil = $gh_user['avatar_url'] ?? null;

try {
    $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE github_id = :github_id LIMIT 1');
    $stmt->execute(['github_id' => $github_id]);
    $usuario = $stmt->fetch();

    if (!$usuario) {
        $stmt_email = $pdo->prepare('SELECT * FROM usuarios WHERE email = :email LIMIT 1');
        $stmt_email->execute(['email' => $email]);
        $usuario = $stmt_email->fetch();

        if ($usuario) {
            $update_fields = ['github_id = :github_id'];
            $params_update = ['github_id' => $github_id, 'id' => $usuario['id_usuario']];

            if (empty($usuario['foto_perfil']) && $foto_perfil) {
                $update_fields[] = 'foto_perfil = :foto_perfil';
                $params_update['foto_perfil'] = $foto_perfil;
            }

            $sql = 'UPDATE usuarios SET ' . implode(', ', $update_fields) . ' WHERE id_usuario = :id';
            $pdo->prepare($sql)->execute($params_update);
        } else {
            $username_sugerido = generar_nombre_usuario_github($pdo, $login_gh);
            $random_pass = bin2hex(random_bytes(32));
            $pass_hash   = password_hash($random_pass, PASSWORD_DEFAULT);
            $id_rol_estudiante = 1;

            $stmt_insert = $pdo->prepare(
                'INSERT INTO usuarios (nombre, apellido, nombre_usuario, email, password_hash, id_rol, foto_perfil, github_id, email_verificado, onboarding_step)
                 VALUES (:nombre, :apellido, :nombre_usuario, :email, :password_hash, :id_rol, :foto_perfil, :github_id, 1, 1)'
            );
            $stmt_insert->execute([
                'nombre'         => $nombre,
                'apellido'       => $apellido,
                'nombre_usuario' => $username_sugerido,
                'email'          => $email,
                'password_hash'  => $pass_hash,
                'id_rol'         => $id_rol_estudiante,
                'foto_perfil'    => $foto_perfil,
                'github_id'      => $github_id,
            ]);

            $id_usuario = (int) $pdo->lastInsertId();
            $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id_usuario = :id LIMIT 1');
            $stmt->execute(['id' => $id_usuario]);
            $usuario = $stmt->fetch();
        }
    }

    if ($usuario && isset($usuario['activo']) && (int) $usuario['activo'] === 0) {
        $motivo = !empty($usuario['motivo_bloqueo']) ? ' Motivo: ' . $usuario['motivo_bloqueo'] : '';
        github_oauth_error('Tu cuenta se encuentra bloqueada o suspendida por la administración.' . $motivo);
    }

    establecer_usuario_sesion(
        (int) $usuario['id_usuario'],
        $usuario['nombre'] . ' ' . $usuario['apellido'],
        $usuario['email'],
        (int) $usuario['id_rol']
    );

    if (!empty($usuario['foto_perfil'])) {
        $_SESSION['usuario_foto'] = $usuario['foto_perfil'];
    }

    if ((int) ($usuario['onboarding_step'] ?? 1) <= 1) {
        header('Location: ../../views/primeros-pasos.php');
    } else {
        header('Location: ../../views/usuario.php');
    }
    exit;
} catch (PDOException $e) {
    error_log('GitHub OAuth: error en base de datos: ' . $e->getMessage());
    github_oauth_error('Ocurrió un error al procesar tu cuenta con GitHub.');
}
