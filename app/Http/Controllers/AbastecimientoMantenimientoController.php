<?php

namespace App\Http\Controllers;

use App\Models\CasoMantenimiento;
use App\Models\RequisicionItem;
use App\Services\AbastecimientoMantenimientoService;
use App\Services\RecepcionMaterialService;
use App\Services\RequisicionMantenimientoService;
use App\Services\VerificadorDisponibilidadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AbastecimientoMantenimientoController extends Controller
{
    public function generar(Request $request, CasoMantenimiento $caso, RequisicionMantenimientoService $service): RedirectResponse
    {
        try {
            $plan = $caso->planes()->latest('version')->latest('id')->first();
            if (!$plan) {
                throw new \InvalidArgumentException('Primero debe generar el plan de mantenimiento.');
            }
            $veredicto = (new VerificadorDisponibilidadService())->verificarPlan($plan->id);
            $service->generar($caso, (int) $request->user()->id, $veredicto);
            return redirect()->route('mantenimiento.casos.show.etapa', ['caso' => $caso, 'etapa' => 'abastecimiento'])->with('success', 'Requisición generada correctamente.');
        } catch (\Throwable $exception) {
            return redirect()->route('mantenimiento.casos.show.etapa', ['caso' => $caso, 'etapa' => 'abastecimiento'])
                ->withErrors(['generar' => $exception->getMessage()], 'abastecimiento');
        }
    }

    public function recibir(Request $request, CasoMantenimiento $caso, RecepcionMaterialService $service): RedirectResponse
    {
        try {
            $item = RequisicionItem::query()->with(['requisicion', 'recurso'])->findOrFail((int) $request->input('item_id'));
            $cantidad = (float) $request->input('cantidad', 0);
            $service->recibir($caso, $item, $cantidad, (int) $request->user()->id);
            return redirect()->route('mantenimiento.casos.show.etapa', ['caso' => $caso, 'etapa' => 'abastecimiento'])->with('success', 'Recepción registrada correctamente.');
        } catch (\Throwable $exception) {
            return redirect()->route('mantenimiento.casos.show.etapa', ['caso' => $caso, 'etapa' => 'abastecimiento'])
                ->withErrors(['recibir' => $exception->getMessage()], 'abastecimiento');
        }
    }

    public function preparar(Request $request, CasoMantenimiento $caso, AbastecimientoMantenimientoService $service): RedirectResponse
    {
        try {
            $plan = $caso->planes()->latest('version')->latest('id')->first();
            if (!$plan) {
                throw new \InvalidArgumentException('Primero debe generar el plan de mantenimiento.');
            }
            $service->preparar($caso, $plan->id);
            return redirect()->route('mantenimiento.casos.show.etapa', ['caso' => $caso, 'etapa' => 'abastecimiento'])->with('success', 'Caso marcado como preparado.');
        } catch (\Throwable $exception) {
            return redirect()->route('mantenimiento.casos.show.etapa', ['caso' => $caso, 'etapa' => 'abastecimiento'])
                ->withErrors(['preparar' => $exception->getMessage()], 'abastecimiento');
        }
    }
}
