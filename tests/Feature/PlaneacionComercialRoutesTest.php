<?php

use App\Models\CampanaComercial;
use App\Models\Planta;
use App\Models\SolicitudDisponibilidad;
use App\Models\User;
use App\Services\PlaneacionComercialService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('protege el listado de campañas contra visitantes y clientes', function () {
    $this->get(route('planeacion-comercial.campanas.index'))
        ->assertRedirect(route('login'));

    $cliente = User::factory()->create(['rol' => 'cliente']);
    $this->actingAs($cliente)
        ->get(route('planeacion-comercial.campanas.index'))
        ->assertForbidden();
});

it('expone bandeja y creación al vendedor, y bandeja al administrador', function () {
    $vendedor = User::factory()->create(['rol' => 'vendedor']);
    $admin = User::factory()->create(['rol' => 'administrador']);

    $this->actingAs($vendedor)
        ->get(route('planeacion-comercial.campanas.index'))
        ->assertOk()
        ->assertSee('Planeación comercial')
        ->assertSee('Nueva campaña')
        ->assertSee('Consultar demanda');

    $this->actingAs($vendedor)
        ->get(route('planeacion-comercial.campanas.create'))
        ->assertOk()
        ->assertSee('Definir campaña');

    $this->actingAs($admin)
        ->get(route('planeacion-comercial.campanas.index'))
        ->assertOk()
        ->assertDontSee('Nueva campaña')
        ->assertSee('Planeación comercial');

    $this->actingAs($admin)
        ->get(route('planeacion-comercial.campanas.create'))
        ->assertForbidden();
});

it('permite crear campaña y filtrar la bandeja por estado y código', function () {
    $vendedor = User::factory()->create(['rol' => 'vendedor']);
    $planta = Planta::factory()->create(['stock_actual' => 6]);

    $this->actingAs($vendedor)
        ->post(route('planeacion-comercial.campanas.store'), [
            'objetivos' => 'Promocionar plantas de interior.',
            'presupuesto' => 300,
            'fecha_inicio' => today()->toDateString(),
            'fecha_fin' => today()->addDays(10)->toDateString(),
            'productos' => [
                ['planta_id' => $planta->id, 'cantidad_meta' => 3, 'descuento_porcentaje' => 5],
                ['planta_id' => '', 'cantidad_meta' => '', 'descuento_porcentaje' => ''],
            ],
        ])
        ->assertRedirect();

    $campana = CampanaComercial::query()->firstOrFail();
    expect($campana->productos)->toHaveCount(1)
        ->and($campana->estado)->toBe('objetivos_definidos');

    $this->actingAs($vendedor)
        ->get(route('planeacion-comercial.campanas.index', [
            'estado' => 'objetivos_definidos',
            'q' => $campana->codigo,
        ]))
        ->assertOk()
        ->assertSee($campana->codigo)
        ->assertSee('Objetivos definidos');
});

it('muestra la etapa actual y oculta acciones de otros roles, pero protege la transición en servidor', function () {
    $vendedor = User::factory()->create(['rol' => 'vendedor']);
    $admin = User::factory()->create(['rol' => 'administrador']);
    $campana = app(PlaneacionComercialService::class)->crear($vendedor, [
        'objetivos' => 'Campaña de primavera',
        'presupuesto' => 100,
        'fecha_inicio' => today()->toDateString(),
        'fecha_fin' => today()->addDays(5)->toDateString(),
        'productos' => [[
            'planta_id' => Planta::factory()->create(['stock_actual' => 5])->id,
            'cantidad_meta' => 2,
        ]],
    ]);

    $this->actingAs($vendedor)
        ->get(route('planeacion-comercial.campanas.show', $campana))
        ->assertOk()
        ->assertSee('Etapas del macroproceso')
        ->assertSee('Registrar análisis de demanda')
        ->assertDontSee('Gestionar faltantes');

    $this->actingAs($admin)
        ->post(route('planeacion-comercial.campanas.transicionar', $campana), [
            'estado' => 'demanda_analizada',
        ])
        ->assertForbidden();
});

it('muestra la gestión de faltantes solo al administrador y rechaza el post del vendedor', function () {
    $vendedor = User::factory()->create(['rol' => 'vendedor']);
    $admin = User::factory()->create(['rol' => 'administrador']);
    $planta = Planta::factory()->create(['stock_actual' => 1, 'stock_minimo' => 0]);
    $service = app(PlaneacionComercialService::class);
    $campana = $service->crear($vendedor, [
        'objetivos' => 'Campaña con faltante de stock',
        'presupuesto' => 100,
        'fecha_inicio' => today()->toDateString(),
        'fecha_fin' => today()->addDays(5)->toDateString(),
        'productos' => [['planta_id' => $planta->id, 'cantidad_meta' => 4]],
    ]);
    $campana = $service->cambiarEstado($campana, 'demanda_analizada', $vendedor);
    $campana = $service->cambiarEstado($campana, 'productos_identificados', $vendedor);

    $this->actingAs($admin)
        ->get(route('planeacion-comercial.campanas.show', $campana))
        ->assertOk()
        ->assertSee('Gestionar faltantes')
        ->assertDontSee('Registrar análisis de demanda');

    $this->actingAs($admin)
        ->post(route('planeacion-comercial.campanas.transicionar', $campana), [
            'estado' => 'gestion_disponibilidad',
        ])
        ->assertRedirect();
    $solicitud = SolicitudDisponibilidad::query()->firstOrFail();

    $this->actingAs($vendedor)
        ->get(route('planeacion-comercial.campanas.show', $campana))
        ->assertOk()
        ->assertDontSee('Atendida');

    $this->actingAs($vendedor)
        ->post(route('planeacion-comercial.solicitudes.disponibilidad', $solicitud), [
            'estado' => 'no_lograda',
        ])
        ->assertForbidden();
});

it('expone el panel de demanda con fechas y métricas legibles', function () {
    $vendedor = User::factory()->create(['rol' => 'vendedor']);

    $this->actingAs($vendedor)
        ->get(route('planeacion-comercial.demanda', [
            'inicio' => today()->subDays(7)->toDateString(),
            'fin' => today()->toDateString(),
        ]))
        ->assertOk()
        ->assertSee('Demanda y oportunidades')
        ->assertSee('Ranking de ventas por planta')
        ->assertSee('Bajo stock mínimo')
        ->assertSee('Plantas sin ventas en el periodo');

    $cliente = User::factory()->create(['rol' => 'cliente']);
    $this->actingAs($cliente)
        ->get(route('planeacion-comercial.demanda'))
        ->assertForbidden();
});

it('rechaza una ventana de demanda con fechas invertidas', function () {
    $admin = User::factory()->create(['rol' => 'administrador']);

    $this->actingAs($admin)
        ->get(route('planeacion-comercial.demanda', [
            'inicio' => today()->toDateString(),
            'fin' => today()->subDay()->toDateString(),
        ]))
        ->assertSessionHasErrors('fin');
});
