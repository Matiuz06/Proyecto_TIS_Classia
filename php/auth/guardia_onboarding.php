<?php

require_once __DIR__ . '/sesion.php';

function requerir_onboarding_completo(string $url_onboarding): void
{
    if (!esta_autenticado() || (int) usuario_actual()['id_rol'] !== 1) {
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
    if (in_array($pagina_actual, ['primeros-pasos.php', 'confirmar-correo.php', 'politica-privacidad.php'], true)) {
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
