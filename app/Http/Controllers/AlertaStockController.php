<?php

namespace App\Http\Controllers;

use App\Models\Alerta;
use App\Services\AlertaStockService;
use Illuminate\Http\Request;

class AlertaStockController extends Controller
{
    public function __construct(private AlertaStockService $alertaStockService)
    {
    }

    public function index(Request $request)
    {
        $estado = $request->query('estado', 'abiertas');
        $estadosValidos = ['abiertas', 'no_leidas', 'leidas', 'resueltas', 'todas'];

        if (! in_array($estado, $estadosValidos, true)) {
            $estado = 'abiertas';
        }

        $alertas = $this->alertaStockService->listarPaginadas($estado);

        return view('alertas-stock.index', compact('alertas', 'estado'));
    }

    public function marcarLeida(Request $request, Alerta $alerta)
    {
        $estado = $request->input('estado', $request->query('estado', 'abiertas'));
        $estadosValidos = ['abiertas', 'no_leidas', 'leidas', 'resueltas', 'todas'];

        if (! in_array($estado, $estadosValidos, true)) {
            $estado = 'abiertas';
        }

        if ($alerta->resuelta_en !== null || $alerta->leida) {
            return redirect()->route('alertas-stock.index', ['estado' => $estado])
                ->with('info', 'La alerta ya estaba marcada como leída o ya está resuelta; no se realizaron cambios.');
        }

        $this->alertaStockService->marcarLeida($alerta);

        return redirect()->route('alertas-stock.index', ['estado' => $estado])
            ->with('success', 'La alerta ha sido marcada como leída.');
    }
}
