<?php

require_once __DIR__ . '/Curso.php';
require_once __DIR__ . '/Servicio.php';

/**
 * Gestiona la persistencia de publicaciones sin acoplar las entidades a PDO.
 */
class PublicacionRepository
{
    private PDO $pdo;

    /** Recibe la conexion PDO compartida del proyecto. */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Guarda una publicacion nueva y devuelve su id.
     */
    public function crear(Publicacion $publicacion): int
    {
        $datos = $publicacion->toArray();
        $usaReturning = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql';

        $sql = "INSERT INTO publicaciones
            (titulo,descripcion,precio,tipo,modalidad,nivel_experiencia,duracion_horas,cupos,disponibilidad,tipo_servicio,estado,imagen,id_usuario,id_categoria)
            VALUES (:titulo,:descripcion,:precio,:tipo,:modalidad,:nivel,:duracion,:cupos,:disponibilidad,:tipo_servicio,:estado,:imagen,:usuario,:categoria)";

        if ($usaReturning) {
            $sql .= ' RETURNING id_publicacion';
        }

        $stmt = $this->pdo->prepare($sql);
        $params = $this->parametrosGuardar($datos);

        unset($params['eliminado']);

        $stmt->execute($params);

        return $usaReturning
            ? (int) $stmt->fetchColumn()
            : (int) $this->pdo->lastInsertId();
    }

    /**
     * Actualiza una publicacion.
     * Si se pasa id de usuario, aplica validacion de propietario.
     */
    public function actualizar(
        Publicacion $publicacion,
        ?int $id_usuario_propietario = null
    ): bool {
        $datos = $publicacion->toArray();

        $sql = "UPDATE publicaciones SET
            titulo=:titulo,
            descripcion=:descripcion,
            precio=:precio,
            tipo=:tipo,
            modalidad=:modalidad,
            nivel_experiencia=:nivel,
            duracion_horas=:duracion,
            cupos=:cupos,
            disponibilidad=:disponibilidad,
            tipo_servicio=:tipo_servicio,
            id_categoria=:categoria,
            estado=:estado,
            imagen=:imagen,
            eliminado_en=:eliminado
            WHERE id_publicacion=:id";

        $params = $this->parametrosGuardar($datos);

        $params['id'] = (int) $datos['id_publicacion'];

        unset($params['usuario']);

        if ($id_usuario_propietario !== null) {
            $sql .= ' AND id_usuario=:propietario';
            $params['propietario'] = $id_usuario_propietario;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount() > 0;
    }

    /**
     * Recupera una publicacion como Curso o Servicio
     * segun el tipo almacenado.
     */
    public function buscarPorId(
        int $id,
        ?int $id_usuario_propietario = null
    ): ?Publicacion {
        $sql = 'SELECT * FROM publicaciones WHERE id_publicacion=:id';

        $params = [
            'id' => $id
        ];

        if ($id_usuario_propietario !== null) {
            $sql .= ' AND id_usuario=:propietario';
            $params['propietario'] = $id_usuario_propietario;
        }

        $stmt = $this->pdo->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);

        $fila = $stmt->fetch();

        return $fila
            ? $this->hidratar($fila)
            : null;
    }

    /**
     * Obtiene las publicaciones activas del catálogo.
     *
     * Permite:
     * - búsqueda por título, descripción, categoría o autor;
     * - filtro por tipo;
     * - filtro por categoría;
     * - ordenamiento por fecha, valoración, popularidad o precio.
     */
    public function obtenerCatalogo(
        string $busqueda = '',
        string $tipo_filtro = '',
        int $categoria_filtro = 0,
        string $orden = 'reciente'
    ): array {
        $sql = "SELECT
                p.*,
                c.nombre_categoria,
                u.nombre AS autor_nombre,
                u.apellido AS autor_apellido,
                COALESCE(AVG(v.puntuacion), 0) AS promedio_valoracion,
                COUNT(DISTINCT dc.id_contratacion) AS total_contrataciones
            FROM publicaciones p
            JOIN categorias c
                ON p.id_categoria = c.id_categoria
            JOIN usuarios u
                ON p.id_usuario = u.id_usuario
            LEFT JOIN valoraciones v
                ON v.id_publicacion = p.id_publicacion
            LEFT JOIN detalles_contratacion dc
                ON dc.id_publicacion = p.id_publicacion
            WHERE p.estado = 'Activo'";

        $params = [];

        /*
         * Búsqueda general.
         */
        if ($busqueda !== '') {
            $sql .= " AND (
                p.titulo LIKE :b_titulo
                OR p.descripcion LIKE :b_desc
                OR c.nombre_categoria LIKE :b_cat
                OR u.nombre LIKE :b_nom
                OR u.apellido LIKE :b_ape
            )";

            $term = '%' . $busqueda . '%';

            $params['b_titulo'] = $term;
            $params['b_desc'] = $term;
            $params['b_cat'] = $term;
            $params['b_nom'] = $term;
            $params['b_ape'] = $term;
        }

        /*
         * Filtro por tipo.
         */
        if ($tipo_filtro === 'curso') {
            $sql .= " AND p.tipo = 'Curso'";
        } elseif ($tipo_filtro === 'servicio') {
            $sql .= " AND p.tipo = 'Servicio'";
        }

        /*
         * Filtro por categoría.
         */
        if ($categoria_filtro > 0) {
            $sql .= " AND p.id_categoria = :categoria";
            $params['categoria'] = $categoria_filtro;
        }

        /*
         * Agrupación necesaria para las funciones AVG y COUNT.
         */
        $sql .= " GROUP BY
            p.id_publicacion,
            c.nombre_categoria,
            u.nombre,
            u.apellido";

        /*
         * Ordenamiento.
         *
         * Los valores posibles son controlados previamente
         * por una whitelist.
         */
        $orden_sql = match ($orden) {
            'valoracion' =>
                'promedio_valoracion DESC,
                 total_contrataciones DESC,
                 p.fecha_creacion DESC',

            'popularidad' =>
                'total_contrataciones DESC,
                 promedio_valoracion DESC,
                 p.fecha_creacion DESC',

            'precio_asc' =>
                'p.precio ASC,
                 p.fecha_creacion DESC',

            'precio_desc' =>
                'p.precio DESC,
                 p.fecha_creacion DESC',

            default =>
                'p.fecha_creacion DESC',
        };

        $sql .= " ORDER BY $orden_sql";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        $publicaciones = [];

        while ($fila = $stmt->fetch()) {
            $publicaciones[] = $this->hidratar($fila);
        }

        return $publicaciones;
    }

// Obtiene las publicaciones pertenecientes a un usuario.
    public function obtenerPorUsuario(int $id_usuario): array
    {
        $sql = "SELECT *
            FROM publicaciones
            WHERE id_usuario = :id_usuario
            ORDER BY fecha_creacion DESC";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            'id_usuario' => $id_usuario
        ]);

        $publicaciones = [];

        while ($fila = $stmt->fetch()) {
            $publicaciones[] = $this->hidratar($fila);
        }

        return $publicaciones;
    }

// Cambia el estado de una publicación y permite validar su propietario.
    public function cambiarEstado(
        int $id_publicacion,
        string $estado,
        ?int $id_usuario_propietario = null
    ): bool {
        $sql = "UPDATE publicaciones
            SET estado = :estado
            WHERE id_publicacion = :id";

        $params = [
            'estado' => $estado,
            'id' => $id_publicacion
        ];

        if ($id_usuario_propietario !== null) {
            $sql .= " AND id_usuario = :propietario";

            $params['propietario'] = $id_usuario_propietario;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount() > 0;
    }

    /**
     * Convierte una fila de base de datos
     * en el subtipo correcto.
     */
    private function hidratar(array $fila): Publicacion
    {
        return ($fila['tipo'] ?? '') === 'Servicio'
            ? new Servicio($fila)
            : new Curso($fila);
    }

    /**
     * Prepara los parámetros utilizados
     * para crear o actualizar una publicación.
     */
    private function parametrosGuardar(array $datos): array
    {
        return [
            'titulo' => $datos['titulo'],
            'descripcion' => $datos['descripcion'],
            'precio' => $datos['precio'],
            'tipo' => $datos['tipo'],
            'modalidad' => $datos['modalidad'],
            'nivel' => $datos['nivel_experiencia'],
            'duracion' => $datos['duracion_horas'],
            'cupos' => $datos['cupos'],
            'disponibilidad' => $datos['disponibilidad'],
            'tipo_servicio' => $datos['tipo'] === 'Servicio'
                ? $datos['tipo_servicio']
                : null,
            'estado' => $datos['estado'],
            'imagen' => $datos['imagen'],
            'usuario' => $datos['id_usuario'],
            'categoria' => $datos['id_categoria'],
            'eliminado' => $datos['eliminado_en'],
        ];
    }
}
