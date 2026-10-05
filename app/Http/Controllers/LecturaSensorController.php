<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLecturaSensorRequest;
use App\Models\Sensor;
use App\Services\LecturaSensorService;
use Illuminate\Http\JsonResponse;
use LogicException;

class LecturaSensorController extends Controller
{
    public function __construct(private readonly LecturaSensorService $service) {}

    public function store(StoreLecturaSensorRequest $request): JsonResponse
    {
        $sensor = $request->attributes->get('authenticated_sensor');

        if (! $sensor instanceof Sensor) {
            throw new LogicException('El middleware de credenciales no proporcionó un sensor autenticado.');
        }

        $resultado = $this->service->registrarLectura($sensor, $request->validated());
        $lectura = $resultado['lectura'];

        return response()->json([
            'message' => 'Lectura registrada correctamente.',
            'data' => [
                'id' => $lectura->id,
                'planta_vendida_id' => $lectura->planta_vendida_id,
                'humedad_suelo' => (float) $lectura->humedad_suelo,
                'temperatura' => (float) $lectura->temperatura,
                'humedad_ambiental' => (float) $lectura->humedad_ambiental,
                'luz' => (float) $lectura->luz,
                'fecha_hora' => $lectura->fecha_hora->toIso8601String(),
            ],
            'desviaciones' => $resultado['desviaciones'],
            'alertas' => $resultado['alertas']['activas'],
            'alertas_resueltas' => $resultado['alertas']['resueltas'],
        ], 201);
    }
}
