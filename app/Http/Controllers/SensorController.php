<?php

namespace App\Http\Controllers;

use App\Services\SensorService;
use Illuminate\Http\Request;

class SensorController extends Controller
{
    protected $service;

    public function __construct(SensorService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $sensores = $this->service->getAll();

        return view('sensores.index', compact('sensores'));
    }

    public function create()
    {
        return view('sensores.create');
    }

    public function store(Request $request)
    {
        $validar_datos = $request->validate([
            'identificador_fisico' => 'required|string|unique:sensores,identificador_fisico',
            'estado' => 'required|in:activo,inactivo'
        ]);

        $this->service->createSensor($validar_datos);

        return redirect()->route('sensores.index');
    }

    public function edit(string $id)
    {
        $sensor = $this->service->getSensorById($id);

        return view('sensores.edit', compact('sensor'));
    }

    public function update(Request $request, string $id)
    {
        $validar_datos = $request->validate([
            'identificador_fisico' => 'required|string|unique:sensores,identificador_fisico,'.$id,
            'estado' => 'required|in:activo,inactivo'
        ]);

        $this->service->updateSensor($id, $validar_datos);

        return redirect()->route('sensores.index');
    }

    public function destroy(string $id)
    {
        $this->service->deleteSensor($id);

        return redirect()->route('sensores.index');
    }
}