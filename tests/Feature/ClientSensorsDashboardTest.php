<?php

use App\Models\Planta;
use App\Models\PlantaVendida;
use App\Models\Sensor;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('cliente ve sus sensores asignados aunque todavía no tengan lecturas', function () {
    $cliente = User::factory()->create(['rol' => 'cliente']);
    $otroCliente = User::factory()->create(['rol' => 'cliente']);
    $vendedor = User::factory()->create(['rol' => 'vendedor']);
    $planta = Planta::factory()->create(['nombre' => 'Monstera']);
    $otraPlanta = Planta::factory()->create(['nombre' => 'Helecho']);
    $venta = Venta::create([
        'user_id' => $cliente->id,
        'vendedor_id' => $vendedor->id,
        'planta_id' => $planta->id,
        'cantidad' => 1,
    ]);
    $otraVenta = Venta::create([
        'user_id' => $otroCliente->id,
        'vendedor_id' => $vendedor->id,
        'planta_id' => $otraPlanta->id,
        'cantidad' => 1,
    ]);
    $sensor = Sensor::create([
        'identificador_fisico' => 'ESP32-CLIENTE-001',
        'estado' => 'activo',
    ]);
    $otroSensor = Sensor::create([
        'identificador_fisico' => 'ESP32-OTRO-001',
        'estado' => 'activo',
    ]);
    PlantaVendida::create([
        'venta_id' => $venta->id,
        'user_id' => $cliente->id,
        'sensor_id' => $sensor->id,
    ]);
    PlantaVendida::create([
        'venta_id' => $otraVenta->id,
        'user_id' => $otroCliente->id,
        'sensor_id' => $otroSensor->id,
    ]);

    $this->actingAs($cliente)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('ESP32-CLIENTE-001')
        ->assertSee('Monstera')
        ->assertSee('Lecturas pendientes')
        ->assertSee('Aún no hay lecturas disponibles')
        ->assertDontSee('Aún no tienes sensores asignados')
        ->assertDontSee('ESP32-OTRO-001')
        ->assertDontSee('Helecho')
        ->assertSee('href="#mis-sensores"', false)
        ->assertDontSee(route('sensores.index'));
});

test('cliente sin sensores asignados ve el estado vacío aunque tenga una unidad pendiente', function () {
    $cliente = User::factory()->create(['rol' => 'cliente']);
    $vendedor = User::factory()->create(['rol' => 'vendedor']);
    $planta = Planta::factory()->create();
    $venta = Venta::create([
        'user_id' => $cliente->id,
        'vendedor_id' => $vendedor->id,
        'planta_id' => $planta->id,
        'cantidad' => 1,
    ]);
    PlantaVendida::create([
        'venta_id' => $venta->id,
        'user_id' => $cliente->id,
        'sensor_id' => null,
    ]);

    $this->actingAs($cliente)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('Aún no tienes sensores asignados')
        ->assertDontSee('Lecturas pendientes');
});
