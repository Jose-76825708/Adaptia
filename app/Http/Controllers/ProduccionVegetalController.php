<?php

namespace App\Http\Controllers;

use App\Models\LoteProduccion;
use App\Services\ProduccionVegetalService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class ProduccionVegetalController extends Controller
{
    public function index(Request $request, ProduccionVegetalService $service)
    {
        $filtros = $request->only(['estado']);

        return view('produccion-vegetal.lotes.index', [
            'lotes' => $service->listar($filtros, 15),
            'filtros' => $filtros,
            'estados' => ProduccionVegetalService::ESTADOS,
        ]);
    }

    public function create(ProduccionVegetalService $service)
    {
        return view('produccion-vegetal.lotes.create', [
            'motivos' => ProduccionVegetalService::MOTIVOS,
            'bajoMinimo' => $service->plantasBajoMinimo(),
        ]);
    }

    public function store(Request $request, ProduccionVegetalService $service)
    {
        $lote = $service->crear($request->user(), $request->all());

        return redirect()
            ->route('produccion-vegetal.lotes.show', $lote)
            ->with('success', 'El lote de producción se creó correctamente.');
    }

    public function show(LoteProduccion $lote, ProduccionVegetalService $service)
    {
        $lote = $service->obtener($lote->id);

        return view('produccion-vegetal.lotes.show', [
            'lote' => $lote,
            'plantas' => $service->plantasParaSeleccion(),
            'metodos' => ProduccionVegetalService::METODOS,
            'tiposCuidado' => ProduccionVegetalService::TIPOS_CUIDADO,
            'ultimaRevision' => $lote->revisiones->sortByDesc('id')->first(),
            'vivas' => ProduccionVegetalService::plantasVivas(
                (int) $lote->cantidad_sembrada,
                (int) $lote->cantidad_perdida
            ),
        ]);
    }

    public function avanzar(Request $request, LoteProduccion $lote, ProduccionVegetalService $service)
    {
        $destino = (string) $request->input('destino');

        return $this->responder(
            fn () => $service->avanzar($lote, $request->user(), $destino, $request->all()),
            'La etapa se registró correctamente.'
        );
    }

    public function registrarCuidado(Request $request, LoteProduccion $lote, ProduccionVegetalService $service)
    {
        return $this->responder(
            fn () => $service->registrarCuidado($lote, $request->user(), $request->all()),
            'El cuidado se registró correctamente.'
        );
    }

    public function registrarRevision(Request $request, LoteProduccion $lote, ProduccionVegetalService $service)
    {
        return $this->responder(
            fn () => $service->registrarRevision($lote, $request->user(), $request->all()),
            'La revisión se registró correctamente.'
        );
    }

    public function clasificar(Request $request, LoteProduccion $lote, ProduccionVegetalService $service)
    {
        return $this->responder(
            fn () => $service->clasificar($lote, $request->user(), $request->all()),
            'El lote se clasificó correctamente.'
        );
    }

    public function cerrar(Request $request, LoteProduccion $lote, ProduccionVegetalService $service)
    {
        return $this->responder(
            fn () => $service->cerrar($lote, $request->user()),
            'El lote se cerró y las plantas producidas pasaron a inventario.'
        );
    }

    private function responder(callable $accion, string $mensaje)
    {
        try {
            $accion();
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['produccion' => $e->getMessage()]);
        }

        return back()->with('success', $mensaje);
    }
}
