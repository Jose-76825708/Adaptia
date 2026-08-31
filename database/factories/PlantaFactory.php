<?php

namespace Database\Factories;

use App\Models\Planta;
use App\Models\TipoPlanta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Planta>
 */
class PlantaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tipo_planta_id' => TipoPlanta::factory(),
            'nombre' => fake()->words(2,true),
            'descripcion' => fake()->paragraph(),
            'imagen' => fake()->word() .'.jpg',
            'luz_requerida' => fake()->randomElement(['baja','media','alta','siempre_en_el_sol']),
            'frecuencia_riego' => fake()->randomElement(['diario','cada_3_dias','semanal','quincenal','mensualmente']),
            'tamaño_adulto' => fake()->randomElement(['pequena','mediana','grande']),
            'nivel_cuidado' => fake()->randomElement(['principiante','intermedio','experto']),
            'tipo_ambiente' => fake()->randomElement(['interiores','exteriores','ambos']),
            'toxicidad' => fake()->boolean(20),
            'estetica' => fake()->randomElement(['follaje','flor','colgantes','suculentas']),
            'precio' => fake()->randomFloat(2, 5, 50)
        ];
    }
}
