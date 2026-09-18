<?php

require_once __DIR__ . '/sesion.php';
require_once __DIR__ . '/../../config/database.php';

iniciar_sesion();

$token = trim($_GET['token'] ?? '');
$resultado = 'invalido';

if ($token !== '') {
    $token_hash = hash('sha256', $token);
    try {
        $stmt = $pdo->prepare(
            'SELECT id_usuario, nuevo_email_pendiente FROM usuarios
             WHERE nuevo_email_token = :token AND nuevo_email_expira > NOW() LIMIT 1'
        );
        $stmt->execute(['token' => $token_hash]);
        $usuario = $stmt->fetch();

        if ($usuario && !empty($usuario['nuevo_email_pendiente'])) {
            $nuevo_email = $usuario['nuevo_email_pendiente'];
            $id_usuario  = (int) $usuario['id_usuario'];

            // Verificar que el nuevo email no esté ya en uso por otro
            $stmt_check = $pdo->prepare('SELECT id_usuario FROM usuarios WHERE email = :email AND id_usuario != :id LIMIT 1');
            $stmt_check->execute(['email' => $nuevo_email, 'id' => $id_usuario]);
            if ($stmt_check->fetch()) {
                $resultado = 'duplicado';
            } else {
                $pdo->prepare(
                    'UPDATE usuarios
                     SET email = :email,
                         email_verificado = 1,
                         nuevo_email_pendiente = NULL,
                         nuevo_email_token = NULL,
                         nuevo_email_expira = NULL
                     WHERE id_usuario = :id'
                )->execute(['email' => $nuevo_email, 'id' => $id_usuario]);

                // Si el usuario está en sesión, actualizar email en sesión
                if (esta_autenticado() && id_usuario_actual() === $id_usuario) {
                    $_SESSION['usuario']['email'] = $nuevo_email;
                }

                $resultado = 'confirmado';
            }
        }
    } catch (PDOException $e) {
        error_log('Error al confirmar nuevo email: ' . $e->getMessage());
        $resultado = 'error';
    }
}

$msgs = [
    'confirmado' => 'Tu nuevo email fue confirmado y actualizado con éxito.',
    'invalido'   => 'El enlace de verificación no es válido o ya expiró.',
    'duplicado'  => 'El email ya está registrado por otra cuenta.',
    'error'      => 'Ocurrió un error al procesar la confirmación.',
];
$_SESSION['exito_perfil']  = $resultado === 'confirmado' ? ($msgs[$resultado] ?? '') : null;
$_SESSION['error_perfil']  = $resultado !== 'confirmado' ? ($msgs[$resultado] ?? 'Error desconocido.') : null;

header('Location: ../../views/editar-perfil.php');
exit;
