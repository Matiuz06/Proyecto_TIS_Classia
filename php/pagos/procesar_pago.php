<?php

require_once __DIR__ . '/../auth/roles.php';

requerir_rol(ROL_ESTUDIANTE, '../../views/usuario.php');

require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../views/carrito.php');
    exit;
}

$usuario = usuario_actual();
$id_usuario = (int) $usuario['id_usuario'];
$id_contratacion = (int) ($_POST['id_contratacion'] ?? 0);
$metodo_pago = $_POST['metodo_pago'] ?? '';
$titular = trim($_POST['titular'] ?? '');
$numero_tarjeta = preg_replace('/\D+/', '', $_POST['numero_tarjeta'] ?? '');
$fecha_expiracion = trim($_POST['fecha_expiracion'] ?? '');
$cvv = preg_replace('/\D+/', '', $_POST['cvv'] ?? '');
$token = $_POST['csrf_token'] ?? '';
$metodos_validos = ['Tarjeta', 'Transferencia', 'MercadoPago', 'Efectivo'];

function volver_pasarela(int $id_contratacion, string $mensaje): void
{
    $_SESSION['pago_error'] = $mensaje;
    header('Location: ../../views/pasarela-pago.php?id_contratacion=' . $id_contratacion);
    exit;
}

if ($id_contratacion <= 0) {
    volver_pasarela(0, 'Contratacion invalida.');
}

if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
    volver_pasarela($id_contratacion, 'La sesion del formulario expiro. Recarga la pagina e intenta nuevamente.');
}

if (!in_array($metodo_pago, $metodos_validos, true)) {
    volver_pasarela($id_contratacion, 'Metodo de pago invalido.');
}

if ($titular === '' || strlen($numero_tarjeta) < 13 || strlen($numero_tarjeta) > 19 || !preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $fecha_expiracion) || strlen($cvv) < 3 || strlen($cvv) > 4) {
    volver_pasarela($id_contratacion, 'Los datos de pago no tienen un formato valido.');
}

try {
    $stmt = $pdo->prepare(
        "SELECT id_contratacion, monto_total, estado
         FROM contrataciones
         WHERE id_contratacion = :id_contratacion AND id_usuario = :id_usuario
         LIMIT 1"
    );
    $stmt->execute([
        'id_contratacion' => $id_contratacion,
        'id_usuario' => $id_usuario,
    ]);
    $contratacion = $stmt->fetch();

    if (!$contratacion) {
        volver_pasarela($id_contratacion, 'La contratacion no existe o no pertenece a tu cuenta.');
    }

    $stmt = $pdo->prepare(
        "SELECT id_pago
         FROM pagos
         WHERE id_contratacion = :id_contratacion AND estado_pago = 'Aprobado'
         LIMIT 1"
    );
    $stmt->execute(['id_contratacion' => $id_contratacion]);

    if ($stmt->fetch()) {
        header('Location: ../../views/confirmacion.php?id_contratacion=' . $id_contratacion);
        exit;
    }

    if ($contratacion['estado'] !== 'Pendiente') {
        volver_pasarela($id_contratacion, 'Esta contratacion no esta pendiente de pago.');
    }

    $referencia = 'CLASSIA-' . date('Ymd') . '-' . bin2hex(random_bytes(4));

    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        "INSERT INTO pagos (monto, metodo_pago, estado_pago, transaccion_ref, id_contratacion)
         VALUES (:monto, :metodo_pago, 'Aprobado', :transaccion_ref, :id_contratacion)"
    );
    $stmt->execute([
        'monto' => (float) $contratacion['monto_total'],
        'metodo_pago' => $metodo_pago,
        'transaccion_ref' => $referencia,
        'id_contratacion' => $id_contratacion,
    ]);

    $stmt = $pdo->prepare(
        "UPDATE contrataciones
         SET estado = 'En Proceso'
         WHERE id_contratacion = :id_contratacion AND estado = 'Pendiente'"
    );
    $stmt->execute(['id_contratacion' => $id_contratacion]);

    $pdo->commit();

    header('Location: ../../views/confirmacion.php?id_contratacion=' . $id_contratacion);
    exit;
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Error al procesar pago: ' . $e->getMessage());
    volver_pasarela($id_contratacion, 'No se pudo procesar el pago.');
}
