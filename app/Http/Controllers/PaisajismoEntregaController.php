<?php

namespace App\Http\Controllers;

use App\Models\ProyectoPaisajismo;
use App\Services\EntregaProyectoService;
use App\Services\CasoMantenimientoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaisajismoEntregaController extends Controller
{
    public function entrega(Request $request,ProyectoPaisajismo $proyecto,EntregaProyectoService $service): RedirectResponse
    {
        try {
            $service->registrarEntrega($proyecto,(int)$request->user()->id,(string)$request->input('ent_resumen',''),(string)$request->input('ent_recomendaciones',''));
            return redirect()->route('paisajismo.proyectos.show',$proyecto)->with('success','Entrega registrada correctamente.');
        } catch (\Throwable $exception) {
            return redirect()->route('paisajismo.proyectos.show',$proyecto)->withErrors(['entrega'=>$exception->getMessage()],'entrega');
        }
    }

    public function satisfaccion(Request $request,ProyectoPaisajismo $proyecto,EntregaProyectoService $service): RedirectResponse
    {
        try {
            if ((int)$proyecto->solicitante_id !== (int)$request->user()->id) abort(404);
            $service->registrarSatisfaccion($proyecto,(string)$request->input('comentario',''),(int)$request->input('calificacion',0));
            return redirect()->route('paisajismo.solicitudes.resumen',$proyecto)->with('success','Satisfacción registrada correctamente.');
        } catch (\Throwable $exception) {
            if ($exception instanceof \Symfony\Component\HttpKernel\Exception\HttpException) throw $exception;
            return redirect()->route('paisajismo.solicitudes.resumen',$proyecto)->withErrors(['satisfaccion'=>$exception->getMessage()],'entrega');
        }
    }

    public function resumen(Request $request,ProyectoPaisajismo $proyecto,EntregaProyectoService $service): View
    {
        if ((int)$proyecto->solicitante_id !== (int)$request->user()->id) abort(404);
        if ($proyecto->estado !== 'entregado') abort(404);

        $resumen=$service->compilarResumen($proyecto);
        return view('paisajismo.solicitudes.resumen',compact('proyecto','resumen'));
    }

    public function mantenimiento(Request $request,ProyectoPaisajismo $proyecto,CasoMantenimientoService $service): RedirectResponse
    {
        if ((int)$proyecto->solicitante_id !== (int)$request->user()->id || $proyecto->estado !== 'entregado') abort(404);

        $caso=$service->abrir((int)$request->user()->id,[
            'area_verde'=>$proyecto->nombre,
            'descripcion'=>'Mantenimiento posterior del proyecto '.$proyecto->codigo.' ('.$proyecto->ubicacion.')',
            'fecha_deseada'=>null,
            'observaciones'=>null,
        ]);

        return redirect()->route('mantenimiento.solicitudes.index')->with('success','Caso de mantenimiento creado: '.$caso->codigo);
    }
}
