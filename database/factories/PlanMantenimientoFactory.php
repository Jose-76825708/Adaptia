<?php

namespace Database\Factories;

use App\Models\CasoMantenimiento;
use App\Models\PlanMantenimiento;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PlanMantenimiento> */
class PlanMantenimientoFactory extends Factory
{
    protected $model = PlanMantenimiento::class;

    public function definition(): array
    {
        return [
            'caso_id' => CasoMantenimiento::factory(),
            'autor_id' => User::factory(),
            'version' => 1,
            'generado_en' => now(),
            'descripcion' => 'Plan de mantenimiento generado.',
        ];
    }
}
