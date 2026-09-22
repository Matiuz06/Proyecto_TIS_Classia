<?php

/**
 * Responsabilidad: Inicia el flujo OAuth con GitHub mediante redirección autorizada.
 */

require_once __DIR__ . '/sesion.php';
require_once __DIR__ . '/../../config/database.php';

iniciar_sesion();

if (esta_autenticado()) {
    header('Location: ../../views/usuario.php');
    exit;
}

$client_id    = getenv('GITHUB_CLIENT_ID');
$redirect_uri = getenv('GITHUB_REDIRECT_URI');

if (!$client_id || !$redirect_uri) {
    error_log('GitHub OAuth: GITHUB_CLIENT_ID o GITHUB_REDIRECT_URI no configurados en .env');
    $_SESSION['login_error'] = 'El inicio de sesión con GitHub no está configurado en el servidor.';
    header('Location: ../../views/login.php');
    exit;
}

$state = bin2hex(random_bytes(16));
$_SESSION['github_oauth_state'] = $state;

$params = http_build_query([
    'client_id'    => $client_id,
    'redirect_uri' => $redirect_uri,
    'scope'        => 'read:user user:email',
    'state'        => $state,
    'allow_signup' => 'true',
]);

header('Location: https://github.com/login/oauth/authorize?' . $params);
exit;
