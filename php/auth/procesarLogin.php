<?php

require_once __DIR__ . "/session.php";

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
            email,
            password_hash,
            id_rol
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

establecer_usuario_sesion(
    (int) $usuario["id_usuario"],
    $usuario["nombre"],
    $usuario["email"],
    (int) $usuario["id_rol"]
);

header("Location: " . LOGIN_OK_URL);
exit;