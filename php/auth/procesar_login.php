<?php

require_once __DIR__ . "/sesion.php";

const LOGIN_URL = "../../views/login.php";
const LOGIN_OK_URL = "../../views/usuario.php";

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
    volver_login("La sesión del formulario expiró. Recargá la página e intentá nuevamente.");
}

$email = trim($_POST["email"] ?? "");
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
            foto_perfil
            ,email_verificado
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
    volver_login(
        "Email o contraseña incorrectos.",
        $email
    );
}

if (isset($usuario['email_verificado']) && !(int) $usuario['email_verificado']) {
    volver_login('Confirmá tu correo electrónico antes de iniciar sesión.', $email);
}

establecer_usuario_sesion(
    (int) $usuario["id_usuario"],
    $usuario["nombre"] . (!empty($usuario["apellido"]) ? ' ' . $usuario["apellido"] : ''),
    $usuario["email"],
    (int) $usuario["id_rol"],
    $usuario["foto_perfil"] ?? null
);

if ((int) $usuario["id_rol"] === 3) {
    header("Location: ../../views/panel-administrador.php");
} elseif ((int) $usuario["id_rol"] === 2) {
    header("Location: ../../views/panel-proveedor.php");
} else {
    header("Location: " . LOGIN_OK_URL);
}
exit;