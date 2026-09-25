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

        return $usaReturning ? (int) $stmt->fetchColumn() : (int) $this->pdo->lastInsertId();
    }

    /**
     * Actualiza una publicacion. Si se pasa id de usuario, aplica ownership.
     */
    public function actualizar(Publicacion $publicacion, ?int $id_usuario_propietario = null): bool
    {
        $datos = $publicacion->toArray();
        $sql = "UPDATE publicaciones SET
            titulo=:titulo, descripcion=:descripcion, precio=:precio, tipo=:tipo,
            modalidad=:modalidad, nivel_experiencia=:nivel, duracion_horas=:duracion,
            cupos=:cupos, disponibilidad=:disponibilidad, tipo_servicio=:tipo_servicio,
            id_categoria=:categoria, estado=:estado, imagen=:imagen, eliminado_en=:eliminado
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
     * Recupera una publicacion como Curso o Servicio segun el tipo almacenado.
     */
    public function buscarPorId(int $id, ?int $id_usuario_propietario = null): ?Publicacion
    {
        $sql = 'SELECT * FROM publicaciones WHERE id_publicacion=:id';
        $params = ['id' => $id];

        if ($id_usuario_propietario !== null) {
            $sql .= ' AND id_usuario=:propietario';
            $params['propietario'] = $id_usuario_propietario;
        }

        $stmt = $this->pdo->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);
        $fila = $stmt->fetch();

        return $fila ? $this->hidratar($fila) : null;
    }

    /**
     * Convierte una fila de base de datos en el subtipo correcto.
     */
    private function hidratar(array $fila): Publicacion
    {
        return ($fila['tipo'] ?? '') === 'Servicio'
            ? new Servicio($fila)
            : new Curso($fila);
    }

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
            'tipo_servicio' => $datos['tipo'] === 'Servicio' ? $datos['tipo_servicio'] : null,
            'estado' => $datos['estado'],
            'imagen' => $datos['imagen'],
            'usuario' => $datos['id_usuario'],
            'categoria' => $datos['id_categoria'],
            'eliminado' => $datos['eliminado_en'],
        ];
    }
}
