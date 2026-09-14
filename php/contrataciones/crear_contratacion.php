<?php

require_once __DIR__ . '/../auth/roles.php';
require_once __DIR__ . '/../../config/database.php';

requerir_rol(ROL_ESTUDIANTE, '../../views/usuario.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../views/carrito.php');
    exit;
}

$token_recibido = $_POST['csrf_token'] ?? '';

if (!hash_equals($_SESSION['csrf_token'] ?? '', $token_recibido)) {
    $_SESSION['carrito_error'] = 'La sesion del formulario expiro. Recarga la pagina e intenta nuevamente.';
    header('Location: ../../views/carrito.php');
    exit;
}

$usuario = usuario_actual();
$id_usuario = (int) $usuario['id_usuario'];
$ids_carrito = array_values(array_unique(array_map('intval', $_SESSION['carrito_publicaciones'] ?? [])));

if (empty($ids_carrito)) {
    $_SESSION['carrito_error'] = 'El carrito esta vacio.';
    header('Location: ../../views/carrito.php');
    exit;
}

try {
    $stmt_publicacion = $pdo->prepare(
        "SELECT id_publicacion, titulo, precio, estado, tipo, tipo_servicio, cupos
         FROM publicaciones
         WHERE id_publicacion = :id_publicacion
         LIMIT 1"
    );

    $publicaciones = [];
    $total = 0;

    foreach ($ids_carrito as $id_publicacion) {
        if ($id_publicacion <= 0) {
            throw new RuntimeException('Publicacion invalida.');
        }

        $stmt_publicacion->execute(['id_publicacion' => $id_publicacion]);
        $publicacion = $stmt_publicacion->fetch();

        if (!$publicacion || $publicacion['estado'] !== 'Activo' || (float) $publicacion['precio'] <= 0) {
            $_SESSION['carrito_error'] = 'Una de las publicaciones seleccionadas no esta disponible.';
            header('Location: ../../views/carrito.php');
            exit;
        }
        if ($publicacion['tipo'] === 'Servicio' && !empty($publicacion['tipo_servicio'])) {
            $_SESSION['carrito_error'] = 'Este servicio requiere una solicitud personalizada antes de contratarse.';
            header('Location: ../../views/servicio-detalle.php?id=' . (int)$publicacion['id_publicacion']);
            exit;
        }
        if ($publicacion['tipo'] === 'Curso') {
            $dup=$pdo->prepare("SELECT COUNT(*) FROM detalles_contratacion dc JOIN contrataciones c ON c.id_contratacion=dc.id_contratacion WHERE c.id_usuario=:u AND dc.id_publicacion=:p AND c.estado IN ('Pendiente','En Proceso','Completada')");
            $dup->execute(['u'=>$id_usuario,'p'=>$publicacion['id_publicacion']]);
            if((int)$dup->fetchColumn()>0){$_SESSION['carrito_error']='Ya tenés este curso contratado o pendiente de pago.';header('Location: ../../views/carrito.php');exit;}
            if(!empty($publicacion['cupos'])){
                $oc=$pdo->prepare("SELECT COALESCE(SUM(dc.cantidad),0) FROM detalles_contratacion dc JOIN contrataciones c ON c.id_contratacion=dc.id_contratacion WHERE dc.id_publicacion=:p AND c.estado<>'Cancelada'");$oc->execute(['p'=>$publicacion['id_publicacion']]);
                if((int)$oc->fetchColumn()>=(int)$publicacion['cupos']){$_SESSION['carrito_error']='No quedan cupos disponibles para el curso ' . $publicacion['titulo'] . '.';header('Location: ../../views/carrito.php');exit;}
            }
        }

        $cantidad = 1;
        $precio = (float) $publicacion['precio'];
        $subtotal = $precio * $cantidad;
        $total += $subtotal;

        $publicaciones[] = [
            'id_publicacion' => (int) $publicacion['id_publicacion'],
            'cantidad' => $cantidad,
            'precio' => $precio,
            'subtotal' => $subtotal,
        ];
    }

    $pdo->beginTransaction();

    $stmt_contratacion = $pdo->prepare(
        "INSERT INTO contrataciones (monto_total, estado, id_usuario)
         VALUES (:monto_total, 'Pendiente', :id_usuario)"
    );
    $stmt_contratacion->execute([
        'monto_total' => $total,
        'id_usuario' => $id_usuario,
    ]);

    $id_contratacion = (int) $pdo->lastInsertId();

    $stmt_detalle = $pdo->prepare(
        "INSERT INTO detalles_contratacion
            (cantidad, precio_unitario, subtotal, id_contratacion, id_publicacion)
         VALUES
            (:cantidad, :precio_unitario, :subtotal, :id_contratacion, :id_publicacion)"
    );

    foreach ($publicaciones as $publicacion) {
        $stmt_detalle->execute([
            'cantidad' => $publicacion['cantidad'],
            'precio_unitario' => $publicacion['precio'],
            'subtotal' => $publicacion['subtotal'],
            'id_contratacion' => $id_contratacion,
            'id_publicacion' => $publicacion['id_publicacion'],
        ]);
    }

    $pdo->commit();

    header('Location: ../../views/pasarela-pago.php?id_contratacion=' . $id_contratacion);
    exit;
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Error al crear contratacion: ' . $e->getMessage());
    $_SESSION['carrito_error'] = 'No se pudo realizar la contratacion.';
    header('Location: ../../views/carrito.php');
    exit;
}
