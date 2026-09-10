<?php

require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../utils/upload_helper.php';

iniciar_sesion();
requerir_autenticacion('../../views/login.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../views/usuario.php');
    exit;
}

$token_recibido = $_POST['csrf_token'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token_recibido)) {
    $_SESSION['error_perfil'] = 'La sesión del formulario expiró. Por favor, intentá nuevamente.';
    header('Location: ../../views/usuario.php');
    exit;
}

$usuario_sesion = usuario_actual();
$id_usuario = (int) ($usuario_sesion['id_usuario'] ?? 0);
$accion = $_POST['accion'] ?? 'subir';

try {
    // Obtener la foto actual
    $stmt = $pdo->prepare("SELECT foto_perfil FROM usuarios WHERE id_usuario = :id LIMIT 1");
    $stmt->execute(['id' => $id_usuario]);
    $foto_actual = $stmt->fetchColumn() ?: null;

    if ($accion === 'eliminar') {
        if ($foto_actual) {
            eliminar_imagen_subida($foto_actual);
        }

        $stmt_update = $pdo->prepare("UPDATE usuarios SET foto_perfil = NULL WHERE id_usuario = :id");
        $stmt_update->execute(['id' => $id_usuario]);

        actualizar_foto_sesion(null);
        $_SESSION['exito_perfil'] = 'Foto de perfil eliminada correctamente.';
        header('Location: ../../views/usuario.php');
        exit;
    }

    if ($accion === 'subir') {
        if (!isset($_FILES['foto_perfil'])) {
            $_SESSION['error_perfil'] = 'No se envió ninguna imagen.';
            header('Location: ../../views/usuario.php');
            exit;
        }

        $resultado = guardar_imagen_subida($_FILES['foto_perfil'], 'perfiles', 5);

        if (!$resultado['ok']) {
            $_SESSION['error_perfil'] = $resultado['error'];
            header('Location: ../../views/usuario.php');
            exit;
        }

        $nueva_ruta = $resultado['ruta'];

        // Eliminar foto anterior si existía
        if ($foto_actual && $foto_actual !== $nueva_ruta) {
            eliminar_imagen_subida($foto_actual);
        }

        $stmt_update = $pdo->prepare("UPDATE usuarios SET foto_perfil = :foto WHERE id_usuario = :id");
        $stmt_update->execute([
            'foto' => $nueva_ruta,
            'id'   => $id_usuario,
        ]);

        actualizar_foto_sesion($nueva_ruta);
        $_SESSION['exito_perfil'] = '¡Foto de perfil actualizada con éxito!';
        header('Location: ../../views/usuario.php');
        exit;
    }
} catch (Exception $e) {
    error_log("Error al gestionar foto de perfil: " . $e->getMessage());
    $_SESSION['error_perfil'] = 'Ocurrió un error al procesar tu solicitud. Por favor, intentá más tarde.';
    header('Location: ../../views/usuario.php');
    exit;
}
