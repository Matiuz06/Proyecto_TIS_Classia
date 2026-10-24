<?php

/** Recibe notificaciones de Mercado Pago y actualiza pagos de forma idempotente. */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/mercado_pago.php';

header('Content-Type: application/json; charset=utf-8');

function mercado_pago_webhook_firma_valida(string $paymentId): bool
{
    $secret = trim((string) (getenv('MP_WEBHOOK_SECRET') ?: ''));
    if ($secret === '') {
        error_log('Mercado Pago webhook: falta configurar MP_WEBHOOK_SECRET.');
        throw new RuntimeException('Configuración de webhook ausente.');
    }

    $signatureHeader = trim((string) ($_SERVER['HTTP_X_SIGNATURE'] ?? ''));
    $requestId = trim((string) ($_SERVER['HTTP_X_REQUEST_ID'] ?? ''));
    if ($signatureHeader === '' || $requestId === '') {
        error_log('Mercado Pago webhook: faltan encabezados de firma.');
        return false;
    }

    $parts = [];
    foreach (explode(',', $signatureHeader) as $segment) {
        $separator = strpos($segment, '=');
        if ($separator === false) {
            continue;
        }

        $key = trim(substr($segment, 0, $separator));
        $value = trim(substr($segment, $separator + 1));
        if ($key !== '' && $value !== '') {
            $parts[$key][] = $value;
        }
    }

    $timestamp = $parts['ts'][0] ?? '';
    $signatures = $parts['v1'] ?? [];
    if ($timestamp === '' || $signatures === []) {
        error_log('Mercado Pago webhook: encabezado x-signature incompleto.');
        return false;
    }

    $manifest = 'id:' . $paymentId . ';request-id:' . $requestId . ';ts:' . $timestamp . ';';
    $expected = hash_hmac('sha256', $manifest, $secret);
    foreach ($signatures as $signature) {
        if (hash_equals($expected, $signature)) {
            return true;
        }
    }

    error_log('Mercado Pago webhook: firma inválida.');
    return false;
}

try {
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $paymentId = (string) ($input['data']['id'] ?? $_GET['data.id'] ?? '');
    $type = (string) ($input['type'] ?? $_GET['type'] ?? '');

    if ($paymentId === '' || ($type !== '' && !in_array($type, ['payment', 'merchant_order'], true))) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Notificación inválida.']);
        exit;
    }

    if (!mercado_pago_webhook_firma_valida($paymentId)) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Notificación no autorizada.']);
        exit;
    }

    if ($type === 'merchant_order') {
        echo json_encode(['success' => true]);
        exit;
    }

    $resultado = mercado_pago_confirmar_pago($pdo, mercado_pago_obtener_pago($paymentId));
    if ($resultado['nuevo_aprobado']) {
        require_once __DIR__ . '/comprobante_pago.php';
        try {
            enviar_comprobante_email($pdo, (int) $resultado['id_contratacion']);
        } catch (Throwable $e) {
            error_log('No se pudo enviar el comprobante del webhook: ' . $e->getMessage());
        }
    }

    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    error_log('Error en webhook de Mercado Pago: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'No se pudo procesar la notificación.']);
}
