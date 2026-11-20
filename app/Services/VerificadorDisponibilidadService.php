<?php

namespace App\Services;

use App\Models\RecursoCatalogo;
use App\Models\TareaPlan;

class VerificadorDisponibilidadService
{
    public function consultarHerramientas(array $herramientas): array
    {
        $resultado = [];
        foreach ($herramientas as $herramienta) {
            $requerido = (float) $herramienta['requerido'];
            $disponible = (float) $herramienta['disponible'];
            $resultado[] = [
                'codigo' => (string) $herramienta['codigo'],
                'nombre' => (string) ($herramienta['nombre'] ?? ''),
                'requerido' => $requerido,
                'disponible' => $disponible,
                'faltante' => max(0, $requerido - $disponible),
                'suficiente' => $disponible >= $requerido,
            ];
        }

        return $resultado;
    }

    public function consultarMateriales(array $materiales): array
    {
        $resultado = [];
        foreach ($materiales as $material) {
            $requerido = (float) $material['requerido'];
            $disponible = (float) $material['disponible'];
            $resultado[] = [
                'codigo' => (string) $material['codigo'],
                'nombre' => (string) ($material['nombre'] ?? ''),
                'requerido' => $requerido,
                'disponible' => $disponible,
                'faltante' => max(0, $requerido - $disponible),
                'suficiente' => $disponible >= $requerido,
            ];
        }

        return $resultado;
    }

    public function calcularVeredicto(array $herramientas, array $materiales): array
    {
        $faltanHerramientas = array_values(array_filter($herramientas, fn (array $item) => !$item['suficiente']));
        $faltanMateriales = array_values(array_filter($materiales, fn (array $item) => !$item['suficiente']));

        if ($faltanHerramientas !== []) {
            $estado = 'bloqueado';
            $motivo = 'Falta una herramienta requerida.';
        } elseif ($faltanMateriales !== []) {
            $estado = 'faltan';
            $motivo = 'Faltan materiales para ejecutar el plan.';
        } else {
            $estado = 'ejecutable';
            $motivo = null;
        }

        return [
            'estado' => $estado,
            'faltan_materiales' => $faltanMateriales,
            'faltan_herramientas' => $faltanHerramientas,
            'motivo' => $motivo,
        ];
    }

    public function verificarPlan(int $planId): array
    {
        $herramientas = [];
        $materiales = [];

        $tareas = TareaPlan::query()
            ->where('plan_id', $planId)
            ->with('recursos.recurso')
            ->orderBy('id')
            ->get();

        foreach ($tareas as $tarea) {
            foreach ($tarea->recursos as $asignacion) {
                $recurso = $asignacion->recurso;
                if (!$recurso instanceof RecursoCatalogo) {
                    continue;
                }

                $cantidad = (float) $asignacion->cantidad;

                if ($recurso->tipo === 'herramienta') {
                    if (!isset($herramientas[$recurso->codigo])) {
                        $herramientas[$recurso->codigo] = [
                            'codigo' => $recurso->codigo,
                            'nombre' => $recurso->nombre,
                            'requerido' => 0,
                            'disponible' => (float) $recurso->stock_actual,
                        ];
                    }
                    $herramientas[$recurso->codigo]['requerido'] = max($herramientas[$recurso->codigo]['requerido'], $cantidad);
                    continue;
                }

                if (!isset($materiales[$recurso->codigo])) {
                    $materiales[$recurso->codigo] = [
                        'codigo' => $recurso->codigo,
                        'nombre' => $recurso->nombre,
                        'requerido' => 0,
                        'disponible' => (float) $recurso->stock_actual,
                    ];
                }
                $materiales[$recurso->codigo]['requerido'] += $cantidad;
            }
        }

        $herramientasVerificadas = $this->consultarHerramientas(array_values($herramientas));
        $materialesVerificados = $this->consultarMateriales(array_values($materiales));

        return $this->calcularVeredicto($herramientasVerificadas, $materialesVerificados);
    }
}
