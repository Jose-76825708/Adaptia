<?php

namespace App\Services;

use App\Models\MovimientoInventario;
use App\Models\Planta;
use App\Models\PlantaVendida;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VentaService
{
    public function __construct(private AlertaStockService $alertaStockService)
    {
    }

    public function getAll(): Collection
    {
        return Venta::with(['cliente', 'vendedor', 'planta', 'plantasVendidas.sensor'])
            ->orderByDesc('fecha')
            ->get();
    }

    /**
     * @return array{clientes: Collection, plantas: Collection}
     */
    public function getDatosFormulario(): array
    {
        return [
            'clientes' => User::query()
                ->where('rol', 'cliente')
                ->orderBy('name')
                ->get(),
            'plantas' => Planta::query()
                ->where('stock_actual', '>', 0)
                ->orderBy('nombre')
                ->get(),
        ];
    }

    public function registrarVenta(array $datos, User $vendedor): Venta
    {
        if ($vendedor->rol !== 'vendedor') {
            throw new AuthorizationException('Solo un vendedor puede registrar ventas.');
        }

        [$venta, $planta] = DB::transaction(function () use ($datos, $vendedor) {
            $cliente = User::query()
                ->whereKey($datos['user_id'])
                ->where('rol', 'cliente')
                ->first();

            if (!$cliente) {
                throw ValidationException::withMessages([
                    'user_id' => 'Selecciona una cuenta de cliente válida.',
                ]);
            }

            $planta = Planta::query()
                ->lockForUpdate()
                ->findOrFail($datos['planta_id']);

            if ($planta->stock_actual < $datos['cantidad']) {
                throw ValidationException::withMessages([
                    'cantidad' => "Stock insuficiente para {$planta->nombre}. Disponible: {$planta->stock_actual}.",
                ]);
            }

            $stockAntes = $planta->stock_actual;

            $venta = Venta::create([
                'user_id' => $cliente->id,
                'vendedor_id' => $vendedor->id,
                'planta_id' => $planta->id,
                'cantidad' => $datos['cantidad'],
            ]);

            $planta->stock_actual -= $datos['cantidad'];
            $planta->save();

            for ($i = 0; $i < $venta->cantidad; $i++) {
                PlantaVendida::create([
                    'venta_id' => $venta->id,
                    'user_id' => $cliente->id,
                    'sensor_id' => null,
                ]);
            }

            MovimientoInventario::create([
                'planta_id' => $planta->id,
                'user_id' => $vendedor->id,
                'tipo' => 'salida',
                'cantidad' => $datos['cantidad'],
                'stock_antes' => $stockAntes,
                'stock_despues' => $planta->stock_actual,
            ]);

            $this->alertaStockService->evaluar($planta, 'Stock bajo detectado tras registrar venta', [
                'planta_id' => $planta->id,
                'planta_nombre' => $planta->nombre,
                'stock_actual' => $planta->stock_actual,
                'stock_minimo' => $planta->stock_minimo,
                'tipo_movimiento' => 'salida',
                'cantidad' => $venta->cantidad,
                'venta_id' => $venta->id,
                'vendedor_id' => $vendedor->id,
            ]);

            return [$venta, $planta];
        });

        return $venta;
    }
}

