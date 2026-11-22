<?php

namespace App\Http\Controllers;

use App\Models\GestionInventario;
use App\Services\GestionInventarioService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class GestionInventarioController extends Controller
{
    public function index(Request $request, GestionInventarioService $service)
    {
        $filtros = $request->only(['estado', 'tipo_item', 'q']);
        if (array_key_exists('q', $filtros) && !is_string($filtros['q'])) {
            $filtros['q'] = null;
        }

        return view('gestion-inventario.index', [
            'gestiones' => $service->listar($filtros, 15),
            'filtros' => $filtros,
            'estados' => GestionInventarioService::ESTADOS,
        ]);
    }

    public function create(Request $request, GestionInventarioService $service)
    {
        $tipo = $request->query('tipo_item');

        return view('gestion-inventario.create', [
            'plantas' => $service->plantasDisponibles(),
            'insumos' => $service->insumosDisponibles(),
            'tipoInicial' => in_array($tipo, GestionInventarioService::TIPOS_ITEM, true) ? $tipo : 'planta',
            'plantaInicial' => $request->query('planta_id'),
            'insumoInicial' => $request->query('recurso_id'),
        ]);
    }

    public function store(Request $request, GestionInventarioService $service): RedirectResponse
    {
        $gestion = $service->crear($request->user(), $request->all());

        return redirect()
            ->route('gestion-inventario.show', $gestion)
            ->with('success', 'Se registró la revisión de existencias.');
    }

    public function show(GestionInventario $gestion, GestionInventarioService $service)
    {
        $gestion = $service->obtener($gestion->id);

        return view('gestion-inventario.show', [
            'gestion' => $gestion,
            'puedeActuar' => $service->puedeActuar(request()->user(), $gestion),
            'tiposIncidencia' => \App\Services\SuficienciaInventarioService::TIPOS_INCIDENCIA,
        ]);
    }

    public function verificar(Request $request, GestionInventario $gestion, GestionInventarioService $service): RedirectResponse
    {
        return $this->ejecutar($gestion, 'Se verificaron el estado y las cantidades.', fn () => $service->verificarEstado($gestion, $request->user(), $request->all()));
    }

    public function reposicion(Request $request, GestionInventario $gestion, GestionInventarioService $service): RedirectResponse
    {
        return $this->ejecutar($gestion, 'Se gestionó la reposición.', fn () => $service->gestionarReposicion($gestion, $request->user(), $request->all()));
    }

    public function compra(Request $request, GestionInventario $gestion, GestionInventarioService $service): RedirectResponse
    {
        return $this->ejecutar($gestion, 'Se registró la compra o abastecimiento.', fn () => $service->registrarCompra($gestion, $request->user(), $request->all()));
    }

    public function recepcion(Request $request, GestionInventario $gestion, GestionInventarioService $service): RedirectResponse
    {
        return $this->ejecutar($gestion, 'Se registró la recepción.', fn () => $service->registrarRecepcion($gestion, $request->user(), $request->all()));
    }

    public function verificarRecepcion(Request $request, GestionInventario $gestion, GestionInventarioService $service): RedirectResponse
    {
        return $this->ejecutar($gestion, 'Se registró la verificación de la recepción.', fn () => $service->verificarRecepcion($gestion, $request->user(), $request->all()));
    }

    public function gestionarIncidencia(Request $request, GestionInventario $gestion, GestionInventarioService $service): RedirectResponse
    {
        return $this->ejecutar($gestion, 'Se gestionó la diferencia o incidencia. Registra la nueva recepción.', fn () => $service->gestionarIncidencia($gestion, $request->user(), $request->all()));
    }

    public function ingreso(Request $request, GestionInventario $gestion, GestionInventarioService $service): RedirectResponse
    {
        return $this->ejecutar($gestion, 'Se registró el ingreso.', fn () => $service->registrarIngreso($gestion, $request->user(), $request->all()));
    }

    public function almacenar(Request $request, GestionInventario $gestion, GestionInventarioService $service): RedirectResponse
    {
        return $this->ejecutar($gestion, 'Se registró el almacenamiento.', fn () => $service->almacenar($gestion, $request->user(), $request->all()));
    }

    public function movimiento(Request $request, GestionInventario $gestion, GestionInventarioService $service): RedirectResponse
    {
        return $this->ejecutar($gestion, 'Se registró el movimiento y se actualizaron las existencias.', fn () => $service->registrarMovimiento($gestion, $request->user()));
    }

    private function ejecutar(GestionInventario $gestion, string $mensaje, callable $actividad): RedirectResponse
    {
        try {
            $actividad();
        } catch (InvalidArgumentException $excepcion) {
            throw ValidationException::withMessages(['gestion' => $excepcion->getMessage()]);
        }

        return redirect()->route('gestion-inventario.show', $gestion)->with('success', $mensaje);
    }
}
