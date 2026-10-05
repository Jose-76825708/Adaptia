<?php

namespace App\Services;

use App\Models\Alerta;
use App\Models\PlantaVendida;

class AlertaMonitoreoService
{
    /**
     * @param  array<int, string>  $variablesEvaluables
     * @param  array<int, array<string, mixed>>  $desviaciones
     * @return array{
     *     activas: array<int, array<string, mixed>>,
     *     resueltas: array<int, array{id: int, variable: string, resuelta_en: string}>
     * }
     */
    public function sincronizar(
        PlantaVendida $unidad,
        array $variablesEvaluables,
        array $desviaciones,
    ): array {
        $porVariable = [];
        foreach ($desviaciones as $desviacion) {
            $porVariable[$desviacion['variable']] = $desviacion;
        }

        $activas = [];
        $resueltas = [];

        foreach ($variablesEvaluables as $variable) {
            $alerta = $unidad->alertas()
                ->where('variable', $variable)
                ->whereNull('resuelta_en')
                ->lockForUpdate()
                ->first();
            $desviacion = $porVariable[$variable] ?? null;

            if ($desviacion === null) {
                if ($alerta !== null) {
                    $alerta->resuelta_en = now();
                    $alerta->save();
                    $resueltas[] = [
                        'id' => $alerta->id,
                        'variable' => $variable,
                        'resuelta_en' => $alerta->resuelta_en->toIso8601String(),
                    ];
                }

                continue;
            }

            $atributos = [
                'tipo' => $desviacion['tipo'],
                'variable' => $variable,
                'valor_medido' => $desviacion['valor_medido'],
                'limite' => $desviacion['limite'],
                'rango_minimo' => $desviacion['rango_esperado']['minimo'],
                'rango_maximo' => $desviacion['rango_esperado']['maximo'],
                'direccion' => $desviacion['direccion'],
                'mensaje' => $desviacion['mensaje'],
                'resuelta_en' => null,
            ];

            if ($alerta === null) {
                $alerta = $unidad->alertas()->create($atributos);
            } else {
                $alerta->update($atributos);
            }

            $activas[] = $this->formatearAlerta($alerta);
        }

        return [
            'activas' => $activas,
            'resueltas' => $resueltas,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatearAlerta(Alerta $alerta): array
    {
        return [
            'id' => $alerta->id,
            'tipo' => $alerta->tipo,
            'variable' => $alerta->variable,
            'valor_medido' => (float) $alerta->valor_medido,
            'limite' => (float) $alerta->limite,
            'direccion' => $alerta->direccion,
            'rango_esperado' => [
                'minimo' => (float) $alerta->rango_minimo,
                'maximo' => (float) $alerta->rango_maximo,
            ],
            'mensaje' => $alerta->mensaje,
            'created_at' => $alerta->created_at?->toIso8601String(),
            'resuelta_en' => $alerta->resuelta_en?->toIso8601String(),
        ];
    }
}
