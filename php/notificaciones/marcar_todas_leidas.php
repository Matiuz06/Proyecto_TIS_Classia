<?php

require_once __DIR__ . '/../auth/sesion.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/NotificacionRepository.php';

header('Content-Type: application/json; charset=utf-8');
iniciar_sesion();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !esta_autenticado()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Solicitud no autorizada.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$datos = $_POST;
if (str_contains(strtolower((string) ($_SERVER['CONTENT_TYPE'] ?? '')), 'application/json')) {
    $json = json_decode((string) file_get_contents('php://input'), true);
    $datos = is_array($json) ? $json : [];
}

$token = (string) ($datos['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
if (!hash_equals((string) ($_SESSION['csrf_token'] ?? ''), $token)) {
    http_response_code(419);
    echo json_encode(['success' => false, 'message' => 'Token CSRF inválido.'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $repository = new NotificacionRepository($pdo);
    $repository->marcarTodasComoLeidas(id_usuario_actual());
    echo json_encode(['success' => true, 'unread_count' => 0], JSON_UNESCAPED_UNICODE);
} catch (Throwable $error) {
    error_log('Error al marcar notificaciones: ' . $error->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'No se pudieron actualizar las notificaciones.'], JSON_UNESCAPED_UNICODE);
}
