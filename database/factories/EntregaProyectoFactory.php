<?php

namespace Database\Factories;

use App\Models\EntregaProyecto;
use App\Models\ProyectoPaisajismo;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<EntregaProyecto> */
class EntregaProyectoFactory extends Factory
{
    protected $model=EntregaProyecto::class;

    public function definition(): array
    {
        return ['proyecto_id'=>ProyectoPaisajismo::factory(),'autor_id'=>User::factory(),'resumen'=>'Proyecto paisajístico instalado y entregado.','recomendaciones'=>'Mantener riego y revisar periódicamente las plantas.','calificacion'=>null,'comentario'=>null,'calificado_en'=>null];
    }
}
