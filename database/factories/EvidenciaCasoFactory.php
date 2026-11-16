<?php

namespace Database\Factories;

use App\Models\CasoMantenimiento;
use App\Models\EvidenciaCaso;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EvidenciaCaso>
 */
class EvidenciaCasoFactory extends Factory
{
    protected $model = EvidenciaCaso::class;

    public function definition(): array
    {
        return [
            'caso_id' => CasoMantenimiento::factory(),
            'autor_id' => User::factory(),
            'tipo' => 'nota',
            'contenido' => 'Se registró una nota de prueba.',
            'ruta' => null,
            'descripcion' => null,
        ];
    }
}
