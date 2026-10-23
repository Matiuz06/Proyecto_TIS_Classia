<?php

$nombreCliente = htmlspecialchars((string) ($nombre_cliente ?? 'Cliente'), ENT_QUOTES, 'UTF-8');
$referencia = htmlspecialchars((string) ($referencia ?? ''), ENT_QUOTES, 'UTF-8');
$fecha = htmlspecialchars((string) ($fecha ?? ''), ENT_QUOTES, 'UTF-8');
$metodo = htmlspecialchars((string) ($metodo ?? 'Tarjeta'), ENT_QUOTES, 'UTF-8');
$total = htmlspecialchars((string) ($total ?? '0,00'), ENT_QUOTES, 'UTF-8');
$items = is_array($items ?? null) ? $items : [];

$filas = '';
foreach ($items as $item) {
    $tituloItem = htmlspecialchars((string) ($item['titulo'] ?? ''), ENT_QUOTES, 'UTF-8');
    $tipo = htmlspecialchars((string) ($item['tipo'] ?? ''), ENT_QUOTES, 'UTF-8');
    $cantidad = (int) ($item['cantidad'] ?? 1);
    $subtotal = htmlspecialchars((string) ($item['subtotal'] ?? '0,00'), ENT_QUOTES, 'UTF-8');
    $filas .= '<tr>'
        . '<td style="padding:10px;border-bottom:1px solid #e2e8f0"><strong>' . $tituloItem . '</strong> <span style="color:#718096;font-size:12px">(' . $tipo . ')</span></td>'
        . '<td style="padding:10px;border-bottom:1px solid #e2e8f0;text-align:center">' . $cantidad . '</td>'
        . '<td style="padding:10px;border-bottom:1px solid #e2e8f0;text-align:right">$' . $subtotal . '</td>'
        . '</tr>';
}

$contenido = '<p>Hola ' . $nombreCliente . ', tu pago fue registrado correctamente.</p>'
    . '<p><strong>Referencia:</strong> ' . $referencia . '<br><strong>Fecha:</strong> ' . $fecha . '<br><strong>Método:</strong> ' . $metodo . '</p>'
    . '<table style="width:100%;border-collapse:collapse;margin:18px 0">'
    . '<thead><tr style="background:#f7fafc"><th style="padding:10px;text-align:left">Ítem</th><th style="padding:10px;text-align:center">Cant.</th><th style="padding:10px;text-align:right">Subtotal</th></tr></thead>'
    . '<tbody>' . $filas . '</tbody>'
    . '<tfoot><tr><td colspan="2" style="padding:14px 10px;text-align:right;font-weight:bold">Total abonado</td><td style="padding:14px 10px;text-align:right;font-weight:bold;color:#146c94">$' . $total . ' UYU</td></tr></tfoot>'
    . '</table>'
    . '<p style="font-size:13px;color:#718096">Este es un comprobante electrónico emitido por Classia.</p>';
$titulo = 'Comprobante de pago - Classia';
require __DIR__ . '/base.php';
