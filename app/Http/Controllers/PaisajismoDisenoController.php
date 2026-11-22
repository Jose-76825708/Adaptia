<?php

namespace App\Http\Controllers;

use App\Models\ProyectoPaisajismo;
use App\Services\DisenoProyectoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaisajismoDisenoController extends Controller
{
    public function propuesta(Request $request, ProyectoPaisajismo $proyecto, DisenoProyectoService $service): RedirectResponse
    {
        try {
            $lineas=$service->normalizarLineas($request->input('dis_lineas',[]));
            $service->guardarPropuesta($proyecto,(int)$request->user()->id,(string)$request->input('dis_concepto',''),$lineas);
            return redirect()->route('paisajismo.proyectos.show',$proyecto)->with('success','Propuesta guardada correctamente.');
        } catch (\Throwable $exception) {
            return redirect()->route('paisajismo.proyectos.show',$proyecto)->withErrors(['propuesta'=>$exception->getMessage()],'diseno');
        }
    }

    public function decision(Request $request, ProyectoPaisajismo $proyecto, DisenoProyectoService $service): RedirectResponse
    {
        try {
            $service->registrarDecision($proyecto,(int)$request->user()->id,(string)$request->input('dis_decision',''),$request->input('dis_motivo'));
            return redirect()->route('paisajismo.proyectos.show',$proyecto)->with('success','Decisión registrada correctamente.');
        } catch (\Throwable $exception) {
            return redirect()->route('paisajismo.proyectos.show',$proyecto)->withErrors(['decision'=>$exception->getMessage()],'diseno');
        }
    }

    public function propuestaCliente(Request $request, ProyectoPaisajismo $proyecto): View
    {
        if ((int)$proyecto->solicitante_id !== (int)$request->user()->id) abort(404);
        $propuesta=$proyecto->propuestas()->with('plantas.planta')->orderByDesc('version')->orderByDesc('id')->first();
        if (!$propuesta) abort(404);

        return view('paisajismo.solicitudes.propuesta',compact('proyecto','propuesta'));
    }
}
