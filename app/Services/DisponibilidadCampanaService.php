<?php

namespace App\Services;

use App\Models\CampanaComercial;
use InvalidArgumentException;

class DisponibilidadCampanaService
{
    /**
     * @return array{suficiente: bool, productos: array<int, array<string, mixed>>, faltantes: array<int, array<string, mixed>>}
     */
    public function verificar(CampanaComercial $campana): array
    {
        $productos = $campana->productos()
            ->where('incluida', true)
            ->with('planta')
            ->get()
            ->map(fn ($producto) => [
                'producto_campana_id' => $producto->id,
                'planta_id' => $producto->planta_id,
                'planta_nombre' => $producto->planta->nombre,
                'requerido' => $producto->cantidad_meta,
                'disponible' => $producto->planta->stock_actual,
            ])
            ->all();

        return $this->calcularVeredicto($productos);
    }

    /**
     * @param array<int, array<string, mixed>> $productos
     * @return array{suficiente: bool, productos: array<int, array<string, mixed>>, faltantes: array<int, array<string, mixed>>}
     */
    public function calcularVeredicto(array $productos): array
    {
        $detalles = [];
        $faltantes = [];

        foreach ($productos as $producto) {
            if (!isset($producto['requerido'], $producto['disponible'])
                || !is_numeric($producto['requerido'])
                || !is_numeric($producto['disponible'])
                || (int) $producto['requerido'] < 1
                || (int) $producto['disponible'] < 0) {
                throw new InvalidArgumentException('Cada producto debe tener cantidades requeridas y disponibles válidas.');
            }

            $requerido = (int) $producto['requerido'];
            $disponible = (int) $producto['disponible'];
            $faltante = max(0, $requerido - $disponible);
            $detalle = $producto + [
                'faltante' => $faltante,
                'suficiente' => $faltante === 0,
            ];
            $detalle['requerido'] = $requerido;
            $detalle['disponible'] = $disponible;
            $detalle['faltante'] = $faltante;
            $detalle['suficiente'] = $faltante === 0;
            $detalles[] = $detalle;

            if ($faltante > 0) {
                $faltantes[] = $detalle;
            }
        }

        return [
            'suficiente' => $detalles !== [] && $faltantes === [],
            'productos' => $detalles,
            'faltantes' => $faltantes,
        ];
    }
}
