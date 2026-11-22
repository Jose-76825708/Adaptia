<?php

namespace App\Services;

use App\Models\RecursoCatalogo;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Catálogo de insumos (recursos de tipo "material"). Son los mismos que usa mantenimiento
 * y los que se eligen en la gestión de inventario.
 */
class InsumoService
{
    public const TIPO = 'material';

    public function getAll(): Collection
    {
        return RecursoCatalogo::query()->where('tipo', self::TIPO)->orderBy('nombre')->get();
    }

    /** Insumos cuyo stock actual está por debajo del mínimo. */
    public function getConStockBajo(): Collection
    {
        return RecursoCatalogo::query()
            ->where('tipo', self::TIPO)
            ->whereNotNull('stock_minimo')
            ->whereColumn('stock_actual', '<', 'stock_minimo')
            ->orderBy('nombre')
            ->get();
    }

    public function obtener(string $id): RecursoCatalogo
    {
        return RecursoCatalogo::query()->where('tipo', self::TIPO)->findOrFail($id);
    }

    public function crear(array $datos): RecursoCatalogo
    {
        return DB::transaction(fn () => RecursoCatalogo::create([
            'tipo' => self::TIPO,
            'codigo' => $this->generarCodigo(),
            'nombre' => trim($datos['nombre']),
            'unidad' => trim($datos['unidad']),
            'stock_actual' => $datos['stock_actual'],
            'stock_minimo' => $datos['stock_minimo'],
        ]));
    }

    public function actualizar(string $id, array $datos): RecursoCatalogo
    {
        $insumo = $this->obtener($id);
        $insumo->update([
            'nombre' => trim($datos['nombre']),
            'unidad' => trim($datos['unidad']),
            'stock_actual' => $datos['stock_actual'],
            'stock_minimo' => $datos['stock_minimo'],
        ]);

        return $insumo->fresh();
    }

    /**
     * @throws InvalidArgumentException si el insumo ya tiene historial que depende de él.
     */
    public function eliminar(string $id): void
    {
        $insumo = $this->obtener($id);

        if ($this->tieneUso($insumo)) {
            throw new InvalidArgumentException(
                "No se puede eliminar «{$insumo->nombre}» porque ya tiene requisiciones, movimientos o gestiones de inventario asociadas."
            );
        }

        $insumo->delete();
    }

    public function tieneUso(RecursoCatalogo $insumo): bool
    {
        foreach (['requisicion_items', 'movimientos_material', 'gestiones_inventario', 'movimientos_gestion_inventario'] as $tabla) {
            if (DB::table($tabla)->where('recurso_id', $insumo->id)->exists()) {
                return true;
            }
        }

        return false;
    }

    /** Siguiente código libre con el formato M-001, M-002… */
    public function generarCodigo(): string
    {
        $maximo = 0;
        foreach (RecursoCatalogo::query()->pluck('codigo') as $codigo) {
            if (preg_match('/^M-(\d+)$/', (string) $codigo, $coincidencia)) {
                $maximo = max($maximo, (int) $coincidencia[1]);
            }
        }

        return sprintf('M-%03d', $maximo + 1);
    }
}
