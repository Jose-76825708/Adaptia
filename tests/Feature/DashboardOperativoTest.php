<?php

use App\Models\Alerta;
use App\Models\CasoMantenimiento;
use App\Models\LecturaSensor;
use App\Models\Planta;
use App\Models\PlantaVendida;
use App\Models\Sensor;
use App\Models\User;
use App\Models\Venta;
use App\Services\DashboardOperativoService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('muestra al administrador un resumen de sus macroprocesos', function () {
    $administrador = User::factory()->create(['rol' => 'administrador']);
    Planta::factory()->create(['stock_actual' => 2, 'stock_minimo' => 5]);
    CasoMantenimiento::factory()->create(['estado' => 'abierto']);

    $this->actingAs($administrador)
        ->get(route('home'))
        ->assertOk()
        ->assertViewIs('dashboard.administrador')
        ->assertSee('Panel de administración')
        ->assertSee('Inventario prioritario')
        ->assertSee('Mantenimiento')
        ->assertSee('Monitoreo IoT')
        ->assertViewHas('resumen', fn (array $resumen) => $resumen['plantas_bajo_stock'] === 1
            && $resumen['casos_abiertos'] === 1);
});

it('muestra al vendedor un panel con sus propias ventas y atajos comerciales', function () {
    $vendedor = User::factory()->create(['rol' => 'vendedor']);
    $otroVendedor = User::factory()->create(['rol' => 'vendedor']);
    $cliente = User::factory()->create(['rol' => 'cliente']);
    $planta = Planta::factory()->create([
        'precio' => 18.50,
        'stock_actual' => 8,
        'stock_minimo' => 2,
    ]);

    Venta::create([
        'user_id' => $cliente->id,
        'vendedor_id' => $vendedor->id,
        'planta_id' => $planta->id,
        'cantidad' => 2,
        'fecha' => now(),
    ]);
    Venta::create([
        'user_id' => $cliente->id,
        'vendedor_id' => $otroVendedor->id,
        'planta_id' => $planta->id,
        'cantidad' => 4,
        'fecha' => now(),
    ]);

    $this->actingAs($vendedor)
        ->get(route('home'))
        ->assertOk()
        ->assertViewIs('dashboard.vendedor')
        ->assertSee('Panel de ventas')
        ->assertSee('Mis ventas recientes')
        ->assertSee('Registrar una venta')
        ->assertViewHas('resumen', fn (array $resumen) => $resumen['ventas_mes'] === 1
            && $resumen['unidades_mes'] === 2
            && $resumen['ingresos_estimados_mes'] === 37.0);
});

it('resume alertas y lecturas IoT activas para el panel administrativo', function () {
    $cliente = User::factory()->create(['rol' => 'cliente']);
    $vendedor = User::factory()->create(['rol' => 'vendedor']);
    $planta = Planta::factory()->create(['nombre' => 'Monstera']);
    $venta = Venta::create([
        'user_id' => $cliente->id,
        'vendedor_id' => $vendedor->id,
        'planta_id' => $planta->id,
        'cantidad' => 1,
        'fecha' => now(),
    ]);
    $sensor = Sensor::create([
        'identificador_fisico' => 'ESP32-PANEL-001',
        'estado' => 'activo',
    ]);
    $unidadVendida = PlantaVendida::create([
        'venta_id' => $venta->id,
        'user_id' => $cliente->id,
        'sensor_id' => $sensor->id,
    ]);
    Alerta::create([
        'planta_vendida_id' => $unidadVendida->id,
        'tipo' => 'cuidado_planta',
        'variable' => 'temperatura',
        'valor_medido' => 35,
        'rango_minimo' => 15,
        'rango_maximo' => 30,
    ]);
    LecturaSensor::create([
        'planta_vendida_id' => $unidadVendida->id,
        'humedad_suelo' => 24,
        'temperatura' => 35,
        'humedad_ambiental' => 45,
        'luz' => 500,
        'fecha_hora' => now(),
    ]);

    $resumen = app(DashboardOperativoService::class)->consultarAlertasMonitoreo();

    expect($resumen['total_activas'])->toBe(1)
        ->and($resumen['alertas'][0]['nombre_planta'])->toBe('Monstera')
        ->and($resumen['lecturas_recientes'][0]['sensor'])->toBe('ESP32-PANEL-001')
        ->and((float) $resumen['lecturas_recientes'][0]['temperatura'])->toBe(35.0);
});
