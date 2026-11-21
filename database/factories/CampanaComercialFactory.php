<?php

namespace Database\Factories;

use App\Models\CampanaComercial;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CampanaComercial>
 */
class CampanaComercialFactory extends Factory
{
    protected $model = CampanaComercial::class;

    public function definition(): array
    {
        return [
            'codigo' => 'CAM-2026-' . fake()->unique()->numerify('#####'),
            'objetivos' => fake()->sentence(),
            'presupuesto' => fake()->randomFloat(2, 0, 10000),
            'fecha_inicio' => today(),
            'fecha_fin' => today()->addDays(30),
            'estado' => 'objetivos_definidos',
            'creador_id' => User::factory()->state(['rol' => 'vendedor']),
        ];
    }
}
