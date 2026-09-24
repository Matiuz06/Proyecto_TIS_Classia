<?php

/**
 * Responsabilidad: Único punto de acceso a la base de datos para el contenido
 * estructurado de un curso (módulos, unidades y recursos).
 *
 * Arquitectura:
 *   Curso
 *    └── Modulo   (curso_modulos)
 *         └── Unidad   (curso_unidades)
 *              └── Recurso  (curso_recursos)
 *
 * Las clases de dominio (Modulo, Unidad, Recurso) son objetos puros sin PDO.
 * Este repositorio es el único responsable de las consultas SQL.
 */

// Cadena de herencia: ElementoCurso → Recurso / Unidad / Modulo
// Cada archivo carga su propia dependencia; basta con requerir Modulo.

require_once __DIR__ . '/Modulo.php';   // carga Unidad → Recurso → ElementoCurso

class ContenidoCursoRepository
{
    private PDO $pdo;

    /** Tablas válidas para el reordenamiento */
    private const TABLAS_ORDEN = ['curso_modulos', 'curso_unidades', 'curso_recursos'];
    private const DIRS_ORDEN   = ['subir', 'bajar'];

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    //LECTURA

    /**
     * Obtiene el curso verificando que pertenezca al docente (o admite admin).
     * Retorna null si no existe o no tiene permisos.
     */
    public function obtenerCursoDelDocente(int $id_publicacion, int $id_usuario, bool $admin = false): ?array
    {
        $sql    = "SELECT p.*, c.nombre_categoria
                   FROM publicaciones p
                   JOIN categorias c ON c.id_categoria = p.id_categoria
                   WHERE p.id_publicacion = :id AND p.tipo = 'Curso'";
        $params = ['id' => $id_publicacion];

        if (!$admin) {
            $sql           .= ' AND p.id_usuario = :u';
            $params['u']    = $id_usuario;
        }

        $s = $this->pdo->prepare($sql);
        $s->execute($params);
        return $s->fetch() ?: null;
    }

    /**
     * Carga los módulos de un curso con sus unidades y recursos,
     * devuelve un array de objetos Modulo (jerarquía completa).
     *
     * @return Modulo[]
     */
    public function obtenerPorCurso(int $id_publicacion): array
    {
        // 1. Módulos
        $s = $this->pdo->prepare(
            'SELECT * FROM curso_modulos WHERE id_publicacion = :id ORDER BY orden, id_modulo'
        );
        $s->execute(['id' => $id_publicacion]);
        $rows_mods = $s->fetchAll();

        if (empty($rows_mods)) {
            return [];
        }

        // 2. Unidades (todas las del curso en una sola consulta)
        $s_uni = $this->pdo->prepare("
            SELECT u.*
            FROM curso_unidades u
            JOIN curso_modulos m ON m.id_modulo = u.id_modulo
            WHERE m.id_publicacion = :id
            ORDER BY u.orden, u.id_unidad
        ");
        $s_uni->execute(['id' => $id_publicacion]);
        $rows_uni = $s_uni->fetchAll();

        // 3. Recursos (todos los del curso en una sola consulta)
        $s_rec = $this->pdo->prepare("
            SELECT r.*
            FROM curso_recursos r
            JOIN curso_unidades u ON u.id_unidad = r.id_unidad
            JOIN curso_modulos m ON m.id_modulo = u.id_modulo
            WHERE m.id_publicacion = :id
            ORDER BY r.orden, r.id_recurso
        ");
        $s_rec->execute(['id' => $id_publicacion]);
        $rows_rec = $s_rec->fetchAll();

        // 4. Construir objetos Recurso agrupados por unidad
        $recursos_por_unidad = [];
        foreach ($rows_rec as $row) {
            $id_u = (int) $row['id_unidad'];
            $recursos_por_unidad[$id_u][] = Recurso::fromArray($row);
        }

        // 5. Construir objetos Unidad agrupados por módulo, con sus recursos
        $unidades_por_modulo = [];
        foreach ($rows_uni as $row) {
            $unidad = Unidad::fromArray($row);
            foreach ($recursos_por_unidad[$unidad->getId()] ?? [] as $recurso) {
                $unidad->addRecurso($recurso);
            }
            $unidades_por_modulo[$unidad->getIdModulo()][] = $unidad;
        }

        // 6. Construir objetos Modulo con sus unidades
        $modulos = [];
        foreach ($rows_mods as $row) {
            $modulo = Modulo::fromArray($row);
            foreach ($unidades_por_modulo[$modulo->getId()] ?? [] as $unidad) {
                $modulo->addUnidad($unidad);
            }
            $modulos[] = $modulo;
        }

        return $modulos;
    }

    /**
     * Alias de obtenerPorCurso() que retorna arrays en lugar de objetos,
     * para compatibilidad con plantillas que iteran $modulo['unidades'].
     *
     * @return array[]
     */
    public function obtenerPorCursoComoArray(int $id_publicacion): array
    {
        return array_map(fn(Modulo $m) => $m->toArray(), $this->obtenerPorCurso($id_publicacion));
    }

    //MÓDULOS

    public function crearModulo(int $id_publicacion, string $titulo, ?string $descripcion, int $orden): int
    {
        $q = $this->pdo->prepare(
            'INSERT INTO curso_modulos(id_publicacion, titulo, descripcion, orden) VALUES(:p, :t, :d, :o)'
        );
        $q->execute(['p' => $id_publicacion, 't' => $titulo, 'd' => $descripcion, 'o' => max(1, $orden)]);
        return (int) $this->pdo->lastInsertId();
    }

    public function editarModulo(int $id_modulo, int $id_publicacion, string $titulo, ?string $descripcion, int $orden): void
    {
        $q = $this->pdo->prepare(
            'UPDATE curso_modulos SET titulo = :t, descripcion = :d, orden = :o WHERE id_modulo = :m AND id_publicacion = :p'
        );
        $q->execute(['t' => $titulo, 'd' => $descripcion, 'o' => max(1, $orden), 'm' => $id_modulo, 'p' => $id_publicacion]);
    }

    public function eliminarModulo(int $id_modulo, int $id_publicacion): void
    {
        $q = $this->pdo->prepare(
            'DELETE FROM curso_modulos WHERE id_modulo = :m AND id_publicacion = :p'
        );
        $q->execute(['m' => $id_modulo, 'p' => $id_publicacion]);
    }

    //UNIDADES

    public function verificarModuloPerteneceACurso(int $id_modulo, int $id_publicacion): bool
    {
        $c = $this->pdo->prepare(
            'SELECT COUNT(*) FROM curso_modulos WHERE id_modulo = :m AND id_publicacion = :p'
        );
        $c->execute(['m' => $id_modulo, 'p' => $id_publicacion]);
        return (bool) $c->fetchColumn();
    }

    public function crearUnidad(int $id_modulo, string $titulo, ?string $descripcion, int $orden): int
    {
        $q = $this->pdo->prepare(
            'INSERT INTO curso_unidades(id_modulo, titulo, descripcion, orden) VALUES(:m, :t, :d, :o)'
        );
        $q->execute(['m' => $id_modulo, 't' => $titulo, 'd' => $descripcion, 'o' => max(1, $orden)]);
        return (int) $this->pdo->lastInsertId();
    }

    public function editarUnidad(int $id_unidad, int $id_publicacion, string $titulo, ?string $descripcion, int $orden): void
    {
        $q = $this->pdo->prepare("
            UPDATE curso_unidades
            SET titulo = :t, descripcion = :d, orden = :o
            WHERE id_unidad = :u
              AND id_modulo IN (SELECT id_modulo FROM curso_modulos WHERE id_publicacion = :p)
        ");
        $q->execute(['t' => $titulo, 'd' => $descripcion, 'o' => max(1, $orden), 'u' => $id_unidad, 'p' => $id_publicacion]);
    }

    public function eliminarUnidad(int $id_unidad, int $id_publicacion): void
    {
        $q = $this->pdo->prepare("
            DELETE FROM curso_unidades
            WHERE id_unidad = :u
              AND id_modulo IN (SELECT id_modulo FROM curso_modulos WHERE id_publicacion = :p)
        ");
        $q->execute(['u' => $id_unidad, 'p' => $id_publicacion]);
    }

    /**
     * Obtiene el id_modulo al que pertenece una unidad, validando que
     * la unidad sea de un curso específico.
     */
    public function obtenerModuloDeLaUnidad(int $id_unidad, int $id_publicacion): int
    {
        $q = $this->pdo->prepare("
            SELECT u.id_modulo
            FROM curso_unidades u
            JOIN curso_modulos m ON m.id_modulo = u.id_modulo
            WHERE u.id_unidad = :u AND m.id_publicacion = :p
        ");
        $q->execute(['u' => $id_unidad, 'p' => $id_publicacion]);
        return (int) $q->fetchColumn();
    }

    public function verificarUnidadPerteneceACurso(int $id_unidad, int $id_publicacion): bool
    {
        $c = $this->pdo->prepare("
            SELECT COUNT(*)
            FROM curso_unidades u
            JOIN curso_modulos m ON m.id_modulo = u.id_modulo
            WHERE u.id_unidad = :u AND m.id_publicacion = :p
        ");
        $c->execute(['u' => $id_unidad, 'p' => $id_publicacion]);
        return (bool) $c->fetchColumn();
    }

    //RECURSOS

    /**
     * Obtiene un recurso del curso verificando pertenencia al curso.
     * Retorna null si no existe.
     */
    public function obtenerRecurso(int $id_recurso, int $id_publicacion): ?array
    {
        $q = $this->pdo->prepare("
            SELECT r.*
            FROM curso_recursos r
            JOIN curso_unidades u ON u.id_unidad = r.id_unidad
            JOIN curso_modulos m ON m.id_modulo = u.id_modulo
            WHERE r.id_recurso = :r AND m.id_publicacion = :p
        ");
        $q->execute(['r' => $id_recurso, 'p' => $id_publicacion]);
        return $q->fetch() ?: null;
    }

    public function crearRecurso(array $datos): int
    {
        $q = $this->pdo->prepare(
            'INSERT INTO curso_recursos(id_unidad, titulo, tipo, url, archivo, descripcion, orden)
             VALUES(:u, :t, :tipo, :url, :a, :d, :o)'
        );
        $q->execute($datos);
        return (int) $this->pdo->lastInsertId();
    }

    public function editarRecurso(int $id_recurso, array $datos): void
    {
        $datos['r'] = $id_recurso;
        $q = $this->pdo->prepare(
            'UPDATE curso_recursos
             SET id_unidad = :u, titulo = :t, tipo = :tipo, url = :url,
                 archivo = :a, descripcion = :d, orden = :o
             WHERE id_recurso = :r'
        );
        $q->execute($datos);
    }

    public function eliminarRecurso(int $id_recurso, int $id_publicacion): ?string
    {
        // Obtener archivo antes de eliminar para poder borrarlo del storage
        $q = $this->pdo->prepare("
            SELECT r.archivo
            FROM curso_recursos r
            JOIN curso_unidades u ON u.id_unidad = r.id_unidad
            JOIN curso_modulos m ON m.id_modulo = u.id_modulo
            WHERE r.id_recurso = :r AND m.id_publicacion = :p
        ");
        $q->execute(['r' => $id_recurso, 'p' => $id_publicacion]);
        $ruta = $q->fetchColumn() ?: null;

        $d = $this->pdo->prepare("
            DELETE FROM curso_recursos
            WHERE id_recurso = :r
              AND id_unidad IN (
                  SELECT u.id_unidad FROM curso_unidades u
                  JOIN curso_modulos m ON m.id_modulo = u.id_modulo
                  WHERE m.id_publicacion = :p
              )
        ");
        $d->execute(['r' => $id_recurso, 'p' => $id_publicacion]);

        return $ruta; // El caller borra el archivo del storage si aplica
    }

    public function obtenerUnidadDelRecurso(int $id_recurso, int $id_publicacion): int
    {
        $q = $this->pdo->prepare("
            SELECT r.id_unidad
            FROM curso_recursos r
            JOIN curso_unidades u ON u.id_unidad = r.id_unidad
            JOIN curso_modulos m ON m.id_modulo = u.id_modulo
            WHERE r.id_recurso = :r AND m.id_publicacion = :p
        ");
        $q->execute(['r' => $id_recurso, 'p' => $id_publicacion]);
        return (int) $q->fetchColumn();
    }

    //REORDENAMIENTO GENÉRICO

    /**
     * Intercambia el orden de dos elementos consecutivos en una tabla.
     *
     * @param string $tabla     Tabla en la que reordenar (lista blanca).
     * @param string $id_col    Columna de clave primaria del elemento.
     * @param int    $id        ID del elemento a mover.
     * @param string $scope_col Columna de ámbito (ej. id_publicacion, id_modulo…).
     * @param int    $scope_id  Valor de ámbito.
     * @param string $dir       'subir' o 'bajar'.
     */
    public function intercambiarOrden(
        string $tabla,
        string $id_col,
        int    $id,
        string $scope_col,
        int    $scope_id,
        string $dir
    ): bool {
        if (!in_array($tabla, self::TABLAS_ORDEN, true) || !in_array($dir, self::DIRS_ORDEN, true)) {
            return false;
        }

        $s = $this->pdo->prepare("SELECT $id_col, orden FROM $tabla WHERE $id_col = :id AND $scope_col = :scope");
        $s->execute(['id' => $id, 'scope' => $scope_id]);
        $actual = $s->fetch();
        if (!$actual) return false;

        $op  = $dir === 'subir' ? '<' : '>';
        $ord = $dir === 'subir' ? 'DESC' : 'ASC';
        $n   = $this->pdo->prepare("
            SELECT $id_col, orden
            FROM $tabla
            WHERE $scope_col = :scope AND orden $op :orden
            ORDER BY orden $ord, $id_col $ord
            LIMIT 1
        ");
        $n->execute(['scope' => $scope_id, 'orden' => (int) $actual['orden']]);
        $vecino = $n->fetch();
        if (!$vecino) return true;

        $this->pdo->beginTransaction();
        $u = $this->pdo->prepare("UPDATE $tabla SET orden = :orden WHERE $id_col = :id");
        $u->execute(['orden' => (int) $vecino['orden'],  'id' => $id]);
        $u->execute(['orden' => (int) $actual['orden'],  'id' => (int) $vecino[$id_col]]);
        return $this->pdo->commit();
    }
}
