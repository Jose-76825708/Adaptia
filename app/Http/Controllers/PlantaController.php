<?php

namespace App\Http\Controllers;

use App\Models\Planta;
use App\Models\TipoPlanta;
use Illuminate\Http\Request;

class PlantaController extends Controller
{
    
    public function index()
    {
        $posts = Planta::all();

        return view('plantas.index', compact('posts'));
    }

    
    public function create()
    {
        $tipo_planta = TipoPlanta::all();

        return view('plantas.create', compact('tipo_planta'));
    }

    
    public function store(Request $request)
    {
        $validar_datos = $request->validate([

            'tipo_planta_id' => 'required|exists:tipo_plantas,id',
            'nombre' => 'required|string',
            'descripcion' => 'required|string',
            'imagen' => 'required|string',
            'luz_requerida' => 'required|in:baja,media,alta,siempre_en_el_sol',
            'frecuencia_riego' => 'required|in:diario,cada_3_dias,semanal,quincenal,mensualmente',
            'tamaño_adulto' => 'required|in:pequena,mediana,grande',
            'nivel_cuidado' => 'required|in:principiante,intermedio,experto',
            'tipo_ambiente' => 'required|in:interiores,exteriores,ambos',
            'toxicidad' => 'required|boolean',
            'estetica' => 'required|in:follaje,flor,colgante,suculentas',
            'precio' => 'required|numeric'


        ]);

        Planta::create($validar_datos);

        return redirect()->route('plantas.index');
    }

    
    public function show(string $id)
    {
        $find = Planta::find($id);

        return view('plantas.show', compact('find'));
    }

    
    public function edit(string $id)
    {
        $tipo_planta = TipoPlanta::all();
        $find = Planta::find($id);

        return view('plantas.edit', compact('tipo_planta','find'));
        
    }

    
    public function update(Request $request, string $id)
    {
        $validar_datos = $request->validate([

            'tipo_planta_id' => 'required|exists:tipo_plantas,id',
            'nombre' => 'required|string',
            'descripcion' => 'required|string',
            'imagen' => 'required|string',
            'luz_requerida' => 'required|in:baja,media,alta,siempre_en_el_sol',
            'frecuencia_riego' => 'required|in:diario,cada_3_dias,semanal,quincenal,mensualmente',
            'tamaño_adulto' => 'required|in:pequena,mediana,grande',
            'nivel_cuidado' => 'required|in:principiante,intermedio,experto',
            'tipo_ambiente' => 'required|in:interiores,exteriores,ambos',
            'toxicidad' => 'required|boolean',
            'estetica' => 'required|in:follaje,flor,colgante,suculentas',
            'precio' => 'required|numeric'


        ]);

        $planta_encontrada = Planta::find($id);

        $planta_encontrada->update($validar_datos);

        return redirect()->route('plantas.index');
    }

    
    public function destroy(string $id)
    {
        Planta::destroy($id);

        return redirect()->route('plantas.index');
    }
}
