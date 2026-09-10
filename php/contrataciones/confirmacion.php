<?php

require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../../config/database.php';

requerir_autenticacion('login.php');

$usuario = usuario_actual();
$id_usuario = (int) $usuario['id_usuario'];
$id_contratacion = filter_input(INPUT_GET, 'id_contratacion', FILTER_VALIDATE_INT);
$contratacion = null;
$detalles = [];
$error = '';

if (!$id_contratacion || $id_contratacion <= 0) {
    $error = 'Contratación inválida.';
} else {
    try {
        $stmt = $pdo->prepare(
            "SELECT id_contratacion, fecha_contratacion, monto_total, estado
             FROM contrataciones
             WHERE id_contratacion = :id_contratacion AND id_usuario = :id_usuario"
        );
        $stmt->execute([
            'id_contratacion' => $id_contratacion,
            'id_usuario' => $id_usuario,
        ]);
        $contratacion = $stmt->fetch();

        if (!$contratacion) {
            $error = 'No tenés permisos para ver esta contratación.';
        } else {
            $stmt = $pdo->prepare(
                "SELECT dc.cantidad, dc.precio_unitario, dc.subtotal, p.titulo, p.tipo, p.imagen
                 FROM detalles_contratacion dc
                 INNER JOIN publicaciones p ON p.id_publicacion = dc.id_publicacion
                 WHERE dc.id_contratacion = :id_contratacion
                 ORDER BY dc.id_detalle ASC"
            );
            $stmt->execute(['id_contratacion' => $id_contratacion]);
            $detalles = $stmt->fetchAll();
        }
    } catch (PDOException $e) {
        error_log('Error al consultar contratación: ' . $e->getMessage());
        $error = 'No se pudo cargar la confirmación.';
    }
}
