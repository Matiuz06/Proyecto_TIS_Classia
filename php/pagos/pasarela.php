<?php

require_once __DIR__ . '/../auth/roles.php';
require_once __DIR__ . '/../../config/database.php';

requerir_autenticacion('../../views/login.php');

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$currentUser = usuario_actual();
$id_usuario = (int) $currentUser['id_usuario'];
$id_contratacion = isset($_GET['id_contratacion']) ? (int) $_GET['id_contratacion'] : 0;
$monto_total = 0.0;
$contratacion = null;
$detalles = [];

if ($id_contratacion > 0) {
    try {
        $stmt = $pdo->prepare('SELECT * FROM contrataciones WHERE id_contratacion = :id AND id_usuario = :user LIMIT 1');
        $stmt->execute(['id' => $id_contratacion, 'user' => $id_usuario]);
        $contratacion = $stmt->fetch();

        if ($contratacion) {
            $monto_total = (float) $contratacion['monto_total'];
            $stmt_det = $pdo->prepare(
                'SELECT dc.*, p.titulo, p.tipo FROM detalles_contratacion dc '
                . 'JOIN publicaciones p ON dc.id_publicacion = p.id_publicacion '
                . 'WHERE dc.id_contratacion = :id'
            );
            $stmt_det->execute(['id' => $id_contratacion]);
            $detalles = $stmt_det->fetchAll();
        }
    } catch (PDOException $e) {
        error_log('Error al consultar contratacion para pago: ' . $e->getMessage());
    }
}

$mensaje_error = $_SESSION['pago_error'] ?? '';
unset($_SESSION['pago_error']);
