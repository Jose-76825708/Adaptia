<?php

namespace App\Http\Controllers;

use App\Models\ProyectoPaisajismo;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaisajismoProyectoController extends Controller
{
    public function index(Request $request): View
    {
        $estado = $request->query('estado');
        $q = $request->query('q');

        $query = ProyectoPaisajismo::query()->with('solicitante');

        if (is_string($estado) && in_array($estado, ['solicitado','propuesto','aprobado','en_instalacion','entregado'], true)) {
            $query->where('estado',$estado);
        }

        if (is_string($q) && trim($q) !== '') {
            $busqueda = trim($q);
            $query->where(function ($sub) use ($busqueda) {
                $sub->where('codigo','like','%'.$busqueda.'%')
                    ->orWhere('nombre','like','%'.$busqueda.'%')
                    ->orWhere('ubicacion','like','%'.$busqueda.'%');
            });
        }

        $proyectos = $query->orderByDesc('created_at')->orderByDesc('id')->paginate(15);

        return view('paisajismo.proyectos.index', compact('proyectos','estado','q'));
    }

    public function show(ProyectoPaisajismo $proyecto): View
    {
        return view('paisajismo.proyectos.show',['proyecto'=>$proyecto->load('solicitante')]);
    }
}
