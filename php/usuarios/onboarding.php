<?php

require_once __DIR__ . '/../auth/sesion.php';
require_once __DIR__ . '/../../config/database.php';

iniciar_sesion();
requerir_autenticacion('../auth/login.php');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$id_usuario = (int) $_SESSION['usuario']['id_usuario'];
$pasos_totales = 9;
$errores_onboarding = [];

$stmt = $pdo->prepare('SELECT nombre, apellido, email_verificado, cedula_identidad, fecha_nacimiento, onboarding_step, onboarding_data FROM usuarios WHERE id_usuario = :id LIMIT 1');
$stmt->execute(['id' => $id_usuario]);
$usuario_onboarding = $stmt->fetch() ?: [];
if ((int) ($usuario_onboarding['email_verificado'] ?? 0) !== 1) {
    header('Location: ../../views/confirmar-correo.php?resultado=pendiente');
    exit;
}
$paso_guardado = (int) ($usuario_onboarding['onboarding_step'] ?? 1);
if ($paso_guardado > $pasos_totales) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Location: ../../views/usuario.php?onboarding=completo');
        exit;
    }
    header('Location: ../../views/usuario.php?onboarding=completo');
    exit;
}
$paso_actual = min($pasos_totales, max(1, (int) ($usuario_onboarding['onboarding_step'] ?? 1)));
$datos_onboarding = json_decode($usuario_onboarding['onboarding_data'] ?? '{}', true);
$datos_onboarding = is_array($datos_onboarding) ? $datos_onboarding : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        $errores_onboarding[] = 'La sesión del formulario expiró. Recargá la página.';
    }

    $paso_enviado = (int) ($_POST['paso'] ?? $paso_actual);
    if ($paso_enviado !== $paso_actual) {
        $errores_onboarding[] = 'El paso enviado ya no está disponible. Recargá la página.';
    }

    if (!$errores_onboarding) {
        $campos_por_paso = [
            1 => ['nombre', 'apellido', 'fecha_nacimiento', 'profesion', 'otra_profesion', 'institucion', 'pais', 'departamento'],
            2 => ['uso_plataforma'],
            3 => ['categorias'],
            4 => ['modalidades'],
            5 => ['nivel_experiencia'],
            6 => ['presupuesto', 'duracion_preferida', 'preferencias_contratacion'],
            7 => ['notificaciones'],
            8 => ['quiere_publicar', 'tipo_proveedor', 'sitio_web', 'descripcion_profesional'],
            9 => ['acepta_personalizacion', 'acepta_privacidad'],
        ];
        $campos_multiples = ['uso_plataforma', 'categorias', 'modalidades', 'preferencias_contratacion', 'notificaciones'];
        foreach ($campos_por_paso[$paso_actual] as $campo) {
            if (!array_key_exists($campo, $_POST)) {
                $datos_onboarding[$campo] = in_array($campo, $campos_multiples, true) ? [] : '';
            }
        }

        $datos_omitidos = ['csrf_token', 'paso'];
        foreach ($_POST as $clave => $valor) {
            if (in_array($clave, $datos_omitidos, true)) {
                continue;
            }
            $datos_onboarding[$clave] = is_array($valor) ? array_values($valor) : trim((string) $valor);
        }

        // Guardar fecha_nacimiento y CI (paso 1) directamente en la tabla si no existían
        if ($paso_actual === 1 && !$errores_onboarding) {
            require_once __DIR__ . '/../utils/cedula_uy.php';
            require_once __DIR__ . '/../utils/mailer.php';

            // --- Fecha de nacimiento ---
            $fn_raw = trim($_POST['fecha_nacimiento'] ?? '');
            $fn_obj = $fn_raw ? DateTime::createFromFormat('Y-m-d', $fn_raw) : false;
            $min_fecha = (new DateTime())->modify('-120 years');
            $max_fecha = (new DateTime())->modify('-13 years');

            if (empty($fn_raw)) {
                $errores_onboarding[] = 'La fecha de nacimiento es obligatoria.';
            } elseif (!$fn_obj || $fn_obj->format('Y-m-d') !== $fn_raw) {
                $errores_onboarding[] = 'La fecha de nacimiento ingresada no es válida.';
            } elseif ($fn_obj < $min_fecha || $fn_obj > $max_fecha) {
                $errores_onboarding[] = 'Debés tener entre 13 y 120 años para completar tu perfil.';
            }

            // --- Cédula de Identidad (solo si el usuario no tiene una) ---
            $tiene_cedula = !empty($usuario_onboarding['cedula_identidad']);
            if (!$tiene_cedula) {
                $cedula_input = trim($_POST['cedula_identidad'] ?? '');
                if (!empty($cedula_input)) {
                    if (!validar_cedula_uruguaya($cedula_input)) {
                        $errores_onboarding[] = 'La Cédula de Identidad ingresada no es válida.';
                    } else {
                        // Verificar duplicado
                        $ci_limpia = limpiar_ci($cedula_input);
                        $ci_hash = hash_ci($ci_limpia);
                        $stmt_ci = $pdo->prepare('SELECT id_usuario FROM usuarios WHERE (cedula_hash = :h OR cedula_identidad = :p) AND id_usuario != :id LIMIT 1');
                        $stmt_ci->execute(['h' => $ci_hash, 'p' => $ci_limpia, 'id' => $id_usuario]);
                        if ($stmt_ci->fetch()) {
                            $errores_onboarding[] = 'La Cédula de Identidad ya está registrada por otro usuario.';
                        }
                    }
                }
            }

            if (!$errores_onboarding) {
                // Guardar fecha de nacimiento si no estaba guardada
                if (empty($usuario_onboarding['fecha_nacimiento']) && !empty($fn_raw)) {
                    $pdo->prepare('UPDATE usuarios SET fecha_nacimiento = :fn WHERE id_usuario = :id')
                        ->execute(['fn' => $fn_raw, 'id' => $id_usuario]);
                }
                // Guardar CI si no tenía y se proporcionó válida
                if (!$tiene_cedula && !empty($cedula_input) && isset($ci_hash)) {
                    $ci_cifrada = encriptar_ci($cedula_input);
                    $pdo->prepare('UPDATE usuarios SET cedula_identidad = :ci, cedula_hash = :h WHERE id_usuario = :id AND cedula_identidad IS NULL')
                        ->execute(['ci' => $ci_cifrada, 'h' => $ci_hash, 'id' => $id_usuario]);
                }
            }
        }

        if ($errores_onboarding) {
            // Volver a mostrar el formulario con errores
            header('Location: ../../views/primeros-pasos.php');
            exit;
        }

        if ($paso_actual === 1 && ($datos_onboarding['profesion'] ?? '') !== 'otro') {
            $datos_onboarding['otra_profesion'] = '';
        }

        $retroceder = ($_POST['accion'] ?? '') === 'retroceder';
        $siguiente_paso = $retroceder
            ? max(1, $paso_actual - 1)
            : ($paso_actual < $pasos_totales ? $paso_actual + 1 : $pasos_totales + 1);
        $actualizar = $pdo->prepare('UPDATE usuarios SET onboarding_step = :paso, onboarding_data = :datos WHERE id_usuario = :id');
        $actualizar->execute([
            'paso' => $siguiente_paso,
            'datos' => json_encode($datos_onboarding, JSON_UNESCAPED_UNICODE),
            'id' => $id_usuario,
        ]);

        if ($siguiente_paso > $pasos_totales) {
            header('Location: ../../views/usuario.php?onboarding=completo');
        } else {
            header('Location: ../../views/primeros-pasos.php');
        }
        exit;
    }
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function atributo_paso(int $paso, int $paso_actual): string
{
    return $paso === $paso_actual ? '' : ' disabled';
}

function clase_paso(int $paso, int $paso_actual): string
{
    if ($paso > $paso_actual) {
        return ' onboarding-step--locked';
    }

    if ($paso < $paso_actual) {
        return ' onboarding-step--completed';
    }

    return ' onboarding-step--active';
}

function valor_onboarding(string $clave, array $datos, string $defecto = ''): string
{
    return htmlspecialchars((string) ($datos[$clave] ?? $defecto), ENT_QUOTES, 'UTF-8');
}
