<?php

use App\Models\Planta;
use App\Models\PlantaVendida;
use App\Models\Sensor;
use App\Models\User;
use App\Models\Venta;
use App\Services\AsignacionSensorService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

function crearUnidadPendiente(User $cliente): PlantaVendida
{
    $vendedor = User::factory()->create(['rol' => 'vendedor']);
    $planta = Planta::factory()->create();
    $venta = Venta::create([
        'user_id' => $cliente->id,
        'vendedor_id' => $vendedor->id,
        'planta_id' => $planta->id,
        'cantidad' => 1,
    ]);

    return PlantaVendida::create([
        'venta_id' => $venta->id,
        'user_id' => $cliente->id,
        'sensor_id' => null,
    ]);
}

function crearSensor(string $identificador, string $estado = 'activo'): Sensor
{
    return Sensor::create([
        'identificador_fisico' => $identificador,
        'estado' => $estado,
    ]);
}

test('lista unidades pendientes con cliente y planta, y sensores activos libres', function () {
    $cliente = User::factory()->create(['rol' => 'cliente']);
    $unidadPendiente = crearUnidadPendiente($cliente);
    $unidadConSensor = crearUnidadPendiente($cliente);
    $sensorOcupado = crearSensor('SENSOR-LIST-001');
    $unidadConSensor->update(['sensor_id' => $sensorOcupado->id]);
    $sensorDisponible = crearSensor('SENSOR-LIST-002');
    crearSensor('SENSOR-LIST-003', 'inactivo');

    $service = app(AsignacionSensorService::class);

    expect($service->getUnidadesPendientes()->modelKeys())->toBe([$unidadPendiente->id])
        ->and($service->getUnidadesPendientes()->first()->relationLoaded('cliente'))->toBeTrue()
        ->and($service->getUnidadesPendientes()->first()->venta->relationLoaded('planta'))->toBeTrue()
        ->and($service->getSensoresDisponibles()->modelKeys())->toBe([$sensorDisponible->id]);
});

test('asigna un sensor activo libre a una unidad pendiente', function () {
    $cliente = User::factory()->create(['rol' => 'cliente']);
    $vendedor = User::factory()->create(['rol' => 'vendedor']);
    $unidad = crearUnidadPendiente($cliente);
    $sensor = crearSensor('SENSOR-ASIGN-001');

    $asignada = app(AsignacionSensorService::class)
        ->asignarSensor($unidad, $sensor->id, $vendedor);

    expect($asignada->sensor_id)->toBe($sensor->id)
        ->and($unidad->fresh()->sensor_id)->toBe($sensor->id)
        ->and($sensor->fresh()->plantaVendida->is($unidad))->toBeTrue();
});

test('rechaza sensores inactivos sin modificar la unidad', function () {
    $cliente = User::factory()->create(['rol' => 'cliente']);
    $vendedor = User::factory()->create(['rol' => 'vendedor']);
    $unidad = crearUnidadPendiente($cliente);
    $sensor = crearSensor('SENSOR-INACTIVO-001', 'inactivo');

    expect(fn () => app(AsignacionSensorService::class)
        ->asignarSensor($unidad, $sensor->id, $vendedor))
        ->toThrow(ValidationException::class);

    expect($unidad->fresh()->sensor_id)->toBeNull();
});

test('rechaza un sensor que ya está asignado a otra unidad', function () {
    $cliente = User::factory()->create(['rol' => 'cliente']);
    $vendedor = User::factory()->create(['rol' => 'vendedor']);
    $unidadAsignada = crearUnidadPendiente($cliente);
    $unidadPendiente = crearUnidadPendiente($cliente);
    $sensor = crearSensor('SENSOR-OCUPADO-001');
    $unidadAsignada->update(['sensor_id' => $sensor->id]);

    expect(fn () => app(AsignacionSensorService::class)
        ->asignarSensor($unidadPendiente, $sensor->id, $vendedor))
        ->toThrow(ValidationException::class);

    expect($unidadPendiente->fresh()->sensor_id)->toBeNull()
        ->and(PlantaVendida::query()->where('sensor_id', $sensor->id)->count())->toBe(1);
});

test('rechaza asignar otro sensor a una unidad que ya tiene sensor', function () {
    $cliente = User::factory()->create(['rol' => 'cliente']);
    $vendedor = User::factory()->create(['rol' => 'vendedor']);
    $unidad = crearUnidadPendiente($cliente);
    $sensorActual = crearSensor('SENSOR-ACTUAL-001');
    $otroSensor = crearSensor('SENSOR-NUEVO-001');
    $unidad->update(['sensor_id' => $sensorActual->id]);

    expect(fn () => app(AsignacionSensorService::class)
        ->asignarSensor($unidad, $otroSensor->id, $vendedor))
        ->toThrow(ValidationException::class);

    expect($unidad->fresh()->sensor_id)->toBe($sensorActual->id)
        ->and($otroSensor->fresh()->plantaVendida)->toBeNull();
});

test('solo un vendedor puede asignar sensores', function () {
    $cliente = User::factory()->create(['rol' => 'cliente']);
    $administrador = User::factory()->create(['rol' => 'administrador']);
    $unidad = crearUnidadPendiente($cliente);
    $sensor = crearSensor('SENSOR-ROL-001');

    expect(fn () => app(AsignacionSensorService::class)
        ->asignarSensor($unidad, $sensor->id, $administrador))
        ->toThrow(AuthorizationException::class);

    expect($unidad->fresh()->sensor_id)->toBeNull();
});

test('revierte la asignación si falla la actualización de la unidad', function () {
    $cliente = User::factory()->create(['rol' => 'cliente']);
    $vendedor = User::factory()->create(['rol' => 'vendedor']);
    $unidad = crearUnidadPendiente($cliente);
    $sensor = crearSensor('SENSOR-ROLLBACK-001');

    DB::listen(function (QueryExecuted $query) {
        if (str_contains(strtolower($query->sql), 'update "plantas_vendidas"')) {
            throw new RuntimeException('Fallo simulado al guardar la asignación.');
        }
    });

    expect(fn () => app(AsignacionSensorService::class)
        ->asignarSensor($unidad, $sensor->id, $vendedor))
        ->toThrow(RuntimeException::class, 'Fallo simulado al guardar la asignación.');

    expect($unidad->fresh()->sensor_id)->toBeNull()
        ->and($sensor->fresh()->plantaVendida)->toBeNull();
});
