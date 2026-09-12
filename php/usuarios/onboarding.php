<?php

require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../../config/database.php';

iniciar_sesion();
requerir_autenticacion('../auth/login.php');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$id_usuario = (int) $_SESSION['usuario']['id_usuario'];
$pasos_totales = 9;
$errores_onboarding = [];

$stmt = $pdo->prepare('SELECT nombre, apellido, onboarding_step, onboarding_data FROM usuarios WHERE id_usuario = :id LIMIT 1');
$stmt->execute(['id' => $id_usuario]);
$usuario_onboarding = $stmt->fetch() ?: [];
$paso_guardado = (int) ($usuario_onboarding['onboarding_step'] ?? 1);
if ($paso_guardado > $pasos_totales && $_SERVER['REQUEST_METHOD'] !== 'POST') {
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
            1 => ['nombre', 'apellido', 'profesion', 'otra_profesion', 'institucion', 'pais', 'departamento'],
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

        $siguiente_paso = $paso_actual < $pasos_totales ? $paso_actual + 1 : $pasos_totales + 1;
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
