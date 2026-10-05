<?php

namespace App\Services;

use App\Models\LecturaSensor;
use App\Models\PlantaVendida;
use App\Models\Sensor;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LecturaSensorService
{
    public function __construct(
        private readonly EvaluadorUmbralesService $evaluadorUmbrales,
        private readonly AlertaMonitoreoService $alertaMonitoreo,
    ) {}

    /**
     * @param array{
     *     planta_vendida_id: int,
     *     humedad_suelo: int|float|string,
     *     temperatura: int|float|string,
     *     humedad_ambiental: int|float|string,
     *     luz: int|float|string,
     *     fecha_hora: string
     * } $datos
     * @return array{
     *     lectura: LecturaSensor,
     *     desviaciones: array<int, array<string, mixed>>,
     *     alertas: array{activas: array<int, array<string, mixed>>, resueltas: array<int, array{id: int, variable: string, resuelta_en: string}>}
     * }
     */
    public function registrarLectura(Sensor $sensor, array $datos): array
    {
        return DB::transaction(function () use ($sensor, $datos) {
            $unidad = PlantaVendida::query()
                ->with('venta.planta')
                ->lockForUpdate()
                ->findOrFail($datos['planta_vendida_id']);

            if ((int) $unidad->sensor_id !== (int) $sensor->id) {
                throw new AuthorizationException('El sensor no está asignado a esta planta vendida.');
            }

            $lectura = LecturaSensor::create([
                'planta_vendida_id' => $unidad->id,
                'humedad_suelo' => $datos['humedad_suelo'],
                'temperatura' => $datos['temperatura'],
                'humedad_ambiental' => $datos['humedad_ambiental'],
                'luz' => $datos['luz'],
                'fecha_hora' => Carbon::parse($datos['fecha_hora'], config('app.timezone'))
                    ->setTimezone(config('app.timezone')),
            ]);

            $desviaciones = $this->evaluadorUmbrales->evaluar($unidad->venta->planta, [
                'humedad_suelo' => $lectura->humedad_suelo,
                'temperatura' => $lectura->temperatura,
                'humedad_ambiental' => $lectura->humedad_ambiental,
                'luz' => $lectura->luz,
            ]);
            $alertas = $this->alertaMonitoreo->sincronizar(
                $unidad,
                $this->evaluadorUmbrales->variablesEvaluables($unidad->venta->planta),
                $desviaciones,
            );

            return [
                'lectura' => $lectura,
                'desviaciones' => $desviaciones,
                'alertas' => $alertas,
            ];
        });
    }
}
