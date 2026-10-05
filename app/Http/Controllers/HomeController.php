<?php

namespace App\Http\Controllers;

use App\Services\MonitoreoClienteService;
use App\Services\RecomendacionService;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    protected $recomendacionService;

    public function __construct(
        RecomendacionService $recomendacionService,
        private readonly MonitoreoClienteService $monitoreoClienteService,
    ) {
        $this->recomendacionService = $recomendacionService;
    }

    public function index()
    {
        // Si no está autenticado, mostrar landing page
        if (! Auth::check()) {
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
            $sensoresAsignados = $this->monitoreoClienteService->obtenerSensoresAsignados($user);

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
        $sensoresAsignados = $this->monitoreoClienteService->obtenerSensoresAsignados(
            Auth::user(),
            incluirHistorial: true,
        );

        return view('home.historial-alertas', compact('sensoresAsignados'));
    }

    public function datosMonitoreoInicio()
    {
        $sensoresAsignados = $this->monitoreoClienteService->obtenerSensoresAsignados(Auth::user());

        return response()->view('home.partials.sensores-cliente', compact('sensoresAsignados'));
    }

    public function datosMonitoreoHistorial()
    {
        $sensoresAsignados = $this->monitoreoClienteService->obtenerSensoresAsignados(
            Auth::user(),
            incluirHistorial: true,
        );

        return response()->view('home.partials.historial-monitoreo', compact('sensoresAsignados'));
    }
}
