<?php

use App\Models\Planta;
use App\Models\PlantaVendida;
use App\Models\Sensor;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('venta se relaciona con su cliente, vendedor y planta', function () {
    $cliente = User::factory()->create(['rol' => 'cliente']);
    $vendedor = User::factory()->create(['rol' => 'vendedor']);
    $planta = Planta::factory()->create();

    $venta = Venta::create([
        'user_id' => $cliente->id,
        'vendedor_id' => $vendedor->id,
        'planta_id' => $planta->id,
        'cantidad' => 2,
    ]);

    expect($venta->cliente->is($cliente))->toBeTrue()
        ->and($venta->vendedor->is($vendedor))->toBeTrue()
        ->and($venta->planta->is($planta))->toBeTrue()
        ->and($venta->cantidad)->toBe(2)
        ->and($cliente->ventasComoCliente->first()->is($venta))->toBeTrue()
        ->and($vendedor->ventasComoVendedor->first()->is($venta))->toBeTrue()
        ->and($planta->ventas->first()->is($venta))->toBeTrue();
});

test('planta vendida se relaciona con venta, cliente y sensor', function () {
    $cliente = User::factory()->create(['rol' => 'cliente']);
    $vendedor = User::factory()->create(['rol' => 'vendedor']);
    $planta = Planta::factory()->create();
    $sensor = Sensor::create([
        'identificador_fisico' => 'SENSOR-REL-001',
        'estado' => 'activo',
    ]);

    $venta = Venta::create([
        'user_id' => $cliente->id,
        'vendedor_id' => $vendedor->id,
        'planta_id' => $planta->id,
        'cantidad' => 1,
    ]);

    $unidad = PlantaVendida::create([
        'venta_id' => $venta->id,
        'user_id' => $cliente->id,
        'sensor_id' => $sensor->id,
    ]);

    expect($unidad->venta->is($venta))->toBeTrue()
        ->and($unidad->cliente->is($cliente))->toBeTrue()
        ->and($unidad->sensor->is($sensor))->toBeTrue()
        ->and($unidad->venta->planta->is($planta))->toBeTrue()
        ->and($venta->plantasVendidas->first()->is($unidad))->toBeTrue()
        ->and($cliente->plantasVendidas->first()->is($unidad))->toBeTrue()
        ->and($sensor->plantaVendida->is($unidad))->toBeTrue();
});
