<?php

namespace Database\Factories;

use App\Models\AvanceInstalacion;
use App\Models\ProyectoPaisajismo;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AvanceInstalacion> */
class AvanceInstalacionFactory extends Factory
{
    protected $model=AvanceInstalacion::class;

    public function definition(): array
    {
        return ['proyecto_id'=>ProyectoPaisajismo::factory(),'autor_id'=>User::factory(),'descripcion'=>'Avance de instalación registrado.','porcentaje'=>50];
    }
}
