<?php

/**
 * Responsabilidad: Define plantillas y campos esperados para tipos de servicios personalizados.
 */

function plantillas_servicio(): array
{
    return [
        'proyecto_educativo' => [
            'nombre' => 'Proyectos educativos',
            'campos' => [
                'institucion' => ['label' => 'Institución', 'tipo' => 'text', 'required' => true],
                'tipo_institucion' => ['label' => 'Tipo de institución', 'tipo' => 'text', 'required' => true],
                'nivel_educativo' => ['label' => 'Nivel educativo', 'tipo' => 'text'],
                'tematicas' => ['label' => 'Temáticas a trabajar', 'tipo' => 'textarea', 'required' => true],
                'necesidad' => ['label' => 'Necesidad o problema que se desea atender', 'tipo' => 'textarea', 'required' => true],
                'objetivos' => ['label' => 'Objetivos esperados', 'tipo' => 'textarea', 'required' => true],
                'destinatarios' => ['label' => 'Población destinataria', 'tipo' => 'text'],
                'cantidad_participantes' => ['label' => 'Cantidad aproximada de participantes', 'tipo' => 'number'],
                'duracion_estimada' => ['label' => 'Duración estimada', 'tipo' => 'text'],
                'recursos_disponibles' => ['label' => 'Recursos disponibles', 'tipo' => 'textarea'],
                'presupuesto' => ['label' => 'Presupuesto estimado', 'tipo' => 'number'],
            ],
        ],
        'formacion_institucional' => [
            'nombre' => 'Formación para empresas e instituciones',
            'campos' => [
                'organizacion' => ['label' => 'Empresa o institución', 'tipo' => 'text', 'required' => true],
                'rubro' => ['label' => 'Rubro o área de actividad', 'tipo' => 'text', 'required' => true],
                'cantidad_participantes' => ['label' => 'Cantidad de participantes', 'tipo' => 'number', 'required' => true],
                'perfil_participantes' => ['label' => 'Perfil y conocimientos previos', 'tipo' => 'textarea'],
                'tematica' => ['label' => 'Temática de la formación', 'tipo' => 'text', 'required' => true],
                'objetivo' => ['label' => 'Objetivo principal', 'tipo' => 'textarea', 'required' => true],
                'modalidad_preferida' => ['label' => 'Modalidad preferida', 'tipo' => 'select', 'opciones' => ['Presencial', 'Virtual', 'Híbrida']],
                'cantidad_jornadas' => ['label' => 'Cantidad estimada de jornadas', 'tipo' => 'number'],
                'disponibilidad' => ['label' => 'Fechas y horarios posibles', 'tipo' => 'textarea'],
                'presupuesto' => ['label' => 'Presupuesto estimado', 'tipo' => 'number'],
            ],
        ],
        'impresion_3d' => [
            'nombre' => 'Diseño e impresión 3D',
            'campos' => [
                'cantidad' => ['label' => 'Cantidad de piezas', 'tipo' => 'number', 'required' => true],
                'material' => ['label' => 'Material preferido', 'tipo' => 'select', 'opciones' => ['PLA', 'PETG', 'ABS', 'Resina', 'A definir']],
                'dimensiones' => ['label' => 'Dimensiones aproximadas', 'tipo' => 'text'],
                'color' => ['label' => 'Color preferido', 'tipo' => 'text'],
                'boceto' => ['label' => '¿Tenés boceto o modelo?', 'tipo' => 'select', 'opciones' => ['Sí', 'No', 'Tengo una referencia']],
                'descripcion_pieza' => ['label' => 'Descripción de la pieza o diseño', 'tipo' => 'textarea', 'required' => true],
                'uso_previsto' => ['label' => 'Uso previsto', 'tipo' => 'textarea'],
                'fecha_necesaria' => ['label' => 'Fecha en la que lo necesitás', 'tipo' => 'date'],
                'presupuesto' => ['label' => 'Presupuesto aproximado', 'tipo' => 'number'],
            ],
        ],
        'robotica_automatizacion' => [
            'nombre' => 'Robótica y automatización',
            'campos' => [
                'idea' => ['label' => 'Idea o problema a resolver', 'tipo' => 'textarea', 'required' => true],
                'objetivo' => ['label' => 'Objetivo final', 'tipo' => 'textarea', 'required' => true],
                'nivel_avance' => ['label' => 'Nivel de avance actual', 'tipo' => 'select', 'opciones' => ['Idea inicial', 'Prototipo', 'Proyecto en desarrollo', 'Proyecto existente']],
                'componentes' => ['label' => 'Componentes o materiales disponibles', 'tipo' => 'textarea'],
                'tecnologias' => ['label' => 'Tecnologías previstas', 'tipo' => 'text'],
                'entorno' => ['label' => 'Entorno donde se utilizará', 'tipo' => 'text'],
                'restricciones' => ['label' => 'Restricciones o requisitos', 'tipo' => 'textarea'],
                'fecha_objetivo' => ['label' => 'Fecha objetivo', 'tipo' => 'date'],
                'presupuesto' => ['label' => 'Presupuesto estimado', 'tipo' => 'number'],
            ],
        ],
        'mentoria' => [
            'nombre' => 'Mentorías y acompañamiento especializado',
            'campos' => [
                'tema' => ['label' => 'Tema principal', 'tipo' => 'text', 'required' => true],
                'dificultades' => ['label' => 'Dificultades actuales', 'tipo' => 'textarea', 'required' => true],
                'conocimientos_previos' => ['label' => 'Conocimientos previos', 'tipo' => 'textarea'],
                'objetivo' => ['label' => 'Objetivo de la mentoría', 'tipo' => 'textarea', 'required' => true],
                'proyecto' => ['label' => 'Proyecto o contexto en el que estás trabajando', 'tipo' => 'textarea'],
                'modalidad_preferida' => ['label' => 'Modalidad preferida', 'tipo' => 'select', 'opciones' => ['Virtual', 'Presencial', 'Indistinta']],
                'fecha_preferida' => ['label' => 'Fecha preferida', 'tipo' => 'date'],
                'horario_preferido' => ['label' => 'Horario preferido', 'tipo' => 'time'],
                'duracion' => ['label' => 'Duración o cantidad de sesiones', 'tipo' => 'text'],
                'presupuesto' => ['label' => 'Presupuesto aproximado', 'tipo' => 'number'],
            ],
        ],
    ];
}

function obtener_plantilla_servicio(?string $tipo): ?array
{
    $plantillas = plantillas_servicio();
    return $tipo !== null && isset($plantillas[$tipo]) ? $plantillas[$tipo] : null;
}
