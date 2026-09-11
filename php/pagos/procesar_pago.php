<?php

require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../auth/roles.php';
require_once __DIR__ . '/../../config/database.php';

iniciar_sesion();
requerir_autenticacion('../../views/login.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../views/carrito.php');
    exit;
}

$token_recibido = $_POST['csrf_token'] ?? '';
if (!hash_equals($_SESSION['csrf_token'] ?? '', $token_recibido)) {
    $_SESSION['pago_error'] = 'La sesión del formulario expiró. Intentá nuevamente.';
    $id_c = (int) ($_POST['id_contratacion'] ?? 0);
    header('Location: ../../views/pasarela-pago.php' . ($id_c > 0 ? "?id_contratacion=$id_c" : ''));
    exit;
}

$usuario = usuario_actual();
$id_usuario = (int) $usuario['id_usuario'];
$id_contratacion = (int) ($_POST['id_contratacion'] ?? 0);
$email = trim($_POST['email'] ?? '');
$numero_tarjeta = preg_replace('/\s+/', '', $_POST['numero_tarjeta'] ?? '');
$fecha_exp = trim($_POST['fecha_expiracion'] ?? '');
$cvv = trim($_POST['cvv'] ?? '');

if ($id_contratacion <= 0) {
    $_SESSION['carrito_error'] = 'No se encontró la orden a abonar.';
    header('Location: ../../views/carrito.php');
    exit;
}

if ($numero_tarjeta === '' || !preg_match('/^[0-9]{13,19}$/', $numero_tarjeta)) {
    $_SESSION['pago_error'] = 'El número de tarjeta es inválido. Debe contener entre 13 y 19 dígitos numéricos.';
    header("Location: ../../views/pasarela-pago.php?id_contratacion=$id_contratacion");
    exit;
}

if (!preg_match('/^(0[1-9]|1[0-2])\/([0-9]{2})$/', $fecha_exp, $matches)) {
    $_SESSION['pago_error'] = 'La fecha de vencimiento debe tener formato MM/AA.';
    header("Location: ../../views/pasarela-pago.php?id_contratacion=$id_contratacion");
    exit;
}

$mes_exp = (int) $matches[1];
$ano_exp = 2000 + (int) $matches[2];
$mes_actual = (int) date('m');
$ano_actual = (int) date('Y');

if ($ano_exp < $ano_actual || ($ano_exp === $ano_actual && $mes_exp < $mes_actual)) {
    $_SESSION['pago_error'] = 'La tarjeta ingresada se encuentra vencida.';
    header("Location: ../../views/pasarela-pago.php?id_contratacion=$id_contratacion");
    exit;
}

if (!preg_match('/^[0-9]{3,4}$/', $cvv)) {
    $_SESSION['pago_error'] = 'El código de seguridad (CVV) debe tener 3 o 4 dígitos numéricos.';
    header("Location: ../../views/pasarela-pago.php?id_contratacion=$id_contratacion");
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id_contratacion, monto_total, estado FROM contrataciones WHERE id_contratacion = :id AND id_usuario = :id_usuario LIMIT 1");
    $stmt->execute(['id' => $id_contratacion, 'id_usuario' => $id_usuario]);
    $contratacion = $stmt->fetch();

    if (!$contratacion) {
        $_SESSION['carrito_error'] = 'No se encontró la contratación especificada.';
        header('Location: ../../views/carrito.php');
        exit;
    }

    $pdo->beginTransaction();

    // Estándar PCI-DSS: NO guardar números de tarjeta completos ni CVV en la BD.
    // Solo guardamos referencia enmascarada tokenizada (ej: CARD-****-1234)
    $ultimos4 = substr($numero_tarjeta, -4);
    $token_transaccion = 'CARD-AUTH-****-' . $ultimos4 . '-' . strtoupper(bin2hex(random_bytes(3)));

    $stmt_pago = $pdo->prepare("
        INSERT INTO pagos (monto, metodo_pago, estado_pago, fecha_pago, transaccion_ref, id_contratacion)
        VALUES (:monto, 'Tarjeta', 'Aprobado', CURRENT_TIMESTAMP, :transaccion_ref, :id_contratacion)
    ");
    $stmt_pago->execute([
        'monto'            => $contratacion['monto_total'],
        'transaccion_ref'  => $token_transaccion,
        'id_contratacion'  => $id_contratacion,
    ]);

    $stmt_up_con = $pdo->prepare("UPDATE contrataciones SET estado = 'Completada' WHERE id_contratacion = :id");
    $stmt_up_con->execute(['id' => $id_contratacion]);

    $pdo->commit();

    // Limpiar carrito tras pago exitoso
    unset($_SESSION['carrito_publicaciones']);

    header("Location: ../../views/confirmacion.php?id_contratacion=$id_contratacion");
    exit;
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Error al procesar pago: " . $e->getMessage());
    $_SESSION['pago_error'] = 'Ocurrió un error al procesar la transacción de pago.';
    header("Location: ../../views/pasarela-pago.php?id_contratacion=$id_contratacion");
    exit;
}
