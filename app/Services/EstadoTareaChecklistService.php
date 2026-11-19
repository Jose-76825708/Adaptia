<?php

namespace App\Services;

use App\Models\CasoMantenimiento;
use App\Models\ItemChecklist;
use InvalidArgumentException;

class EstadoTareaChecklistService
{
    public function calcularEstado(array $cumplidos): string
    {
        if ($cumplidos === []) {
            return 'pendiente';
        }

        foreach ($cumplidos as $cumplido) {
            if (!$cumplido) {
                return 'en_curso';
            }
        }

        return 'completada';
    }

    public function actualizarItem(CasoMantenimiento $caso, ItemChecklist $item, bool $cumplido): string
    {
        if ($caso->estado !== 'en_ejecucion') {
            throw new InvalidArgumentException('Las acciones del checklist solo se aceptan durante la ejecución.');
        }

        $plan = $caso->planes()->latest('version')->latest('id')->first();
        $tarea = $item->tarea;
        if (!$plan || !$tarea || (int) $tarea->plan_id !== (int) $plan->id) {
            throw new InvalidArgumentException('El ítem no pertenece al plan vigente del caso.');
        }

        $item->cumplido = $cumplido;
        $item->save();

        $estados = $tarea->checklist()->pluck('cumplido')->map(fn ($valor) => (bool) $valor)->all();
        $estado = $this->calcularEstado($estados);
        $tarea->estado = $estado;
        $tarea->save();

        return $estado;
    }
}
