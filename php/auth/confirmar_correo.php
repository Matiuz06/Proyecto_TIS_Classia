<?php

require_once __DIR__ . '/../../config/database.php';

$token = trim($_GET['token'] ?? '');
$resultado = 'invalido';

if ($token !== '') {
    $stmt = $pdo->prepare(
        'SELECT id_usuario FROM usuarios WHERE email_verificacion_token = :token AND email_verificacion_expira > NOW() LIMIT 1'
    );
    $stmt->execute(['token' => hash('sha256', $token)]);
    $usuario = $stmt->fetch();

    if ($usuario) {
        $actualizar = $pdo->prepare(
            'UPDATE usuarios SET email_verificado = 1, email_verificacion_token = NULL, email_verificacion_expira = NULL WHERE id_usuario = :id'
        );
        $actualizar->execute(['id' => (int) $usuario['id_usuario']]);
        $resultado = 'confirmado';
    }
}

header('Location: ../../views/confirmar-correo.php?resultado=' . $resultado);
exit;
