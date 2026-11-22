<?php

namespace Database\Factories;

use App\Models\CampanaComercial;
use App\Models\Planta;
use App\Models\ProductoCampana;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductoCampana>
 */
class ProductoCampanaFactory extends Factory
{
    protected $model = ProductoCampana::class;

    public function definition(): array
    {
        return [
            'campana_id' => CampanaComercial::factory(),
            'planta_id' => Planta::factory(),
            'cantidad_meta' => fake()->numberBetween(1, 50),
            'descuento_porcentaje' => null,
            'incluida' => true,
        ];
    }
}
