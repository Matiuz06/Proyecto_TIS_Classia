<?php

/**
 * Flash toasts seguros para renderizar con ClassiaToast en el siguiente GET.
 */

require_once __DIR__ . '/../auth/sesion.php';

function set_toast(
    string $type,
    string $title,
    ?string $description = null,
    array $options = []
): void {
    iniciar_sesion();

    $allowedTypes = ['success', 'error', 'warning', 'info'];
    $type = in_array($type, $allowedTypes, true) ? $type : 'info';

    $toast = [
        'type' => $type,
        'title' => $title,
    ];

    if ($description !== null && $description !== '') {
        $toast['description'] = $description;
    }

    if (isset($options['duration']) && is_numeric($options['duration'])) {
        $toast['duration'] = max(0, (int) $options['duration']);
    }

    if (
        isset($options['action'])
        && is_array($options['action'])
        && !empty($options['action']['label'])
        && !empty($options['action']['href'])
    ) {
        $href = (string) $options['action']['href'];
        if (!preg_match('/^\s*javascript:/i', $href)) {
            $toast['action'] = [
                'label' => (string) $options['action']['label'],
                'href' => $href,
            ];
        }
    }

    $_SESSION['classia_toasts'] ??= [];
    $_SESSION['classia_toasts'][] = $toast;
}

function consume_toasts(): array
{
    iniciar_sesion();

    $toasts = $_SESSION['classia_toasts'] ?? [];
    unset($_SESSION['classia_toasts']);

    return is_array($toasts) ? array_values($toasts) : [];
}
