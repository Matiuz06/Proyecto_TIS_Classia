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
    $idUsuario = id_usuario_actual();
    $limite = (int) ($_GET['limite'] ?? 10);
    $pagina = (int) ($_GET['pagina'] ?? 1);
    $historial = $repository->historial($idUsuario, $pagina, $limite);

    echo json_encode([
        'success' => true,
        'items' => $historial['items'],
        'unread_count' => $repository->contarNoLeidas($idUsuario),
        'pagination' => [
            'page' => $historial['pagina'],
            'per_page' => $historial['por_pagina'],
            'total' => $historial['total'],
        ],
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $error) {
    error_log('Error al obtener notificaciones: ' . $error->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'No se pudieron obtener las notificaciones.'], JSON_UNESCAPED_UNICODE);
}
