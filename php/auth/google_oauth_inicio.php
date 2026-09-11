<?php

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../../config/database.php';

iniciar_sesion();

if (esta_autenticado()) {
    header('Location: ../../views/usuario.php');
    exit;
}

$client_id    = getenv('GOOGLE_CLIENT_ID');
$redirect_uri = getenv('GOOGLE_REDIRECT_URI');

if (!$client_id || !$redirect_uri) {
    error_log('Google OAuth: CLIENT_ID o REDIRECT_URI no configurados en .env');
    $_SESSION['login_error'] = 'El inicio de sesión con Google no está configurado correctamente.';
    header('Location: ../../views/login.php');
    exit;
}

$state = bin2hex(random_bytes(16));
$_SESSION['google_oauth_state'] = $state;

$params = http_build_query([
    'client_id'             => $client_id,
    'redirect_uri'          => $redirect_uri,
    'response_type'         => 'code',
    'scope'                 => 'openid email profile',
    'state'                 => $state,
    'access_type'           => 'online',
    'prompt'                => 'select_account',  
]);

header('Location: https://accounts.google.com/o/oauth2/v2/auth?' . $params);
exit;
