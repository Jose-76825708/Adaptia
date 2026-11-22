<?php

namespace App\Services;

use App\Exceptions\StockInsuficienteException;
use App\Models\GestionInventario;
use App\Models\MovimientoGestionInventario;
use App\Models\Planta;
use App\Models\RecursoCatalogo;
use Illuminate\Support\Facades\DB;

/**
 * Registra el movimiento de inventario de una gestión y actualiza las existencias.
 * Plantas: reutiliza MovimientoInventarioService (así aparece también en el módulo de inventario).
 * Insumos: actualiza el stock del catálogo de recursos y deja el rastro en movimientos_gestion_inventario.
 */
class MovimientoGestionInventarioService
{
    public function __construct(private readonly MovimientoInventarioService $movimientoPlantas)
    {
    }

    public function registrar(
        GestionInventario $gestion,
        string $tipo,
        float $cantidad,
        string $motivo,
        int $autorId
    ): MovimientoGestionInventario {
        if (!in_array($tipo, ['entrada', 'salida'], true)) {
            throw new \InvalidArgumentException('El tipo de movimiento debe ser entrada o salida.');
        }
        if ($cantidad <= 0) {
            throw new \InvalidArgumentException('La cantidad del movimiento debe ser mayor que cero.');
        }

        return DB::transaction(function () use ($gestion, $tipo, $cantidad, $motivo, $autorId) {
            return $gestion->tipo_item === 'planta'
                ? $this->registrarPlanta($gestion, $tipo, $cantidad, $motivo, $autorId)
                : $this->registrarInsumo($gestion, $tipo, $cantidad, $motivo, $autorId);
        });
    }

    private function registrarPlanta(GestionInventario $gestion, string $tipo, float $cantidad, string $motivo, int $autorId): MovimientoGestionInventario
    {
        $planta = Planta::query()->lockForUpdate()->findOrFail($gestion->planta_id);
        $stockAntes = (float) $planta->stock_actual;

        $movimiento = $this->movimientoPlantas->createMovimiento([
            'planta_id' => $planta->id,
            'user_id' => $autorId,
            'tipo' => $tipo,
            'cantidad' => (int) round($cantidad),
        ]);

        return MovimientoGestionInventario::create([
            'gestion_id' => $gestion->id,
            'tipo' => $tipo,
            'planta_id' => $planta->id,
            'movimiento_inventario_id' => $movimiento->id,
            'cantidad' => $cantidad,
            'stock_antes' => $stockAntes,
            'stock_despues' => (float) $planta->fresh()->stock_actual,
            'motivo' => $motivo,
            'autor_id' => $autorId,
        ]);
    }

    private function registrarInsumo(GestionInventario $gestion, string $tipo, float $cantidad, string $motivo, int $autorId): MovimientoGestionInventario
    {
        $recurso = RecursoCatalogo::query()->lockForUpdate()->findOrFail($gestion->recurso_id);
        $stockAntes = (float) $recurso->stock_actual;

        if ($tipo === 'salida' && $stockAntes < $cantidad) {
            throw new StockInsuficienteException(
                "Stock insuficiente para {$recurso->nombre}. Disponible: {$stockAntes}, requerido: {$cantidad}"
            );
        }

        $stockDespues = round($tipo === 'entrada' ? $stockAntes + $cantidad : $stockAntes - $cantidad, 2);
        $recurso->stock_actual = $stockDespues;
        $recurso->save();

        return MovimientoGestionInventario::create([
            'gestion_id' => $gestion->id,
            'tipo' => $tipo,
            'recurso_id' => $recurso->id,
            'cantidad' => $cantidad,
            'stock_antes' => $stockAntes,
            'stock_despues' => $stockDespues,
            'motivo' => $motivo,
            'autor_id' => $autorId,
        ]);
    }
}
