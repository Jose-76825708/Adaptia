<?php

namespace App\Http\Controllers;

use App\Services\DashboardOperativoService;
use App\Services\MonitoreoClienteService;
use App\Services\RecomendacionService;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    protected $recomendacionService;

    public function __construct(
        RecomendacionService $recomendacionService,
        private readonly MonitoreoClienteService $monitoreoClienteService,
        private readonly DashboardOperativoService $dashboardOperativoService,
    ) {
        $this->recomendacionService = $recomendacionService;
    }

    public function index()
    {
        $user = Auth::user();

        if ($user?->rol === 'administrador') {
            return view('dashboard.administrador', [
                'resumen' => $this->dashboardOperativoService->resumenAdministrador(),
            ]);
        }

        if ($user?->rol === 'vendedor') {
            return view('dashboard.vendedor', [
                'resumen' => $this->dashboardOperativoService->resumenVendedor($user),
            ]);
        }

        if ($user?->rol !== 'cliente') {
            return view('home');
        }

        $perfil = $user->perfilCliente;
        $recomendaciones = $perfil ? $this->recomendacionService->generarRecomendaciones($perfil->toArray()) : [];
        $sensoresAsignados = $this->monitoreoClienteService->obtenerSensoresAsignados($user);

        return view('home.client', compact('perfil', 'recomendaciones', 'sensoresAsignados'));
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
