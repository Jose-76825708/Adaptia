<?php

namespace Database\Factories;

use App\Models\GestionInventario;
use App\Models\Planta;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GestionInventario>
 */
class GestionInventarioFactory extends Factory
{
    protected $model = GestionInventario::class;

    public function definition(): array
    {
        return [
            'codigo' => 'INV-2026-' . fake()->unique()->numerify('#####'),
            'tipo_item' => 'planta',
            'planta_id' => Planta::factory()->state(['stock_actual' => 10, 'stock_minimo' => 2]),
            'recurso_id' => null,
            'cantidad_requerida' => 5,
            'motivo' => fake()->sentence(),
            'stock_sistema' => 10,
            'estado' => 'existencias_revisadas',
            'creador_id' => User::factory()->state(['rol' => 'vendedor']),
        ];
    }
}
