<?php

namespace App\Services;

class DecisionCierreService
{
    public const ESTANDARES_CIERRE = [
        'severidad_permitida' => ['baja', 'media'],
        'tareas_sin_completar_maximo' => 0,
        'incidencias_abiertas_maximo' => 0,
    ];

    public function decidir(string $severidadFinal, int $tareasSinCompletar, int $incidenciasAbiertas): array
    {
        $correcciones = [];

        if (!in_array($severidadFinal, self::ESTANDARES_CIERRE['severidad_permitida'], true)) {
            $correcciones[] = 'La severidad final es alta.';
        }

        if ($tareasSinCompletar > 0) {
            $correcciones[] = 'Quedan tareas sin completar.';
        }

        if ($incidenciasAbiertas > 0) {
            $correcciones[] = 'Hay incidencias abiertas.';
        }

        return [
            'puede_cerrar' => $correcciones === [],
            'correcciones' => $correcciones,
        ];
    }
}
