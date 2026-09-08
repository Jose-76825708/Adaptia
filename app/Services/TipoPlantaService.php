<?php

namespace App\Services;

use App\Models\TipoPlanta;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class TipoPlantaService
{
    /**
     * Obtiene todos los tipos de planta.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return TipoPlanta::all();
    }

    /**
     * Crea un nuevo tipo de planta.
     *
     * @param array $data
     * @return Model
     */
    public function createTipo(array $data): Model
    {
        return TipoPlanta::create($data);
    }

    /**
     * Busca un tipo de planta por su ID.
     * Lanza un ModelNotFoundException si no existe (provoca un 404).
     *
     * @param string $id
     * @return Model
     */
    public function getTipoById(string $id): Model
    {
        return TipoPlanta::findOrFail($id);
    }

    /**
     * Actualiza un tipo de planta existente.
     *
     * @param string $id
     * @param array $data
     * @return Model
     */
    public function updateTipo(string $id, array $data): Model
    {
        $tipo = $this->getTipoById($id);
        $tipo->update($data);
        return $tipo;
    }

    /**
     * Elimina un tipo de planta.
     *
     * @param string $id
     * @return bool
     */
    public function deleteTipo(string $id): bool
    {
        $tipo = $this->getTipoById($id);
        return $tipo->delete();
    }
}
