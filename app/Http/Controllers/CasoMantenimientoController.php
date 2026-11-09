<?php

namespace App\Http\Controllers;

use App\Models\CasoMantenimiento;
use App\Services\CasoMantenimientoService;
use Illuminate\Http\Request;

class CasoMantenimientoController extends Controller
{
    public function index(Request $request, CasoMantenimientoService $service)
    {
        $filtros = $request->only(['estado', 'urgencia', 'tipo', 'q']);

        if (array_key_exists('q', $filtros) && !is_string($filtros['q'])) {
            $filtros['q'] = null;
        } elseif (is_string($filtros['q'] ?? null)) {
            $filtros['q'] = trim($filtros['q']);
        }

        return view('mantenimiento.casos.index', [
            'casos' => $service->listar($filtros, 15),
            'filtros' => $filtros,
        ]);
    }

    public function show(CasoMantenimiento $caso)
    {
        return view('mantenimiento.casos.show', [
            'caso' => $caso->load('solicitante'),
        ]);
    }
}
