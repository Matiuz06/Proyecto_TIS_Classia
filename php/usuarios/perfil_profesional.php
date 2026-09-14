<?php

require_once __DIR__ . '/../auth/roles.php';
require_once __DIR__ . '/../../config/database.php';

function obtener_perfil_profesional(PDO $pdo, int $id_usuario): ?array
{
    $stmt = $pdo->prepare("SELECT pp.*, u.nombre, u.apellido, u.email, u.foto_perfil, u.fecha_registro FROM usuarios u LEFT JOIN perfiles_profesionales pp ON pp.id_usuario=u.id_usuario WHERE u.id_usuario=:id AND u.id_rol = 2");
    $stmt->execute(['id'=>$id_usuario]);
    return $stmt->fetch() ?: null;
}

function guardar_perfil_profesional(PDO $pdo, int $id_usuario, array $data): void
{
    $sql = "INSERT INTO perfiles_profesionales (id_usuario,titulo_profesional,presentacion,experiencia,formacion,certificaciones,habilidades,especialidades,idiomas,ubicacion,modalidad_trabajo,portfolio_url,linkedin_url,tiempo_respuesta,visibilidad)
            VALUES (:id,:titulo,:presentacion,:experiencia,:formacion,:certificaciones,:habilidades,:especialidades,:idiomas,:ubicacion,:modalidad,:portfolio,:linkedin,:respuesta,:visibilidad)
            ON DUPLICATE KEY UPDATE titulo_profesional=VALUES(titulo_profesional),presentacion=VALUES(presentacion),experiencia=VALUES(experiencia),formacion=VALUES(formacion),certificaciones=VALUES(certificaciones),habilidades=VALUES(habilidades),especialidades=VALUES(especialidades),idiomas=VALUES(idiomas),ubicacion=VALUES(ubicacion),modalidad_trabajo=VALUES(modalidad_trabajo),portfolio_url=VALUES(portfolio_url),linkedin_url=VALUES(linkedin_url),tiempo_respuesta=VALUES(tiempo_respuesta),visibilidad=VALUES(visibilidad)";
    $stmt=$pdo->prepare($sql);
    $stmt->execute([
        'id'=>$id_usuario,
        'titulo'=>trim($data['titulo_profesional'] ?? '') ?: null,
        'presentacion'=>trim($data['presentacion'] ?? '') ?: null,
        'experiencia'=>trim($data['experiencia'] ?? '') ?: null,
        'formacion'=>trim($data['formacion'] ?? '') ?: null,
        'certificaciones'=>trim($data['certificaciones'] ?? '') ?: null,
        'habilidades'=>trim($data['habilidades'] ?? '') ?: null,
        'especialidades'=>trim($data['especialidades'] ?? '') ?: null,
        'idiomas'=>trim($data['idiomas'] ?? '') ?: null,
        'ubicacion'=>trim($data['ubicacion'] ?? '') ?: null,
        'modalidad'=>trim($data['modalidad_trabajo'] ?? '') ?: null,
        'portfolio'=>filter_var($data['portfolio_url'] ?? '', FILTER_VALIDATE_URL) ?: null,
        'linkedin'=>filter_var($data['linkedin_url'] ?? '', FILTER_VALIDATE_URL) ?: null,
        'respuesta'=>trim($data['tiempo_respuesta'] ?? '') ?: null,
        'visibilidad'=>in_array(($data['visibilidad'] ?? ''), ['Registrados','Relacionados'], true) ? $data['visibilidad'] : 'Registrados',
    ]);
}

function usuario_relacionado_con_docente(PDO $pdo, int $id_visitante, int $id_docente): bool
{
    if ($id_visitante === $id_docente) return true;
    $sql = "SELECT 1 FROM detalles_contratacion dc
            JOIN contrataciones c ON c.id_contratacion=dc.id_contratacion
            JOIN publicaciones p ON p.id_publicacion=dc.id_publicacion
            WHERE c.id_usuario=:visitante AND p.id_usuario=:docente LIMIT 1";
    $stmt=$pdo->prepare($sql); $stmt->execute(['visitante'=>$id_visitante,'docente'=>$id_docente]);
    if ($stmt->fetchColumn()) return true;
    $stmt=$pdo->prepare("SELECT 1 FROM solicitudes s JOIN publicaciones p ON p.id_publicacion=s.id_publicacion WHERE s.id_usuario=:visitante AND p.id_usuario=:docente AND s.estado IN ('Aceptada','En Proceso','Realizada') LIMIT 1");
    $stmt->execute(['visitante'=>$id_visitante,'docente'=>$id_docente]);
    return (bool)$stmt->fetchColumn();
}
