<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../solicitudes/plantillas_servicio.php';

function obtener_categorias(PDO $pdo): array
{
    return $pdo->query("SELECT id_categoria,nombre_categoria,descripcion FROM categorias ORDER BY nombre_categoria")->fetchAll();
}

function resolver_categoria_publicacion(PDO $pdo, int $id_usuario, int $id_categoria, string $nueva_categoria, string $descripcion_categoria = ''): array
{
    $nueva_categoria = trim($nueva_categoria);
    if ($nueva_categoria !== '') {
        if (mb_strlen($nueva_categoria) > 100) {
            return ['ok'=>false,'error'=>'La nueva categoría no puede superar 100 caracteres.','id'=>0];
        }
        $stmt=$pdo->prepare("SELECT id_categoria FROM categorias WHERE LOWER(nombre_categoria)=LOWER(:n) LIMIT 1");
        $stmt->execute(['n'=>$nueva_categoria]);
        $existente=$stmt->fetchColumn();
        if ($existente) return ['ok'=>true,'error'=>'','id'=>(int)$existente];
        $stmt=$pdo->prepare("INSERT INTO categorias (nombre_categoria,descripcion,creada_por) VALUES (:n,:d,:u)");
        $stmt->execute(['n'=>$nueva_categoria,'d'=>trim($descripcion_categoria) ?: null,'u'=>$id_usuario]);
        return ['ok'=>true,'error'=>'','id'=>(int)$pdo->lastInsertId()];
    }
    if ($id_categoria <= 0) return ['ok'=>false,'error'=>'Seleccioná una categoría o creá una nueva.','id'=>0];
    $stmt=$pdo->prepare("SELECT id_categoria FROM categorias WHERE id_categoria=:id");
    $stmt->execute(['id'=>$id_categoria]);
    if (!$stmt->fetchColumn()) return ['ok'=>false,'error'=>'La categoría seleccionada no existe.','id'=>0];
    return ['ok'=>true,'error'=>'','id'=>$id_categoria];
}

function validar_datos_publicacion(array $data): array
{
    $errores=[];
    $titulo=trim($data['titulo'] ?? '');
    $descripcion=trim($data['descripcion'] ?? '');
    $tipo=trim($data['tipo'] ?? '');
    $precio=$data['precio'] ?? '';
    if ($titulo==='' || $descripcion==='' || $tipo==='' || $precio==='') $errores[]='Completá todos los campos obligatorios.';
    if (mb_strlen($titulo)>200) $errores[]='El título no puede superar 200 caracteres.';
    if (!in_array($tipo,['Curso','Servicio'],true)) $errores[]='El tipo de publicación no es válido.';
    if (!is_numeric($precio) || (float)$precio<=0 || (float)$precio>99999999.99) $errores[]='El precio no es válido.';

    $modalidad=trim((string)($data['modalidad'] ?? ''));
    if ($modalidad!=='' && !in_array($modalidad,['virtual','presencial','hibrida','producto-entregable'],true)) {
        $errores[]='La modalidad no es válida.';
    }
    $nivel=trim((string)($data['nivel_experiencia'] ?? ''));
    if ($nivel!=='' && !in_array($nivel,['inicial','intermedio','avanzado','depende-categoria'],true)) {
        $errores[]='El nivel de experiencia no es válido.';
    }
    $duracion=trim((string)($data['duracion_horas'] ?? ''));
    if ($duracion!=='' && (!ctype_digit($duracion) || (int)$duracion<1 || (int)$duracion>10000)) {
        $errores[]='La duración debe ser una cantidad de horas válida.';
    }
    $cupos=trim((string)($data['cupos'] ?? ''));
    if ($cupos!=='' && (!ctype_digit($cupos) || (int)$cupos<1)) $errores[]='Los cupos deben ser un entero mayor a cero.';

    if ($tipo==='Servicio') {
        $ts=trim((string)($data['tipo_servicio'] ?? ''));
        if ($ts!=='') {
            $plantillas=plantillas_servicio();
            if (!isset($plantillas[$ts])) $errores[]='La plantilla seleccionada para el servicio no es válida.';
        }
    }
    return $errores;
}
