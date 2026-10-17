<?php

use App\Models\Alerta;
use App\Models\LecturaSensor;
use App\Models\Planta;
use App\Models\PlantaVendida;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('lecturas y alertas resuelven la planta vendida relacionada', function () {
    $cliente = User::factory()->create(['rol' => 'cliente']);
    $vendedor = User::factory()->create(['rol' => 'vendedor']);
    $venta = Venta::create([
        'user_id' => $cliente->id,
        'vendedor_id' => $vendedor->id,
        'planta_id' => Planta::factory()->create()->id,
        'cantidad' => 1,
    ]);
    $unidad = PlantaVendida::create([
        'venta_id' => $venta->id,
        'user_id' => $cliente->id,
    ]);
    $lectura = LecturaSensor::create([
        'planta_vendida_id' => $unidad->id,
        'humedad_suelo' => 40,
        'temperatura' => 23,
        'humedad_ambiental' => 50,
        'luz' => 1500,
        'fecha_hora' => now(),
    ]);
    $alerta = Alerta::create([
        'planta_vendida_id' => $unidad->id,
        'tipo' => 'riego',
        'mensaje' => 'Revisar humedad.',
    ]);

    expect($lectura->plantaVendida->is($unidad))->toBeTrue()
        ->and($alerta->plantaVendida->is($unidad))->toBeTrue();
});
