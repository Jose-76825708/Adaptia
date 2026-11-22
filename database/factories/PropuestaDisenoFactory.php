<?php

namespace Database\Factories;

use App\Models\PropuestaDiseno;
use App\Models\ProyectoPaisajismo;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PropuestaDiseno> */
class PropuestaDisenoFactory extends Factory
{
    protected $model=PropuestaDiseno::class;

    public function definition(): array
    {
        return ['proyecto_id'=>ProyectoPaisajismo::factory(),'autor_id'=>User::factory(),'version'=>1,'concepto'=>'Propuesta integral de diseño paisajístico.','costo_plantas'=>100,'costo_mano_obra'=>150,'total'=>250,'decision'=>null,'motivo_decision'=>null,'decidida_en'=>null];
    }
}
