<?php

/** Crea una preferencia única de Checkout Pro para una contratación existente. */

require_once __DIR__ . '/../auth/sesion.php';
require_once __DIR__ . '/../auth/roles.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/mercado_pago.php';

iniciar_sesion();
requerir_autenticacion('../../views/login.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../views/carrito.php');
    exit;
}

$idContratacion = (int) ($_POST['id_contratacion'] ?? 0);
$tokenRecibido = $_POST['csrf_token'] ?? '';

if (!hash_equals($_SESSION['csrf_token'] ?? '', $tokenRecibido)) {
    $_SESSION['pago_error'] = 'La sesión del formulario expiró. Intentá nuevamente.';
    header('Location: ../../views/pasarela-pago.php?id_contratacion=' . $idContratacion);
    exit;
}

if ($idContratacion <= 0) {
    $_SESSION['carrito_error'] = 'No se encontró la orden a abonar.';
    header('Location: ../../views/carrito.php');
    exit;
}

$usuario = usuario_actual();
$idUsuario = (int) $usuario['id_usuario'];

try {
    $stmt = $pdo->prepare(
        'SELECT c.id_contratacion, c.monto_total, c.estado, u.email
         FROM contrataciones c
         INNER JOIN usuarios u ON u.id_usuario = c.id_usuario
         WHERE c.id_contratacion = :id AND c.id_usuario = :id_usuario
         LIMIT 1'
    );
    $stmt->execute(['id' => $idContratacion, 'id_usuario' => $idUsuario]);
    $contratacion = $stmt->fetch();

    if (!$contratacion) {
        $_SESSION['carrito_error'] = 'No se encontró la contratación especificada.';
        header('Location: ../../views/carrito.php');
        exit;
    }

    if ($contratacion['estado'] !== 'Pendiente') {
        $_SESSION['pago_error'] = 'Esta contratación ya fue procesada o no admite un nuevo pago.';
        header('Location: ../../views/pasarela-pago.php?id_contratacion=' . $idContratacion);
        exit;
    }

    $stmt = $pdo->prepare(
        'SELECT dc.id_publicacion, dc.cantidad, dc.precio_unitario, dc.subtotal, p.titulo, p.tipo
         FROM detalles_contratacion dc
         INNER JOIN publicaciones p ON p.id_publicacion = dc.id_publicacion
         WHERE dc.id_contratacion = :id
         ORDER BY dc.id_detalle ASC'
    );
    $stmt->execute(['id' => $idContratacion]);
    $detalles = $stmt->fetchAll();

    if (!$detalles) {
        throw new RuntimeException('La contratación no contiene publicaciones.');
    }

    $sumaDetalles = 0.0;
    foreach ($detalles as $detalle) {
        $sumaDetalles += (float) $detalle['subtotal'];
    }
    if (round($sumaDetalles, 2) !== round((float) $contratacion['monto_total'], 2)) {
        throw new RuntimeException('El total de la contratación no coincide con sus detalles.');
    }

    $preferencia = mercado_pago_crear_preferencia($detalles, $idContratacion, (string) $contratacion['email']);
    $checkoutUrl = (string) ($preferencia['init_point'] ?? $preferencia['sandbox_init_point'] ?? '');

    if ($checkoutUrl === '' || !filter_var($checkoutUrl, FILTER_VALIDATE_URL)) {
        throw new RuntimeException('Mercado Pago no devolvió una URL de Checkout Pro.');
    }

    header('Location: ' . $checkoutUrl);
    exit;
} catch (Throwable $e) {
    error_log('Error al crear preferencia de Mercado Pago: ' . $e->getMessage());
    $_SESSION['pago_error'] = 'No se pudo iniciar el pago con Mercado Pago. Intentá nuevamente.';
    header('Location: ../../views/pasarela-pago.php?id_contratacion=' . $idContratacion);
    exit;
}
