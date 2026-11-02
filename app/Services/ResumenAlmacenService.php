<?php

namespace App\Services;

use App\Models\Alerta;
use App\Models\MovimientoInventario;
use App\Models\Planta;
use Illuminate\Database\Eloquent\Collection;

class ResumenAlmacenService
{
    public const MOVIMIENTOS_RECIENTES = 5;

    /**
     * Datos del panel resumen del almacén (solo lectura).
     *
     * @return array{
     *     stock_total: int,
     *     plantas_bajo_minimo: int,
     *     alertas_abiertas: int,
     *     alertas_no_leidas: int,
     *     movimientos_recientes: Collection
     * }
     */
    public function obtenerResumen(): array
    {
        return [
            'stock_total' => $this->stockTotal(),
            'plantas_bajo_minimo' => $this->plantasBajoMinimo(),
            'alertas_abiertas' => $this->alertasAbiertas()->count(),
            'alertas_no_leidas' => $this->alertasAbiertas()->where('leida', false)->count(),
            'movimientos_recientes' => $this->movimientosRecientes(),
        ];
    }

    private function stockTotal(): int
    {
        return (int) Planta::query()->sum('stock_actual');
    }

    /**
     * Misma regla que AlertaStockService::esStockBajo:
     * el mínimo debe ser mayor que 0 y el stock estar por debajo de él.
     */
    private function plantasBajoMinimo(): int
    {
        return Planta::query()
            ->where('stock_minimo', '>', 0)
            ->whereColumn('stock_actual', '<', 'stock_minimo')
            ->count();
    }

    private function alertasAbiertas()
    {
        return Alerta::query()
            ->whereNotNull('planta_id')
            ->where('tipo', 'stock_bajo')
            ->whereNull('resuelta_en');
    }

    private function movimientosRecientes(): Collection
    {
        return MovimientoInventario::query()
            ->with(['planta', 'user', 'anulacion'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(self::MOVIMIENTOS_RECIENTES)
            ->get();
    }
}
