<?php

require_once __DIR__ . '/../auth/roles.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/CursoProgresoRepository.php';
require_once __DIR__ . '/CertificadoPdf.php';

requerir_autenticacion('../../views/login.php');

$usuario = usuario_actual();
$idUsuario = (int)($usuario['id_usuario'] ?? 0);
$idCurso = (int)($_GET['id_curso'] ?? 0);

if ($idUsuario <= 0 || $idCurso <= 0) {
    http_response_code(400);
    exit('Solicitud invalida.');
}

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM contrataciones c
    JOIN detalles_contratacion dc ON dc.id_contratacion = c.id_contratacion
    JOIN pagos pag ON pag.id_contratacion = c.id_contratacion AND pag.estado_pago = 'Aprobado'
    WHERE c.id_usuario = :usuario
      AND dc.id_publicacion = :curso
      AND c.estado IN ('Completada', 'En Proceso')
");
$stmt->execute(['usuario' => $idUsuario, 'curso' => $idCurso]);
if ((int)$stmt->fetchColumn() === 0) {
    http_response_code(403);
    exit('No tenes permisos para descargar este certificado.');
}

$repo = new CursoProgresoRepository($pdo);
$certificado = $repo->emitirCertificadoSiCorresponde($idUsuario, $idCurso);
if (!$certificado) {
    http_response_code(403);
    exit('El certificado estara disponible cuando apruebes el curso.');
}

$alumno = trim(($certificado['nombre'] ?? '') . ' ' . ($certificado['apellido'] ?? ''));
$curso = (string)($certificado['curso_titulo'] ?? 'curso');
$archivoCurso = preg_replace('/[^a-z0-9-]+/i', '-', strtolower($curso));
$archivoCurso = trim((string)$archivoCurso, '-') ?: 'curso';

(new CertificadoPdf())->generar([
    'alumno' => $alumno,
    'curso' => $curso,
    'porcentaje' => (float)$certificado['porcentaje_aprobacion'],
    'fecha' => (string)$certificado['fecha_emision'],
    'codigo' => (string)$certificado['codigo_verificacion'],
    'archivo' => 'certificado-classia-' . $archivoCurso . '.pdf',
]);
