<?php

require_once __DIR__ . "/session.php";

const ROL_ESTUDIANTE = 1;
const ROL_DOCENTE = 2;
const ROL_ADMIN = 3;

function tiene_rol(int $id_rol): bool
{
    $usuario = usuario_actual();

    return $usuario !== null && (int) $usuario["id_rol"] === $id_rol;
}

function es_estudiante(): bool
{
    return tiene_rol(ROL_ESTUDIANTE);
}

function es_docente(): bool
{
    return tiene_rol(ROL_DOCENTE);
}

function es_admin(): bool
{
    return tiene_rol(ROL_ADMIN);
}

function requerir_rol(int $id_rol, string $redirigir): void
{
    if (!esta_autenticado()) {
        header("Location: " . ruta_login_por_contexto());
        exit;
    }

    if (tiene_rol($id_rol)) {
        return;
    }

    $_SESSION["mensaje_acceso"] = "No tenes permisos para acceder a esta seccion.";
    header("Location: " . $redirigir);
    exit;
}

function ruta_login_por_contexto(): string
{
    $script = str_replace("\\", "/", $_SERVER["SCRIPT_NAME"] ?? "");

    if (strpos($script, "/views/") !== false) {
        return "login.php";
    }

    return "../../views/login.php";
}
