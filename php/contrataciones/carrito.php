<?php

/**
 * Responsabilidad: Administra el carrito de publicaciones guardado en sesión.
 */

require_once __DIR__ . '/../auth/sesion.php';
require_once __DIR__ . '/../auth/roles.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../utils/toast.php';

iniciar_sesion();

$esAjax = ($_SERVER['HTTP_ACCEPT'] ?? '') === 'application/json'
    || ($_POST['ajax'] ?? '') === '1';

function responder_carrito_json(array $datos, int $estado = 200): void
{
    http_response_code($estado);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($esAjax) {
    set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
        throw new ErrorException($message, 0, $severity, $file, $line);
    });
    set_exception_handler(static function (Throwable $error): void {
        error_log('Error en carrito AJAX: ' . $error->getMessage());
        responder_carrito_json(['success' => false, 'message' => 'No se pudo agregar el curso al carrito.'], 500);
    });
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && ($_POST['accion'] ?? '') === 'agregar') {
    if ($esAjax && !esta_autenticado()) {
        responder_carrito_json(['success' => false, 'message' => 'Debes iniciar sesión para agregar cursos al carrito.'], 401);
    }
    requerir_autenticacion('../../views/login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token_recibido = $_POST['csrf_token'] ?? '';
    $accion = $_POST['accion'] ?? '';
    $id_publicacion = (int) ($_POST['id_publicacion'] ?? 0);

    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token_recibido)) {
        if ($esAjax) {
            responder_carrito_json(['success' => false, 'message' => 'La sesión del formulario expiró. Recarga la página e intenta nuevamente.'], 419);
        }
        $_SESSION['carrito_error'] = 'La sesión del formulario expiró. Recarga la página e intenta nuevamente.';
    } elseif ($accion === 'agregar' && $id_publicacion > 0) {
        if (es_admin()) {
            if ($esAjax) {
                responder_carrito_json(['success' => false, 'message' => 'El administrador no realiza compras de cursos ni servicios.'], 403);
            }
            $_SESSION['carrito_error'] = 'El administrador no realiza compras de cursos ni servicios.';
            header("Location: ../../views/catalogo.php");
            exit;
        }

        $stmtCheck = $pdo->prepare("SELECT id_usuario, tipo, estado FROM publicaciones WHERE id_publicacion = :id LIMIT 1");
        $stmtCheck->execute(['id' => $id_publicacion]);
        $publicacion = $stmtCheck->fetch();
        if (!$publicacion || ($publicacion['estado'] ?? '') !== 'Activo') {
            if ($esAjax) {
                responder_carrito_json(['success' => false, 'message' => 'El curso no está disponible.'], 404);
            }
            $_SESSION['carrito_error'] = 'La publicación no está disponible.';
            header('Location: ../../views/catalogo.php');
            exit;
        }
        $autor_id = (int) $publicacion['id_usuario'];
        $currUser = usuario_actual();
        if ($currUser && (int)$currUser['id_usuario'] === $autor_id) {
            if ($esAjax) {
                responder_carrito_json(['success' => false, 'message' => 'No puedes comprar tus propias publicaciones.'], 403);
            }
            $_SESSION['carrito_error'] = 'No puedes comprar tus propias publicaciones.';
            header("Location: ../../views/catalogo.php");
            exit;
        }

        $_SESSION['carrito_publicaciones'] = $_SESSION['carrito_publicaciones'] ?? [];
        $yaExiste = in_array($id_publicacion, array_map('intval', $_SESSION['carrito_publicaciones']), true);
        if (!$yaExiste) {
            $_SESSION['carrito_publicaciones'][] = $id_publicacion;
            if ($esAjax) {
                responder_carrito_json([
                    'success' => true,
                    'message' => 'El curso se agregó al carrito.',
                    'cart_count' => count(array_unique(array_map('intval', $_SESSION['carrito_publicaciones']))),
                    'already_exists' => false,
                ]);
            }
            set_toast('success', 'Agregado al carrito', null, [
                'action' => ['label' => 'Ver carrito', 'href' => '../../views/carrito.php'],
            ]);
        } else {
            if ($esAjax) {
                responder_carrito_json([
                    'success' => true,
                    'message' => 'Este curso ya estaba en tu carrito.',
                    'cart_count' => count(array_unique(array_map('intval', $_SESSION['carrito_publicaciones']))),
                    'already_exists' => true,
                ]);
            }
            set_toast('warning', 'Ya esta en el carrito', 'Este elemento ya habia sido agregado.');
        }
        $destinos_permitidos = [
            'catalogo' => '../../views/catalogo.php?carrito=agregado',
            'carrito'  => '../../views/carrito.php',
        ];
        $clave_destino = $_POST['redirect'] ?? 'carrito';
        $destino = $destinos_permitidos[$clave_destino] ?? $destinos_permitidos['carrito'];
        header("Location: " . $destino);
        exit;
    } elseif ($accion === 'quitar' && $id_publicacion > 0) {
        $_SESSION['carrito_publicaciones'] = array_values(array_filter(
            $_SESSION['carrito_publicaciones'] ?? [],
            fn($id) => (int) $id !== $id_publicacion
        ));
        set_toast('info', 'Eliminado del carrito');
    } elseif ($accion === 'vaciar') {
        unset($_SESSION['carrito_publicaciones']);
        set_toast('info', 'Carrito vaciado');
    }
}

$mensaje_error = $_SESSION['carrito_error'] ?? '';
unset($_SESSION['carrito_error']);

$ids_carrito = array_values(array_unique(array_map('intval', $_SESSION['carrito_publicaciones'] ?? [])));
$items = [];
$total = 0;

if (!empty($ids_carrito)) {
    try {
        $stmt = $pdo->prepare(
            "SELECT id_publicacion, titulo, precio, tipo, estado, imagen
             FROM publicaciones
             WHERE id_publicacion = :id_publicacion"
        );

        foreach ($ids_carrito as $id_publicacion) {
            $stmt->execute(['id_publicacion' => $id_publicacion]);
            $item = $stmt->fetch();

            if ($item && $item['estado'] === 'Activo') {
                $item['cantidad'] = 1;
                $item['subtotal'] = (float) $item['precio'];
                $items[] = $item;
                $total += (float) $item['subtotal'];
            }
        }
    } catch (PDOException $e) {
        error_log('Error al consultar publicaciones del carrito: ' . $e->getMessage());
        $mensaje_error = 'Error al cargar los elementos del carrito.';
    }
}
