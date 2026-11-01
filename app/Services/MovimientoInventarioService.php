<?php

namespace App\Services;

use App\Exceptions\StockInsuficienteException;
use App\Models\MovimientoInventario;
use App\Models\Planta;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MovimientoInventarioService
{
    public function __construct(private AlertaStockService $alertaStockService)
    {
    }

    /**
     * Obtiene todos los movimientos de inventario con sus relaciones.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return MovimientoInventario::with(['planta', 'user'])->get();
    }

    /**
     * Busca un movimiento de inventario por su ID.
     *
     * @param string $id
     * @return Model
     */
    public function getById(string $id): Model
    {
        return MovimientoInventario::with(['planta', 'user'])->findOrFail($id);
    }

    /**
     * Crea un nuevo movimiento de inventario.
     *
     * @param array $data
     * @return Model
     */
    public function createMovimiento(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $planta = Planta::query()->lockForUpdate()->findOrFail($data['planta_id']);
            $stockAntes = $planta->stock_actual;

            if ($data['tipo'] === 'entrada') {
                $planta->increment('stock_actual', $data['cantidad']);
            } else {
                if ($planta->stock_actual < $data['cantidad']) {
                    throw new StockInsuficienteException("Stock insuficiente para {$planta->nombre}. Disponible: {$planta->stock_actual}, requerido: {$data['cantidad']}");
                }
                $planta->decrement('stock_actual', $data['cantidad']);
            }
            $planta->save();

            $movimiento = MovimientoInventario::create([
                ...$data,
                'stock_antes' => $stockAntes,
                'stock_despues' => $planta->stock_actual,
            ]);

            $this->alertaStockService->evaluar($planta, 'Stock bajo detectado tras movimiento de inventario', [
                'planta_id' => $planta->id,
                'planta_nombre' => $planta->nombre,
                'stock_actual' => $planta->stock_actual,
                'stock_minimo' => $planta->stock_minimo,
                'tipo_movimiento' => $data['tipo'],
                'cantidad' => $data['cantidad'],
            ]);

            return $movimiento;
        });
    }

    /**
     * Actualiza un movimiento de inventario existente.
     *
     * @param string $id
     * @param array $data
     * @return Model
     */
    public function updateMovimiento(string $id, array $data): Model
    {
        return DB::transaction(function () use ($id, $data) {
            $movimiento = $this->getById($id);

            $planta = Planta::query()->lockForUpdate()->findOrFail($movimiento->planta_id);
            $stockAntes = $planta->stock_actual;
            $efectoActual = ($movimiento->tipo === 'entrada') ? $movimiento->cantidad : -$movimiento->cantidad;

            $nuevoTipo = $data['tipo'] ?? $movimiento->tipo;
            $nuevaCantidad = $data['cantidad'] ?? $movimiento->cantidad;
            $nuevoEfecto = ($nuevoTipo === 'entrada') ? $nuevaCantidad : -$nuevaCantidad;
            $diferencia = $nuevoEfecto - $efectoActual;

            $movimiento->update($data);

            if ($diferencia > 0) {
                $planta->increment('stock_actual', $diferencia);
            } elseif ($diferencia < 0) {
                $reduccion = abs($diferencia);
                if ($planta->stock_actual < $reduccion) {
                    throw new StockInsuficienteException("Stock insuficiente para actualizar movimiento. Disponible: {$planta->stock_actual}, requerido: {$reduccion}");
                }
                $planta->decrement('stock_actual', $reduccion);
            }
            $planta->save();

            $movimiento->update([
                'stock_antes' => $stockAntes,
                'stock_despues' => $planta->stock_actual,
            ]);

            $this->alertaStockService->evaluar($planta, 'Stock bajo detectado tras actualización de movimiento', [
                'planta_id' => $planta->id,
                'planta_nombre' => $planta->nombre,
                'stock_actual' => $planta->stock_actual,
                'stock_minimo' => $planta->stock_minimo,
                'tipo_movimiento_original' => $movimiento->tipo,
                'nuevo_tipo_movimiento' => $data['tipo'] ?? $movimiento->tipo,
                'cantidad_original' => $movimiento->cantidad,
                'nueva_cantidad' => $data['cantidad'] ?? $movimiento->cantidad,
                'diferencia_stock' => $diferencia,
            ]);

            return $movimiento;
        });
    }

    /**
     * Elimina un movimiento de inventario.
     *
     * @param string $id
     * @return bool
     */
    public function deleteMovimiento(string $id): bool
    {
        return DB::transaction(function () use ($id) {
            $movimiento = $this->getById($id);

            $planta = Planta::query()->lockForUpdate()->findOrFail($movimiento->planta_id);

            if ($movimiento->tipo === 'entrada') {
                if ($planta->stock_actual < $movimiento->cantidad) {
                    throw new StockInsuficienteException("Stock insuficiente para revertir movimiento. Disponible: {$planta->stock_actual}, requerido: {$movimiento->cantidad}");
                }
                $planta->decrement('stock_actual', $movimiento->cantidad);
            } else {
                $planta->increment('stock_actual', $movimiento->cantidad);
            }
            $planta->save();

            $this->alertaStockService->evaluar($planta, 'Stock bajo detectado tras eliminación de movimiento', [
                'planta_id' => $planta->id,
                'planta_nombre' => $planta->nombre,
                'stock_actual' => $planta->stock_actual,
                'stock_minimo' => $planta->stock_minimo,
                'tipo_movimiento' => $movimiento->tipo,
                'cantidad_revertida' => $movimiento->cantidad,
            ]);

            return $movimiento->delete();
        });
    }
}
