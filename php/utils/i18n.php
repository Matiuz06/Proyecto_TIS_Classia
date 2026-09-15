<?php

function inicializar_i18n(): string
{
    // La plataforma opera exclusivamente en español.
    // Los archivos de traducción se conservan pero el selector de idioma fue eliminado.
    return 'es';
}

function __t(string $key, ?string $fallback = null): string
{
    static $translations = null;

    if ($translations === null) {
        $filePath = __DIR__ . '/../lang/es.php';
        if (file_exists($filePath)) {
            $translations = require $filePath;
        } else {
            $translations = [];
        }
    }

    return $translations[$key] ?? $fallback ?? $key;
}

function obtener_idioma_actual(): string
{
    return 'es';
}

function __t_db(?string $value, ?string $fallback = null): string
{
    if ($value === null || $value === '') {
        return (string)($fallback ?? '');
    }

    $dict = [
        // Categorías
        'Programación'                           => 'cat_programming',
        'Programación & Web'                     => 'cat_programming',
        'Programación y Desarrollo'              => 'cat_programming',
        'Robótica y Automatización'              => 'cat_robotics',
        'Diseño e Impresión 3D'                  => 'cat_3d_design',
        'Impresión 3D & Prototipos'              => 'cat_3d_prototypes',
        'Electrónica y Hardware'                 => 'cat_electronics',
        'Electrónica y Microcontroladores'       => 'cat_electronics',
        'Ciberseguridad'                         => 'cat_cybersecurity',
        'Ciberseguridad & Redes'                 => 'cat_cybersecurity',
        'Ciberseguridad y Redes'                 => 'cat_cybersecurity',
        'Gestión y Proyectos'                    => 'cat_management',
        'Gestión de Proyectos Tecnológicos'      => 'cat_management',
        'Gestión & Consultoría'                  => 'cat_management_consulting',
        'Diseño UX/UI'                           => 'cat_ux_ui',
        'Diseño UX / UI'                         => 'cat_ux_ui',
        'Diseño Web y UX/UI'                     => 'cat_ux_ui',
        'Idiomas'                                => 'cat_languages',
        'Idiomas Técnicos'                       => 'cat_languages',
        'Idiomas y Comunicación Técnica'         => 'cat_languages',
        'Inteligencia Artificial'                => 'cat_ai',
        'Inteligencia Artificial y Datos'        => 'cat_ai',
        'Mentorías y Capacitación'               => 'cat_mentorship',
        'Metodologías Ágiles'                    => 'cat_agile',
        'Todas las disciplinas'                  => 'cat_all_disciplines',

        // Tipos y modalidades
        'Curso'                                  => 'label_course',
        'Servicio'                               => 'label_service',
        'Presencial'                             => 'label_presential',
        'Online'                                 => 'label_online',
        'Híbrido'                                => 'label_hybrid',
        'Principiante'                           => 'level_beginner',
        'Intermedio'                             => 'level_intermediate',
        'Avanzado'                               => 'level_advanced',

        // Estados
        'Activo'                                 => 'status_active',
        'Inactivo'                               => 'status_inactive',
        'Pausado'                                => 'status_paused',
        'Eliminado'                              => 'status_deleted',
        'Pendiente'                              => 'status_pending',
        'Aceptada'                               => 'status_accepted',
        'Rechazada'                              => 'status_rejected',
        'Contraoferta'                           => 'status_counteroffer',
        'En Proceso'                             => 'status_in_progress',
        'Realizada'                              => 'status_completed',
        'Completada'                             => 'status_completed',
        'Cancelada'                              => 'status_cancelled',
        'Aprobado'                               => 'status_approved',

        // Roles
        'Estudiante / Cliente'                   => 'role_student_client',
        'Docente / Proveedor'                    => 'role_teacher_provider',
        'Administrador'                          => 'role_admin',
        'Estudiante'                             => 'role_student',
        'Docente'                                => 'role_teacher',
        'Proveedor'                              => 'role_provider',
        'Usuario'                                => 'role_user',
    ];

    $trimmed = trim($value);
    if (isset($dict[$trimmed])) {
        return __t($dict[$trimmed], $fallback ?? $trimmed);
    }

    return $fallback ?? $trimmed;
}
