<?php

/**
 * Responsabilidad: Obtiene publicaciones activas filtradas para el catálogo.
 */

require_once __DIR__ . '/../auth/sesion.php';
iniciar_sesion();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once __DIR__ . '/../../config/database.php';

$busqueda        = trim($_GET['busqueda'] ?? '');
$tipo_filtro     = trim($_GET['tipo'] ?? '');
$categoria_filtro = isset($_GET['categoria']) ? (int) $_GET['categoria'] : 0;

// Whitelist de criterios de ordenamiento permitidos (REQ-SER-06)
$ordenes_permitidos = ['reciente', 'valoracion', 'popularidad', 'precio_asc', 'precio_desc'];
$orden = in_array($_GET['orden'] ?? '', $ordenes_permitidos, true)
    ? $_GET['orden']
    : 'reciente';
$preferencias_usuario = [];

if (esta_autenticado()) {
    try {
        $stmt_preferencias = $pdo->prepare('SELECT onboarding_data FROM usuarios WHERE id_usuario = :id LIMIT 1');
        $stmt_preferencias->execute(['id' => (int) usuario_actual()['id_usuario']]);
        $preferencias_guardadas = $stmt_preferencias->fetchColumn();
        $preferencias_usuario = json_decode($preferencias_guardadas ?: '{}', true);
        $preferencias_usuario = is_array($preferencias_usuario) ? $preferencias_usuario : [];
    } catch (PDOException $e) {
        error_log("Error al consultar preferencias del usuario: " . $e->getMessage());
    }
}

$categorias = [];
try {
    $stmt_cat = $pdo->query("SELECT id_categoria, nombre_categoria FROM categorias ORDER BY nombre_categoria ASC");
    $categorias = $stmt_cat->fetchAll();
} catch (PDOException $e) {
    error_log("Error al consultar categorias: " . $e->getMessage());
}

// JOIN con valoraciones y contrataciones para soportar ordenamiento dinámico
$sql = "SELECT
            p.*,
            c.nombre_categoria,
            u.nombre  AS autor_nombre,
            u.apellido AS autor_apellido,
            COALESCE(AVG(v.puntuacion), 0)     AS promedio_valoracion,
            COUNT(DISTINCT dc.id_contratacion) AS total_contrataciones
        FROM publicaciones p
        JOIN categorias c  ON p.id_categoria = c.id_categoria
        JOIN usuarios u    ON p.id_usuario   = u.id_usuario
        LEFT JOIN valoraciones v
            ON v.id_publicacion = p.id_publicacion
        LEFT JOIN detalles_contratacion dc
            ON dc.id_publicacion = p.id_publicacion
        WHERE p.estado = 'Activo'";
$params = [];

if ($busqueda !== '') {
    $sql .= " AND (p.titulo LIKE :b_titulo OR p.descripcion LIKE :b_desc OR c.nombre_categoria LIKE :b_cat OR u.nombre LIKE :b_nom OR u.apellido LIKE :b_ape)";
    $term = '%' . $busqueda . '%';
    $params['b_titulo'] = $term;
    $params['b_desc']   = $term;
    $params['b_cat']    = $term;
    $params['b_nom']    = $term;
    $params['b_ape']    = $term;
}

if ($tipo_filtro === 'curso') {
    $sql .= " AND p.tipo = 'Curso'";
} elseif ($tipo_filtro === 'servicio') {
    $sql .= " AND p.tipo = 'Servicio'";
}

if ($categoria_filtro > 0) {
    $sql .= " AND p.id_categoria = :categoria";
    $params['categoria'] = $categoria_filtro;
}

// GROUP BY necesario por los LEFT JOINs de agregación
$sql .= " GROUP BY p.id_publicacion, c.nombre_categoria, u.nombre, u.apellido";

// Ordenamiento dinámico
$orden_sql = match ($orden) {
    'valoracion'  => 'promedio_valoracion DESC, total_contrataciones DESC, p.fecha_creacion DESC',
    'popularidad' => 'total_contrataciones DESC, promedio_valoracion DESC, p.fecha_creacion DESC',
    'precio_asc'  => 'p.precio ASC,  p.fecha_creacion DESC',
    'precio_desc' => 'p.precio DESC, p.fecha_creacion DESC',
    default       => 'p.fecha_creacion DESC',
};
$sql .= " ORDER BY $orden_sql";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $publicaciones = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error al consultar catalogo: " . $e->getMessage());
    $publicaciones = [];
}

$cursos = array_filter($publicaciones, fn($p) => $p['tipo'] === 'Curso');
$servicios = array_filter($publicaciones, fn($p) => $p['tipo'] === 'Servicio');

if (!function_exists('puntaje_recomendacion')) {
    function puntaje_recomendacion(array $publicacion, array $preferencias): int
    {
        if (empty($preferencias)) {
            return 0;
        }

        $puntaje = 0;
        $categorias_preferidas = $preferencias['categorias'] ?? [];
        $categorias_preferidas = is_array($categorias_preferidas) ? $categorias_preferidas : [$categorias_preferidas];
        $mapa_categorias = [
            'programacion' => ['programación', 'desarrollo'],
            'robotica-automatizacion' => ['robótica', 'automatización', 'electrónica'],
            'diseno-impresion-3d' => ['3d', 'diseño e impresión'],
            'mentorias' => ['mentorías', 'capacitación'],
            'diseno' => ['diseño', 'ux', 'interfaz'],
            'educacion' => ['educación', 'didáctica'],
            'gestion' => ['gestión', 'proyectos'],
            'formacion-institucional' => ['gestión', 'proyectos'],
            'proyectos-educativos' => ['proyectos', 'educación'],
        ];
        $nombre_categoria = function_exists('mb_strtolower')
            ? mb_strtolower($publicacion['nombre_categoria'] ?? '')
            : strtolower($publicacion['nombre_categoria'] ?? '');

        foreach ($categorias_preferidas as $categoria) {
            foreach ($mapa_categorias[$categoria] ?? [] as $termino) {
                if (strpos($nombre_categoria, $termino) !== false) {
                    $puntaje += 10;
                    break;
                }
            }
        }

        $usos = $preferencias['uso_plataforma'] ?? [];
        $usos = is_array($usos) ? $usos : [$usos];
        if ($publicacion['tipo'] === 'Curso' && in_array('explorar-servicios', $usos, true)) {
            $puntaje += 2;
        }
        if ($publicacion['tipo'] === 'Servicio' && in_array('contratar-servicios', $usos, true)) {
            $puntaje += 2;
        }
        if (in_array('publicar-servicios', $usos, true) && $publicacion['tipo'] === 'Servicio') {
            $puntaje += 1;
        }

        $presupuesto = $preferencias['presupuesto'] ?? '';
        $precio = (float) ($publicacion['precio'] ?? 0);
        $rangos_presupuesto = [
            'gratuito' => $precio <= 0,
            'menos-de-1000' => $precio < 1000,
            'entre-1000-y-3000' => $precio >= 1000 && $precio <= 3000,
            'entre-3000-y-10000' => $precio > 3000 && $precio <= 10000,
            'mas-de-10000' => $precio > 10000,
        ];
        if (!empty($rangos_presupuesto[$presupuesto]) && $rangos_presupuesto[$presupuesto]) {
            $puntaje += 4;
        }

        $modalidades = $preferencias['modalidades'] ?? [];
        $modalidades = is_array($modalidades) ? $modalidades : [$modalidades];
        if (!empty($publicacion['modalidad']) && in_array($publicacion['modalidad'], $modalidades, true)) {
            $puntaje += 5;
        }

        $nivel_preferido = $preferencias['nivel_experiencia'] ?? '';
        if ($nivel_preferido !== '' && $publicacion['nivel_experiencia'] === $nivel_preferido) {
            $puntaje += 5;
        }

        $duracion_preferida = $preferencias['duracion_preferida'] ?? '';
        $duracion = (int) ($publicacion['duracion_horas'] ?? 0);
        $coincide_duracion = [
            'menos-de-10-horas' => $duracion > 0 && $duracion < 10,
            'entre-10-y-20-horas' => $duracion >= 10 && $duracion <= 20,
            'mas-de-20-horas' => $duracion > 20,
        ];
        if (!empty($coincide_duracion[$duracion_preferida]) && $coincide_duracion[$duracion_preferida]) {
            $puntaje += 4;
        }

        return $puntaje;
    }
}

$recomendaciones = [];
$personalizacion_aceptada = ($preferencias_usuario['acepta_personalizacion'] ?? '') === 'si';
if ($personalizacion_aceptada && empty($busqueda) && empty($tipo_filtro) && $categoria_filtro === 0) {
    $publicaciones_recomendadas = $publicaciones;
    usort($publicaciones_recomendadas, function (array $a, array $b) use ($preferencias_usuario): int {
        $puntaje_a = puntaje_recomendacion($a, $preferencias_usuario);
        $puntaje_b = puntaje_recomendacion($b, $preferencias_usuario);

        if ($puntaje_a === $puntaje_b) {
            return strcmp((string) $b['fecha_creacion'], (string) $a['fecha_creacion']);
        }

        return $puntaje_b <=> $puntaje_a;
    });
    $recomendaciones = array_slice(array_filter(
        $publicaciones_recomendadas,
        fn(array $publicacion): bool => puntaje_recomendacion($publicacion, $preferencias_usuario) > 0
    ), 0, 6);
}
