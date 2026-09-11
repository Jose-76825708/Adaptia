<?php

namespace App\Services;

use App\Models\MovimientoInventario;
use App\Models\Planta;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

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
        return MovimientoInventario::create($data);
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
        $movimiento = $this->getById($id);
        $movimiento->update($data);
        return $movimiento;
    }

    /**
     * Elimina un movimiento de inventario.
     *
     * @param string $id
     * @return bool
     */
    public function deleteMovimiento(string $id): bool
    {
        $movimiento = $this->getById($id);
        return $movimiento->delete();
    }
}