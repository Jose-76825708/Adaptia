<?php

namespace Database\Factories;

use App\Models\ProyectoPaisajismo;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ProyectoPaisajismo> */
class ProyectoPaisajismoFactory extends Factory
{
    protected $model=ProyectoPaisajismo::class;

    public function definition(): array
    {
        return [
            'codigo'=>'PAI-2026-'.str_pad((string) fake()->unique()->numberBetween(1,99999),5,'0',STR_PAD_LEFT),'solicitante_id'=>User::factory(),'nombre'=>'Jardín residencial','ubicacion'=>'Huancayo',
            'descripcion'=>'Diseño y adecuación integral de un área verde residencial.',
            'area_m2'=>120.50,'presupuesto_estimado'=>5000.00,'fecha_deseada'=>now()->addMonth()->format('Y-m-d'),'estado'=>'solicitado',
        ];
    }
}
