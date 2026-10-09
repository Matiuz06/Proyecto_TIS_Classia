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
require_once __DIR__ . '/PublicacionRepository.php';


$repository = new PublicacionRepository($pdo);


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


try {
   $publicaciones = $repository->obtenerCatalogo(
       $busqueda,
       $tipo_filtro,
       $categoria_filtro,
       $orden
   );
} catch (PDOException $e) {
   error_log("Error al consultar catalogo: " . $e->getMessage());
   $publicaciones = [];
}


$cursos = array_filter($publicaciones, fn($p) => $p->getTipo() === 'Curso');
$servicios = array_filter($publicaciones, fn($p) => $p->getTipo() === 'Servicio');

if (!function_exists('puntaje_recomendacion')) {
    function puntaje_recomendacion(Publicacion $publicacion, array $preferencias): int {
        if (empty($preferencias)) {
            return 0;
        }
        
        $puntaje = 0;
        $categorias_preferidas = $preferencias['categorias'] ?? [];
        $categorias_preferidas = is_array($categorias_preferidas)
        ? $categorias_preferidas
        : [$categorias_preferidas];
        
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

        $nombre_categoria = $publicacion->getNombreCategoria() ?? '';

        $nombre_categoria = function_exists('mb_strtolower')
            ? mb_strtolower($nombre_categoria)
            : strtolower($nombre_categoria);

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

        if ( 
            $publicacion->getTipo() === 'Curso' &&
            in_array('explorar-servicios', $usos, true)
            ) {
            $puntaje += 2;
        }

        if (
            $publicacion->getTipo() === 'Servicio' &&
            in_array('contratar-servicios', $usos, true)
        ) {
            $puntaje += 2;
        }

        if (
            $publicacion->getTipo() === 'Servicio' &&
            in_array('publicar-servicios', $usos, true)
        ) {
            $puntaje += 1;
        }

        $presupuesto = $preferencias['presupuesto'] ?? '';
        $precio = $publicacion->getPrecio();

        $rangos_presupuesto = [
            'gratuito' => $precio <= 0,
            'menos-de-1000' => $precio < 1000,
            'entre-1000-y-3000' => $precio >= 1000 && $precio <= 3000,
            'entre-3000-y-10000' => $precio > 3000 && $precio <= 10000,
            'mas-de-10000' => $precio > 10000,
        ];

        if (
            isset($rangos_presupuesto[$presupuesto]) &&
            $rangos_presupuesto[$presupuesto]
        ) {
            $puntaje += 4;
        }

        $modalidades = $preferencias['modalidades'] ?? [];
        $modalidades = is_array($modalidades)
            ? $modalidades
            : [$modalidades];

        if (
            $publicacion->getModalidad() !== null &&
            in_array($publicacion->getModalidad(), $modalidades, true)
        ) {
            $puntaje += 5;
        }

        $nivel_preferido = $preferencias['nivel_experiencia'] ?? '';

        if (
            $nivel_preferido !== '' &&
            $publicacion->getNivelExperiencia() === $nivel_preferido
        ) {
            $puntaje += 5;
        }

        $duracion_preferida = $preferencias['duracion_preferida'] ?? '';
        $duracion = $publicacion->getDuracionHoras();

        $coincide_duracion = [
            'menos-de-10-horas' =>
                $duracion !== null &&
                $duracion > 0 &&
                $duracion < 10,

            'entre-10-y-20-horas' =>
                $duracion !== null &&
                $duracion >= 10 &&
                $duracion <= 20,

            'mas-de-20-horas' =>
                $duracion !== null &&
                $duracion > 20,
        ];

        if (
            isset($coincide_duracion[$duracion_preferida]) &&
            $coincide_duracion[$duracion_preferida]
        ) {
            $puntaje += 4;
        }

        return $puntaje;
    }
}
$recomendaciones = [];
$personalizacion_aceptada = ($preferencias_usuario['acepta_personalizacion'] ?? '') === 'si';
if ($personalizacion_aceptada && empty($busqueda) && empty($tipo_filtro) && $categoria_filtro === 0) {
   $publicaciones_recomendadas = $publicaciones;
   usort($publicaciones_recomendadas, function (Publicacion $a, Publicacion $b) use ($preferencias_usuario): int {
       $puntaje_a = puntaje_recomendacion($a, $preferencias_usuario);
       $puntaje_b = puntaje_recomendacion($b, $preferencias_usuario);


       if ($puntaje_a === $puntaje_b) {
           return strcmp((string) $b->getFechaCreacion(), (string) $a->getFechaCreacion());
       }


       return $puntaje_b <=> $puntaje_a;
   });
   $recomendaciones = array_slice(array_filter(
       $publicaciones_recomendadas,
       fn(Publicacion $publicacion): bool => puntaje_recomendacion($publicacion, $preferencias_usuario) > 0
   ), 0, 6);
}
