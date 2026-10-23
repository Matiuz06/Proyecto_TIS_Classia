<?php

/**
 * Responsabilidad: Envia correos transaccionales mediante PHPMailer y SMTP.
 */

function cargar_configuracion_correo(): array
{
    $envPath = __DIR__ . '/../../.env';
    if (file_exists($envPath)) {
        foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $linea) {
            $linea = trim($linea);
            if ($linea === '' || str_starts_with($linea, '#') || !str_contains($linea, '=')) {
                continue;
            }
            [$clave, $valor] = explode('=', $linea, 2);
            $clave = trim($clave);
            if (getenv($clave) === false) {
                putenv($clave . '=' . trim($valor));
                $_ENV[$clave] = trim($valor);
            }
        }
    }

    return [
        'base_url' => rtrim(getenv('APP_URL') ?: 'http://localhost', '/'),
        'to' => getenv('MAIL_TO') ?: 'anitechsa2026@gmail.com',
        'host' => getenv('SMTP_HOST') ?: '',
        'port' => (int) (getenv('SMTP_PORT') ?: 587),
        'username' => getenv('SMTP_USER') ?: '',
        'password' => getenv('SMTP_PASS') ?: '',
        'secure' => strtolower(getenv('SMTP_SECURE') ?: 'tls'),
        'from' => getenv('SMTP_FROM_EMAIL') ?: '',
        'name' => getenv('SMTP_FROM_NAME') ?: 'Classia',
    ];
}

function render_email_template(string $template, array $data = []): string
{
    $path = __DIR__ . '/../emails/' . basename($template) . '.php';
    if (!is_file($path)) {
        error_log('[Mailer] Plantilla no encontrada: ' . $template);
        return '';
    }

    extract($data, EXTR_SKIP);
    ob_start();
    require $path;
    return (string) ob_get_clean();
}

function enviar_correo(
    string $destinatario,
    string $asunto,
    string $html,
    ?string $textoPlano = null
): bool {
    $autoload = __DIR__ . '/../../vendor/autoload.php';
    if (is_file($autoload)) {
        require_once $autoload;
    }

    if (!class_exists(\PHPMailer\PHPMailer\PHPMailer::class)) {
        error_log('[Mailer] PHPMailer no esta instalado. Ejecutar composer install.');
        return false;
    }

    $config = cargar_configuracion_correo();
    if (!validar_configuracion_correo($config, $destinatario)) {
        return false;
    }

    try {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->CharSet = 'UTF-8';
        $mail->Host = $config['host'];
        $mail->Port = $config['port'];
        $mail->SMTPAuth = $config['username'] !== '' && $config['password'] !== '';

        if ($mail->SMTPAuth) {
            $mail->Username = $config['username'];
            $mail->Password = $config['password'];
        }

        if ($config['secure'] === 'tls') {
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        } elseif ($config['secure'] === 'ssl') {
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
        } else {
            $mail->SMTPSecure = false;
            $mail->SMTPAutoTLS = false;
        }

        $mail->setFrom($config['from'], $config['name']);
        $mail->addAddress($destinatario);
        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body = $html;
        $mail->AltBody = $textoPlano ?: generar_texto_plano_correo($html);

        return $mail->send();
    } catch (Throwable $e) {
        error_log('[Mailer] ' . $e->getMessage());
        return false;
    }
}

function validar_configuracion_correo(array $config, string $destinatario): bool
{
    if ($config['host'] === '') {
        error_log('[Mailer] SMTP_HOST no configurado.');
        return false;
    }
    if ($config['port'] < 1 || $config['port'] > 65535) {
        error_log('[Mailer] SMTP_PORT invalido.');
        return false;
    }
    if (!filter_var($config['from'], FILTER_VALIDATE_EMAIL)) {
        error_log('[Mailer] SMTP_FROM_EMAIL invalido.');
        return false;
    }
    if (!filter_var($destinatario, FILTER_VALIDATE_EMAIL)) {
        error_log('[Mailer] Destinatario invalido.');
        return false;
    }
    if (!in_array($config['secure'], ['tls', 'ssl', 'none'], true)) {
        error_log('[Mailer] SMTP_SECURE invalido.');
        return false;
    }
    return true;
}

function generar_texto_plano_correo(string $html): string
{
    $texto = html_entity_decode(strip_tags(str_replace(['</p>', '<br>', '<br/>', '<br />'], "\n", $html)), ENT_QUOTES, 'UTF-8');
    return trim(preg_replace("/[ \t]+\n|\n{3,}/", "\n\n", $texto) ?? $texto);
}

function plantilla_correo(string $titulo, string $contenido): string
{
    return render_email_template('base', [
        'titulo' => $titulo,
        'contenido' => $contenido,
    ]);
}
