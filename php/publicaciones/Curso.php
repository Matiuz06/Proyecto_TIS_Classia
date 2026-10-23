<?php

require_once __DIR__ . '/Publicacion.php';

/**
 * Publicacion de tipo curso.
 *
 * Los datos estructurados del curso viven en curso_modulos, curso_unidades y
 * curso_recursos; esta entidad representa la fila de publicaciones.
 */
class Curso extends Publicacion
{
    /**
     * @param array $datos Fila de publicaciones o datos validados del formulario.
     */
    public function __construct(array $datos)
    {
        $datos['tipo'] = 'Curso';
        parent::__construct($datos);
    }
}
