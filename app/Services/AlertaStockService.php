<?php

namespace App\Services;

use App\Models\Alerta;
use App\Models\Planta;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use InvalidArgumentException;

class AlertaStockService
{
    /**
     * Evalúa si la planta requiere una alerta persistente de stock bajo.
     * La validación ocurre siempre después del cambio de stock para cubrir
     * también el caso en el que el stock vuelve a la normalidad.
     */
    public function evaluar(Planta $planta, string $contexto = 'Stock bajo detectado', array $extra = []): ?Alerta
    {
        if (! $planta->exists) {
            throw new InvalidArgumentException('La planta debe existir para evaluar su stock bajo.');
        }

        $stockBajo = $this->esStockBajo((int) $planta->stock_actual, (int) $planta->stock_minimo);

        if ($stockBajo) {
            Session::flash(
                'stock_warning',
                "Advertencia: El stock de {$planta->nombre} está bajo el mínimo. " .
                    "Actual: {$planta->stock_actual}, Mínimo: {$planta->stock_minimo}"
            );

            Log::warning($contexto, array_merge([
                'planta_id' => $planta->id,
                'planta_nombre' => $planta->nombre,
                'stock_actual' => $planta->stock_actual,
                'stock_minimo' => $planta->stock_minimo,
            ], $extra));
        }

        if (! $stockBajo) {
            return $this->resolverAlertaAbiertaPorPlanta($planta);
        }

        $alertaAbierta = $this->alertaAbiertaPorPlanta($planta);

        if ($alertaAbierta !== null) {
            return $alertaAbierta;
        }

        return DB::transaction(function () use ($planta) {
            $alertaAbierta = $this->alertaAbiertaPorPlanta($planta);

            if ($alertaAbierta !== null) {
                return $alertaAbierta;
            }

            return Alerta::create([
                'planta_id' => $planta->id,
                'planta_vendida_id' => null,
                'tipo' => 'stock_bajo',
                'variable' => 'stock',
                'valor_medido' => $planta->stock_actual,
                'limite' => $planta->stock_minimo,
                'rango_minimo' => $planta->stock_minimo,
                'rango_maximo' => null,
                'direccion' => 'bajo',
                'mensaje' => "La planta {$planta->nombre} está bajo el stock mínimo. Stock actual: {$planta->stock_actual}, mínimo: {$planta->stock_minimo}.",
                'leida' => false,
                'resuelta_en' => null,
            ]);
        });
    }

    private function alertaAbiertaPorPlanta(Planta $planta): ?Alerta
    {
        return Alerta::query()
            ->where('planta_id', $planta->id)
            ->where('tipo', 'stock_bajo')
            ->whereNull('resuelta_en')
            ->first();
    }

    private function resolverAlertaAbiertaPorPlanta(Planta $planta): ?Alerta
    {
        return DB::transaction(function () use ($planta) {
            $alerta = Alerta::query()
                ->where('planta_id', $planta->id)
                ->where('tipo', 'stock_bajo')
                ->whereNull('resuelta_en')
                ->lockForUpdate()
                ->first();

            if ($alerta === null) {
                return null;
            }

            $alerta->resuelta_en = now();
            $alerta->save();

            return $alerta;
        });
    }

    private function esStockBajo(int $actual, int $minimo): bool
    {
        return $minimo > 0 && $actual < $minimo;
    }
}
