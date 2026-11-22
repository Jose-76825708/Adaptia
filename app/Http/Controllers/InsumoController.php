<?php

namespace App\Http\Controllers;

use App\Services\InsumoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class InsumoController extends Controller
{
    public function __construct(private readonly InsumoService $insumoService)
    {
    }

    public function index()
    {
        return view('insumos.index', [
            'posts' => $this->insumoService->getAll(),
            'insumosStockBajo' => $this->insumoService->getConStockBajo(),
        ]);
    }

    public function create()
    {
        return view('insumos.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->insumoService->crear($request->validate($this->reglas()));

        return redirect()->route('insumos.index')->with('success', 'El insumo se creó correctamente.');
    }

    public function edit(string $id)
    {
        return view('insumos.edit', ['find' => $this->insumoService->obtener($id)]);
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $this->insumoService->actualizar($id, $request->validate($this->reglas((int) $id)));

        return redirect()->route('insumos.index')->with('success', 'El insumo se actualizó correctamente.');
    }

    public function destroy(string $id): RedirectResponse
    {
        try {
            $this->insumoService->eliminar($id);
        } catch (InvalidArgumentException $excepcion) {
            return redirect()->route('insumos.index')->withErrors(['insumo' => $excepcion->getMessage()]);
        }

        return redirect()->route('insumos.index')->with('success', 'El insumo se eliminó correctamente.');
    }

    private function reglas(?int $ignorarId = null): array
    {
        return [
            'nombre' => [
                'required', 'string', 'max:150',
                Rule::unique('recursos_catalogo', 'nombre')->where('tipo', InsumoService::TIPO)->ignore($ignorarId),
            ],
            'unidad' => ['required', 'string', 'max:30'],
            'stock_actual' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'stock_minimo' => ['required', 'numeric', 'min:0', 'max:99999999'],
        ];
    }
}
