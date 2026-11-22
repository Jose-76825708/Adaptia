<?php

namespace Database\Factories;

use App\Models\Planta;
use App\Models\PropuestaDiseno;
use App\Models\PropuestaPlanta;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PropuestaPlanta> */
class PropuestaPlantaFactory extends Factory
{
    protected $model=PropuestaPlanta::class;

    public function definition(): array
    {
        return ['propuesta_id'=>PropuestaDiseno::factory(),'planta_id'=>Planta::factory(),'cantidad'=>2,'precio_unitario'=>20];
    }
}
