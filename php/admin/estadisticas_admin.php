<?php

/**
 * Responsabilidad: Módulo de analíticas y estadísticas para el panel administrativo.
 * Requerimientos:
 * - REQ-ADM-02: Estadísticas de visitas y contrataciones por servicio y período.
 * - REQ-ADM-04: Panel de analíticas con gráficos de ingresos, contrataciones y valoraciones.
 */

require_once __DIR__ . '/../../config/database.php';

//Registra o incrementa el contador de visitas diario de una publicación.
function registrar_visita_publicacion(PDO $pdo, int $id_publicacion): void
{
    if ($id_publicacion <= 0) {
        return;
    }

    try {
        $fecha = date('Y-m-d');
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver === 'pgsql') {
            $stmt = $pdo->prepare(
                "INSERT INTO publicacion_visitas (id_publicacion, fecha_visita, visitas)
                 VALUES (:id_pub, :fecha, 1)
                 ON CONFLICT (id_publicacion, fecha_visita)
                 DO UPDATE SET visitas = publicacion_visitas.visitas + 1"
            );
        } else {
            $stmt = $pdo->prepare(
                "INSERT INTO publicacion_visitas (id_publicacion, fecha_visita, visitas)
                 VALUES (:id_pub, :fecha, 1)
                 ON DUPLICATE KEY UPDATE visitas = visitas + 1"
            );
        }

        $stmt->execute([
            ':id_pub' => $id_publicacion,
            ':fecha'  => $fecha,
        ]);
    } catch (PDOException $e) {
        error_log('Error al registrar visita de publicación: ' . $e->getMessage());
    }
}

//Determina las fechas de inicio y fin para el filtro de analíticas.
function obtener_rango_fechas_analiticas(string $periodo, ?string $fecha_desde = null, ?string $fecha_hasta = null): array
{
    $hoy = date('Y-m-d');

    switch ($periodo) {
        case '7d':
            $desde = date('Y-m-d', strtotime('-6 days'));
            $hasta = $hoy;
            $etiqueta = 'Últimos 7 días';
            break;

        case 'mes':
            $desde = date('Y-m-01');
            $hasta = date('Y-m-t');
            $etiqueta = 'Este mes (' . date('F Y') . ')';
            break;

        case 'anio':
            $desde = date('Y-01-01');
            $hasta = date('Y-12-31');
            $etiqueta = 'Este año (' . date('Y') . ')';
            break;

        case 'todo':
            $desde = '2020-01-01';
            $hasta = $hoy;
            $etiqueta = 'Todo el histórico';
            break;

        case 'custom':
            $desde = (!empty($fecha_desde) && strtotime($fecha_desde)) ? date('Y-m-d', strtotime($fecha_desde)) : date('Y-m-d', strtotime('-29 days'));
            $hasta = (!empty($fecha_hasta) && strtotime($fecha_hasta)) ? date('Y-m-d', strtotime($fecha_hasta)) : $hoy;
            if ($desde > $hasta) {
                $temp = $desde;
                $desde = $hasta;
                $hasta = $temp;
            }
            $etiqueta = 'Personalizado (' . date('d/m/Y', strtotime($desde)) . ' - ' . date('d/m/Y', strtotime($hasta)) . ')';
            break;

        case '30d':
        default:
            $periodo  = '30d';
            $desde    = date('Y-m-d', strtotime('-29 days'));
            $hasta    = $hoy;
            $etiqueta = 'Últimos 30 días';
            break;
    }

    return [
        'codigo'   => $periodo,
        'desde'    => $desde,
        'hasta'    => $hasta,
        'etiqueta' => $etiqueta,
    ];
}

//Obtiene métricas clave (KPIs) para el rango de fechas y filtro de servicio.
function obtener_kpis_analiticas(PDO $pdo, string $desde, string $hasta, int $id_publicacion = 0): array
{
    $kpis = [
        'ingresos_totales'       => 0.0,
        'contrataciones_totales' => 0,
        'visitas_totales'        => 0,
        'tasa_conversion'        => 0.0,
        'promedio_valoracion'    => 0.0,
        'total_valoraciones'     => 0,
    ];

    try {
        // 1. Ingresos y contrataciones
        $paramsContrat = [':desde' => $desde . ' 00:00:00', ':hasta' => $hasta . ' 23:59:59'];
        $filtroPub = '';
        if ($id_publicacion > 0) {
            $filtroPub = ' AND dc.id_publicacion = :id_pub ';
            $paramsContrat[':id_pub'] = $id_publicacion;
        }

        $sqlContrat = "
            SELECT 
                COUNT(DISTINCT c.id_contratacion) AS total_contrataciones,
                COALESCE(SUM(dc.precio_unitario), 0) AS total_ingresos
            FROM contrataciones c
            JOIN detalles_contratacion dc ON c.id_contratacion = dc.id_contratacion
            WHERE c.fecha_contratacion BETWEEN :desde AND :hasta
              AND c.estado IN ('Pendiente', 'En Proceso', 'Completada')
              {$filtroPub}
        ";
        $stmt = $pdo->prepare($sqlContrat);
        $stmt->execute($paramsContrat);
        $res = $stmt->fetch();
        if ($res) {
            $kpis['contrataciones_totales'] = (int) $res['total_contrataciones'];
            $kpis['ingresos_totales']       = (float) $res['total_ingresos'];
        }

        // 2. Visitas totales
        $paramsVisitas = [':desde' => $desde, ':hasta' => $hasta];
        $filtroVisPub = '';
        if ($id_publicacion > 0) {
            $filtroVisPub = ' AND id_publicacion = :id_pub ';
            $paramsVisitas[':id_pub'] = $id_publicacion;
        }

        $sqlVisitas = "
            SELECT COALESCE(SUM(visitas), 0) AS total_visitas
            FROM publicacion_visitas
            WHERE fecha_visita BETWEEN :desde AND :hasta
              {$filtroVisPub}
        ";
        $stmt = $pdo->prepare($sqlVisitas);
        $stmt->execute($paramsVisitas);
        $kpis['visitas_totales'] = (int) $stmt->fetchColumn();

        // 3. Tasa de conversión
        if ($kpis['visitas_totales'] > 0) {
            $kpis['tasa_conversion'] = round(($kpis['contrataciones_totales'] / $kpis['visitas_totales']) * 100, 2);
        }

        // 4. Valoraciones en el período
        $paramsVal = [':desde' => $desde . ' 00:00:00', ':hasta' => $hasta . ' 23:59:59'];
        $filtroValPub = '';
        if ($id_publicacion > 0) {
            $filtroValPub = ' AND id_publicacion = :id_pub ';
            $paramsVal[':id_pub'] = $id_publicacion;
        }

        $sqlVal = "
            SELECT 
                COUNT(*) AS total_val,
                COALESCE(AVG(puntuacion), 0) AS promedio_val
            FROM valoraciones
            WHERE fecha_valoracion BETWEEN :desde AND :hasta
              {$filtroValPub}
        ";
        $stmt = $pdo->prepare($sqlVal);
        $stmt->execute($paramsVal);
        $resVal = $stmt->fetch();
        if ($resVal) {
            $kpis['total_valoraciones']  = (int) $resVal['total_val'];
            $kpis['promedio_valoracion'] = round((float) $resVal['promedio_val'], 1);
        }
    } catch (PDOException $e) {
        error_log('Error en obtener_kpis_analiticas: ' . $e->getMessage());
    }

    return $kpis;
}

//Genera la serie temporal de datos (días o meses) para los gráficos interactivos.
function obtener_series_graficos(PDO $pdo, string $desde, string $hasta, int $id_publicacion = 0): array
{
    $labels          = [];
    $visitas_serie   = [];
    $contrat_serie   = [];
    $ingresos_serie  = [];

    try {
        $inicio = new DateTime($desde);
        $fin    = new DateTime($hasta);
        $fin->modify('+1 day');
        $intervalo = new DateInterval('P1D');
        $periodo = new DatePeriod($inicio, $intervalo, $fin);

        $mapVisitas = [];
        $mapContrat = [];
        $mapIngreso = [];

        foreach ($periodo as $dt) {
            $f = $dt->format('Y-m-d');
            $mapVisitas[$f] = 0;
            $mapContrat[$f] = 0;
            $mapIngreso[$f] = 0.0;
        }

        // Cargar visitas por día
        $pVis = [':desde' => $desde, ':hasta' => $hasta];
        $filtroPubVis = '';
        if ($id_publicacion > 0) {
            $filtroPubVis = ' AND id_publicacion = :id_pub ';
            $pVis[':id_pub'] = $id_publicacion;
        }
        $stmt = $pdo->prepare("
            SELECT fecha_visita, SUM(visitas) AS total
            FROM publicacion_visitas
            WHERE fecha_visita BETWEEN :desde AND :hasta
              {$filtroPubVis}
            GROUP BY fecha_visita
        ");
        $stmt->execute($pVis);
        foreach ($stmt->fetchAll() as $row) {
            $f = $row['fecha_visita'];
            if (isset($mapVisitas[$f])) {
                $mapVisitas[$f] = (int) $row['total'];
            }
        }

        // Cargar contrataciones e ingresos por día
        $pContrat = [':desde' => $desde . ' 00:00:00', ':hasta' => $hasta . ' 23:59:59'];
        $filtroPubContrat = '';
        if ($id_publicacion > 0) {
            $filtroPubContrat = ' AND dc.id_publicacion = :id_pub ';
            $pContrat[':id_pub'] = $id_publicacion;
        }
        $stmt = $pdo->prepare("
            SELECT 
                DATE(c.fecha_contratacion) AS fecha,
                COUNT(DISTINCT c.id_contratacion) AS total_contrat,
                COALESCE(SUM(dc.precio_unitario), 0) AS total_monto
            FROM contrataciones c
            JOIN detalles_contratacion dc ON c.id_contratacion = dc.id_contratacion
            WHERE c.fecha_contratacion BETWEEN :desde AND :hasta
              AND c.estado IN ('Pendiente', 'En Proceso', 'Completada')
              {$filtroPubContrat}
            GROUP BY DATE(c.fecha_contratacion)
        ");
        $stmt->execute($pContrat);
        foreach ($stmt->fetchAll() as $row) {
            $f = $row['fecha'];
            if (isset($mapContrat[$f])) {
                $mapContrat[$f] = (int) $row['total_contrat'];
                $mapIngreso[$f] = (float) $row['total_monto'];
            }
        }

        foreach ($mapVisitas as $f => $vis) {
            $labels[]         = date('d M', strtotime($f));
            $visitas_serie[]  = $vis;
            $contrat_serie[]  = $mapContrat[$f] ?? 0;
            $ingresos_serie[] = $mapIngreso[$f] ?? 0.0;
        }
    } catch (Exception $e) {
        error_log('Error en obtener_series_graficos: ' . $e->getMessage());
    }

    return [
        'labels'         => $labels,
        'visitas'        => $visitas_serie,
        'contrataciones' => $contrat_serie,
        'ingresos'       => $ingresos_serie,
    ];
}

//Obtiene la distribución de valoraciones (1 a 5 estrellas) para el gráfico de satisfacción.
function obtener_distribucion_valoraciones(PDO $pdo, string $desde, string $hasta, int $id_publicacion = 0): array
{
    $distribucion = [
        '1' => 0,
        '2' => 0,
        '3' => 0,
        '4' => 0,
        '5' => 0,
    ];

    try {
        $params = [':desde' => $desde . ' 00:00:00', ':hasta' => $hasta . ' 23:59:59'];
        $filtro = '';
        if ($id_publicacion > 0) {
            $filtro = ' AND id_publicacion = :id_pub ';
            $params[':id_pub'] = $id_publicacion;
        }

        $sql = "
            SELECT puntuacion, COUNT(*) AS total
            FROM valoraciones
            WHERE fecha_valoracion BETWEEN :desde AND :hasta
              {$filtro}
            GROUP BY puntuacion
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        foreach ($stmt->fetchAll() as $row) {
            $pts = (string) $row['puntuacion'];
            if (isset($distribucion[$pts])) {
                $distribucion[$pts] = (int) $row['total'];
            }
        }
    } catch (PDOException $e) {
        error_log('Error en obtener_distribucion_valoraciones: ' . $e->getMessage());
    }

    return $distribucion;
}

//Obtiene el top de publicaciones por volumen de ingresos en el período.
function obtener_top_servicios_ingresos(PDO $pdo, string $desde, string $hasta, int $limit = 5): array
{
    try {
        $sql = "
            SELECT 
                p.id_publicacion,
                p.titulo,
                p.tipo,
                COALESCE(SUM(dc.precio_unitario), 0) AS total_ingresos,
                COUNT(DISTINCT c.id_contratacion) AS total_contrataciones
            FROM publicaciones p
            JOIN detalles_contratacion dc ON p.id_publicacion = dc.id_publicacion
            JOIN contrataciones c ON dc.id_contratacion = c.id_contratacion
            WHERE c.fecha_contratacion BETWEEN :desde AND :hasta
              AND c.estado IN ('Pendiente', 'En Proceso', 'Completada')
            GROUP BY p.id_publicacion, p.titulo, p.tipo
            ORDER BY total_ingresos DESC
            LIMIT :limite
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':desde', $desde . ' 00:00:00');
        $stmt->bindValue(':hasta', $hasta . ' 23:59:59');
        $stmt->bindValue(':limite', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Error en obtener_top_servicios_ingresos: ' . $e->getMessage());
        return [];
    }
}

//Obtiene el desglose tabular de estadísticas por servicio y período
function obtener_desglose_servicios_periodo(PDO $pdo, string $desde, string $hasta, int $id_publicacion = 0): array
{
    try {
        $params = [
            ':desde_vis' => $desde,
            ':hasta_vis' => $hasta,
            ':desde_cnt' => $desde . ' 00:00:00',
            ':hasta_cnt' => $hasta . ' 23:59:59',
            ':desde_val' => $desde . ' 00:00:00',
            ':hasta_val' => $hasta . ' 23:59:59',
        ];

        $filtro = '';
        if ($id_publicacion > 0) {
            $filtro = ' WHERE p.id_publicacion = :id_pub ';
            $params[':id_pub'] = $id_publicacion;
        }

        $sql = "
            SELECT 
                p.id_publicacion,
                p.titulo,
                p.tipo,
                p.precio,
                p.estado,
                c.nombre_categoria,
                CONCAT(u.nombre, ' ', u.apellido) AS docente_nombre,
                COALESCE(vis.total_visitas, 0) AS visitas_periodo,
                COALESCE(cnt.total_contrataciones, 0) AS contrataciones_periodo,
                COALESCE(cnt.total_ingresos, 0) AS ingresos_periodo,
                COALESCE(val.promedio_val, 0) AS promedio_valoracion,
                COALESCE(val.total_val, 0) AS total_valoraciones
            FROM publicaciones p
            JOIN categorias c ON p.id_categoria = c.id_categoria
            JOIN usuarios u ON p.id_usuario = u.id_usuario
            LEFT JOIN (
                SELECT id_publicacion, SUM(visitas) AS total_visitas
                FROM publicacion_visitas
                WHERE fecha_visita BETWEEN :desde_vis AND :hasta_vis
                GROUP BY id_publicacion
            ) vis ON p.id_publicacion = vis.id_publicacion
            LEFT JOIN (
                SELECT 
                    dc.id_publicacion,
                    COUNT(DISTINCT co.id_contratacion) AS total_contrataciones,
                    SUM(dc.precio_unitario) AS total_ingresos
                FROM detalles_contratacion dc
                JOIN contrataciones co ON dc.id_contratacion = co.id_contratacion
                WHERE co.fecha_contratacion BETWEEN :desde_cnt AND :hasta_cnt
                  AND co.estado IN ('Pendiente', 'En Proceso', 'Completada')
                GROUP BY dc.id_publicacion
            ) cnt ON p.id_publicacion = cnt.id_publicacion
            LEFT JOIN (
                SELECT 
                    id_publicacion,
                    AVG(puntuacion) AS promedio_val,
                    COUNT(*) AS total_val
                FROM valoraciones
                WHERE fecha_valoracion BETWEEN :desde_val AND :hasta_val
                GROUP BY id_publicacion
            ) val ON p.id_publicacion = val.id_publicacion
            {$filtro}
            ORDER BY contrataciones_periodo DESC, visitas_periodo DESC, p.titulo ASC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $filas = $stmt->fetchAll();

        foreach ($filas as &$fila) {
            $vis = (int) $fila['visitas_periodo'];
            $cnt = (int) $fila['contrataciones_periodo'];
            $fila['conversion_pct'] = ($vis > 0) ? round(($cnt / $vis) * 100, 2) : 0.0;
        }

        return $filas;
    } catch (PDOException $e) {
        error_log('Error en obtener_desglose_servicios_periodo: ' . $e->getMessage());
        return [];
    }
}
