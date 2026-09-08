<?php

namespace App\Services;

use App\Models\Planta;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class PlantaService
{
    /**
     * Obtiene todas las plantas con su tipo relacionado.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return Planta::with('tipoPlanta')->get();
    }

    /**
     * Busca una planta por su ID.
     *
     * @param string $id
     * @return Model
     */
    public function getPlantaById(string $id): Model
    {
        return Planta::findOrFail($id);
    }

    /**
     * Crea una nueva planta.
     *
     * @param array $data
     * @return Model
     */
    public function createPlanta(array $data): Model
    {
        return Planta::create($data);
    }

    /**
     * Actualiza una planta existente.
     *
     * @param string $id
     * @param array $data
     * @return Model
     */
    public function updatePlanta(string $id, array $data): Model
    {
        $planta = $this->getPlantaById($id);
        $planta->update($data);
        return $planta;
    }

    /**
     * Elimina una planta.
     *
     * @param string $id
     * @return bool
     */
    public function deletePlanta(string $id): bool
    {
        $planta = $this->getPlantaById($id);
        return $planta->delete();
    }
}
