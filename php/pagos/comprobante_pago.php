<?php

require_once __DIR__ . '/../auth/sesion.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../utils/mailer.php';

function obtener_datos_comprobante(PDO $pdo, int $id_contratacion, int $id_usuario = 0): ?array
{
    $sql = "
        SELECT 
            c.id_contratacion,
            c.fecha_contratacion,
            c.monto_total,
            c.estado AS estado_contratacion,
            u.id_usuario,
            u.nombre,
            u.apellido,
            u.email,
            p.id_pago,
            p.monto AS monto_pago,
            p.metodo_pago,
            p.estado_pago,
            p.fecha_pago,
            p.transaccion_ref
        FROM contrataciones c
        INNER JOIN usuarios u ON u.id_usuario = c.id_usuario
        LEFT JOIN pagos p ON p.id_contratacion = c.id_contratacion
        WHERE c.id_contratacion = :id_contratacion
    ";

    $params = ['id_contratacion' => $id_contratacion];
    if ($id_usuario > 0) {
        $sql .= " AND c.id_usuario = :id_usuario";
        $params['id_usuario'] = $id_usuario;
    }

    $sql .= " ORDER BY p.id_pago DESC LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $info = $stmt->fetch();

    if (!$info) {
        return null;
    }

    $stmt_det = $pdo->prepare("
        SELECT dc.cantidad, dc.precio_unitario, dc.subtotal, pub.titulo, pub.tipo
        FROM detalles_contratacion dc
        INNER JOIN publicaciones pub ON pub.id_publicacion = dc.id_publicacion
        WHERE dc.id_contratacion = :id_contratacion
        ORDER BY dc.id_detalle ASC
    ");
    $stmt_det->execute(['id_contratacion' => $id_contratacion]);
    $detalles = $stmt_det->fetchAll();

    $info['detalles'] = $detalles;
    return $info;
}

function renderizar_html_comprobante_email(array $data): string
{
    $nombreCliente = htmlspecialchars(trim($data['nombre'] . ' ' . $data['apellido']));
    $ref = htmlspecialchars($data['transaccion_ref'] ?? ('ORD-' . $data['id_contratacion']));
    $fecha = date('d/m/Y H:i', strtotime($data['fecha_pago'] ?? $data['fecha_contratacion']));
    $total = number_format((float) $data['monto_total'], 2, ',', '.');
    $metodo = htmlspecialchars($data['metodo_pago'] ?? 'Tarjeta');

    $filas = '';
    foreach ($data['detalles'] as $item) {
        $tit = htmlspecialchars($item['titulo']);
        $tipo = htmlspecialchars($item['tipo']);
        $cant = (int) $item['cantidad'];
        $sub = number_format((float) $item['subtotal'], 2, ',', '.');
        $filas .= "
            <tr>
                <td style='padding: 10px; border-bottom: 1px solid #e2e8f0; font-size: 14px;'>
                    <strong>{$tit}</strong> <span style='color: #718096; font-size: 12px;'>({$tipo})</span>
                </td>
                <td style='padding: 10px; border-bottom: 1px solid #e2e8f0; font-size: 14px; text-align: center;'>{$cant}</td>
                <td style='padding: 10px; border-bottom: 1px solid #e2e8f0; font-size: 14px; text-align: right;'>\${$sub}</td>
            </tr>
        ";
    }

    return "
    <!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <title>Comprobante de Pago — Classia</title>
    </head>
    <body style='font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, sans-serif; background-color: #f7fafc; margin: 0; padding: 24px;'>
        <div style='max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; border: 1px solid #e2e8f0; overflow: hidden;'>
            <div style='padding: 24px; border-bottom: 2px solid #2b6cb0; background: #ebf8ff;'>
                <h1 style='margin: 0; font-size: 20px; color: #2b6cb0;'>Classia · Comprobante de Pago</h1>
                <p style='margin: 4px 0 0; font-size: 13px; color: #4a5568;'>Transacción aprobada y registrada exitosamente.</p>
            </div>
            
            <div style='padding: 24px;'>
                <table style='width: 100%; font-size: 13px; color: #4a5568; margin-bottom: 20px;'>
                    <tr>
                        <td style='padding: 4px 0;'><strong>N.º Transacción:</strong> {$ref}</td>
                        <td style='padding: 4px 0; text-align: right;'><strong>Fecha:</strong> {$fecha}</td>
                    </tr>
                    <tr>
                        <td style='padding: 4px 0;'><strong>Cliente:</strong> {$nombreCliente}</td>
                        <td style='padding: 4px 0; text-align: right;'><strong>Método:</strong> {$metodo}</td>
                    </tr>
                </table>

                <table style='width: 100%; border-collapse: collapse; margin-bottom: 24px;'>
                    <thead>
                        <tr style='background: #f7fafc; color: #2d3748; font-size: 12px; text-transform: uppercase;'>
                            <th style='padding: 10px; text-align: left; border-bottom: 2px solid #cbd5e0;'>Ítem</th>
                            <th style='padding: 10px; text-align: center; border-bottom: 2px solid #cbd5e0;'>Cant.</th>
                            <th style='padding: 10px; text-align: right; border-bottom: 2px solid #cbd5e0;'>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        {$filas}
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan='2' style='padding: 14px 10px; font-weight: bold; font-size: 16px; text-align: right; color: #2d3748;'>Total Abonado:</td>
                            <td style='padding: 14px 10px; font-weight: bold; font-size: 18px; text-align: right; color: #2b6cb0;'>\${$total} UYU</td>
                        </tr>
                    </tfoot>
                </table>

                <div style='padding: 12px 16px; background: #edf2f7; border-radius: 6px; font-size: 12px; color: #718096; text-align: center;'>
                    Este es un comprobante electrónico emitido por Classia (AniTech). Podés acceder a tus cursos y servicios directamente desde tu perfil.
                </div>
            </div>
        </div>
    </body>
    </html>
    ";
}

function enviar_comprobante_email(PDO $pdo, int $id_contratacion, string $destinatario = ''): bool
{
    $data = obtener_datos_comprobante($pdo, $id_contratacion);
    if (!$data) {
        return false;
    }

    $email = $destinatario !== '' ? $destinatario : ($data['email'] ?? '');
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $asunto = 'Comprobante de Pago — Classia (Orden #' . $data['id_contratacion'] . ')';
    $html = renderizar_html_comprobante_email($data);

    return enviar_correo($email, $asunto, $html);
}
