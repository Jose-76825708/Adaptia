<?php

namespace App\Http\Controllers;

use App\Services\CasoMantenimientoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SolicitudMantenimientoController extends Controller
{
    public function __construct(private CasoMantenimientoService $casoMantenimientoService)
    {
    }

    public function create(): View
    {
        return view('mantenimiento.solicitudes.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $caso = $this->casoMantenimientoService->abrir(
            $request->user()->id,
            $request->only(['area_verde', 'descripcion', 'fecha_deseada', 'observaciones'])
        );

        return redirect()->route('mantenimiento.solicitudes.index')
            ->with('success', 'Caso abierto correctamente: ' . $caso->codigo);
    }

    public function index(Request $request): View
    {
        $casos = $this->casoMantenimientoService->listarPorSolicitante($request->user()->id);

        return view('mantenimiento.solicitudes.index', compact('casos'));
    }
}
