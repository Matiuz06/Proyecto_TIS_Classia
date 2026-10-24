<?php

require_once __DIR__ . '/../auth/sesion.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/NotificacionRepository.php';

header('Content-Type: application/json; charset=utf-8');
iniciar_sesion();

function responder_notificacion(array $datos, int $estado = 200): void
{
    http_response_code($estado);
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !esta_autenticado()) {
    responder_notificacion(['success' => false, 'message' => 'Solicitud no autorizada.'], 401);
}

$datos = $_POST;
if (str_contains(strtolower((string) ($_SERVER['CONTENT_TYPE'] ?? '')), 'application/json')) {
    $json = json_decode((string) file_get_contents('php://input'), true);
    $datos = is_array($json) ? $json : [];
}

$token = (string) ($datos['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
if (!hash_equals((string) ($_SESSION['csrf_token'] ?? ''), $token)) {
    responder_notificacion(['success' => false, 'message' => 'Token CSRF inválido.'], 419);
}

$id = (int) ($datos['id_notificacion'] ?? 0);
if ($id <= 0) {
    responder_notificacion(['success' => false, 'message' => 'Notificación inválida.'], 422);
}

try {
    $repository = new NotificacionRepository($pdo);
    if (!$repository->marcarComoLeida($id, id_usuario_actual())) {
        responder_notificacion(['success' => false, 'message' => 'Notificación no encontrada.'], 404);
    }
    responder_notificacion(['success' => true, 'unread_count' => $repository->contarNoLeidas(id_usuario_actual())]);
} catch (Throwable $error) {
    error_log('Error al marcar notificación: ' . $error->getMessage());
    responder_notificacion(['success' => false, 'message' => 'No se pudo actualizar la notificación.'], 500);
}
