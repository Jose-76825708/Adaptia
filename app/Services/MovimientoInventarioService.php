<?php

namespace App\Services;

use App\Exceptions\MovimientoNoAnulableException;
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
        return MovimientoInventario::with(['planta', 'user', 'anulacion'])->get();
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

            $this->aplicarCambioStock($planta, $data['tipo'], (int) $data['cantidad']);

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
     * Anula un movimiento creando un contramovimiento de signo contrario.
     * El movimiento original nunca se modifica ni se elimina.
     *
     * @param string $id
     * @param string $motivo
     * @param int $userId
     * @return Model
     *
     * @throws MovimientoNoAnulableException
     * @throws StockInsuficienteException
     */
    public function anularMovimiento(string $id, string $motivo, int $userId): Model
    {
        return DB::transaction(function () use ($id, $motivo, $userId) {
            $original = MovimientoInventario::query()->lockForUpdate()->findOrFail($id);

            if ($original->esAnulacion()) {
                throw new MovimientoNoAnulableException('No se puede anular un movimiento que ya es una anulación.');
            }

            if ($original->anulacion()->exists()) {
                throw new MovimientoNoAnulableException('El movimiento ya fue anulado.');
            }

            $planta = Planta::query()->lockForUpdate()->findOrFail($original->planta_id);
            $stockAntes = $planta->stock_actual;
            $tipoInverso = $original->tipo === 'entrada' ? 'salida' : 'entrada';

            $this->aplicarCambioStock($planta, $tipoInverso, (int) $original->cantidad);

            $anulacion = MovimientoInventario::create([
                'planta_id' => $original->planta_id,
                'user_id' => $userId,
                'tipo' => $tipoInverso,
                'cantidad' => $original->cantidad,
                'motivo' => $motivo,
                'stock_antes' => $stockAntes,
                'stock_despues' => $planta->stock_actual,
                'movimiento_original_id' => $original->id,
            ]);

            $this->alertaStockService->evaluar($planta, 'Stock bajo detectado tras anulación de movimiento', [
                'planta_id' => $planta->id,
                'planta_nombre' => $planta->nombre,
                'stock_actual' => $planta->stock_actual,
                'stock_minimo' => $planta->stock_minimo,
                'movimiento_original_id' => $original->id,
                'tipo_anulacion' => $tipoInverso,
                'cantidad_revertida' => $original->cantidad,
            ]);

            return $anulacion;
        });
    }

    /**
     * Aplica una entrada o salida sobre el stock de la planta bloqueada.
     *
     * @throws StockInsuficienteException
     */
    private function aplicarCambioStock(Planta $planta, string $tipo, int $cantidad): void
    {
        if ($tipo === 'entrada') {
            $planta->increment('stock_actual', $cantidad);
        } else {
            if ($planta->stock_actual < $cantidad) {
                throw new StockInsuficienteException("Stock insuficiente para {$planta->nombre}. Disponible: {$planta->stock_actual}, requerido: {$cantidad}");
            }
            $planta->decrement('stock_actual', $cantidad);
        }
        $planta->save();
    }
}
