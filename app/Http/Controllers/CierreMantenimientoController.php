<?php

namespace App\Http\Controllers;

use App\Models\CasoMantenimiento;
use App\Services\CierreMantenimientoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CierreMantenimientoController extends Controller
{
    public function cerrar(Request $request, CasoMantenimiento $caso, CierreMantenimientoService $service): RedirectResponse
    {
        try {
            $service->cerrar($caso, (int) $request->user()->id);
            return redirect()->route('mantenimiento.casos.show.etapa', ['caso' => $caso, 'etapa' => 'cierre'])->with('success', 'Caso cerrado correctamente.');
        } catch (\Throwable $exception) {
            return redirect()->route('mantenimiento.casos.show.etapa', ['caso' => $caso, 'etapa' => 'cierre'])
                ->withErrors(['cerrar' => $exception->getMessage()], 'cierre');
        }
    }

    public function reabrir(Request $request, CasoMantenimiento $caso, CierreMantenimientoService $service): RedirectResponse
    {
        try {
            $service->reabrir($caso, (int) $request->user()->id, (string) $request->input('motivo', ''));
            return redirect()->route('mantenimiento.casos.show.etapa', ['caso' => $caso, 'etapa' => 'cierre'])->with('success', 'Caso reabierto correctamente.');
        } catch (\Throwable $exception) {
            return redirect()->route('mantenimiento.casos.show.etapa', ['caso' => $caso, 'etapa' => 'cierre'])
                ->withErrors(['reabrir' => $exception->getMessage()], 'cierre');
        }
    }

    public function informe(Request $request, CasoMantenimiento $caso): View
    {
        $usuario = $request->user();
        if ($usuario->rol !== 'administrador' && (int) $usuario->id !== (int) $caso->solicitante_id) {
            abort(404);
        }

        $informe = $caso->informes()->latest('version')->latest('id')->first();
        if (!$informe) {
            abort(404);
        }

        return view('mantenimiento.casos.informe', compact('caso', 'informe'));
    }
}
