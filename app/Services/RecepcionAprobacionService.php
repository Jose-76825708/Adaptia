<?php

namespace App\Services;

use App\Models\IncidenciaRecepcion;
use App\Models\Planta;
use App\Models\Recepcion;
use Illuminate\Support\Facades\DB;

class RecepcionAprobacionService
{
    public function aprobar(Recepcion $recepcion): Recepcion
    {
        return DB::transaction(function () use ($recepcion) {
            $tieneDiferencia = false;

            foreach ($recepcion->detalles as $detalle) {
                $estadoItem = $this->resolverEstadoItem($detalle->cantidad_esperada, $detalle->cantidad_recibida);
                $detalle->update([
                    'estado_item' => $estadoItem,
                ]);

                if ($estadoItem !== 'conforme') {
                    $tieneDiferencia = true;
                    IncidenciaRecepcion::create([
                        'recepcion_id' => $recepcion->id,
                        'planta_id' => $detalle->planta_id,
                        'cantidad_esperada' => $detalle->cantidad_esperada,
                        'cantidad_recibida' => $detalle->cantidad_recibida,
                        'diferencia' => $detalle->cantidad_recibida - $detalle->cantidad_esperada,
                        'motivo' => $detalle->cantidad_recibida > $detalle->cantidad_esperada ? 'sobrante' : 'faltante',
                        'estado' => 'abierta',
                        'observaciones' => 'La recepción no fue conforme durante la aprobación.',
                    ]);
                }
            }

            if ($tieneDiferencia) {
                $recepcion->update([
                    'estado' => 'no_conforme',
                    'observaciones' => $recepcion->observaciones ?? 'Recepción no conforme por diferencias de cantidad.',
                ]);

                $recepcion->auditorias()->create([
                    'usuario_id' => auth()->id() ?? $recepcion->usuario_almacen_id,
                    'accion' => 'rechazada',
                    'detalle' => 'Recepción rechazada por diferencias de cantidad.',
                ]);

                return $recepcion->fresh(['detalles.planta', 'incidencias', 'auditorias.usuario']);
            }

            foreach ($recepcion->detalles as $detalle) {
                if ($detalle->cantidad_recibida <= 0) {
                    continue;
                }

                $planta = Planta::find($detalle->planta_id);
                if ($planta) {
                    $planta->increment('stock_actual', $detalle->cantidad_recibida);
                }
            }

            $recepcion->update(['estado' => 'conforme']);

            $recepcion->auditorias()->create([
                'usuario_id' => auth()->id() ?? $recepcion->usuario_almacen_id,
                'accion' => 'aprobada',
                'detalle' => 'Recepción aprobada y stock actualizado.',
            ]);

            return $recepcion->fresh(['detalles.planta', 'incidencias', 'auditorias.usuario']);
        });
    }

    public function rechazar(Recepcion $recepcion, ?string $observaciones = null): Recepcion
    {
        return DB::transaction(function () use ($recepcion, $observaciones) {
            foreach ($recepcion->detalles as $detalle) {
                $detalle->update([
                    'estado_item' => 'no_conforme',
                ]);

                $diferencia = $detalle->cantidad_recibida - $detalle->cantidad_esperada;
                if ($diferencia != 0) {
                    IncidenciaRecepcion::create([
                        'recepcion_id' => $recepcion->id,
                        'planta_id' => $detalle->planta_id,
                        'cantidad_esperada' => $detalle->cantidad_esperada,
                        'cantidad_recibida' => $detalle->cantidad_recibida,
                        'diferencia' => $diferencia,
                        'motivo' => $diferencia > 0 ? 'sobrante' : 'faltante',
                        'estado' => 'abierta',
                        'observaciones' => $observaciones ?? 'Recepción no conforme.',
                    ]);
                }
            }

            $recepcion->update([
                'estado' => 'no_conforme',
                'observaciones' => $observaciones ?? ($recepcion->observaciones ?? 'Recepción rechazada por no conformidad.'),
            ]);

            $recepcion->auditorias()->create([
                'usuario_id' => auth()->id() ?? $recepcion->usuario_almacen_id,
                'accion' => 'rechazada',
                'detalle' => $observaciones ?? 'Recepción rechazada por no conformidad.',
            ]);

            return $recepcion->fresh(['detalles.planta', 'incidencias', 'auditorias.usuario']);
        });
    }

    private function resolverEstadoItem(int $esperada, int $recibida): string
    {
        if ($recibida != $esperada) {
            return 'no_conforme';
        }

        return 'conforme';
    }
}
