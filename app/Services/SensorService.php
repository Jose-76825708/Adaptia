<?php

namespace App\Services;

use App\Models\Sensor;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class SensorService
{
    /**
     * Obtiene todos los sensores.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return Sensor::all();
    }

    /**
     * Crea un nuevo sensor.
     *
     * @param array $data
     * @return Model
     */
    public function createSensor(array $data): Model
    {
        return Sensor::create($data);
    }

    /**
     * Busca un sensor por su ID.
     * Lanza un ModelNotFoundException si no existe (provoca un 404).
     *
     * @param string $id
     * @return Model
     */
    public function getSensorById(string $id): Model
    {
        return Sensor::findOrFail($id);
    }

    /**
     * Actualiza un sensor existente.
     *
     * @param string $id
     * @param array $data
     * @return Model
     */
    public function updateSensor(string $id, array $data): Model
    {
        $sensor = $this->getSensorById($id);
        $sensor->update($data);
        return $sensor;
    }

    /**
     * Elimina un sensor.
     *
     * @param string $id
     * @return bool
     */
    public function deleteSensor(string $id): bool
    {
        $sensor = $this->getSensorById($id);
        return $sensor->delete();
    }
}