<?php

require_once __DIR__ . '/../auth/sesion.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/NotificacionRepository.php';

header('Content-Type: application/json; charset=utf-8');
iniciar_sesion();

if (!esta_autenticado()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Sesión requerida.'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $repository = new NotificacionRepository($pdo);
    echo json_encode([
        'success' => true,
        'unread_count' => $repository->contarNoLeidas(id_usuario_actual()),
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $error) {
    error_log('Error al contar notificaciones: ' . $error->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'No se pudo obtener el contador.'], JSON_UNESCAPED_UNICODE);
}
