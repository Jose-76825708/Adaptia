<?php

use App\Models\Alerta;
use App\Models\Planta;
use App\Models\PlantaVendida;
use App\Models\Sensor;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

test('alertas admite cuidado de planta y datos del episodio sin perder compatibilidad', function () {
    $columns = [
        'tipo',
        'variable',
        'valor_medido',
        'limite',
        'rango_minimo',
        'rango_maximo',
        'direccion',
        'mensaje',
        'resuelta_en',
    ];

    expect(Schema::hasColumns('alertas', $columns))->toBeTrue();

    $cliente = User::factory()->create(['rol' => 'cliente']);
    $vendedor = User::factory()->create(['rol' => 'vendedor']);
    $planta = Planta::factory()->create();
    $venta = Venta::create([
        'user_id' => $cliente->id,
        'vendedor_id' => $vendedor->id,
        'planta_id' => $planta->id,
        'cantidad' => 1,
    ]);
    $unidad = PlantaVendida::create([
        'venta_id' => $venta->id,
        'user_id' => $cliente->id,
    ]);

    $alertaExistente = Alerta::create([
        'planta_vendida_id' => $unidad->id,
        'tipo' => 'stock_bajo',
    ]);
    $alertaMonitoreo = Alerta::create([
        'planta_vendida_id' => $unidad->id,
        'tipo' => 'cuidado_planta',
        'variable' => 'temperatura',
        'valor_medido' => 35,
        'limite' => 30,
        'rango_minimo' => 15,
        'rango_maximo' => 30,
        'direccion' => 'alto',
        'mensaje' => 'La temperatura supera el rango para Aloe Vera.',
        'resuelta_en' => now(),
    ]);

    expect($alertaExistente->fresh()->tipo)->toBe('stock_bajo')
        ->and($alertaMonitoreo->fresh()->tipo)->toBe('cuidado_planta')
        ->and($alertaMonitoreo->fresh()->variable)->toBe('temperatura')
        ->and((float) $alertaMonitoreo->fresh()->valor_medido)->toBe(35.0)
        ->and((float) $alertaMonitoreo->fresh()->limite)->toBe(30.0)
        ->and((float) $alertaMonitoreo->fresh()->rango_minimo)->toBe(15.0)
        ->and((float) $alertaMonitoreo->fresh()->rango_maximo)->toBe(30.0)
        ->and($alertaMonitoreo->fresh()->mensaje)->toBe('La temperatura supera el rango para Aloe Vera.')
        ->and($alertaMonitoreo->fresh()->direccion)->toBe('alto')
        ->and($alertaMonitoreo->fresh()->resuelta_en)->not->toBeNull()
        ->and($unidad->fresh()->alertas)->toHaveCount(2);
});
