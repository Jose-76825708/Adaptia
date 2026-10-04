<?php

namespace App\Http\Controllers;

use App\Models\PlantaVendida;
use App\Models\User;
use App\Services\AsignacionSensorService;
use Illuminate\Http\Request;

class PlantaVendidaController extends Controller
{
    public function __construct(private AsignacionSensorService $service)
    {
    }

    public function index()
    {
        $unidadesPendientes = $this->service->getUnidadesPendientes();
        $sensoresDisponibles = $this->service->getSensoresDisponibles();

        return view('plantas-vendidas.index', compact('unidadesPendientes', 'sensoresDisponibles'));
    }

    public function asignarSensor(Request $request, PlantaVendida $unidad)
    {
        $datos = $request->validate([
            'sensor_id' => ['required', 'integer', 'exists:sensores,id'],
        ]);

        $vendedor = $request->user();

        if (!$vendedor instanceof User) {
            abort(403);
        }

        $this->service->asignarSensor($unidad, $datos['sensor_id'], $vendedor);

        return redirect()->route('plantas-vendidas.index')
            ->with('success', 'Sensor asignado correctamente.');
    }
}
