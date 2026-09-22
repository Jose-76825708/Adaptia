<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\RecomendacionService;

class HomeController extends Controller
{
    protected $recomendacionService;

    public function __construct(RecomendacionService $recomendacionService)
    {
        $this->recomendacionService = $recomendacionService;
    }

    public function index()
    {
        // Si no está autenticado, mostrar landing page
        if (!Auth::check()) {
            return view('home');
        }

        $user = Auth::user();

        // Si es cliente, mostrar dashboard personalizado
        if ($user->rol === 'cliente') {
            // Obtener o crear el perfil del cliente
            $perfil = $user->perfilCliente;

            // Si no tiene perfil, redirigir a crear uno
            if (!$perfil) {
                return redirect()->route('perfil.edit')
                    ->with('info', 'Por favor complete su perfil para recibir recomendaciones personalizadas');
            }

            // Generar recomendaciones usando el servicio
            $recomendaciones = $this->recomendacionService->generarRecomendaciones($perfil->toArray());

            return view('home.client', compact('perfil', 'recomendaciones'));
        }

        // Para otros roles (admin, vendedor, etc.) mostrar landing page por ahora
        // Esto cambiará cuando se implementen sus respectivas fases
        return view('home');
    }
}