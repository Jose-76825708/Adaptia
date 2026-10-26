<?php

namespace App\Services;

use App\Models\DetalleRecepcion;
use App\Models\Recepcion;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RecepcionService
{
    public function listar(): Collection
    {
        return Recepcion::with(['almacenista', 'detalles.planta', 'incidencias'])->latest()->get();
    }

    public function crear(array $datos): Recepcion
    {
        return DB::transaction(function () use ($datos) {
            $recepcion = Recepcion::create([
                'usuario_almacen_id' => $datos['usuario_almacen_id'],
                'tipo' => $datos['tipo'],
                'estado' => $datos['estado'] ?? 'pendiente',
                'fecha_recepcion' => $datos['fecha_recepcion'] ?? now(),
                'observaciones' => $datos['observaciones'] ?? null,
            ]);

            foreach ($datos['detalles'] ?? [] as $detalle) {
                DetalleRecepcion::create([
                    'recepcion_id' => $recepcion->id,
                    'planta_id' => $detalle['planta_id'],
                    'cantidad_esperada' => (int) $detalle['cantidad_esperada'],
                    'cantidad_recibida' => (int) $detalle['cantidad_recibida'],
                    'unidad' => $detalle['unidad'] ?? 'unidad',
                    'estado_item' => $this->evaluarEstadoItem($detalle),
                ]);
            }

            $recepcion->auditorias()->create([
                'usuario_id' => $datos['usuario_almacen_id'],
                'accion' => 'creada',
                'detalle' => 'Recepción registrada en el almacén.',
            ]);

            return $recepcion;
        });
    }

    private function evaluarEstadoItem(array $detalle): string
    {
        $cantidadEsperada = (int) ($detalle['cantidad_esperada'] ?? 0);
        $cantidadRecibida = (int) ($detalle['cantidad_recibida'] ?? 0);

        if ($cantidadRecibida == $cantidadEsperada) {
            return 'conforme';
        }

        return 'no_conforme';
    }
}
