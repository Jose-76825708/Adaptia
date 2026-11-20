<?php

namespace App\Services;

use App\Models\CasoMantenimiento;
use App\Models\PlanMantenimiento;
use App\Models\RecursoCatalogo;
use App\Models\RequisicionMantenimiento;
use App\Models\RequisicionItem;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RequisicionMantenimientoService
{
    public const ESTADOS = ['pendiente', 'parcial', 'recibida'];

    public function generarDesdeFaltantes(array $faltantes): array
    {
        $items = [];
        foreach ($faltantes as $faltante) {
            $cantidad = (float) ($faltante['faltante'] ?? 0);
            if ($cantidad > 0) {
                $items[] = [
                    'codigo' => (string) $faltante['codigo'],
                    'cantidad_solicitada' => $cantidad,
                ];
            }
        }

        return $items;
    }

    public function generarDesdeVeredicto(array $veredicto): array
    {
        if (($veredicto['estado'] ?? null) === 'bloqueado') {
            throw new InvalidArgumentException('El caso está bloqueado por una herramienta faltante y no admite requisición.');
        }

        if (($veredicto['estado'] ?? null) !== 'faltan' || ($veredicto['faltan_materiales'] ?? []) === []) {
            throw new InvalidArgumentException('No faltan materiales para generar la requisición.');
        }

        return $this->generarDesdeFaltantes($veredicto['faltan_materiales']);
    }

    public function hayRequisicionAbierta(CasoMantenimiento $caso): bool
    {
        return RequisicionMantenimiento::query()
            ->where('caso_id', $caso->id)
            ->whereIn('estado', ['pendiente', 'parcial'])
            ->exists();
    }

    public function generar(CasoMantenimiento $caso, int $autorId, array $veredicto): RequisicionMantenimiento
    {
        if ($caso->estado !== 'planificado') {
            throw new InvalidArgumentException('La requisición solo se genera en estado planificado.');
        }

        if ($this->hayRequisicionAbierta($caso)) {
            throw new InvalidArgumentException('Ya existe una requisición no recibida para este caso.');
        }

        $items = $this->generarDesdeVeredicto($veredicto);

        return DB::transaction(function () use ($caso, $autorId, $items) {
            $requisicion = RequisicionMantenimiento::create([
                'caso_id' => $caso->id,
                'autor_id' => $autorId,
                'estado' => 'pendiente',
                'motivo' => null,
            ]);

            foreach ($items as $item) {
                $recurso = RecursoCatalogo::query()->where('codigo', $item['codigo'])->first();
                if (!$recurso || $recurso->tipo !== 'material') {
                    throw new InvalidArgumentException('Solo se pueden requisar recursos de tipo material.');
                }

                RequisicionItem::create([
                    'requisicion_id' => $requisicion->id,
                    'recurso_id' => $recurso->id,
                    'cantidad_solicitada' => $item['cantidad_solicitada'],
                    'cantidad_recibida' => 0,
                    'estado' => 'pendiente',
                ]);
            }

            return $requisicion->fresh('items');
        });
    }
}
