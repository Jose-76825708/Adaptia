<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\VentaService;
use Illuminate\Http\Request;

class VentaController extends Controller
{
    public function __construct(private VentaService $service)
    {
    }

    public function index()
    {
        $ventas = $this->service->getAll();

        return view('ventas.index', compact('ventas'));
    }

    public function create()
    {
        $datosFormulario = $this->service->getDatosFormulario();

        return view('ventas.create', $datosFormulario);
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'planta_id' => ['required', 'integer', 'exists:plantas,id'],
            'cantidad' => ['required', 'integer', 'min:1'],
        ]);

        $vendedor = $request->user();

        if (!$vendedor instanceof User) {
            abort(403);
        }

        $this->service->registrarVenta($datos, $vendedor);

        return redirect()->route('ventas.index')
            ->with('success', 'Venta registrada correctamente.');
    }
}
