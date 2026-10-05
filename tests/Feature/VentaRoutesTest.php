<?php

use App\Models\MovimientoInventario;
use App\Models\Planta;
use App\Models\PlantaVendida;
use App\Models\Sensor;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('vendedor puede consultar el listado y abrir el formulario de ventas', function () {
    $vendedor = User::factory()->create(['rol' => 'vendedor']);
    $cliente = User::factory()->create(['rol' => 'cliente']);
    $planta = Planta::factory()->create(['stock_actual' => 4]);

    $this->actingAs($vendedor)
        ->get(route('ventas.index'))
        ->assertOk()
        ->assertViewIs('ventas.index')
        ->assertSee('Todavía no hay ventas registradas.')
        ->assertSee('Registrar Venta');

    $this->get(route('ventas.create'))
        ->assertOk()
        ->assertViewIs('ventas.create')
        ->assertSee($cliente->email)
        ->assertSee($planta->nombre)
        ->assertSee('Stock disponible: 4');
});

test('listado de ventas muestra el sensor asignado y las unidades pendientes', function () {
    $vendedor = User::factory()->create(['rol' => 'vendedor']);
    $cliente = User::factory()->create(['rol' => 'cliente']);
    $planta = Planta::factory()->create(['nombre' => 'Monstera']);
    $venta = Venta::create([
        'user_id' => $cliente->id,
        'vendedor_id' => $vendedor->id,
        'planta_id' => $planta->id,
        'cantidad' => 2,
    ]);
    $sensor = Sensor::create([
        'identificador_fisico' => 'ESP32-GARDEN-001',
        'estado' => 'activo',
    ]);
    $unidadAsignada = PlantaVendida::create([
        'venta_id' => $venta->id,
        'user_id' => $cliente->id,
        'sensor_id' => $sensor->id,
    ]);
    $unidadPendiente = PlantaVendida::create([
        'venta_id' => $venta->id,
        'user_id' => $cliente->id,
        'sensor_id' => null,
    ]);

    $this->actingAs($vendedor)
        ->get(route('ventas.index'))
        ->assertOk()
        ->assertSee('Sensores')
        ->assertSee("Unidad #{$unidadAsignada->id}:")
        ->assertSee('ESP32-GARDEN-001')
        ->assertSee("Unidad #{$unidadPendiente->id}:")
        ->assertSee('Pendiente de asignación');
});

test('listado identifica unidades antiguas que no tienen filas individuales', function () {
    $vendedor = User::factory()->create(['rol' => 'vendedor']);
    $cliente = User::factory()->create(['rol' => 'cliente']);
    $planta = Planta::factory()->create();
    Venta::create([
        'user_id' => $cliente->id,
        'vendedor_id' => $vendedor->id,
        'planta_id' => $planta->id,
        'cantidad' => 2,
    ]);

    $this->actingAs($vendedor)
        ->get(route('ventas.index'))
        ->assertOk()
        ->assertSeeText('unidades sin registro individual')
        ->assertSeeText('2');
});

test('vendedor registra una venta usando su usuario autenticado', function () {
    $vendedor = User::factory()->create(['rol' => 'vendedor']);
    $cliente = User::factory()->create(['rol' => 'cliente']);
    $planta = Planta::factory()->create(['stock_actual' => 6]);

    $this->actingAs($vendedor)
        ->post(route('ventas.store'), [
            'user_id' => $cliente->id,
            'planta_id' => $planta->id,
            'cantidad' => 2,
            'vendedor_id' => User::factory()->create(['rol' => 'administrador'])->id,
        ])
        ->assertRedirect(route('ventas.index'))
        ->assertSessionHas('success');

    expect(Venta::query()->first()->vendedor_id)->toBe($vendedor->id)
        ->and($planta->fresh()->stock_actual)->toBe(4)
        ->and(MovimientoInventario::query()->first()->user_id)->toBe($vendedor->id);
});

test('administrador y cliente no pueden consultar o registrar ventas', function () {
    foreach (['administrador', 'cliente'] as $rol) {
        $usuario = User::factory()->create(['rol' => $rol]);

        $this->actingAs($usuario)
            ->get(route('ventas.index'))
            ->assertForbidden();

        $this->get(route('ventas.create'))
            ->assertForbidden();

        $this->post(route('ventas.store'), [
            'user_id' => User::factory()->create(['rol' => 'cliente'])->id,
            'planta_id' => Planta::factory()->create(['stock_actual' => 3])->id,
            'cantidad' => 1,
        ])->assertForbidden();
    }
});

test('sidebar muestra el enlace de ventas solo al vendedor', function () {
    $vendedor = User::factory()->create(['rol' => 'vendedor']);
    $administrador = User::factory()->create(['rol' => 'administrador']);

    $this->actingAs($vendedor)
        ->get(route('ventas.index'))
        ->assertSee(route('ventas.index'))
        ->assertSee('type="submit"', false)
        ->assertSee('Cerrar sesión');

    $this->actingAs($administrador)
        ->get(route('plantas.index'))
        ->assertDontSee(route('ventas.index'));
});

test('formulario muestra error claro cuando la cantidad excede el stock', function () {
    $vendedor = User::factory()->create(['rol' => 'vendedor']);
    $cliente = User::factory()->create(['rol' => 'cliente']);
    $planta = Planta::factory()->create([
        'nombre' => 'Helecho de prueba',
        'stock_actual' => 1,
    ]);

    $this->actingAs($vendedor)
        ->get(route('ventas.create'))
        ->assertOk();

    $this->post(route('ventas.store'), [
        'user_id' => $cliente->id,
        'planta_id' => $planta->id,
        'cantidad' => 2,
    ])->assertRedirect(route('ventas.create'))
        ->assertSessionHasErrors('cantidad');

    $this->get(route('ventas.create'))
        ->assertOk()
        ->assertSee('Stock insuficiente para Helecho de prueba.')
        ->assertSee('value="2"', false);
});

test('rechaza cantidades cero y no enteras sin crear venta ni movimiento', function () {
    $vendedor = User::factory()->create(['rol' => 'vendedor']);
    $cliente = User::factory()->create(['rol' => 'cliente']);
    $planta = Planta::factory()->create(['stock_actual' => 5]);

    foreach ([0, 1.5] as $cantidad) {
        $this->from(route('ventas.create'))
            ->actingAs($vendedor)
            ->post(route('ventas.store'), [
                'user_id' => $cliente->id,
                'planta_id' => $planta->id,
                'cantidad' => $cantidad,
            ])
            ->assertRedirect(route('ventas.create'))
            ->assertSessionHasErrors('cantidad');
    }

    expect(Venta::count())->toBe(0)
        ->and(MovimientoInventario::count())->toBe(0)
        ->and($planta->fresh()->stock_actual)->toBe(5);
});
