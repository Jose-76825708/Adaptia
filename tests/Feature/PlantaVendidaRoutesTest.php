<?php

use App\Models\Planta;
use App\Models\PlantaVendida;
use App\Models\Sensor;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

function crearUnidadParaAsignacion(User $cliente): PlantaVendida
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

test('rutas de unidades vendidas están protegidas para vendedores', function () {
    foreach (['plantas-vendidas.index', 'plantas-vendidas.asignar-sensor'] as $routeName) {
        $route = Route::getRoutes()->getByName($routeName);

        expect($route)->not->toBeNull()
            ->and($route->gatherMiddleware())->toContain('auth')
            ->toContain('role:vendedor');
    }
});

test('cliente y administrador no pueden entrar a las rutas de asignación', function () {
    foreach (['cliente', 'administrador'] as $rol) {
        $usuario = User::factory()->create(['rol' => $rol]);
        $unidad = crearUnidadParaAsignacion(User::factory()->create(['rol' => 'cliente']));
        $sensor = Sensor::create([
            'identificador_fisico' => "SENSOR-BLOQUEADO-{$rol}",
            'estado' => 'activo',
        ]);

        $this->actingAs($usuario)
            ->get(route('plantas-vendidas.index'))
            ->assertForbidden();

        $this->post(route('plantas-vendidas.asignar-sensor', $unidad), [
            'sensor_id' => $sensor->id,
        ])->assertForbidden();
    }
});

test('vendedor ve unidades pendientes, sensores disponibles y enlace de navegación', function () {
    $vendedor = User::factory()->create(['rol' => 'vendedor']);
    $cliente = User::factory()->create(['rol' => 'cliente', 'name' => 'Cliente de prueba']);
    $unidad = crearUnidadParaAsignacion($cliente);
    $planta = $unidad->venta->planta;
    $sensor = Sensor::create([
        'identificador_fisico' => 'SENSOR-VISTA-001',
        'estado' => 'activo',
    ]);

    $this->actingAs($vendedor)
        ->get(route('plantas-vendidas.index'))
        ->assertOk()
        ->assertViewIs('plantas-vendidas.index')
        ->assertSee("#{$unidad->id}")
        ->assertSee($planta->nombre)
        ->assertSee($cliente->name)
        ->assertSee($sensor->identificador_fisico)
        ->assertSee('Sensor para la unidad #' . $unidad->id)
        ->assertSee('id="sensor-select-' . $unidad->id . '"', false)
        ->assertSee(route('plantas-vendidas.index'));
});

test('muestra estados vacíos cuando no hay unidades pendientes o sensores disponibles', function () {
    $vendedor = User::factory()->create(['rol' => 'vendedor']);
    $this->actingAs($vendedor)
        ->get(route('plantas-vendidas.index'))
        ->assertOk()
        ->assertSee('No hay unidades pendientes de asignación de sensor.');

    $cliente = User::factory()->create(['rol' => 'cliente']);
    crearUnidadParaAsignacion($cliente);
    Sensor::create([
        'identificador_fisico' => 'SENSOR-INACTIVO-VISTA',
        'estado' => 'inactivo',
    ]);

    $this->get(route('plantas-vendidas.index'))
        ->assertOk()
        ->assertSee('No hay sensores activos disponibles.');
});

test('sidebar oculta la asignación de sensores a los demás roles', function () {
    $administrador = User::factory()->create(['rol' => 'administrador']);

    $this->actingAs($administrador)
        ->get(route('plantas.index'))
        ->assertOk()
        ->assertDontSee(route('plantas-vendidas.index'));
});

test('vendedor asigna sensor a una unidad sin enviar datos de usuario', function () {
    $vendedor = User::factory()->create(['rol' => 'vendedor']);
    $cliente = User::factory()->create(['rol' => 'cliente']);
    $unidad = crearUnidadParaAsignacion($cliente);
    $sensor = Sensor::create([
        'identificador_fisico' => 'SENSOR-ROUTE-001',
        'estado' => 'activo',
    ]);

    $this->actingAs($vendedor)
        ->post(route('plantas-vendidas.asignar-sensor', $unidad), [
            'sensor_id' => $sensor->id,
            'user_id' => User::factory()->create(['rol' => 'administrador'])->id,
            'vendedor_id' => User::factory()->create(['rol' => 'administrador'])->id,
        ])
        ->assertRedirect(route('plantas-vendidas.index'))
        ->assertSessionHas('success');

    expect($unidad->fresh()->sensor_id)->toBe($sensor->id)
        ->and($unidad->fresh()->user_id)->toBe($cliente->id);

    $this->get(route('plantas-vendidas.index'))
        ->assertOk()
        ->assertSee('Sensor asignado correctamente.')
        ->assertSee('No hay unidades pendientes de asignación de sensor.');
});

test('rechaza sensor inexistente en la ruta de asignación', function () {
    $vendedor = User::factory()->create(['rol' => 'vendedor']);
    $unidad = crearUnidadParaAsignacion(User::factory()->create(['rol' => 'cliente']));

    $this->from(route('plantas-vendidas.index'))
        ->actingAs($vendedor)
        ->post(route('plantas-vendidas.asignar-sensor', $unidad), [
            'sensor_id' => 99999,
        ])
        ->assertRedirect(route('plantas-vendidas.index'))
        ->assertSessionHasErrors('sensor_id');

    $this->assertDatabaseHas('plantas_vendidas', [
        'id' => $unidad->id,
        'sensor_id' => null,
    ]);
});
