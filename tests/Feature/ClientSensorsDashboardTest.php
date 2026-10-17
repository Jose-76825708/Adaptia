<?php

use App\Models\Planta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MonitoreoFixtures;

uses(RefreshDatabase::class);

test('cliente ve sus sensores asignados aunque todavía no tengan lecturas', function () {
    $cliente = MonitoreoFixtures::cliente();
    $otroCliente = MonitoreoFixtures::cliente();
    $vendedor = MonitoreoFixtures::vendedor();
    MonitoreoFixtures::unidadConSensor(
        $cliente,
        $vendedor,
        'Monstera',
        'ESP32-CLIENTE-001'
    );
    MonitoreoFixtures::unidadConSensor(
        $otroCliente,
        $vendedor,
        'Helecho',
        'ESP32-OTRO-001'
    );

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
    $cliente = MonitoreoFixtures::cliente();
    $vendedor = MonitoreoFixtures::vendedor();
    $unidad = MonitoreoFixtures::unidadConSensor(
        $cliente,
        $vendedor,
        'Aloe Vera',
        'ESP32-CLIENTE-LECTURAS',
        [
            'humedad_suelo_min' => 20,
            'humedad_suelo_max' => 40,
        ]
    );
    MonitoreoFixtures::lectura($unidad, [
        'humedad_suelo' => 18,
        'temperatura' => 24,
        'humedad_ambiental' => 50,
        'luz' => 4000,
    ]);
    MonitoreoFixtures::alerta($unidad, [
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
    $cliente = MonitoreoFixtures::cliente();
    $otroCliente = MonitoreoFixtures::cliente();
    $vendedor = MonitoreoFixtures::vendedor();
    $unidad = MonitoreoFixtures::unidadConSensor(
        $cliente,
        $vendedor,
        'Aloe Vera',
        'ESP32-HISTORIAL-CLIENTE'
    );
    $otraUnidad = MonitoreoFixtures::unidadConSensor(
        $otroCliente,
        $vendedor,
        'Planta privada',
        'ESP32-HISTORIAL-OTRO'
    );

    foreach ([['humedad_suelo' => 18, 'fecha_hora' => now()->subHour()], ['humedad_suelo' => 25, 'fecha_hora' => now()]] as $lectura) {
        MonitoreoFixtures::lectura($unidad, [
            'humedad_suelo' => $lectura['humedad_suelo'],
            'temperatura' => 24,
            'humedad_ambiental' => 50,
            'luz' => 4000,
            'fecha_hora' => $lectura['fecha_hora'],
        ]);
    }

    MonitoreoFixtures::alerta($unidad, [
        'variable' => 'humedad_suelo',
        'mensaje' => 'Alerta privada del cliente.',
    ]);
    MonitoreoFixtures::alerta($unidad, [
        'tipo' => 'cuidado_planta',
        'variable' => 'temperatura',
        'mensaje' => 'Alerta resuelta del cliente.',
        'resuelta_en' => now(),
    ]);
    MonitoreoFixtures::alerta($otraUnidad, [
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
    $cliente = MonitoreoFixtures::cliente();
    $vendedor = MonitoreoFixtures::vendedor();
    $planta = Planta::factory()->create();
    $venta = MonitoreoFixtures::venta($cliente, $vendedor, $planta);
    MonitoreoFixtures::unidad($venta, $cliente);

    $this->actingAs($cliente)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('Aún no tienes sensores asignados')
        ->assertDontSee('Esperando lectura');
});

test('consultas de actualización devuelven solo el fragmento de monitoreo del cliente autenticado', function () {
    $cliente = MonitoreoFixtures::cliente();
    $otroCliente = MonitoreoFixtures::cliente();
    $vendedor = MonitoreoFixtures::vendedor();
    $unidad = MonitoreoFixtures::unidadConSensor(
        $cliente,
        $vendedor,
        'Aloe Vera',
        'ESP32-POLLING-CLIENTE',
        [],
        ['token_hash' => hash('sha256', 'secreto-privado')]
    );
    $otraUnidad = MonitoreoFixtures::unidadConSensor(
        $otroCliente,
        $vendedor,
        'Planta privada',
        'ESP32-POLLING-OTRO'
    );
    MonitoreoFixtures::lectura($unidad, [
        'humedad_suelo' => 18,
        'temperatura' => 24,
        'humedad_ambiental' => 50,
        'luz' => 4000,
    ]);
    MonitoreoFixtures::alerta($unidad, [
        'variable' => 'humedad_suelo',
        'mensaje' => 'Alerta privada del cliente.',
    ]);
    MonitoreoFixtures::lectura($otraUnidad, [
        'humedad_suelo' => 10,
        'temperatura' => 20,
        'humedad_ambiental' => 40,
        'luz' => 1000,
    ]);
    MonitoreoFixtures::alerta($otraUnidad, [
        'variable' => 'humedad_suelo',
        'mensaje' => 'Alerta de otro cliente.',
    ]);

    $this->actingAs($cliente)
        ->get(route('cliente.monitoreo.inicio'))
        ->assertOk()
        ->assertSee('id="mis-sensores"', false)
        ->assertSee('data-monitoring-poll-interval="15000"', false)
        ->assertSee(route('cliente.monitoreo.inicio'), false)
        ->assertSee('ESP32-POLLING-CLIENTE')
        ->assertSeeText('18 %')
        ->assertSee('Alerta privada del cliente.')
        ->assertDontSee('Recomendaciones para ti')
        ->assertDontSee('ESP32-POLLING-OTRO')
        ->assertDontSee('Planta privada')
        ->assertDontSee('Alerta de otro cliente.')
        ->assertDontSee(hash('sha256', 'secreto-privado'));

    $this->assertDatabaseCount('lecturas_sensores', 2);
    $this->assertDatabaseCount('alertas', 2);

    $this->get(route('cliente.monitoreo.historial'))
        ->assertOk()
        ->assertSee('id="historial-monitoreo"', false)
        ->assertSee('data-monitoring-poll-interval="30000"', false)
        ->assertSee(route('cliente.monitoreo.historial'), false)
        ->assertSee('ESP32-POLLING-CLIENTE')
        ->assertSee('Tendencia histórica de humedad del suelo')
        ->assertSee('Alerta privada del cliente.')
        ->assertDontSee('Historial y alertas')
        ->assertDontSee('ESP32-POLLING-OTRO')
        ->assertDontSee('Planta privada')
        ->assertDontSee('Alerta de otro cliente.')
        ->assertDontSee(hash('sha256', 'secreto-privado'));

    $this->assertDatabaseCount('lecturas_sensores', 2);
    $this->assertDatabaseCount('alertas', 2);
});

test('consultas de actualización requieren cliente autenticado', function () {
    $this->get(route('cliente.monitoreo.inicio'))
        ->assertRedirect(route('login'));

    $administrador = User::factory()->create(['rol' => 'administrador']);

    $this->actingAs($administrador)
        ->get(route('cliente.monitoreo.inicio'))
        ->assertForbidden();

    $this->get(route('cliente.monitoreo.historial'))
        ->assertForbidden();
});

test('una nueva lectura recibida por la api aparece en el siguiente fragmento de monitoreo', function () {
    $cliente = MonitoreoFixtures::cliente();
    $vendedor = MonitoreoFixtures::vendedor();
    $unidad = MonitoreoFixtures::unidadConSensor(
        $cliente,
        $vendedor,
        'Aloe Vera',
        'ESP32-POLLING-LECTURA-NUEVA',
        [
            'humedad_suelo_min' => 30,
            'humedad_suelo_max' => 60,
        ],
        ['token_hash' => hash('sha256', 'token-polling-prueba')]
    );
    MonitoreoFixtures::lectura($unidad, [
        'humedad_suelo' => 35,
        'temperatura' => 24,
        'humedad_ambiental' => 50,
        'luz' => 4000,
    ]);

    $this->actingAs($cliente)
        ->get(route('cliente.monitoreo.inicio'))
        ->assertOk()
        ->assertSeeText('35 %')
        ->assertDontSee('24 %');

    $this->withHeader('X-Sensor-Token', 'token-polling-prueba')
        ->postJson('/api/lecturas', [
            'planta_vendida_id' => $unidad->id,
            'humedad_suelo' => 24,
            'temperatura' => 24,
            'humedad_ambiental' => 50,
            'luz' => 4000,
            'fecha_hora' => now()->addMinute()->toIso8601String(),
        ])
        ->assertCreated()
        ->assertJsonPath('data.humedad_suelo', 24)
        ->assertJsonPath('alertas.0.tipo', 'riego');

    $this->get(route('cliente.monitoreo.inicio'))
        ->assertOk()
        ->assertSeeText('24 %')
        ->assertSee('La humedad del suelo está por debajo del rango recomendado para tu Aloe Vera.')
        ->assertDontSee('35 %');

    $this->assertDatabaseCount('lecturas_sensores', 2);
    $this->assertDatabaseCount('alertas', 1);
});
