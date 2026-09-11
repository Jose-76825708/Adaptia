<?php

namespace App\Http\Controllers;

use App\Services\MovimientoInventarioService;
use Illuminate\Http\Request;

class MovimientoInventarioController extends Controller
{
    protected $service;

    public function __construct(MovimientoInventarioService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $movimientos = $this->service->getAll();

        return view('movimientos-inventario.index', compact('movimientos'));
    }

    public function create()
    {
        $plantas = $this->service->getAll();
        return view('movimientos-inventario.create', compact('plantas'));
    }

    public function store(Request $request)
    {
        $validar_datos = $request->validate([
            'planta_id' => 'required|exists:plantas,id',
            'user_id' => 'required|exists:users,id',
            'tipo' => 'required|in:entrada,salida',
            'cantidad' => 'required|integer|min:1',
        ]);

        $this->service->createMovimiento($validar_datos);

        return redirect()->route('movimientos-inventario.index')
            ->with('success', 'Movimiento de inventario registrado correctamente.');
    }

    public function edit(string $id)
    {
        $movimiento = $this->service->getById($id);
        $plantas = $this->service->getAll();

        return view('movimientos-inventario.edit', compact('movimiento', 'plantas'));
    }

    public function update(Request $request, string $id)
    {
        $validar_datos = $request->validate([
            'planta_id' => 'required|exists:plantas,id',
            'user_id' => 'required|exists:users,id',
            'tipo' => 'required|in:entrada,salida',
            'cantidad' => 'required|integer|min:1',
        ]);

        $this->service->updateMovimiento($id, $validar_datos);

        return redirect()->route('movimientos-inventario.index')
            ->with('success', 'Movimiento de inventario actualizado correctamente.');
    }

    public function destroy(string $id)
    {
        $this->service->deleteMovimiento($id);

        return redirect()->route('movimientos-inventario.index')
            ->with('success', 'Movimiento de inventario eliminado correctamente.');
    }
}