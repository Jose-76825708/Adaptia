<?php

namespace App\Http\Controllers;

use App\Models\Recepcion;
use App\Services\RecepcionAprobacionService;
use App\Services\RecepcionService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RecepcionController extends Controller
{
    public function __construct(private RecepcionService $service)
    {
    }

    public function index(): View
    {
        return view('recepciones.index', [
            'recepciones' => $this->service->listar(),
        ]);
    }

    public function show(Recepcion $recepcion): View
    {
        return view('recepciones.show', [
            'recepcion' => $recepcion->load(['detalles.planta', 'incidencias', 'auditorias.usuario']),
        ]);
    }

    public function create(): View
    {
        return view('recepciones.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'usuario_almacen_id' => ['required', 'exists:users,id'],
            'tipo' => ['required', 'in:compra,entrega,devolucion'],
            'estado' => ['nullable', 'in:pendiente,conforme,no_conforme'],
            'fecha_recepcion' => ['required', 'date_format:Y-m-d\TH:i'],
            'observaciones' => ['nullable', 'string'],
            'detalles' => ['required', 'array'],
            'detalles.*.planta_id' => ['required', 'exists:plantas,id'],
            'detalles.*.cantidad_esperada' => ['required', 'integer', 'min:1'],
            'detalles.*.cantidad_recibida' => ['required', 'integer', 'min:0'],
            'detalles.*.unidad' => ['required', 'string'],
        ]);

        $this->service->crear($data);

        return redirect()->route('recepciones.index')->with('success', 'Recepción registrada correctamente.');
    }

    public function aprobar(Recepcion $recepcion, RecepcionAprobacionService $aprobacionService)
    {
        $aprobacionService->aprobar($recepcion);

        return redirect()->route('recepciones.index')->with('success', 'Recepción aprobada y stock actualizado.');
    }

    public function rechazar(Recepcion $recepcion, RecepcionAprobacionService $aprobacionService, Request $request)
    {
        $observaciones = $request->validate([
            'observaciones' => ['nullable', 'string', 'max:500'],
        ]);

        $aprobacionService->rechazar($recepcion, $observaciones['observaciones'] ?? null);

        return redirect()->route('recepciones.index')->with('success', 'Recepción marcada como no conforme.');
    }
}
