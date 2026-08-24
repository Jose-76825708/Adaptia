<?php

namespace App\Http\Controllers;

use App\Models\TipoPlanta;
use Illuminate\Http\Request;

class TipoPlantaController extends Controller
{

    public function index()
    {
        $posts = TipoPlanta::all();

        return view('tipoPlantas.index', compact('posts'));
    }

    
    public function create()
    {
        return view('tipoPlantas.create');
    }

    
    public function store(Request $request)
    {
        $validar_datos = $request->validate([

            'nombre' => 'required|string'

        ]);

        TipoPlanta::create($validar_datos);

        return redirect()->route('tipoPlantas.index');
    }

    
    public function show(string $id)
    {
        $find = TipoPlanta::find($id);

        return view('tipoPlantas.show', compact('find'));
    }

    
    public function edit(string $id)
    {
        $find = TipoPlanta::find($id);

        return view('tipoPlantas.edit', compact('find'));
    }

    
    public function update(Request $request, string $id)
    {
        $validar_datos = $request->validate([

            'nombre' => 'required|string'

        ]);

        $tipo_encontrado = TipoPlanta::find($id);

        $tipo_encontrado->update($validar_datos);

        return redirect()->route('tipoPlantas.index');
    }

    public function destroy(string $id)
    {
        TipoPlanta::destroy($id);

        return redirect()->route('tipoPlantas.index');
    }
}
