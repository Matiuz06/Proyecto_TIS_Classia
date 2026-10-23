<?php

/**
 * Responsabilidad: Autentica credenciales, valida CSRF/reCAPTCHA y prepara sesión o 2FA.
 */

require_once __DIR__ . "/sesion.php";
require_once __DIR__ . "/../utils/recaptcha.php";
require_once __DIR__ . "/../utils/auditoria.php";
require_once __DIR__ . "/../utils/toast.php";

const LOGIN_URL = "../../views/login.php";
const LOGIN_OK_URL = "../../views/usuario.php";
const VERIFY_2FA_URL = "../../views/verificar-2fa.php";

function volver_login(string $mensaje, string $email = ""): void
{
    iniciar_sesion();

    $_SESSION["login_error"] = $mensaje;
    $_SESSION["login_email"] = $email;

    header("Location: " . LOGIN_URL);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: " . LOGIN_URL);
    exit;
}

iniciar_sesion();

$token_csrf = $_POST['csrf_token'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token_csrf)) {
    registrar_auditoria('CSRF_FALLIDO', 'Token CSRF inválido o expirado en intento de login', 'WARNING');
    volver_login("La sesión del formulario expiró. Recargá la página e intentá nuevamente.");
}

$recaptcha_response = $_POST['g-recaptcha-response'] ?? '';
$res_recaptcha = verificar_recaptcha($recaptcha_response);
if (!$res_recaptcha['exito']) {
    registrar_auditoria('RECAPTCHA_FALLIDO', 'Fallo de verificación de reCAPTCHA en login', 'WARNING');
    volver_login($res_recaptcha['mensaje'], $_POST['email'] ?? '');
}

$email = strtolower(trim($_POST["email"] ?? ""));
$password = $_POST["password"] ?? "";

if ($email === "") {
    volver_login("El email es obligatorio.");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    volver_login("Ingrese un email válido.", $email);
}

if ($password === "") {
    volver_login("La contraseña es obligatoria.", $email);
}

// Control de tasa de intentos (Rate limiting / Brute-force protection - NIST SP 800-63B / CIS 5.4)
$max_intentos = 5;
$tiempo_bloqueo = 900; // 15 minutos
if (!isset($_SESSION['login_throttle'])) {
    $_SESSION['login_throttle'] = [];
}
$intentos_data = $_SESSION['login_throttle'][$email] ?? ['count' => 0, 'primer_intento' => time()];
if ($intentos_data['count'] >= $max_intentos && (time() - $intentos_data['primer_intento']) < $tiempo_bloqueo) {
    $minutos_restantes = ceil(($tiempo_bloqueo - (time() - $intentos_data['primer_intento'])) / 60);
    registrar_auditoria('LOGIN_BLOQUEADO_RATE_LIMIT', "Bloqueo por exceso de intentos para {$email}", 'WARNING');
    volver_login("Demasiados intentos fallidos. Por seguridad, intente de nuevo en {$minutos_restantes} minuto(s).", $email);
}

try {
    require_once __DIR__ . "/../../config/database.php";

    $stmt = $pdo->prepare(
        "SELECT
            id_usuario,
            nombre,
            apellido,
            email,
            password_hash,
            id_rol,
            foto_perfil,
            email_verificado,
            onboarding_step,
            dos_factores_activo,
            dos_factores_secreto,
            COALESCE(activo, 1) AS activo,
            motivo_bloqueo
         FROM usuarios
         WHERE email = :email
         LIMIT 1"
    );

    $stmt->execute([
        "email" => $email
    ]);

    $usuario = $stmt->fetch();
} catch (Throwable $e) {
    error_log("Error en login: " . $e->getMessage());

    volver_login(
        "No se pudo iniciar sesión. Intente nuevamente.",
        $email
    );
}

if (
    !$usuario ||
    !password_verify($password, $usuario["password_hash"])
) {
    // Incrementar contador de intentos para este correo
    if ((time() - $intentos_data['primer_intento']) > $tiempo_bloqueo) {
        $intentos_data = ['count' => 1, 'primer_intento' => time()];
    } else {
        $intentos_data['count']++;
    }
    $_SESSION['login_throttle'][$email] = $intentos_data;

    registrar_auditoria('LOGIN_FALLIDO', "Credenciales incorrectas para el correo: {$email}", 'WARNING', [
        'email' => $email,
        'intentos' => $intentos_data['count']
    ]);

    volver_login(
        "Email o contraseña incorrectos.",
        $email
    );
}

// Credenciales válidas: restablecer contador de fuerza bruta
unset($_SESSION['login_throttle'][$email]);

if (isset($usuario['activo']) && (int) $usuario['activo'] === 0) {
    $motivo = !empty($usuario['motivo_bloqueo']) ? htmlspecialchars($usuario['motivo_bloqueo']) : '';
    $_SESSION['cuenta_bloqueada_motivo'] = $motivo;
    registrar_auditoria('LOGIN_CUENTA_BLOQUEADA', "Intento de inicio en cuenta bloqueada: {$email}", 'WARNING', ['motivo' => $motivo], (int)$usuario['id_usuario']);
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header("Location: ../../views/cuenta-bloqueada.php");
    exit;
}

if (isset($usuario['email_verificado']) && !(int) $usuario['email_verificado']) {
    registrar_auditoria('LOGIN_EMAIL_NO_VERIFICADO', "Intento de inicio sin email verificado: {$email}", 'INFO', null, (int)$usuario['id_usuario']);
    volver_login('Confirmá tu correo electrónico antes de iniciar sesión.', $email);
}

if (!empty($usuario['dos_factores_activo'])) {
    registrar_auditoria('LOGIN_2FA_SOLICITADO', "Credenciales válidas, esperando segundo factor", 'INFO', null, (int)$usuario['id_usuario']);
    $_SESSION['2fa_pending'] = [
        'id_usuario'  => (int) $usuario['id_usuario'],
        'nombre'      => $usuario['nombre'] . (!empty($usuario['apellido']) ? ' ' . $usuario['apellido'] : ''),
        'email'       => $usuario['email'],
        'id_rol'      => (int) $usuario['id_rol'],
        'foto_perfil' => $usuario['foto_perfil'] ?? null,
        'tiempo'      => time(),
    ];
    header("Location: " . VERIFY_2FA_URL);
    exit;
}

establecer_usuario_sesion(
    (int) $usuario["id_usuario"],
    $usuario["nombre"] . (!empty($usuario["apellido"]) ? ' ' . $usuario["apellido"] : ''),
    $usuario["email"],
    (int) $usuario["id_rol"],
    $usuario["foto_perfil"] ?? null
);

registrar_auditoria('LOGIN_EXITOSO', "Inicio de sesión exitoso", 'INFO', ['id_rol' => (int)$usuario['id_rol']], (int)$usuario['id_usuario']);
set_toast('success', 'Sesion iniciada');

if ((int) $usuario["id_rol"] === 3) {
    header("Location: ../../views/panel-administrador.php");
} elseif ((int) $usuario["id_rol"] === 2) {
    header("Location: ../../views/panel-proveedor.php");
} elseif ((int) ($usuario["onboarding_step"] ?? 1) <= 9) {
    header("Location: ../../views/primeros-pasos.php");
} else {
    header("Location: " . LOGIN_OK_URL);
}
exit;
