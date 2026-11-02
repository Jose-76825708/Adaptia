<?php

namespace App\Http\Controllers;

use App\Exceptions\MovimientoNoAnulableException;
use App\Exceptions\StockInsuficienteException;
use App\Http\Requests\AnularMovimientoRequest;
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

    public function anular(AnularMovimientoRequest $request, string $id)
    {
        try {
            $this->service->anularMovimiento($id, $request->validated('motivo'), Auth::id());
        } catch (StockInsuficienteException|MovimientoNoAnulableException $e) {
            return redirect()->route('movimientos-inventario.index')
                ->with('error', $e->getMessage());
        }

        return redirect()->route('movimientos-inventario.index')
            ->with('success', 'Movimiento anulado correctamente.');
    }
}
