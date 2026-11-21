<?php

namespace App\Services;

use App\Models\Planta;
use App\Models\Venta;
use Carbon\CarbonInterface;
use InvalidArgumentException;

class DemandaComercialService
{
    /**
     * @return array{ranking_ventas: array, stock_bajo: array, sin_ventas: array}
     */
    public function consultar(CarbonInterface $inicio, CarbonInterface $fin): array
    {
        if ($inicio->gt($fin)) {
            throw new InvalidArgumentException('La fecha inicial debe ser anterior o igual a la fecha final.');
        }

        $plantas = Planta::query()
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'stock_actual', 'stock_minimo'])
            ->map(fn (Planta $planta) => [
                'planta_id' => $planta->id,
                'nombre' => $planta->nombre,
                'stock_actual' => $planta->stock_actual,
                'stock_minimo' => $planta->stock_minimo,
            ])
            ->all();

        $ventas = Venta::query()
            ->whereBetween('fecha', [$inicio, $fin])
            ->selectRaw('planta_id, SUM(cantidad) as cantidad_vendida')
            ->groupBy('planta_id')
            ->get()
            ->map(fn ($venta) => [
                'planta_id' => (int) $venta->planta_id,
                'cantidad_vendida' => (int) $venta->cantidad_vendida,
            ])
            ->all();

        return $this->calcularResumen($plantas, $ventas);
    }

    /**
     * @param array<int, array<string, mixed>> $plantas
     * @param array<int, array<string, mixed>> $ventas
     * @return array{ranking_ventas: array, stock_bajo: array, sin_ventas: array}
     */
    public function calcularResumen(array $plantas, array $ventas): array
    {
        $porPlanta = [];
        foreach ($ventas as $venta) {
            if (!isset($venta['planta_id'], $venta['cantidad_vendida'])
                || !is_numeric($venta['planta_id'])
                || !is_numeric($venta['cantidad_vendida'])
                || (int) $venta['cantidad_vendida'] < 0) {
                throw new InvalidArgumentException('Los datos de ventas deben incluir planta y cantidad válidas.');
            }

            $plantaId = (int) $venta['planta_id'];
            $porPlanta[$plantaId] = ($porPlanta[$plantaId] ?? 0) + (int) $venta['cantidad_vendida'];
        }

        $ranking = [];
        $stockBajo = [];
        $sinVentas = [];

        foreach ($plantas as $planta) {
            if (!isset($planta['planta_id'], $planta['nombre'], $planta['stock_actual'], $planta['stock_minimo'])) {
                throw new InvalidArgumentException('Cada planta debe incluir nombre, stock actual y stock mínimo.');
            }

            $plantaId = (int) $planta['planta_id'];
            $cantidadVendida = $porPlanta[$plantaId] ?? 0;
            $detalle = [
                'planta_id' => $plantaId,
                'nombre' => (string) $planta['nombre'],
                'cantidad_vendida' => $cantidadVendida,
                'stock_actual' => (int) $planta['stock_actual'],
                'stock_minimo' => (int) $planta['stock_minimo'],
            ];

            if ($cantidadVendida > 0) {
                $ranking[] = $detalle;
            } else {
                $sinVentas[] = $detalle;
            }

            if ((int) $planta['stock_actual'] < (int) $planta['stock_minimo']) {
                $stockBajo[] = $detalle;
            }
        }

        usort($ranking, fn (array $a, array $b) => ($b['cantidad_vendida'] <=> $a['cantidad_vendida'])
            ?: strcasecmp($a['nombre'], $b['nombre']));

        return [
            'ranking_ventas' => $ranking,
            'stock_bajo' => $stockBajo,
            'sin_ventas' => $sinVentas,
        ];
    }
}
