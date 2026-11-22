<?php

namespace App\Services;

use App\Models\Sensor;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SensorService
{
    /**
     * Obtiene todos los sensores.
     */
    public function getAll(): Collection
    {
        return Sensor::all();
    }

    /**
     * Crea un nuevo sensor.
     */
    public function createSensor(array $data): Model
    {
        return Sensor::create($data);
    }

    /**
     * Busca un sensor por su ID.
     * Lanza un ModelNotFoundException si no existe (provoca un 404).
     */
    public function getSensorById(string $id): Model
    {
        return Sensor::findOrFail($id);
    }

    /**
     * Actualiza un sensor existente.
     */
    public function updateSensor(string $id, array $data): Model
    {
        $sensor = $this->getSensorById($id);
        $sensor->update($data);

        return $sensor;
    }

    public function generarCredencial(string $id): string
    {
        return DB::transaction(function () use ($id) {
            $sensor = Sensor::query()
                ->lockForUpdate()
                ->findOrFail($id);
            $token = Str::random(64);

            $sensor->token_hash = hash('sha256', $token);
            $sensor->save();

            return $token;
        });
    }

    /**
     * Elimina un sensor.
     */
    public function deleteSensor(string $id): bool
    {
        return DB::transaction(function () use ($id) {
            $sensor = Sensor::query()
                ->lockForUpdate()
                ->findOrFail($id);

            if ($sensor->plantaVendida()->exists()) {
                throw ValidationException::withMessages([
                    'sensor' => 'No se puede eliminar un sensor asignado a una planta vendida. Puedes desactivarlo para conservar su historial.',
                ]);
            }

            return $sensor->delete();
        });
    }
}
