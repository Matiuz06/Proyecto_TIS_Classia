<?php

function iniciar_sesion(): void
{
    if (session_status() !== PHP_SESSION_NONE) {
        return;
    }

    if (PHP_VERSION_ID >= 70300) {
        session_set_cookie_params([
            "lifetime" => 0,
            "path" => "/",
            "secure" => !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off",
            "httponly" => true,
            "samesite" => "Lax",
        ]);
    }

    session_start();
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
}

function esta_autenticado(): bool
{
    iniciar_sesion();

    if (!isset($_SESSION["usuario"]) || !is_array($_SESSION["usuario"])) {
        return false;
    }

    return isset(
        $_SESSION["usuario"]["id_usuario"],
        $_SESSION["usuario"]["nombre"],
        $_SESSION["usuario"]["email"],
        $_SESSION["usuario"]["id_rol"]
    );
}

function usuario_actual(): ?array
{
    return esta_autenticado() ? $_SESSION["usuario"] : null;
}

function requerir_autenticacion(string $login_url): void
{
    if (esta_autenticado()) {
        return;
    }

    header("Location: {$login_url}");
    exit;
}

function cerrar_sesion(): void
{
    iniciar_sesion();

    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();

        if (PHP_VERSION_ID >= 70300) {
            setcookie(session_name(), "", [
                "expires" => time() - 42000,
                "path" => $params["path"],
                "domain" => $params["domain"],
                "secure" => $params["secure"],
                "httponly" => $params["httponly"],
                "samesite" => $params["samesite"] ?? "Lax",
            ]);
        } else {
            setcookie(
                session_name(),
                "",
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
    }

    session_destroy();
}
