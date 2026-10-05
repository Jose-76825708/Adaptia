<?php

use App\Models\Alerta;
use App\Models\LecturaSensor;
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
        ->assertSee('Esperando lectura')
        ->assertSee('Aún no hay lecturas disponibles')
        ->assertDontSee('Aún no tienes sensores asignados')
        ->assertDontSee('ESP32-OTRO-001')
        ->assertDontSee('Helecho')
        ->assertSee('href="#mis-sensores"', false)
        ->assertDontSee(route('sensores.index'));
});

test('cliente ve las lecturas recientes y alertas activas de sus sensores', function () {
    $cliente = User::factory()->create(['rol' => 'cliente']);
    $vendedor = User::factory()->create(['rol' => 'vendedor']);
    $planta = Planta::factory()->create([
        'nombre' => 'Aloe Vera',
        'humedad_suelo_min' => 20,
        'humedad_suelo_max' => 40,
    ]);
    $venta = Venta::create([
        'user_id' => $cliente->id,
        'vendedor_id' => $vendedor->id,
        'planta_id' => $planta->id,
        'cantidad' => 1,
    ]);
    $sensor = Sensor::create([
        'identificador_fisico' => 'ESP32-CLIENTE-LECTURAS',
        'estado' => 'activo',
    ]);
    $unidad = PlantaVendida::create([
        'venta_id' => $venta->id,
        'user_id' => $cliente->id,
        'sensor_id' => $sensor->id,
    ]);
    LecturaSensor::create([
        'planta_vendida_id' => $unidad->id,
        'humedad_suelo' => 18,
        'temperatura' => 24,
        'humedad_ambiental' => 50,
        'luz' => 4000,
        'fecha_hora' => now(),
    ]);
    Alerta::create([
        'planta_vendida_id' => $unidad->id,
        'tipo' => 'riego',
        'variable' => 'humedad_suelo',
        'valor_medido' => 18,
        'limite' => 20,
        'direccion' => 'bajo',
        'mensaje' => 'La humedad del suelo está por debajo del rango recomendado para tu Aloe Vera.',
    ]);

    $this->actingAs($cliente)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('ESP32-CLIENTE-LECTURAS')
        ->assertSeeText('18 %')
        ->assertSeeText('24 °C')
        ->assertSee('Requiere atención')
        ->assertSee('Alertas activas')
        ->assertSee('La humedad del suelo está por debajo del rango recomendado para tu Aloe Vera.');
});

test('historial del cliente muestra tendencias y alertas solo de sus propias plantas', function () {
    $cliente = User::factory()->create(['rol' => 'cliente']);
    $otroCliente = User::factory()->create(['rol' => 'cliente']);
    $vendedor = User::factory()->create(['rol' => 'vendedor']);
    $planta = Planta::factory()->create(['nombre' => 'Aloe Vera']);
    $otraPlanta = Planta::factory()->create(['nombre' => 'Planta privada']);
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
        'identificador_fisico' => 'ESP32-HISTORIAL-CLIENTE',
        'estado' => 'activo',
    ]);
    $otroSensor = Sensor::create([
        'identificador_fisico' => 'ESP32-HISTORIAL-OTRO',
        'estado' => 'activo',
    ]);
    $unidad = PlantaVendida::create([
        'venta_id' => $venta->id,
        'user_id' => $cliente->id,
        'sensor_id' => $sensor->id,
    ]);
    $otraUnidad = PlantaVendida::create([
        'venta_id' => $otraVenta->id,
        'user_id' => $otroCliente->id,
        'sensor_id' => $otroSensor->id,
    ]);

    foreach ([['humedad_suelo' => 18, 'fecha_hora' => now()->subHour()], ['humedad_suelo' => 25, 'fecha_hora' => now()]] as $lectura) {
        LecturaSensor::create([
            'planta_vendida_id' => $unidad->id,
            'humedad_suelo' => $lectura['humedad_suelo'],
            'temperatura' => 24,
            'humedad_ambiental' => 50,
            'luz' => 4000,
            'fecha_hora' => $lectura['fecha_hora'],
        ]);
    }

    Alerta::create([
        'planta_vendida_id' => $unidad->id,
        'tipo' => 'riego',
        'variable' => 'humedad_suelo',
        'mensaje' => 'Alerta privada del cliente.',
    ]);
    Alerta::create([
        'planta_vendida_id' => $unidad->id,
        'tipo' => 'cuidado_planta',
        'variable' => 'temperatura',
        'mensaje' => 'Alerta resuelta del cliente.',
        'resuelta_en' => now(),
    ]);
    Alerta::create([
        'planta_vendida_id' => $otraUnidad->id,
        'tipo' => 'cuidado_planta',
        'variable' => 'temperatura',
        'mensaje' => 'Alerta de otro cliente.',
    ]);

    $this->actingAs($cliente)
        ->get(route('historial-alertas'))
        ->assertOk()
        ->assertSee('Historial y alertas')
        ->assertSee('Aloe Vera')
        ->assertSee('Tendencia histórica de humedad del suelo')
        ->assertSee('2 lecturas')
        ->assertSee('Alerta privada del cliente.')
        ->assertSee('Alerta resuelta del cliente.')
        ->assertSee('Resuelta')
        ->assertDontSee('Planta privada')
        ->assertDontSee('ESP32-HISTORIAL-OTRO')
        ->assertDontSee('Alerta de otro cliente.');
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
        ->assertDontSee('Esperando lectura');
});
