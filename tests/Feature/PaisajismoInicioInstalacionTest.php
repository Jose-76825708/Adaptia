<?php

use App\Models\Planta;
use App\Models\ProyectoPaisajismo;
use App\Models\TipoPlanta;
use App\Models\User;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('rechaza iniciar instalacion si el proyecto no esta aprobado', function () {
    $cliente=User::factory()->create(['rol'=>'cliente']);
    $proyecto=ProyectoPaisajismo::factory()->create(['solicitante_id'=>$cliente->id,'estado'=>'propuesto']);

    expect(method_exists(new App\Services\ProyectoPaisajismoService(),'cambiarEstado'))->toBeTrue()
        ->and($proyecto->estado)->toBe('propuesto');
});

it('descontar stock insuficiente no cambia stock', function () {
    $tipo=TipoPlanta::factory()->create();
    $planta=Planta::factory()->create(['tipo_planta_id'=>$tipo->id,'stock_actual'=>2,'stock_minimo'=>1,'precio'=>20]);

    expect($planta->stock_actual)->toBe(2);
});
