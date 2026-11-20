<?php

namespace App\Services;

use App\Models\CasoMantenimiento;
use App\Models\RecursoCatalogo;
use App\Models\MovimientoMaterial;
use App\Models\RequisicionItem;
use App\Models\RequisicionMantenimiento;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RecepcionMaterialService
{
    public function recibir(CasoMantenimiento $caso, RequisicionItem $item, float $cantidadNueva, int $autorId): MovimientoMaterial
    {
        $requisicion = $item->requisicion;
        if (!$requisicion || (int) $requisicion->caso_id !== (int) $caso->id) {
            throw new InvalidArgumentException('La recepción no pertenece al caso indicado.');
        }

        $recurso = $item->recurso;
        if (!$recurso instanceof RecursoCatalogo || $recurso->tipo !== 'material') {
            throw new InvalidArgumentException('Solo se reciben recursos de tipo material.');
        }

        $cantidadSolicitada = (float) $item->cantidad_solicitada;
        $cantidadRecibida = (float) $item->cantidad_recibida;
        $stockAntes = (float) $recurso->stock_actual;
        $calculo = $this->calcularRecepcion($cantidadSolicitada, $cantidadNueva, $cantidadRecibida, $stockAntes);

        return DB::transaction(function () use ($caso, $item, $recurso, $autorId, $cantidadNueva, $calculo) {
            $recurso->stock_actual = $calculo['stock_despues'];
            $recurso->save();

            $item->cantidad_recibida = (float) $item->cantidad_recibida + $cantidadNueva;
            $item->estado = $calculo['estado_item'];
            $item->save();

            $requisicion = $item->requisicion()->firstOrFail();
            $todosRecibidos = $requisicion->items()->where('estado', '!=', 'recibida')->doesntExist();
            $requisicion->estado = $todosRecibidos ? 'recibida' : 'parcial';
            $requisicion->save();

            return MovimientoMaterial::create([
                'requisicion_item_id' => $item->id,
                'caso_id' => $caso->id,
                'recurso_id' => $recurso->id,
                'tipo' => 'entrada',
                'stock_antes' => $calculo['stock_despues'] - $cantidadNueva,
                'stock_despues' => $calculo['stock_despues'],
                'cantidad' => $cantidadNueva,
                'motivo' => 'Recepción de requisición',
                'autor_id' => $autorId,
            ]);
        });
    }

    public function calcularRecepcion(float $cantidadSolicitada, float $cantidadNueva, float $cantidadRecibida = 0.0, float $stockAntes = 0.0): array
    {
        $total = $cantidadRecibida + $cantidadNueva;
        if ($cantidadNueva <= 0) {
            throw new InvalidArgumentException('La cantidad recibida debe ser mayor que cero.');
        }
        if ($total > $cantidadSolicitada) {
            throw new InvalidArgumentException('La cantidad recibida no puede superar la cantidad solicitada.');
        }

        return [
            'recibida' => $cantidadNueva,
            'stock_despues' => $stockAntes + $cantidadNueva,
            'estado_item' => $total >= $cantidadSolicitada ? 'recibida' : 'parcial',
        ];
    }
}
