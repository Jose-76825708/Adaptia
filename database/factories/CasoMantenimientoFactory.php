<?php

namespace Database\Factories;

use App\Models\CasoMantenimiento;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CasoMantenimiento>
 */
class CasoMantenimientoFactory extends Factory
{
    protected $model = CasoMantenimiento::class;

    public function definition(): array
    {
        return [
            'codigo' => 'MAN-2026-00001',
            'solicitante_id' => User::factory(),
            'area_verde' => 'Jardín principal',
            'descripcion' => 'Necesito revisión general del área de jardines.',
            'fecha_deseada' => '2026-11-10',
            'observaciones' => 'Revisión general en la zona de plantas.',
            'tipo_mantenimiento' => 'general',
            'urgencia' => 'media',
            'estado' => 'abierto',
        ];
    }
}
