<?php

use App\Models\Planta;
use App\Models\PlantaVendida;
use App\Models\Sensor;
use App\Models\User;
use App\Models\Venta;
use App\Services\SensorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

test('no permite eliminar un sensor asignado y conserva la relación', function () {
    $cliente = User::factory()->create(['rol' => 'cliente']);
    $vendedor = User::factory()->create(['rol' => 'vendedor']);
    $planta = Planta::factory()->create();
    $venta = Venta::create([
        'user_id' => $cliente->id,
        'vendedor_id' => $vendedor->id,
        'planta_id' => $planta->id,
        'cantidad' => 1,
    ]);
    $sensor = Sensor::create([
        'identificador_fisico' => 'SENSOR-PROTEGIDO-001',
        'estado' => 'activo',
    ]);
    $unidad = PlantaVendida::create([
        'venta_id' => $venta->id,
        'user_id' => $cliente->id,
        'sensor_id' => $sensor->id,
    ]);

    expect(fn () => app(SensorService::class)->deleteSensor((string) $sensor->id))
        ->toThrow(ValidationException::class);

    expect(Sensor::find($sensor->id))->not->toBeNull()
        ->and($unidad->fresh()->sensor_id)->toBe($sensor->id);
});

test('permite eliminar un sensor que no está asignado', function () {
    $sensor = Sensor::create([
        'identificador_fisico' => 'SENSOR-LIBRE-001',
        'estado' => 'activo',
    ]);

    expect(app(SensorService::class)->deleteSensor((string) $sensor->id))->toBeTrue()
        ->and(Sensor::find($sensor->id))->toBeNull();
});
