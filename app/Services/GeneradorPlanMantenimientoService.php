<?php

namespace App\Services;

use InvalidArgumentException;

class GeneradorPlanMantenimientoService
{
    public const PLANTILLAS = [
        'riego' => [
            ['codigo' => 'T1', 'titulo' => 'Revisar humedad del sustrato', 'descripcion' => 'Revisar la humedad y registrar el estado del sustrato.', 'prioridad' => 'alta', 'criticidad' => 'media', 'duracion_dias' => 1, 'depende_de_codigo' => null, 'recursos' => [['codigo' => 'H-001', 'cantidad' => 1]], 'checklist' => ['Medir humedad', 'Registrar lectura', 'Confirmar drenaje']],
            ['codigo' => 'T2', 'titulo' => 'Ajustar el riego y verificar drenaje', 'descripcion' => 'Ajustar el riego según el resultado y comprobar el drenaje.', 'prioridad' => 'media', 'criticidad' => 'media', 'duracion_dias' => 1, 'depende_de_codigo' => 'T1', 'recursos' => [['codigo' => 'H-001', 'cantidad' => 1]], 'checklist' => ['Ajustar frecuencia', 'Verificar salida', 'Probar humedad']],
        ],
        'poda' => [
            ['codigo' => 'T1', 'titulo' => 'Revisar ramas secas y podar', 'descripcion' => 'Revisar y podar las ramas deterioradas.', 'prioridad' => 'alta', 'criticidad' => 'alta', 'duracion_dias' => 2, 'depende_de_codigo' => null, 'recursos' => [['codigo' => 'H-002', 'cantidad' => 1]], 'checklist' => ['Identificar ramas', 'Podar con seguridad', 'Revisar cortes']],
            ['codigo' => 'T2', 'titulo' => 'Limpiar restos de poda', 'descripcion' => 'Retirar y limpiar los restos generados.', 'prioridad' => 'media', 'criticidad' => 'baja', 'duracion_dias' => 1, 'depende_de_codigo' => 'T1', 'recursos' => [['codigo' => 'H-003', 'cantidad' => 1]], 'checklist' => ['Retirar restos', 'Desinfectar herramientas', 'Revisar el área']],
        ],
        'fertilizacion' => [
            ['codigo' => 'T1', 'titulo' => 'Aplicar fertilizante', 'descripcion' => 'Aplicar el fertilizante y abono según las necesidades.', 'prioridad' => 'alta', 'criticidad' => 'alta', 'duracion_dias' => 2, 'depende_de_codigo' => null, 'recursos' => [['codigo' => 'M-002', 'cantidad' => 1], ['codigo' => 'M-001', 'cantidad' => 2]], 'checklist' => ['Preparar dosis', 'Aplicar fertilizante', 'Registrar aplicación']],
            ['codigo' => 'T2', 'titulo' => 'Verificar la respuesta de la planta', 'descripcion' => 'Comprobar la respuesta vegetal después de fertilizar.', 'prioridad' => 'baja', 'criticidad' => 'media', 'duracion_dias' => 1, 'depende_de_codigo' => 'T1', 'recursos' => [], 'checklist' => ['Inspeccionar follaje', 'Medir humedad', 'Registrar resultado']],
        ],
        'limpieza' => [
            ['codigo' => 'T1', 'titulo' => 'Retirar residuos', 'descripcion' => 'Retirar residuos y elementos deteriorados del área.', 'prioridad' => 'media', 'criticidad' => 'baja', 'duracion_dias' => 1, 'depende_de_codigo' => null, 'recursos' => [['codigo' => 'H-003', 'cantidad' => 1]], 'checklist' => ['Retirar residuos', 'Separar material', 'Limpiar superficie']],
            ['codigo' => 'T2', 'titulo' => 'Revisar el área y ajustar', 'descripcion' => 'Revisar el área y realizar ajustes finales.', 'prioridad' => 'baja', 'criticidad' => 'baja', 'duracion_dias' => 1, 'depende_de_codigo' => 'T1', 'recursos' => [], 'checklist' => ['Revisar el área', 'Ajustar disposición', 'Confirmar limpieza']],
        ],
        'reposicion' => [
            ['codigo' => 'T1', 'titulo' => 'Retirar la planta afectada', 'descripcion' => 'Retirar la planta deteriorada con seguridad.', 'prioridad' => 'alta', 'criticidad' => 'alta', 'duracion_dias' => 1, 'depende_de_codigo' => null, 'recursos' => [['codigo' => 'H-002', 'cantidad' => 1]], 'checklist' => ['Aislar planta', 'Retirar planta', 'Revisar sustrato']],
            ['codigo' => 'T2', 'titulo' => 'Preparar la nueva maceta con tierra', 'descripcion' => 'Preparar la maceta y colocar tierra nueva.', 'prioridad' => 'alta', 'criticidad' => 'media', 'duracion_dias' => 2, 'depende_de_codigo' => 'T1', 'recursos' => [['codigo' => 'M-005', 'cantidad' => 1], ['codigo' => 'M-004', 'cantidad' => 1]], 'checklist' => ['Preparar maceta', 'Agregar tierra', 'Comprobar drenaje']],
            ['codigo' => 'T3', 'titulo' => 'Trasplantar y regar', 'descripcion' => 'Trasplantar la nueva planta y aplicar riego inicial.', 'prioridad' => 'media', 'criticidad' => 'media', 'duracion_dias' => 1, 'depende_de_codigo' => 'T2', 'recursos' => [['codigo' => 'H-001', 'cantidad' => 1]], 'checklist' => ['Trasplantar', 'Regar', 'Ubicar la planta']],
        ],
        'control_plagas' => [
            ['codigo' => 'T1', 'titulo' => 'Inspeccionar el área afectada', 'descripcion' => 'Inspeccionar el área para confirmar la presencia de plagas.', 'prioridad' => 'critica', 'criticidad' => 'alta', 'duracion_dias' => 1, 'depende_de_codigo' => null, 'recursos' => [], 'checklist' => ['Identificar plaga', 'Delimitar área', 'Registrar hallazgos']],
            ['codigo' => 'T2', 'titulo' => 'Aplicar tratamiento', 'descripcion' => 'Aplicar el tratamiento recomendado al área afectada.', 'prioridad' => 'critica', 'criticidad' => 'alta', 'duracion_dias' => 2, 'depende_de_codigo' => 'T1', 'recursos' => [['codigo' => 'M-003', 'cantidad' => 1]], 'checklist' => ['Preparar tratamiento', 'Aplicar tratamiento', 'Aislar el área']],
            ['codigo' => 'T3', 'titulo' => 'Saneamiento de restos', 'descripcion' => 'Sanear y limpiar los restos afectados.', 'prioridad' => 'media', 'criticidad' => 'media', 'duracion_dias' => 1, 'depende_de_codigo' => 'T2', 'recursos' => [['codigo' => 'H-003', 'cantidad' => 1]], 'checklist' => ['Retirar restos', 'Limpiar herramientas', 'Confirmar saneamiento']],
        ],
        'general' => [
            ['codigo' => 'T1', 'titulo' => 'Revisión integral del área', 'descripcion' => 'Revisar integralmente el área de mantenimiento.', 'prioridad' => 'media', 'criticidad' => 'media', 'duracion_dias' => 1, 'depende_de_codigo' => null, 'recursos' => [], 'checklist' => ['Inspeccionar área', 'Registrar estado', 'Priorizar ajustes']],
            ['codigo' => 'T2', 'titulo' => 'Ajustar variables básicas', 'descripcion' => 'Ajustar las variables básicas detectadas durante la revisión.', 'prioridad' => 'media', 'criticidad' => 'media', 'duracion_dias' => 1, 'depende_de_codigo' => 'T1', 'recursos' => [['codigo' => 'H-001', 'cantidad' => 1]], 'checklist' => ['Ajustar condición', 'Verificar resultado', 'Cerrar revisión']],
        ],
    ];

    public function derivarTareas(string $tipo, string $severidadDiagnostico, array $variablesAfectadas): array
    {
        if (!array_key_exists($tipo, self::PLANTILLAS)) {
            throw new InvalidArgumentException('El tipo de mantenimiento no está definido.');
        }

        $tareas = array_map(fn (array $tarea) => $tarea, self::PLANTILLAS[$tipo]);

        if ($severidadDiagnostico === 'alta') {
            foreach ($tareas as $indice => $tarea) {
                $tareas[$indice]['prioridad'] = $this->subirPrioridad($tarea['prioridad']);
            }
        }

        if ($variablesAfectadas !== []) {
            $codigoUltimo = end($tareas)['codigo'];
            $checklist = [];
            foreach (array_values(array_unique($variablesAfectadas)) as $variable) {
                $checklist[] = 'Revisar ' . (string) $variable;
            }

            $tareas[] = [
                'codigo' => 'T' . (count($tareas) + 1),
                'titulo' => 'Seguimiento de variables afectadas',
                'descripcion' => 'Dar seguimiento a las variables que participaron en el diagnóstico.',
                'prioridad' => 'media',
                'criticidad' => 'media',
                'duracion_dias' => 1,
                'depende_de_codigo' => $codigoUltimo,
                'recursos' => [],
                'checklist' => $checklist,
            ];
        }

        return $tareas;
    }

    private function subirPrioridad(string $prioridad): string
    {
        return match ($prioridad) {
            'baja' => 'media',
            'media' => 'alta',
            'alta', 'critica' => 'critica',
        };
    }
}
