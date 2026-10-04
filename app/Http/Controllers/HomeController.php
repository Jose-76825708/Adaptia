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

        if ($user->rol === 'vendedor') {
            return redirect()->route('sensores.index');
        }

        // Si es cliente, mostrar dashboard personalizado
        if ($user->rol === 'cliente') {
            // Obtener o crear el perfil del cliente
            $perfil = $user->perfilCliente;

            // Generar recomendaciones usando el servicio (si tiene perfil) o array vacío (si no tiene)
            $recomendaciones = $perfil ? $this->recomendacionService->generarRecomendaciones($perfil->toArray()) : [];
            $sensoresAsignados = $user->plantasVendidas()
                ->with(['sensor', 'venta.planta'])
                ->whereNotNull('sensor_id')
                ->get();

            return view('home.client', compact('perfil', 'recomendaciones', 'sensoresAsignados'));
        }

        // Para otros roles mostrar la landing page pública.
        return view('home');
    }

    /**
     * Mostrar la vista de historial y alertas del cliente
     */
    public function historialAlertas()
    {
        return view('home.historial-alertas');
    }
}