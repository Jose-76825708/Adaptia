<?php

namespace App\Http\Controllers;

use App\Models\CasoMantenimiento;
use App\Models\EventoEjecucion;
use App\Models\ItemChecklist;
use App\Services\EjecucionMantenimientoService;
use App\Services\EstadoTareaChecklistService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EjecucionMantenimientoController extends Controller
{
    public function registrarEvento(Request $request, CasoMantenimiento $caso, EjecucionMantenimientoService $service): RedirectResponse
    {
        try {
            $tareaTexto = trim((string) $request->input('tarea', ''));
            $tareaId = null;

            if ($tareaTexto !== '') {
                $plan = $caso->planes()->latest('version')->latest('id')->first();
                if ($plan) {
                    $tarea = $plan->tareas()
                        ->where(function ($query) use ($tareaTexto) {
                            $query->where('codigo', $tareaTexto)
                                ->orWhere('titulo', 'like', '%' . $tareaTexto . '%');
                        })
                        ->orderBy('id')
                        ->first();
                    $tareaId = $tarea?->id;
                }
            }

            $descripcion = trim((string) $request->input('descripcion', ''));
            if ($tareaTexto !== '' && !str_contains($descripcion, $tareaTexto)) {
                $descripcion = trim($tareaTexto . ($descripcion !== '' ? '. ' . $descripcion : ''));
            }

            $service->registrar(
                $caso,
                (int) $request->user()->id,
                (string) $request->input('tipo', ''),
                $descripcion,
                $tareaId,
            );
            return redirect()->route('mantenimiento.casos.show.etapa', ['caso' => $caso, 'etapa' => 'ejecucion'])->with('success', 'Evento registrado correctamente.');
        } catch (\Throwable $exception) {
            return redirect()->route('mantenimiento.casos.show.etapa', ['caso' => $caso, 'etapa' => 'ejecucion'])
                ->withErrors(['evento' => $exception->getMessage()], 'ejecucion');
        }
    }

    public function actualizarChecklist(Request $request, CasoMantenimiento $caso, ItemChecklist $item, EstadoTareaChecklistService $service): RedirectResponse
    {
        try {
            $service->actualizarItem($caso, $item, $request->boolean('cumplido'));
            return redirect()->route('mantenimiento.casos.show.etapa', ['caso' => $caso, 'etapa' => 'ejecucion'])->with('success', 'Checklist actualizado.');
        } catch (\Throwable $exception) {
            return redirect()->route('mantenimiento.casos.show.etapa', ['caso' => $caso, 'etapa' => 'ejecucion'])
                ->withErrors(['checklist' => $exception->getMessage()], 'ejecucion');
        }
    }

    public function resolverIncidencia(Request $request, CasoMantenimiento $caso, EventoEjecucion $evento, EjecucionMantenimientoService $service): RedirectResponse
    {
        try {
            $service->resolverIncidencia($caso, $evento->id, (int) $request->user()->id);
            return redirect()->route('mantenimiento.casos.show.etapa', ['caso' => $caso, 'etapa' => 'ejecucion'])->with('success', 'Incidencia resuelta.');
        } catch (\Throwable $exception) {
            return redirect()->route('mantenimiento.casos.show.etapa', ['caso' => $caso, 'etapa' => 'ejecucion'])
                ->withErrors(['incidencia' => $exception->getMessage()], 'ejecucion');
        }
    }
}
