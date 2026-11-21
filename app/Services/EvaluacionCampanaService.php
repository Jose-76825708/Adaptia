<?php

namespace App\Services;

use App\Models\CampanaComercial;
use App\Models\Venta;

class EvaluacionCampanaService
{
    /**
     * @return array<int, array<string, int|float>>
     */
    public function evaluar(CampanaComercial $campana): array
    {
        $productos = $campana->productos()
            ->where('incluida', true)
            ->with('planta')
            ->get();

        $ventas = Venta::query()
            ->whereIn('planta_id', $productos->pluck('planta_id'))
            ->whereBetween('fecha', [
                $campana->fecha_inicio->copy()->startOfDay(),
                $campana->fecha_fin->copy()->endOfDay(),
            ])
            ->selectRaw('planta_id, SUM(cantidad) as cantidad_vendida')
            ->groupBy('planta_id')
            ->get()
            ->map(fn ($venta) => [
                'planta_id' => (int) $venta->planta_id,
                'cantidad_vendida' => (int) $venta->cantidad_vendida,
            ])
            ->all();

        return $this->calcularResultados(
            $productos->map(fn ($producto) => [
                'producto_campana_id' => $producto->id,
                'planta_id' => $producto->planta_id,
                'cantidad_meta' => $producto->cantidad_meta,
                'precio_actual' => $producto->planta->precio,
            ])->all(),
            $ventas
        );
    }

    /**
     * @param array<int, array<string, mixed>> $productos
     * @param array<int, array<string, mixed>> $ventas
     * @return array<int, array<string, int|float>>
     */
    public function calcularResultados(array $productos, array $ventas): array
    {
        $vendidoPorPlanta = [];
        foreach ($ventas as $venta) {
            if (!isset($venta['planta_id'], $venta['cantidad_vendida'])
                || !is_numeric($venta['planta_id'])
                || !is_numeric($venta['cantidad_vendida'])
                || (int) $venta['cantidad_vendida'] < 0) {
                throw new \InvalidArgumentException('Los datos de ventas deben incluir planta y cantidad válidas.');
            }

            $plantaId = (int) $venta['planta_id'];
            $vendidoPorPlanta[$plantaId] = ($vendidoPorPlanta[$plantaId] ?? 0) + (int) $venta['cantidad_vendida'];
        }

        $resultados = [];
        foreach ($productos as $producto) {
            if (!isset($producto['producto_campana_id'], $producto['planta_id'], $producto['cantidad_meta'], $producto['precio_actual'])
                || !is_numeric($producto['cantidad_meta'])
                || (int) $producto['cantidad_meta'] < 1
                || !is_numeric($producto['precio_actual'])
                || (float) $producto['precio_actual'] < 0) {
                throw new \InvalidArgumentException('Cada producto debe tener meta y precio válidos.');
            }

            $cantidadMeta = (int) $producto['cantidad_meta'];
            $cantidadVendida = $vendidoPorPlanta[(int) $producto['planta_id']] ?? 0;
            $resultados[] = [
                'producto_campana_id' => (int) $producto['producto_campana_id'],
                'cantidad_meta' => $cantidadMeta,
                'cantidad_vendida' => $cantidadVendida,
                'porcentaje_cumplimiento' => round(($cantidadVendida / $cantidadMeta) * 100, 2),
                'ingresos_estimados' => round($cantidadVendida * (float) $producto['precio_actual'], 2),
            ];
        }

        return $resultados;
    }
}
