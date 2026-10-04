<?php

use App\Models\Planta;
use App\Models\Sensor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

test('sensores admite un hash de credencial opcional y único', function () {
    expect(Schema::hasColumn('sensores', 'token_hash'))->toBeTrue();

    $sensor = Sensor::create([
        'identificador_fisico' => 'ESP32-IOT-001',
        'estado' => 'activo',
    ]);
    $sensor->token_hash = hash('sha256', 'clave-secreta-prueba');
    $sensor->save();

    expect($sensor->fresh()->token_hash)->toBe(hash('sha256', 'clave-secreta-prueba'));

    $otroSensor = Sensor::create([
        'identificador_fisico' => 'ESP32-IOT-002',
        'estado' => 'activo',
    ]);
    $otroSensor->token_hash = $sensor->token_hash;

    expect(fn () => $otroSensor->save())->toThrow(QueryException::class);
});

test('plantas admite rangos opcionales para las cuatro variables IoT', function () {
    $columns = [
        'humedad_suelo_min',
        'humedad_suelo_max',
        'temperatura_min',
        'temperatura_max',
        'humedad_ambiental_min',
        'humedad_ambiental_max',
        'luz_min',
        'luz_max',
    ];

    expect(Schema::hasColumns('plantas', $columns))->toBeTrue();

    $planta = Planta::factory()->create([
        'humedad_suelo_min' => 20,
        'humedad_suelo_max' => 60,
        'temperatura_min' => 15,
        'temperatura_max' => 30,
        'humedad_ambiental_min' => 35,
        'humedad_ambiental_max' => 75,
        'luz_min' => 200,
        'luz_max' => 1200,
    ]);

    expect((float) $planta->fresh()->humedad_suelo_min)->toBe(20.0)
        ->and((float) $planta->fresh()->temperatura_max)->toBe(30.0)
        ->and((float) $planta->fresh()->humedad_ambiental_min)->toBe(35.0)
        ->and((float) $planta->fresh()->luz_max)->toBe(1200.0);
});
