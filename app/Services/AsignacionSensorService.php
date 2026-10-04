<?php

namespace App\Services;

use App\Models\PlantaVendida;
use App\Models\Sensor;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AsignacionSensorService
{
    public function getUnidadesPendientes(): Collection
    {
        return PlantaVendida::query()
            ->with(['venta.planta', 'cliente'])
            ->whereNull('sensor_id')
            ->orderBy('created_at')
            ->get();
    }

    public function getSensoresDisponibles(): Collection
    {
        return Sensor::query()
            ->where('estado', 'activo')
            ->whereDoesntHave('plantaVendida')
            ->orderBy('identificador_fisico')
            ->get();
    }

    public function asignarSensor(
        PlantaVendida $unidad,
        int $sensorId,
        User $vendedor
    ): PlantaVendida {
        if ($vendedor->rol !== 'vendedor') {
            throw new AuthorizationException('Solo un vendedor puede asignar sensores.');
        }

        return DB::transaction(function () use ($unidad, $sensorId) {
            $sensor = Sensor::query()
                ->lockForUpdate()
                ->find($sensorId);

            if (!$sensor || $sensor->estado !== 'activo') {
                throw ValidationException::withMessages([
                    'sensor_id' => 'Selecciona un sensor activo válido.',
                ]);
            }

            $unidadBloqueada = PlantaVendida::query()
                ->lockForUpdate()
                ->findOrFail($unidad->id);

            if ($unidadBloqueada->sensor_id !== null) {
                throw ValidationException::withMessages([
                    'planta_vendida_id' => 'Esta unidad ya tiene un sensor asignado.',
                ]);
            }

            $sensorAsignado = PlantaVendida::query()
                ->where('sensor_id', $sensor->id)
                ->exists();

            if ($sensorAsignado) {
                throw ValidationException::withMessages([
                    'sensor_id' => 'Este sensor ya está asignado a otra unidad.',
                ]);
            }

            $unidadBloqueada->sensor_id = $sensor->id;
            $unidadBloqueada->save();

            return $unidadBloqueada;
        });
    }
}
