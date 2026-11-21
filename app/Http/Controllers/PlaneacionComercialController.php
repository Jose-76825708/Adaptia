<?php

namespace App\Http\Controllers;

use App\Models\CampanaComercial;
use App\Models\SolicitudDisponibilidad;
use App\Services\DemandaComercialService;
use App\Services\DisponibilidadCampanaService;
use App\Services\PlaneacionComercialService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class PlaneacionComercialController extends Controller
{
    public function index(Request $request, PlaneacionComercialService $service)
    {
        $filtros = $request->only(['estado', 'q']);
        if (array_key_exists('q', $filtros) && !is_string($filtros['q'])) {
            $filtros['q'] = null;
        }

        return view('planeacion-comercial.campanas.index', [
            'campanas' => $service->listar($filtros, 15),
            'filtros' => $filtros,
            'estados' => PlaneacionComercialService::ESTADOS,
        ]);
    }

    public function create(PlaneacionComercialService $service)
    {
        return view('planeacion-comercial.campanas.create', [
            'plantas' => $service->plantasDisponibles(),
        ]);
    }

    public function store(Request $request, PlaneacionComercialService $service)
    {
        $datos = $request->all();
        $datos['productos'] = $this->filtrarProductos($request->input('productos'));
        $campana = $service->crear($request->user(), $datos);

        return redirect()
            ->route('planeacion-comercial.campanas.show', $campana)
            ->with('success', 'La campaña comercial se creó correctamente.');
    }

    public function show(
        CampanaComercial $campana,
        PlaneacionComercialService $service,
        DisponibilidadCampanaService $disponibilidad
    ) {
        $campana = $service->obtener($campana->id);
        $veredicto = $disponibilidad->verificar($campana);
        $productosActivos = $campana->productos->where('incluida', true)->values();
        $solicitudes = $productosActivos->pluck('solicitudDisponibilidad')->filter()->values();
        $solicitudesResueltas = $solicitudes->isNotEmpty()
            && $solicitudes->every(fn ($solicitud) => $solicitud->estado !== 'pendiente');
        $puedeVolverARevision = $solicitudesResueltas
            && $solicitudes->every(fn ($solicitud) => $solicitud->estado === 'atendida')
            && $veredicto['suficiente'];
        $puedeAjustar = $solicitudesResueltas
            && $solicitudes->contains(fn ($solicitud) => $solicitud->estado === 'no_lograda');

        return view('planeacion-comercial.campanas.show', [
            'campana' => $campana,
            'veredicto' => $veredicto,
            'plantas' => $service->plantasDisponibles(),
            'solicitudes' => $solicitudes,
            'puedeVolverARevision' => $puedeVolverARevision,
            'puedeAjustar' => $puedeAjustar,
            'fechaFinalizada' => $campana->fecha_fin->lt(today()),
        ]);
    }

    public function transicionar(Request $request, CampanaComercial $campana, PlaneacionComercialService $service)
    {
        $datos = $request->validate([
            'estado' => ['required', 'string', Rule::in(PlaneacionComercialService::ESTADOS)],
            'ajustes_siguiente_ciclo' => ['nullable', 'string', 'max:5000'],
        ]);

        try {
            $actualizada = $service->cambiarEstado(
                $campana,
                $datos['estado'],
                $request->user(),
                $request->only('ajustes_siguiente_ciclo')
            );
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages(['estado' => $exception->getMessage()]);
        }

        return redirect()
            ->route('planeacion-comercial.campanas.show', $actualizada)
            ->with('success', 'La campaña avanzó a la etapa seleccionada.');
    }

    public function ajustarOferta(Request $request, CampanaComercial $campana, PlaneacionComercialService $service)
    {
        try {
            $actualizada = $service->ajustarOferta(
                $campana,
                $request->user(),
                $this->filtrarProductos($request->input('productos'))
            );
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages(['productos' => $exception->getMessage()]);
        }

        return redirect()
            ->route('planeacion-comercial.campanas.show', $actualizada)
            ->with('success', 'La oferta se ajustó. Revisa la disponibilidad antes de planificarla.');
    }

    public function gestionarDisponibilidad(
        Request $request,
        SolicitudDisponibilidad $solicitud,
        PlaneacionComercialService $service
    ) {
        $datos = $request->validate([
            'estado' => ['required', Rule::in(['atendida', 'no_lograda'])],
            'notas' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $solicitud = $service->gestionarSolicitud(
                $solicitud,
                $request->user(),
                $datos['estado'],
                $datos['notas'] ?? null
            );
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages(['estado' => $exception->getMessage()]);
        }

        return redirect()
            ->route('planeacion-comercial.campanas.show', $solicitud->productoCampana->campana_id)
            ->with('success', 'Se registró el resultado de disponibilidad.');
    }

    public function demanda(
        Request $request,
        DemandaComercialService $service
    ) {
        $datos = $request->validate([
            'inicio' => ['nullable', 'date_format:Y-m-d'],
            'fin' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:inicio'],
        ]);
        $inicio = Carbon::parse($datos['inicio'] ?? today()->startOfMonth()->toDateString())->startOfDay();
        $fin = Carbon::parse($datos['fin'] ?? today()->toDateString())->endOfDay();
        if ($inicio->gt($fin)) {
            throw ValidationException::withMessages(['fin' => 'La fecha final debe ser igual o posterior a la inicial.']);
        }

        return view('planeacion-comercial.demanda', [
            'demanda' => $service->consultar($inicio, $fin),
            'inicio' => $inicio->toDateString(),
            'fin' => $fin->toDateString(),
        ]);
    }

    private function filtrarProductos(mixed $productos): array
    {
        if (!is_array($productos)) {
            return [];
        }

        return array_values(array_filter(
            $productos,
            fn ($producto) => is_array($producto) && filled($producto['planta_id'] ?? null)
        ));
    }
}
