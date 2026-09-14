<?php

function cargar_configuracion_correo(): array
{
    $env_path = __DIR__ . '/../../.env';
    if (file_exists($env_path)) {
        $lineas = file($env_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lineas as $linea) {
            $linea = trim($linea);
            if ($linea === '' || strpos($linea, '#') === 0 || strpos($linea, '=') === false) {
                continue;
            }
            [$clave, $valor] = explode('=', $linea, 2);
            if (getenv(trim($clave)) === false) {
                putenv(trim($clave) . '=' . trim($valor));
            }
        }
    }

    $config = [
        'driver' => strtolower(getenv('MAIL_DRIVER') ?: 'mail'),
        'from' => getenv('MAIL_FROM') ?: 'no-reply@classia.local',
        'name' => getenv('MAIL_FROM_NAME') ?: 'Classia',
        'to' => getenv('MAIL_TO') ?: 'anitechsa2026@gmail.com',
        'base_url' => rtrim(getenv('APP_URL') ?: 'http://localhost', '/'),
        'host' => getenv('MAIL_HOST') ?: 'localhost',
        'port' => (int) (getenv('MAIL_PORT') ?: 25),
        'username' => getenv('MAIL_USERNAME') ?: '',
        'password' => getenv('MAIL_PASSWORD') ?: '',
        'encryption' => strtolower(getenv('MAIL_ENCRYPTION') ?: 'none'),
    ];

    return $config;
}

function enviar_correo(string $destinatario, string $asunto, string $contenido): bool
{
    $config = cargar_configuracion_correo();
    $remitente = sprintf('%s <%s>', $config['name'], $config['from']);
    $cabeceras = [
        'From: ' . $remitente,
        'Reply-To: ' . $config['from'],
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'X-Mailer: Classia PHP',
    ];

    if ($config['driver'] === 'smtp') {
        return enviar_por_smtp($config, $destinatario, $asunto, $contenido);
    }

    return mail($destinatario, $asunto, $contenido, implode("\r\n", $cabeceras));
}

function enviar_por_smtp(array $config, string $destinatario, string $asunto, string $contenido): bool
{
    $host = $config['host'];
    $port = $config['port'];
    $socket_host = $config['encryption'] === 'ssl' ? 'ssl://' . $host : $host;
    $socket = @fsockopen($socket_host, $port, $errno, $error, 10);

    if (!$socket || !smtp_respuesta_correcta($socket, [220])) {
        return false;
    }

    $dominio = $_SERVER['SERVER_NAME'] ?? 'localhost';
    if (!smtp_comando($socket, 'EHLO ' . $dominio, [250])) {
        fclose($socket);
        return false;
    }

    if ($config['encryption'] === 'tls') {
        if (!smtp_comando($socket, 'STARTTLS', [220])
            || !stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)
            || !smtp_comando($socket, 'EHLO ' . $dominio, [250])) {
            fclose($socket);
            return false;
        }
    }

    if ($config['username'] !== '') {
        if (!smtp_comando($socket, 'AUTH LOGIN', [334])
            || !smtp_comando($socket, base64_encode($config['username']), [334])
            || !smtp_comando($socket, base64_encode($config['password']), [235])) {
            fclose($socket);
            return false;
        }
    }

    $remitente = $config['from'];
    $correcto = smtp_comando($socket, 'MAIL FROM:<' . $remitente . '>', [250])
        && smtp_comando($socket, 'RCPT TO:<' . $destinatario . '>', [250, 251])
        && smtp_comando($socket, 'DATA', [354]);

    if ($correcto) {
        $cabeceras = "From: " . $config['name'] . " <" . $remitente . ">\r\n"
            . "To: <" . $destinatario . ">\r\n"
            . "Subject: " . mb_encode_mimeheader($asunto, 'UTF-8') . "\r\n"
            . "MIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\n\r\n";
        $cuerpo = preg_replace('/\r?\n\./', "\r\n..", $cabeceras . $contenido);
        fwrite($socket, $cuerpo . "\r\n.\r\n");
        $correcto = smtp_respuesta_correcta($socket, [250]);
    }

    smtp_comando($socket, 'QUIT', [221]);
    fclose($socket);
    return $correcto;
}

function smtp_comando($socket, string $comando, array $codigos): bool
{
    fwrite($socket, $comando . "\r\n");
    return smtp_respuesta_correcta($socket, $codigos);
}

function smtp_respuesta_correcta($socket, array $codigos): bool
{
    $respuesta = '';
    do {
        $linea = fgets($socket, 512);
        if ($linea === false) {
            return false;
        }
        $respuesta = $linea;
    } while (isset($linea[3]) && $linea[3] === '-');

    return in_array((int) substr($respuesta, 0, 3), $codigos, true);
}

function plantilla_correo(string $titulo, string $contenido): string
{
    return '<!doctype html><html lang="es"><body style="font-family:Arial,sans-serif;color:#17212b;line-height:1.5">'
        . '<h1 style="color:#146c94">' . htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') . '</h1>'
        . $contenido
        . '<p>Saludos,<br>Equipo Classia</p></body></html>';
}
