<?php

namespace App\Http\Controllers;

use App\Services\PlantaService;
use App\Services\TipoPlantaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PlantaController extends Controller

{
    protected $plantaService;
    protected $tipoPlantaService;

    public function __construct(PlantaService $plantaService, TipoPlantaService $tipoPlantaService)
    {
        $this->plantaService = $plantaService;
        $this->tipoPlantaService = $tipoPlantaService;
    }

    public function index()
    {
        $posts = $this->plantaService->getAll();

        return view('plantas.index', compact('posts'));
    }

    public function create()
    {
        $tipos = $this->tipoPlantaService->getAll();

        return view('plantas.create', compact('tipos'));
    }

    public function store(Request $request)
    {
        $validar_datos = $request->validate([
            'tipo_planta_id' => 'required|exists:tipo_plantas,id',
            'nombre' => 'required|string',
            'precio' => 'required|numeric|min:0',
            'luz_requerida' => 'required|in:baja,media,alta,siempre_en_el_sol',
            'tamaño_adulto' => 'required|in:pequena,mediana,grande',
            'tipo_ambiente' => 'required|in:interiores,exteriores,ambos',
            'frecuencia_riego' => 'required|in:diario,cada_3_dias,semanal,quincenal,mensualmente',
            'estetica' => 'required|in:follaje,flor,colgantes,suculentas',
            'nivel_cuidado' => 'required|in:principiante,intermedio,experto',
            'stock_actual' => 'required|integer|min:0',
            'stock_minimo' => 'required|integer|min:0',
            'es_toxica' => 'nullable',
            'imagen' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        // Manejo del checkbox es_toxica -> toxicidad en DB
        $validar_datos['toxicidad'] = $request->has('es_toxica') ? 1 : 0;

        // Procesar la imagen
        if ($request->hasFile('imagen')) {
            $path = $request->file('imagen')->store('plantas', 'public');
            $validar_datos['imagen'] = $path;
        }

        $this->plantaService->createPlanta($validar_datos);

        return redirect()->route('plantas.index');
    }

    public function show(string $id)
    {
        $find = $this->plantaService->getPlantaById($id);

        return view('plantas.show', compact('find'));
    }

    public function edit(string $id)
    {
        $tipos = $this->tipoPlantaService->getAll();
        $find = $this->plantaService->getPlantaById($id);

        return view('plantas.edit', compact('tipos', 'find'));
    }

    public function update(Request $request, string $id)
    {
        $validar_datos = $request->validate([
            'tipo_planta_id' => 'required|exists:tipo_plantas,id',
            'nombre' => 'required|string',
            'precio' => 'required|numeric|min:0',
            'luz_requerida' => 'required|in:baja,media,alta,siempre_en_el_sol',
            'tamaño_adulto' => 'required|in:pequena,mediana,grande',
            'tipo_ambiente' => 'required|in:interiores,exteriores,ambos',
            'frecuencia_riego' => 'required|in:diario,cada_3_dias,semanal,quincenal,mensualmente',
            'estetica' => 'required|in:follaje,flor,colgantes,suculentas',
            'nivel_cuidado' => 'required|in:principiante,intermedio,experto',
            'stock_actual' => 'required|integer|min:0',
            'stock_minimo' => 'required|integer|min:0',
            'es_toxica' => 'nullable',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        // Manejo del checkbox es_toxica -> toxicidad en DB
        $validar_datos['toxicidad'] = $request->has('es_toxica') ? 1 : 0;

        if ($request->hasFile('imagen')) {
            $planta = $this->plantaService->getPlantaById($id);

            // Eliminar imagen anterior si existe para no llenar el servidor de basura
            if ($planta->imagen) {
                Storage::disk('public')->delete($planta->imagen);
            }

            $path = $request->file('imagen')->store('plantas', 'public');
            $validar_datos['imagen'] = $path;
        }

        $this->plantaService->updatePlanta($id, $validar_datos);

        return redirect()->route('plantas.index');
    }

    public function destroy(string $id)
    {
        $this->plantaService->deletePlanta($id);

        return redirect()->route('plantas.index');
    }
}
