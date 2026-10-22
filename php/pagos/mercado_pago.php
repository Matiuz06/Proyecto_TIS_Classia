<?php

/**
 * Integración mínima con Checkout Pro mediante la API HTTP oficial.
 * No requiere agregar otra dependencia al proyecto.
 */

require_once __DIR__ . '/../notificaciones/NotificacionService.php';

function mercado_pago_notificar_servicio_contratado(PDO $pdo, int $idContratacion): void
{
    try {
        $stmt = $pdo->prepare(
            'SELECT s.id_solicitud, p.id_usuario AS id_proveedor, p.titulo AS servicio
             FROM solicitudes s
             INNER JOIN publicaciones p ON p.id_publicacion = s.id_publicacion
             WHERE s.id_contratacion = :id AND p.tipo = \'Servicio\'
             LIMIT 1'
        );
        $stmt->execute(['id' => $idContratacion]);
        $solicitud = $stmt->fetch();

        if (!$solicitud || (int) $solicitud['id_proveedor'] <= 0) {
            return;
        }

        $notificaciones = new NotificacionService(new NotificacionRepository($pdo));
        $notificaciones->crear([
            'id_usuario' => (int) $solicitud['id_proveedor'],
            'categoria' => 'solicitudes',
            'titulo' => 'Servicio contratado',
            'mensaje' => 'Se confirmó el pago del servicio "' . $solicitud['servicio'] . '". Ya podés comenzar a gestionarlo.',
            'url_destino' => 'views/solicitud-servicio-detalle.php?id=' . (int) $solicitud['id_solicitud'],
            'tipo_entidad' => 'solicitud_servicio',
            'id_entidad' => (int) $solicitud['id_solicitud'],
            'clave_idempotencia' => 'servicio-contratado:' . $idContratacion,
        ]);
    } catch (Throwable $e) {
        error_log('Notificacion de servicio contratado ' . $idContratacion . ': ' . $e->getMessage());
    }
}

function mercado_pago_request(string $method, string $url, ?array $payload = null): array
{
    $accessToken = getenv('MP_ACCESS_TOKEN') ?: '';
    if ($accessToken === '') {
        throw new RuntimeException('Falta la configuración de Mercado Pago.');
    }

    $ch = curl_init($url);
    if ($ch === false) {
        throw new RuntimeException('No se pudo iniciar la conexión con Mercado Pago.');
    }

    $headers = [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json',
        'Accept: application/json',
    ];

    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST => strtoupper($method),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 20,
    ]);

    if ($payload !== null) {
        $encodedPayload = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($encodedPayload === false) {
            error_log('Mercado Pago JSON error: ' . json_last_error_msg());
            throw new RuntimeException('No se pudo preparar la solicitud de Mercado Pago.');
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedPayload);
    }

    $raw = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    $errorNumber = curl_errno($ch);
    curl_close($ch);

    if ($raw === false || $error !== '') {
        error_log('Mercado Pago cURL error ' . $errorNumber . ': ' . ($error !== '' ? $error : 'respuesta vacía'));
        throw new RuntimeException('No se pudo conectar con Mercado Pago.');
    }

    $data = json_decode($raw, true);
    if (!is_array($data)) {
        error_log('Mercado Pago HTTP ' . $status . ': respuesta JSON inválida (' . json_last_error_msg() . ').');
        throw new RuntimeException('Mercado Pago devolvió una respuesta inválida.');
    }

    if ($status < 200 || $status >= 300) {
        $errorCode = (string) ($data['error'] ?? $data['code'] ?? 'sin código');
        $errorMessage = (string) ($data['message'] ?? 'respuesta de error');
        error_log('Mercado Pago HTTP ' . $status . ' [' . $errorCode . ']: ' . $errorMessage);
        throw new RuntimeException('Mercado Pago no pudo procesar la operación.');
    }

    return $data;
}

function mercado_pago_base_url(): string
{
    $baseUrl = rtrim((string) (getenv('APP_URL') ?: ''), '/');
    if ($baseUrl === '') {
        throw new RuntimeException('Falta la configuración de APP_URL.');
    }

    $configuredHost = strtolower((string) (parse_url($baseUrl, PHP_URL_HOST) ?: ''));
    $host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
    $isLocalConfiguredUrl = preg_match('/^(localhost|127\.0\.0\.1)$/', $configuredHost) === 1;
    if ($isLocalConfiguredUrl && $host !== '' && preg_match('/^(localhost|127\.0\.0\.1)(:\d+)?$/', $host)) {
        $path = (string) (parse_url($baseUrl, PHP_URL_PATH) ?: '');
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        return $scheme . '://' . $host . '/' . trim($path, '/');
    }

    return $baseUrl;
}

function mercado_pago_crear_preferencia(array $detalles, int $idContratacion, string $email = ''): array
{
    $items = [];
    foreach ($detalles as $detalle) {
        $items[] = [
            'id' => (string) $detalle['id_publicacion'],
            'title' => (string) $detalle['titulo'],
            'description' => (string) ($detalle['tipo'] ?? 'Publicación Classia'),
            'quantity' => (int) $detalle['cantidad'],
            'currency_id' => 'UYU',
            'unit_price' => round((float) $detalle['precio_unitario'], 2),
        ];
    }

    $baseUrl = mercado_pago_base_url();
    $returnUrl = $baseUrl . '/views/confirmacion.php?id_contratacion=' . $idContratacion;

    $payload = [
        'items' => $items,
        'external_reference' => (string) $idContratacion,
        'back_urls' => [
            'success' => $returnUrl,
            'pending' => $returnUrl,
            'failure' => $returnUrl,
        ],
        'notification_url' => $baseUrl . '/php/pagos/mercado_pago_webhook.php',
    ];

    // Mercado Pago rechaza el retorno automático cuando las URLs son localhost.
    if (preg_match('/^https?:\/\/(localhost|127\.0\.0\.1)(:\d+)?(?:\/|$)/i', $baseUrl) !== 1) {
        $payload['auto_return'] = 'approved';
    }

    if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $payload['payer'] = ['email' => $email];
    }

    return mercado_pago_request('POST', 'https://api.mercadopago.com/checkout/preferences', $payload);
}

function mercado_pago_obtener_pago(string $paymentId): array
{
    if (!preg_match('/^[0-9]+$/', $paymentId)) {
        throw new RuntimeException('Identificador de pago inválido.');
    }

    return mercado_pago_request('GET', 'https://api.mercadopago.com/v1/payments/' . $paymentId);
}

function mercado_pago_estado_local(string $status): string
{
    return match (strtolower($status)) {
        'approved' => 'Aprobado',
        'pending', 'in_process', 'in_mediation' => 'Pendiente',
        'rejected', 'cancelled' => 'Rechazado',
        default => 'Pendiente',
    };
}

function mercado_pago_confirmar_pago(PDO $pdo, array $payment): array
{
    $paymentId = (string) ($payment['id'] ?? '');
    $externalReference = (string) ($payment['external_reference'] ?? '');
    $amount = round((float) ($payment['transaction_amount'] ?? 0), 2);
    $currency = (string) ($payment['currency_id'] ?? 'UYU');
    $estado = mercado_pago_estado_local((string) ($payment['status'] ?? 'pending'));
    $idContratacion = filter_var($externalReference, FILTER_VALIDATE_INT);

    if ($paymentId === '' || !$idContratacion || $amount <= 0 || $currency !== 'UYU') {
        throw new RuntimeException('La respuesta de pago no contiene una referencia válida.');
    }

    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare(
        'SELECT id_contratacion, id_usuario, monto_total, estado
         FROM contrataciones
         WHERE id_contratacion = :id
         LIMIT 1
         FOR UPDATE'
        );
        $stmt->execute(['id' => $idContratacion]);
        $contratacion = $stmt->fetch();

        if (!$contratacion || round((float) $contratacion['monto_total'], 2) !== $amount) {
            throw new RuntimeException('El monto o la contratación no coinciden.');
        }

        $stmt = $pdo->prepare('SELECT id_pago, id_contratacion, estado_pago FROM pagos WHERE transaccion_ref = :ref LIMIT 1');
        $stmt->execute(['ref' => $paymentId]);
        $pagoExistente = $stmt->fetch();
        if ($pagoExistente && (int) $pagoExistente['id_contratacion'] !== $idContratacion) {
            throw new RuntimeException('El pago ya está asociado a otra contratación.');
        }
        $eraAprobado = $pagoExistente && $pagoExistente['estado_pago'] === 'Aprobado';
        $estadoPersistido = $eraAprobado ? 'Aprobado' : $estado;

        if ($pagoExistente) {
            $stmt = $pdo->prepare(
                'UPDATE pagos
                 SET monto = :monto, metodo_pago = \'MercadoPago\', estado_pago = :estado, fecha_pago = CURRENT_TIMESTAMP
                 WHERE id_pago = :id_pago'
            );
            $stmt->execute([
                'monto' => $amount,
                'estado' => $estadoPersistido,
                'id_pago' => $pagoExistente['id_pago'],
            ]);
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO pagos (monto, metodo_pago, estado_pago, fecha_pago, transaccion_ref, id_contratacion)
                 VALUES (:monto, \'MercadoPago\', :estado, CURRENT_TIMESTAMP, :ref, :id_contratacion)'
            );
            $stmt->execute([
                'monto' => $amount,
                'estado' => $estadoPersistido,
                'ref' => $paymentId,
                'id_contratacion' => $idContratacion,
            ]);
        }

        if ($estadoPersistido === 'Aprobado') {
            $stmt = $pdo->prepare("UPDATE contrataciones SET estado = 'En Proceso' WHERE id_contratacion = :id AND estado <> 'En Proceso'");
            $stmt->execute(['id' => $idContratacion]);
            $pdo->prepare("UPDATE solicitudes SET estado = 'En Proceso' WHERE id_contratacion = :id AND estado = 'Aceptada'")
                ->execute(['id' => $idContratacion]);
        }

        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }

    if ($estadoPersistido === 'Aprobado') {
        mercado_pago_notificar_servicio_contratado($pdo, $idContratacion);
    }

    return [
        'id_contratacion' => $idContratacion,
        'estado_pago' => $estadoPersistido,
        'id_pago' => $paymentId,
        'nuevo_aprobado' => $estadoPersistido === 'Aprobado' && !$eraAprobado,
    ];
}
