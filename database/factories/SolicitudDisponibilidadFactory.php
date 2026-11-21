<?php

namespace Database\Factories;

use App\Models\ProductoCampana;
use App\Models\SolicitudDisponibilidad;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SolicitudDisponibilidad>
 */
class SolicitudDisponibilidadFactory extends Factory
{
    protected $model = SolicitudDisponibilidad::class;

    public function definition(): array
    {
        return [
            'producto_campana_id' => ProductoCampana::factory(),
            'faltante' => fake()->numberBetween(1, 20),
            'estado' => 'pendiente',
            'notas' => null,
        ];
    }
}
