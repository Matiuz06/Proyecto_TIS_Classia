<?php

require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../../config/database.php';

iniciar_sesion();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token_recibido = $_POST['csrf_token'] ?? '';
    $accion = $_POST['accion'] ?? '';
    $id_publicacion = (int) ($_POST['id_publicacion'] ?? 0);

    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token_recibido)) {
        $_SESSION['carrito_error'] = 'La sesión del formulario expiró. Recargá la página e intentá nuevamente.';
    } elseif ($accion === 'agregar' && $id_publicacion > 0) {
        $_SESSION['carrito_publicaciones'] = $_SESSION['carrito_publicaciones'] ?? [];
        if (!in_array($id_publicacion, $_SESSION['carrito_publicaciones'], true)) {
            $_SESSION['carrito_publicaciones'][] = $id_publicacion;
        }
        $destino = !empty($_POST['redirect']) ? $_POST['redirect'] : 'carrito.php';
        header("Location: " . $destino);
        exit;
    } elseif ($accion === 'quitar' && $id_publicacion > 0) {
        $_SESSION['carrito_publicaciones'] = array_values(array_filter(
            $_SESSION['carrito_publicaciones'] ?? [],
            fn($id) => (int) $id !== $id_publicacion
        ));
    } elseif ($accion === 'vaciar') {
        unset($_SESSION['carrito_publicaciones']);
    }
}

$mensaje_error = $_SESSION['carrito_error'] ?? '';
unset($_SESSION['carrito_error']);

$ids_carrito = array_values(array_unique(array_map('intval', $_SESSION['carrito_publicaciones'] ?? [])));
$items = [];
$total = 0;

if (!empty($ids_carrito)) {
    try {
        $stmt = $pdo->prepare(
            "SELECT id_publicacion, titulo, precio, tipo, estado, imagen
             FROM publicaciones
             WHERE id_publicacion = :id_publicacion"
        );

        foreach ($ids_carrito as $id_publicacion) {
            $stmt->execute(['id_publicacion' => $id_publicacion]);
            $item = $stmt->fetch();

            if ($item && $item['estado'] === 'Activo') {
                $item['cantidad'] = 1;
                $item['subtotal'] = (float) $item['precio'];
                $items[] = $item;
                $total += (float) $item['subtotal'];
            }
        }
    } catch (PDOException $e) {
        error_log('Error al consultar publicaciones del carrito: ' . $e->getMessage());
        $mensaje_error = 'Error al cargar los elementos del carrito.';
    }
}
