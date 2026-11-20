<?php

namespace App\Http\Controllers;

use App\Models\CasoMantenimiento;
use App\Services\PlanMantenimientoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PlanMantenimientoController extends Controller
{
    public function generar(Request $request, CasoMantenimiento $caso, PlanMantenimientoService $service): RedirectResponse
    {
        try {
            $service->generarYPersistir($caso, (int) $request->user()->id);
            return redirect()->route('mantenimiento.casos.show.etapa', ['caso' => $caso, 'etapa' => 'plan'])->with('success', 'Plan generado correctamente.');
        } catch (\InvalidArgumentException $exception) {
            return redirect()->route('mantenimiento.casos.show.etapa', ['caso' => $caso, 'etapa' => 'plan'])
                ->withErrors(['generar' => $exception->getMessage()], 'plan');
        }
    }
}
