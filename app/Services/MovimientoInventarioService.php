<?php

namespace App\Services;

use App\Models\MovimientoInventario;
use App\Models\Planta;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class MovimientoInventarioService
{
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
            $movimiento = MovimientoInventario::create($data);

            // Actualizar stock de la planta
            $planta = Planta::findOrFail($data['planta_id']);

            if ($data['tipo'] === 'entrada') {
                $planta->increment('stock_actual', $data['cantidad']);
            } else { // salida
                if ($planta->stock_actual < $data['cantidad']) {
                    throw new \Exception("Stock insuficiente para {$planta->nombre}. Disponible: {$planta->stock_actual}, requerido: {$data['cantidad']}");
                }
                $planta->decrement('stock_actual', $data['cantidad']);
            }
            $planta->save();

            // Verificar y notificar stock bajo después de actualizar el inventario
            if ($planta->stock_actual < $planta->stock_minimo) {
                // Flash message para feedback inmediato al usuario
                Session::flash('stock_warning',
                    "Advertencia: El stock de {$planta->nombre} está bajo el mínimo. " .
                    "Actual: {$planta->stock_actual}, Mínimo: {$planta->stock_minimo}"
                );

                // Entrada en log para historial permanente y auditoría
                Log::warning("Stock bajo detectado tras movimiento de inventario", [
                    'planta_id' => $planta->id,
                    'planta_nombre' => $planta->nombre,
                    'stock_actual' => $planta->stock_actual,
                    'stock_minimo' => $planta->stock_minimo,
                    'tipo_movimiento' => $data['tipo'],
                    'cantidad' => $data['cantidad']
                ]);
            }

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

            // Calcular el efecto actual del movimiento
            $planta = Planta::findOrFail($movimiento->planta_id);
            $efectoActual = ($movimiento->tipo === 'entrada') ? $movimiento->cantidad : -$movimiento->cantidad;

            // Calcular el nuevo efecto basado en los datos actualizados
            $nuevoTipo = $data['tipo'] ?? $movimiento->tipo;
            $nuevaCantidad = $data['cantidad'] ?? $movimiento->cantidad;
            $nuevoEfecto = ($nuevoTipo === 'entrada') ? $nuevaCantidad : -$nuevaCantidad;

            // La diferencia es lo que necesitamos ajustar en el stock
            $diferencia = $nuevoEfecto - $efectoActual;

            // Actualizar el movimiento
            $movimiento->update($data);

            // Ajustar el stock de la planta según la diferencia
            if ($diferencia > 0) {
                // Necesitamos aumentar el stock
                $planta->increment('stock_actual', $diferencia);
            } elseif ($diferencia < 0) {
                // Necesitamos disminuir el stock
                $reduccion = abs($diferencia);
                if ($planta->stock_actual < $reduccion) {
                    throw new \Exception("Stock insuficiente para actualizar movimiento. Disponible: {$planta->stock_actual}, requerido: {$reduccion}");
                }
                $planta->decrement('stock_actual', $reduccion);
            }
            $planta->save();

            // Verificar y notificar stock bajo después de actualizar el inventario
            if ($planta->stock_actual < $planta->stock_minimo) {
                // Flash message para feedback inmediato al usuario
                Session::flash('stock_warning',
                    "Advertencia: El stock de {$planta->nombre} está bajo el mínimo. " .
                    "Actual: {$planta->stock_actual}, Mínimo: {$planta->stock_minimo}"
                );

                // Entrada en log para historial permanente y auditoría
                Log::warning("Stock bajo detectado tras actualización de movimiento", [
                    'planta_id' => $planta->id,
                    'planta_nombre' => $planta->nombre,
                    'stock_actual' => $planta->stock_actual,
                    'stock_minimo' => $planta->stock_minimo,
                    'tipo_movimiento_original' => $movimiento->tipo,
                    'nuevo_tipo_movimiento' => $data['tipo'] ?? $movimiento->tipo,
                    'cantidad_original' => $movimiento->cantidad,
                    'nueva_cantidad' => $data['cantidad'] ?? $movimiento->cantidad,
                    'diferencia_stock' => $diferencia
                ]);
            }

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

            // Revertir el efecto del movimiento en el stock
            $planta = Planta::findOrFail($movimiento->planta_id);

            if ($movimiento->tipo === 'entrada') {
                // Era una entrada, ahora la removemos: disminuir stock
                if ($planta->stock_actual < $movimiento->cantidad) {
                    throw new \Exception("Stock insuficiente para revertir movimiento. Disponible: {$planta->stock_actual}, requerido: {$movimiento->cantidad}");
                }
                $planta->decrement('stock_actual', $movimiento->cantidad);
            } else { // salida
                // Era una salida, ahora la removemos: aumentar stock
                $planta->increment('stock_actual', $movimiento->cantidad);
            }
            $planta->save();

            // Verificar y notificar stock bajo después de actualizar el inventario
            if ($planta->stock_actual < $planta->stock_minimo) {
                // Flash message para feedback inmediato al usuario
                Session::flash('stock_warning',
                    "Advertencia: El stock de {$planta->nombre} está bajo el mínimo. " .
                    "Actual: {$planta->stock_actual}, Mínimo: {$planta->stock_minimo}"
                );

                // Entrada en log para historial permanente y auditoría
                Log::warning("Stock bajo detectado tras eliminación de movimiento", [
                    'planta_id' => $planta->id,
                    'planta_nombre' => $planta->nombre,
                    'stock_actual' => $planta->stock_actual,
                    'stock_minimo' => $planta->stock_minimo,
                    'tipo_movimiento' => $movimiento->tipo,
                    'cantidad_revertida' => $movimiento->cantidad
                ]);
            }

            // Eliminar el movimiento
            return $movimiento->delete();
        });
    }
}