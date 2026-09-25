<?php

/**
 * Responsabilidad: Redirige usuarios según estado de autenticación y onboarding.
 */

require_once __DIR__ . '/sesion.php';

function verificar_estado_usuario_activo(string $url_login = 'login.php'): void
{
    if (!esta_autenticado()) {
        return;
    }

    $usr = usuario_actual();
    $id_usuario = (int) ($usr['id_usuario'] ?? 0);
    if ($id_usuario <= 0) {
        return;
    }

    global $pdo;
    try {
        if (!isset($pdo)) {
            require_once __DIR__ . '/../../config/database.php';
        }
    } catch (Throwable $e) {
        error_log('Error al cargar la conexión para validar usuario activo: ' . $e->getMessage());
        return;
    }

    try {
        $stmt = $pdo->prepare('SELECT activo, motivo_bloqueo FROM usuarios WHERE id_usuario = :id LIMIT 1');
        $stmt->execute(['id' => $id_usuario]);
        $row = $stmt->fetch();

        if ($row && isset($row['activo']) && (int) $row['activo'] === 0) {
            $motivo = !empty($row['motivo_bloqueo']) ? htmlspecialchars($row['motivo_bloqueo']) : '';

            // Destruir sesión completamente y abrir una nueva para pasar el mensaje
            cerrar_sesion();

            // cerrar_sesion() llama session_destroy() que deja el status en PHP_SESSION_NONE
            // Iniciamos manualmente para poder setear la variable de bloqueo
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['cuenta_bloqueada_motivo'] = $motivo;

            // Determinar ruta correcta a la página de bloqueado según la ubicación del script actual
            $script_dir = basename(dirname($_SERVER['SCRIPT_NAME'] ?? ''));
            $bloqueado_url = ($script_dir === 'views')
                ? 'cuenta-bloqueada.php'
                : 'views/cuenta-bloqueada.php';

            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Pragma: no-cache');
            header('Location: ' . $bloqueado_url);
            exit;
        }
    } catch (PDOException $e) {
        error_log('Error al verificar usuario activo: ' . $e->getMessage());
    }
}

function requerir_onboarding_completo(string $url_onboarding): void
{
    if (!esta_autenticado()) {
        return;
    }

    // Verificar bloqueo para TODOS los roles (estudiante, docente, admin)
    $login_url = (basename(dirname($_SERVER['SCRIPT_NAME'] ?? '')) === 'views') ? 'login.php' : 'views/login.php';
    verificar_estado_usuario_activo($login_url);

    // Solo los estudiantes (rol=1) necesitan completar el onboarding
    if ((int) usuario_actual()['id_rol'] !== ROL_ESTUDIANTE) {
        return;
    }

    global $pdo;
    try {
        if (!isset($pdo)) {
            require_once __DIR__ . '/../../config/database.php';
        }
    } catch (Throwable $e) {
        error_log('Error al cargar la conexión para validar onboarding: ' . $e->getMessage());
        return;
    }

    $pagina_actual = basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH));
    if (in_array($pagina_actual, ['primeros-pasos.php', 'confirmar-correo.php', 'politica-privacidad.php', 'cuenta-bloqueada.php'], true)) {
        return;
    }

    try {
        $stmt = $pdo->prepare('SELECT onboarding_step FROM usuarios WHERE id_usuario = :id LIMIT 1');
        $stmt->execute(['id' => (int) usuario_actual()['id_usuario']]);
        $paso = (int) $stmt->fetchColumn();

        if ($paso > 0 && $paso <= 9) {
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Pragma: no-cache');
            header('Location: ' . $url_onboarding);
            exit;
        }
    } catch (PDOException $e) {
        error_log('Error al validar onboarding: ' . $e->getMessage());
    }
}
