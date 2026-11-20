<?php

namespace Database\Factories;

use App\Models\RecursoCatalogo;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<RecursoCatalogo> */
class RecursoCatalogoFactory extends Factory
{
    protected $model = RecursoCatalogo::class;

    public function definition(): array
    {
        return [
            'tipo' => 'material',
            'codigo' => 'M-' . fake()->unique()->numberBetween(100, 999),
            'nombre' => fake()->word(),
            'unidad' => 'unidad',
            'stock_actual' => 10,
            'stock_minimo' => 2,
        ];
    }
}
