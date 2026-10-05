<?php

use App\Models\Alerta;
use App\Models\Planta;
use App\Models\PlantaVendida;
use App\Models\Sensor;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

function crearUnidadIoTParaLecturas(string $identificador): array
{
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
        'identificador_fisico' => $identificador,
        'estado' => 'activo',
    ]);
    $unidad = PlantaVendida::create([
        'venta_id' => $venta->id,
        'user_id' => $cliente->id,
        'sensor_id' => $sensor->id,
    ]);

    return [$sensor, $unidad];
}

function payloadLecturaValida(int $plantaVendidaId): array
{
    return [
        'planta_vendida_id' => $plantaVendidaId,
        'humedad_suelo' => 24.5,
        'temperatura' => 22.3,
        'humedad_ambiental' => 55.0,
        'luz' => 320,
        'fecha_hora' => '2026-09-11T17:30:00Z',
    ];
}

test('registra una lectura autenticada y vinculada a la unidad del sensor', function () {
    [$sensor, $unidad] = crearUnidadIoTParaLecturas('ESP32-LECTURA-001');
    $token = 'token-secreto-prueba';
    $sensor->forceFill(['token_hash' => hash('sha256', $token)])->save();

    $this->withHeader('X-Sensor-Token', $token)
        ->postJson('/api/lecturas', payloadLecturaValida($unidad->id))
        ->assertCreated()
        ->assertJsonPath('message', 'Lectura registrada correctamente.')
        ->assertJsonPath('data.planta_vendida_id', $unidad->id)
        ->assertJsonPath('data.humedad_suelo', 24.5)
        ->assertJsonPath('data.temperatura', 22.3)
        ->assertJsonPath('data.humedad_ambiental', 55)
        ->assertJsonPath('data.luz', 320)
        ->assertJsonPath('desviaciones', []);

    $this->assertDatabaseHas('lecturas_sensores', [
        'planta_vendida_id' => $unidad->id,
        'humedad_suelo' => 24.5,
        'temperatura' => 22.3,
        'humedad_ambiental' => 55,
        'luz' => 320,
        'fecha_hora' => '2026-09-11 17:30:00',
    ]);

    expect(Schema::hasColumn('lecturas_sensores', 'humedad'))->toBeFalse()
        ->and(Schema::hasColumns('lecturas_sensores', [
            'humedad_suelo',
            'temperatura',
            'humedad_ambiental',
            'luz',
        ]))->toBeTrue();
});

test('evalúa cada medición contra los rangos de la especie al registrar la lectura', function () {
    [$sensor, $unidad] = crearUnidadIoTParaLecturas('ESP32-LECTURA-006');
    $token = 'token-evaluacion';
    $sensor->forceFill(['token_hash' => hash('sha256', $token)])->save();
    $unidad->venta->planta->update([
        'nombre' => 'Aloe Vera',
        'humedad_suelo_min' => 30,
        'humedad_suelo_max' => 60,
        'temperatura_min' => 15,
        'temperatura_max' => 30,
        'humedad_ambiental_min' => 40,
        'humedad_ambiental_max' => 70,
        'luz_min' => 200,
        'luz_max' => 1000,
    ]);

    $this->withHeader('X-Sensor-Token', $token)
        ->postJson('/api/lecturas', payloadLecturaValida($unidad->id))
        ->assertCreated()
        ->assertJsonPath('desviaciones.0.variable', 'humedad_suelo')
        ->assertJsonPath('desviaciones.0.valor_medido', 24.5)
        ->assertJsonPath('desviaciones.0.limite', 30)
        ->assertJsonPath('desviaciones.0.direccion', 'bajo')
        ->assertJsonCount(1, 'desviaciones')
        ->assertJsonPath('alertas.0.tipo', 'riego')
        ->assertJsonPath('alertas.0.rango_esperado.minimo', 30)
        ->assertJsonPath('alertas.0.rango_esperado.maximo', 60)
        ->assertJsonPath(
            'alertas.0.mensaje',
            'La humedad del suelo está por debajo del rango recomendado para tu Aloe Vera. Medición: 24.5 %; rango esperado: 30–60 %.'
        );

    $this->assertDatabaseCount('lecturas_sensores', 1);
});

test('mantiene una alerta por episodio, la resuelve al recuperarse y crea otra si reaparece', function () {
    [$sensor, $unidad] = crearUnidadIoTParaLecturas('ESP32-LECTURA-007');
    $token = 'token-episodio';
    $sensor->forceFill(['token_hash' => hash('sha256', $token)])->save();
    $unidad->venta->planta->update([
        'nombre' => 'Aloe Vera',
        'humedad_suelo_min' => 30,
        'humedad_suelo_max' => 60,
        'temperatura_min' => 15,
        'temperatura_max' => 30,
        'humedad_ambiental_min' => 40,
        'humedad_ambiental_max' => 70,
        'luz_min' => 200,
        'luz_max' => 1000,
    ]);

    $primeraRespuesta = $this->withHeader('X-Sensor-Token', $token)
        ->postJson('/api/lecturas', payloadLecturaValida($unidad->id))
        ->assertCreated()
        ->assertJsonCount(1, 'alertas')
        ->assertJsonCount(0, 'alertas_resueltas');
    $primeraAlertaId = $primeraRespuesta->json('alertas.0.id');

    $segundaLectura = payloadLecturaValida($unidad->id);
    $segundaLectura['humedad_suelo'] = 25;
    $this->withHeader('X-Sensor-Token', $token)
        ->postJson('/api/lecturas', $segundaLectura)
        ->assertCreated()
        ->assertJsonCount(1, 'alertas')
        ->assertJsonPath('alertas.0.id', $primeraAlertaId)
        ->assertJsonPath('alertas.0.valor_medido', 25);

    $this->assertDatabaseCount('alertas', 1);
    $this->assertDatabaseHas('alertas', [
        'id' => $primeraAlertaId,
        'planta_vendida_id' => $unidad->id,
        'variable' => 'humedad_suelo',
        'valor_medido' => 25,
        'rango_minimo' => 30,
        'rango_maximo' => 60,
        'resuelta_en' => null,
    ]);

    $lecturaRecuperada = payloadLecturaValida($unidad->id);
    $lecturaRecuperada['humedad_suelo'] = 45;
    $recuperacion = $this->withHeader('X-Sensor-Token', $token)
        ->postJson('/api/lecturas', $lecturaRecuperada)
        ->assertCreated()
        ->assertJsonCount(0, 'alertas')
        ->assertJsonPath('alertas_resueltas.0.id', $primeraAlertaId);
    expect($recuperacion->json('alertas_resueltas.0.resuelta_en'))->not->toBeNull();

    $tercerEpisodio = payloadLecturaValida($unidad->id);
    $tercerEpisodio['humedad_suelo'] = 20;
    $terceraRespuesta = $this->withHeader('X-Sensor-Token', $token)
        ->postJson('/api/lecturas', $tercerEpisodio)
        ->assertCreated()
        ->assertJsonCount(1, 'alertas')
        ->assertJsonPath('alertas.0.variable', 'humedad_suelo');

    $nuevaAlertaId = $terceraRespuesta->json('alertas.0.id');
    expect($nuevaAlertaId)->not->toBe($primeraAlertaId);

    $this->assertDatabaseCount('alertas', 2);
    expect(Alerta::findOrFail($primeraAlertaId)->resuelta_en)->not->toBeNull();
    $this->assertDatabaseHas('alertas', [
        'id' => $nuevaAlertaId,
        'resuelta_en' => null,
    ]);
});

test('mantiene episodios independientes por unidad y variable', function () {
    [$sensor, $unidad] = crearUnidadIoTParaLecturas('ESP32-LECTURA-008');
    $token = 'token-variables';
    $sensor->forceFill(['token_hash' => hash('sha256', $token)])->save();
    $unidad->venta->planta->update([
        'nombre' => 'Aloe Vera',
        'humedad_suelo_min' => 30,
        'humedad_suelo_max' => 60,
        'temperatura_min' => 15,
        'temperatura_max' => 30,
    ]);

    $fueraDeDosRangos = payloadLecturaValida($unidad->id);
    $fueraDeDosRangos['temperatura'] = 35;
    $primera = $this->withHeader('X-Sensor-Token', $token)
        ->postJson('/api/lecturas', $fueraDeDosRangos)
        ->assertCreated()
        ->assertJsonCount(2, 'alertas');

    $alertaSueloId = collect($primera->json('alertas'))
        ->firstWhere('variable', 'humedad_suelo')['id'];
    $alertaTemperaturaId = collect($primera->json('alertas'))
        ->firstWhere('variable', 'temperatura')['id'];

    $soloSueloFuera = payloadLecturaValida($unidad->id);
    $soloSueloFuera['temperatura'] = 20;
    $segunda = $this->withHeader('X-Sensor-Token', $token)
        ->postJson('/api/lecturas', $soloSueloFuera)
        ->assertCreated()
        ->assertJsonCount(1, 'alertas')
        ->assertJsonPath('alertas.0.id', $alertaSueloId)
        ->assertJsonPath('alertas_resueltas.0.id', $alertaTemperaturaId);

    expect(Alerta::findOrFail($alertaTemperaturaId)->resuelta_en)->not->toBeNull()
        ->and(Alerta::findOrFail($alertaSueloId)->resuelta_en)->toBeNull();
    $this->assertDatabaseCount('alertas', 2);
});

test('rechaza solicitudes sin una credencial válida o de un sensor inactivo', function () {
    [$sensor, $unidad] = crearUnidadIoTParaLecturas('ESP32-LECTURA-002');
    $token = 'otro-token-secreto';
    $sensor->forceFill(['token_hash' => hash('sha256', $token)])->save();

    $this->postJson('/api/lecturas', payloadLecturaValida($unidad->id))
        ->assertUnauthorized();

    $this->withHeader('X-Sensor-Token', 'token-incorrecto')
        ->postJson('/api/lecturas', payloadLecturaValida($unidad->id))
        ->assertUnauthorized();

    $sensor->update(['estado' => 'inactivo']);
    $this->withHeader('X-Sensor-Token', $token)
        ->postJson('/api/lecturas', payloadLecturaValida($unidad->id))
        ->assertUnauthorized();

    $this->assertDatabaseCount('lecturas_sensores', 0);
});

test('un sensor autenticado solo puede reportar la unidad que tiene asignada', function () {
    [$sensorA, $unidadA] = crearUnidadIoTParaLecturas('ESP32-LECTURA-003');
    [, $unidadB] = crearUnidadIoTParaLecturas('ESP32-LECTURA-004');
    $token = 'token-sensor-a';
    $sensorA->forceFill(['token_hash' => hash('sha256', $token)])->save();

    $this->withHeader('X-Sensor-Token', $token)
        ->postJson('/api/lecturas', payloadLecturaValida($unidadB->id))
        ->assertForbidden();

    $this->withHeader('X-Sensor-Token', $token)
        ->postJson('/api/lecturas', payloadLecturaValida($unidadA->id + $unidadB->id + 100))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('planta_vendida_id');

    $this->assertDatabaseCount('lecturas_sensores', 0);
});

test('valida mediciones físicas y formato de fecha antes de guardar', function () {
    [$sensor, $unidad] = crearUnidadIoTParaLecturas('ESP32-LECTURA-005');
    $token = 'token-validacion';
    $sensor->forceFill(['token_hash' => hash('sha256', $token)])->save();
    $payload = payloadLecturaValida($unidad->id);
    $payload['humedad_suelo'] = 101;
    $payload['temperatura'] = -41;
    $payload['humedad_ambiental'] = -1;
    $payload['luz'] = 65536;
    $payload['fecha_hora'] = '11/09/2026 17:30';

    $this->withHeader('X-Sensor-Token', $token)
        ->postJson('/api/lecturas', $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'humedad_suelo',
            'temperatura',
            'humedad_ambiental',
            'luz',
            'fecha_hora',
        ]);

    $this->assertDatabaseCount('lecturas_sensores', 0);
});
