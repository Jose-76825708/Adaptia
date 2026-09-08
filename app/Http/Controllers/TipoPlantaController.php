<?php

namespace App\Http\Controllers;

use App\Services\TipoPlantaService;
use Illuminate\Http\Request;

class TipoPlantaController extends Controller
{
    protected $service;

    public function __construct(TipoPlantaService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $posts = $this->service->getAll();

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

        $this->service->createTipo($validar_datos);

        return redirect()->route('tipoPlantas.index');
    }

public function edit(string $id)
    {
        $find = $this->service->getTipoById($id);

        return view('tipoPlantas.edit', compact('find'));
    }

    public function update(Request $request, string $id)
    {
        $validar_datos = $request->validate([
            'nombre' => 'required|string'
        ]);

        $this->service->updateTipo($id, $validar_datos);

        return redirect()->route('tipoPlantas.index');
    }

    public function destroy(string $id)
    {
        $this->service->deleteTipo($id);

        return redirect()->route('tipoPlantas.index');
    }
}
