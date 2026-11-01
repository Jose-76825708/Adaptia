<?php

namespace App\Http\Controllers;

use App\Http\Requests\MovimientoInventarioRequest;
use App\Models\Planta;
use App\Services\MovimientoInventarioService;
use Illuminate\Support\Facades\Auth;

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
        $plantas = Planta::all();
        return view('movimientos-inventario.create', compact('plantas'));
    }

    public function store(MovimientoInventarioRequest $request)
    {
        $datos = $request->validated();
        $datos['user_id'] = Auth::id();

        $this->service->createMovimiento($datos);

        return redirect()->route('movimientos-inventario.index')
            ->with('success', 'Movimiento de inventario registrado correctamente.');
    }

    public function edit(string $id)
    {
        $movimiento = $this->service->getById($id);
        $plantas = Planta::all();

        return view('movimientos-inventario.edit', compact('movimiento', 'plantas'));
    }

    public function update(MovimientoInventarioRequest $request, string $id)
    {
        $datos = $request->validated();

        $this->service->updateMovimiento($id, $datos);

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
