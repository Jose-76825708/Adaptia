<?php

namespace App\Services;

use Carbon\Carbon;
use Carbon\CarbonInterface;

class ComparacionEjecucionService
{
    public function comparar(array $tareas, array $eventos, ?CarbonInterface $hoy = null): array
    {
        $hoy = $hoy ?? Carbon::now();
        $completadas = [];
        $pendientes = [];
        $retrasadas = [];

        foreach ($tareas as $tarea) {
            if (($tarea['estado'] ?? 'pendiente') === 'completada') {
                $completadas[] = $tarea;
                continue;
            }

            $pendientes[] = $tarea;
            $fecha = $tarea['fecha_estimada'] ?? null;
            if ($fecha !== null) {
                $fechaEstimada = Carbon::parse($fecha)->startOfDay();
                if ($fechaEstimada->lt($hoy->copy()->startOfDay())) {
                    $retrasadas[] = $tarea;
                }
            }
        }

        $incidenciasAbiertas = array_values(array_filter($eventos, function (array $evento) {
            return ($evento['tipo'] ?? null) === 'incidencia'
                && ($evento['resuelta_en'] ?? null) === null;
        }));

        $riesgos = [];
        $recomendaciones = [];

        if ($retrasadas !== []) {
            $riesgos[] = 'Hay tareas con fecha estimada vencida.';
            $recomendaciones[] = 'Reprogramar las tareas retrasadas y revisar sus dependencias.';
        }

        if ($incidenciasAbiertas !== []) {
            $riesgos[] = 'Hay incidencias abiertas.';
            $recomendaciones[] = 'Resolver las incidencias abiertas antes del cierre.';
        }

        if ($pendientes !== [] && $completadas === []) {
            $riesgos[] = 'No se ha completado ninguna tarea.';
            $recomendaciones[] = 'Registrar avances en las tareas pendientes.';
        }

        return [
            'completadas' => $completadas,
            'pendientes' => $pendientes,
            'retrasadas' => $retrasadas,
            'incidencias_abiertas' => $incidenciasAbiertas,
            'riesgos' => $riesgos,
            'recomendaciones' => $recomendaciones,
        ];
    }
}
