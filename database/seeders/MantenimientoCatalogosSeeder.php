<?php

namespace Database\Seeders;

use App\Models\RecursoCatalogo;
use Illuminate\Database\Seeder;

class MantenimientoCatalogosSeeder extends Seeder
{
    public function run(): void
    {
        $catalogo = [
            ['tipo' => 'herramienta', 'codigo' => 'H-001', 'nombre' => 'Manguera de riego', 'unidad' => 'pieza', 'stock_actual' => 2, 'stock_minimo' => null],
            ['tipo' => 'herramienta', 'codigo' => 'H-002', 'nombre' => 'Tijeras de poda', 'unidad' => 'pieza', 'stock_actual' => 2, 'stock_minimo' => null],
            ['tipo' => 'herramienta', 'codigo' => 'H-003', 'nombre' => 'Kit de limpieza', 'unidad' => 'kit', 'stock_actual' => 1, 'stock_minimo' => null],
            ['tipo' => 'material', 'codigo' => 'M-001', 'nombre' => 'Abono orgánico', 'unidad' => 'kg', 'stock_actual' => 10, 'stock_minimo' => 5],
            ['tipo' => 'material', 'codigo' => 'M-002', 'nombre' => 'Fertilizante foliar', 'unidad' => 'litro', 'stock_actual' => 5, 'stock_minimo' => 2],
            ['tipo' => 'material', 'codigo' => 'M-003', 'nombre' => 'Insecticida', 'unidad' => 'litro', 'stock_actual' => 3, 'stock_minimo' => 1],
            ['tipo' => 'material', 'codigo' => 'M-004', 'nombre' => 'Tierra', 'unidad' => 'saco', 'stock_actual' => 8, 'stock_minimo' => 3],
            ['tipo' => 'material', 'codigo' => 'M-005', 'nombre' => 'Maceta', 'unidad' => 'unidad', 'stock_actual' => 6, 'stock_minimo' => 2],
        ];

        foreach ($catalogo as $recurso) {
            $existente = RecursoCatalogo::query()->where('codigo', $recurso['codigo'])->first();

            if ($existente) {
                $existente->update([
                    'tipo' => $recurso['tipo'],
                    'nombre' => $recurso['nombre'],
                    'unidad' => $recurso['unidad'],
                    'stock_minimo' => $recurso['stock_minimo'],
                ]);
                continue;
            }

            RecursoCatalogo::create([
                'tipo' => $recurso['tipo'],
                'codigo' => $recurso['codigo'],
                'nombre' => $recurso['nombre'],
                'unidad' => $recurso['unidad'],
                'stock_actual' => $recurso['stock_actual'],
                'stock_minimo' => $recurso['stock_minimo'],
            ]);
        }
    }
}
