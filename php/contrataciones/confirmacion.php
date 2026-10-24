<?php

/** Consulta y confirma el estado real de una contratación al volver de Mercado Pago. */

require_once __DIR__ . '/../auth/sesion.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../pagos/mercado_pago.php';

requerir_autenticacion('login.php');

$usuario = usuario_actual();
$idUsuario = (int) $usuario['id_usuario'];
$id_contratacion = filter_input(INPUT_GET, 'id_contratacion', FILTER_VALIDATE_INT);
$contratacion = null;
$detalles = [];
$pago = null;
$error = '';
$mensaje_pago = '';

if (!$id_contratacion || $id_contratacion <= 0) {
    $error = 'Contratación inválida.';
} else {
    try {
        $stmt = $pdo->prepare(
            'SELECT id_contratacion, fecha_contratacion, monto_total, estado
             FROM contrataciones
             WHERE id_contratacion = :id_contratacion AND id_usuario = :id_usuario'
        );
        $stmt->execute([
            'id_contratacion' => $id_contratacion,
            'id_usuario' => $idUsuario,
        ]);
        $contratacion = $stmt->fetch();

        if (!$contratacion) {
            $error = 'No tenés permisos para ver esta contratación.';
        } else {
            $paymentId = trim((string) ($_GET['payment_id'] ?? ''));
            if ($paymentId !== '') {
                try {
                    $resultado = mercado_pago_confirmar_pago($pdo, mercado_pago_obtener_pago($paymentId));
                    if ((int) $resultado['id_contratacion'] !== $id_contratacion) {
                        throw new RuntimeException('La referencia de pago no coincide con la contratación.');
                    }
                    $mensaje_pago = $resultado['estado_pago'];
                    if ($resultado['estado_pago'] === 'Aprobado') {
                        // El webhook puede haber confirmado antes el pago; limpiar el carrito sigue siendo idempotente.
                        unset($_SESSION['carrito_publicaciones']);
                    }
                    if ($resultado['nuevo_aprobado']) {
                        require_once __DIR__ . '/../pagos/comprobante_pago.php';
                        try {
                            enviar_comprobante_email($pdo, $id_contratacion);
                        } catch (Throwable $e) {
                            error_log('No se pudo enviar el comprobante de Mercado Pago: ' . $e->getMessage());
                        }
                    }
                } catch (Throwable $e) {
                    error_log('Error al confirmar retorno de Mercado Pago: ' . $e->getMessage());
                    $mensaje_pago = 'Pendiente';
                }
            }

            $stmt = $pdo->prepare(
                'SELECT id_pago, monto, metodo_pago, estado_pago, fecha_pago, transaccion_ref
                 FROM pagos
                 WHERE id_contratacion = :id_contratacion
                 ORDER BY id_pago DESC
                 LIMIT 1'
            );
            $stmt->execute(['id_contratacion' => $id_contratacion]);
            $pago = $stmt->fetch() ?: null;

            $stmt = $pdo->prepare(
                'SELECT dc.cantidad, dc.precio_unitario, dc.subtotal, p.titulo, p.tipo, p.imagen
                 FROM detalles_contratacion dc
                 INNER JOIN publicaciones p ON p.id_publicacion = dc.id_publicacion
                 WHERE dc.id_contratacion = :id_contratacion
                 ORDER BY dc.id_detalle ASC'
            );
            $stmt->execute(['id_contratacion' => $id_contratacion]);
            $detalles = $stmt->fetchAll();

            if ($mensaje_pago === '') {
                $mensaje_pago = $pago['estado_pago'] ?? 'Pendiente';
            }

            $stmt = $pdo->prepare(
                'SELECT id_contratacion, fecha_contratacion, monto_total, estado
                 FROM contrataciones
                 WHERE id_contratacion = :id_contratacion AND id_usuario = :id_usuario'
            );
            $stmt->execute(['id_contratacion' => $id_contratacion, 'id_usuario' => $idUsuario]);
            $contratacion = $stmt->fetch() ?: $contratacion;
        }
    } catch (PDOException $e) {
        error_log('Error al consultar contratación: ' . $e->getMessage());
        $error = 'No se pudo cargar la confirmación.';
    }
}
