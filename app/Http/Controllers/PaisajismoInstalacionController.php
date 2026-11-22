<?php

namespace App\Http\Controllers;

use App\Models\ProyectoPaisajismo;
use App\Services\InstalacionProyectoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaisajismoInstalacionController extends Controller
{
    public function iniciar(Request $request,ProyectoPaisajismo $proyecto,InstalacionProyectoService $service): RedirectResponse
    {
        try {
            $service->iniciarInstalacion($proyecto,(int)$request->user()->id);
            return redirect()->route('paisajismo.proyectos.show',$proyecto)->with('success','Instalación iniciada correctamente.');
        } catch (\Throwable $exception) {
            return redirect()->route('paisajismo.proyectos.show',$proyecto)->withErrors(['iniciar'=>$exception->getMessage()],'instalacion');
        }
    }

    public function avance(Request $request,ProyectoPaisajismo $proyecto,InstalacionProyectoService $service): RedirectResponse
    {
        try {
            $porcentaje=(int)$request->input('ins_porcentaje',-1);
            $service->registrarAvance($proyecto,(int)$request->user()->id,(string)$request->input('ins_descripcion',''),$porcentaje);
            return redirect()->route('paisajismo.proyectos.show',$proyecto)->with('success','Avance registrado correctamente.');
        } catch (\Throwable $exception) {
            return redirect()->route('paisajismo.proyectos.show',$proyecto)->withErrors(['avance'=>$exception->getMessage()],'instalacion');
        }
    }
}
