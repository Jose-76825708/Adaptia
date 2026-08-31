<?php

namespace Database\Factories;

use App\Models\TipoPlanta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TipoPlanta>
 */
class TipoPlantaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => 'Ornamentales'
        ];
    }
}
