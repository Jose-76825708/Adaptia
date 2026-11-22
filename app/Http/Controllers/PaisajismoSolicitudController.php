<?php

namespace App\Http\Controllers;

use App\Models\ProyectoPaisajismo;
use App\Services\ProyectoPaisajismoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

class PaisajismoSolicitudController extends Controller
{
    public function index(Request $request, ProyectoPaisajismoService $service): View
    {
        $proyectos = ProyectoPaisajismo::where('solicitante_id',$request->user()->id)
            ->withCount('propuestas')
            ->orderByDesc('created_at')->orderByDesc('id')->paginate(10);

        return view('paisajismo.solicitudes.index', compact('proyectos'));
    }

    public function create(): View
    {
        return view('paisajismo.solicitudes.create');
    }

    public function store(Request $request, ProyectoPaisajismoService $service): RedirectResponse
    {
        try {
            $service->abrir((int)$request->user()->id,$request->only(['nombre','ubicacion','descripcion','area_m2','presupuesto_estimado','fecha_deseada']));
            return redirect()->route('paisajismo.solicitudes.index')->with('success','Solicitud de paisajismo registrada correctamente.');
        } catch (ValidationException $exception) {
            return redirect()->route('paisajismo.solicitudes.create')->withErrors($exception->errors())->withInput();
        }
    }

    public function show(ProyectoPaisajismo $proyecto, Request $request): View
    {
        if ((int)$proyecto->solicitante_id !== (int)$request->user()->id) {
            abort(404);
        }

        $proyecto->loadCount('propuestas');
        return view('paisajismo.solicitudes.show', compact('proyecto'));
    }

    public function propuesta(ProyectoPaisajismo $proyecto, Request $request): View
    {
        if ((int)$proyecto->solicitante_id !== (int)$request->user()->id) {
            abort(404);
        }

        abort(404);
    }
}
