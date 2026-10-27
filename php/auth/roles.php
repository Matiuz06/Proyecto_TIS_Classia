<?php

/**
 * Responsabilidad: Define roles y barreras de autorización reutilizables.
 */

require_once __DIR__ . "/sesion.php";

const ROL_ESTUDIANTE = 1;
const ROL_DOCENTE = 2;
const ROL_ADMIN = 3;

/**
 * Comprueba si el usuario autenticado posee un rol determinado.
 *
 * @param int $id_rol Identificador numérico del rol a verificar.
 * @return bool True si el usuario tiene el rol; false si no está autenticado o no coincide.
 */
function tiene_rol(int $id_rol): bool
{
    $usuario = usuario_actual();

    return $usuario !== null && (int) $usuario["id_rol"] === $id_rol;
}

/**
 * Comprueba si el usuario actual posee el rol de estudiante.
 *
 * @return bool True si es estudiante; false en caso contrario.
 */
function es_estudiante(): bool
{
    return tiene_rol(ROL_ESTUDIANTE);
}

/**
 * Comprueba si el usuario actual posee el rol de docente.
 *
 * @return bool True si es docente; false en caso contrario.
 */
function es_docente(): bool
{
    return tiene_rol(ROL_DOCENTE);
}

/**
 * Comprueba si el usuario actual posee el rol de administrador.
 *
 * @return bool True si es administrador; false en caso contrario.
 */
function es_admin(): bool
{
    return tiene_rol(ROL_ADMIN);
}

/**
 * Exige que el usuario posea un rol específico; si no, redirige con mensaje de advertencia.
 *
 * @param int    $id_rol    Identificador del rol obligatorio.
 * @param string $redirigir Ruta de redirección en caso de permiso denegado.
 * @return void
 */
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

/**
 * Exige que el usuario posea al menos uno de los roles autorizados pasados en el listado.
 *
 * @param array  $roles     Lista de identificadores de roles permitidos.
 * @param string $redirigir Ruta de redirección en caso de no contar con ningún rol autorizado.
 * @return void
 */
function requerir_cualquier_rol(array $roles, string $redirigir): void
{
    if (!esta_autenticado()) {
        header("Location: " . ruta_login_por_contexto());
        exit;
    }

    foreach ($roles as $rol) {
        if (tiene_rol((int) $rol)) {
            return;
        }
    }

    $_SESSION["mensaje_acceso"] = "No tenes permisos para acceder a esta seccion.";
    header("Location: " . $redirigir);
    exit;
}

/**
 * Calcula la ruta relativa adecuada hacia la vista de login según el contexto del script actual.
 *
 * @return string Ruta relativa adecuada al formulario de autenticación.
 */
function ruta_login_por_contexto(): string
{
    $script = str_replace("\\", "/", $_SERVER["SCRIPT_NAME"] ?? "");

    if (strpos($script, "/views/") !== false) {
        return "login.php";
    }

    return "../../views/login.php";
}

/**
 * Retorna el nombre legible descriptivo correspondiente a un rol de usuario.
 *
 * @param int $id_rol Identificador numérico del rol.
 * @return string Etiqueta textual representativa del rol.
 */
function nombre_rol(int $id_rol): string
{
    $nombres = [
        ROL_ESTUDIANTE => 'Estudiante / Cliente',
        ROL_DOCENTE    => 'Docente / Proveedor',
        ROL_ADMIN      => 'Administrador',
    ];

    return $nombres[$id_rol] ?? 'Usuario';
}
