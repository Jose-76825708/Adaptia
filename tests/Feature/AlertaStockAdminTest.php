<?php

use App\Models\Alerta;
use App\Models\Planta;
use App\Models\PlantaVendida;
use App\Models\TipoPlanta;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function crearPlantaConStock(string $nombre = 'Aloe Vera', array $overrides = []): Planta
{
    $tipo = TipoPlanta::firstOrCreate(['nombre' => 'Tipo de prueba']);

    return Planta::create(array_merge([
        'tipo_planta_id' => $tipo->id,
        'nombre' => $nombre,
        'descripcion' => 'Planta de prueba',
        'imagen' => 'aloe.jpg',
        'luz_requerida' => 'alta',
        'frecuencia_riego' => 'semanal',
        'tamaño_adulto' => 'mediana',
        'nivel_cuidado' => 'principiante',
        'tipo_ambiente' => 'interiores',
        'toxicidad' => false,
        'estetica' => 'follaje',
        'precio' => 49.99,
        'stock_actual' => 2,
        'stock_minimo' => 5,
    ], $overrides));
}

function crearAlertaStock(Planta $planta, array $overrides = []): Alerta
{
    return Alerta::create(array_merge([
        'planta_id' => $planta->id,
        'planta_vendida_id' => null,
        'tipo' => 'stock_bajo',
        'variable' => 'stock',
        'valor_medido' => $planta->stock_actual,
        'limite' => $planta->stock_minimo,
        'rango_minimo' => $planta->stock_minimo,
        'rango_maximo' => null,
        'direccion' => 'bajo',
        'mensaje' => 'Alerta de prueba',
        'leida' => false,
        'resuelta_en' => null,
    ], $overrides));
}

function crearAlertaPlantavendida(): Alerta
{
    $cliente = User::factory()->create(['rol' => 'cliente']);
    $vendedor = User::factory()->create(['rol' => 'vendedor']);
    $planta = crearPlantaConStock('Planta Vendida');
    $venta = Venta::create([
        'user_id' => $cliente->id,
        'vendedor_id' => $vendedor->id,
        'planta_id' => $planta->id,
        'cantidad' => 1,
        'fecha' => now(),
    ]);
    $plantasVendidas = PlantaVendida::create([
        'venta_id' => $venta->id,
        'user_id' => $cliente->id,
    ]);

    return Alerta::create([
        'planta_id' => null,
        'planta_vendida_id' => $plantasVendidas->id,
        'tipo' => 'stock_bajo',
        'variable' => 'stock',
        'valor_medido' => 1,
        'limite' => 2,
        'rango_minimo' => 2,
        'rango_maximo' => null,
        'direccion' => 'bajo',
        'mensaje' => 'Alerta de planta vendida',
        'leida' => false,
        'resuelta_en' => null,
    ]);
}

test('admin can view alerts page', function () {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $planta = crearPlantaConStock('Aloe Vera');
    crearAlertaStock($planta);

    $this->actingAs($admin)
        ->get(route('alertas-stock.index'))
        ->assertOk()
        ->assertSeeText('Alertas de stock')
        ->assertSeeText('Aloe Vera');
});

test('non-admin user gets 403', function () {
    $vendedor = User::factory()->create(['rol' => 'vendedor']);

    $this->actingAs($vendedor)
        ->get(route('alertas-stock.index'))
        ->assertForbidden();
});

test('guest is redirected to login', function () {
    $this->get(route('alertas-stock.index'))->assertRedirect(route('login'));
});

test('admin can mark an alert as read', function () {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $planta = crearPlantaConStock('Orquídea');
    $alerta = crearAlertaStock($planta);

    $response = $this->actingAs($admin)
        ->patch(route('alertas-stock.marcar-leida', ['alerta' => $alerta->id, 'estado' => 'abiertas']));

    $response->assertRedirect(route('alertas-stock.index', ['estado' => 'abiertas']))
        ->assertSessionHas('success', 'La alerta ha sido marcada como leída.');

    $alerta->refresh();
    expect($alerta->leida)->toBeTrue()
        ->and($alerta->resuelta_en)->toBeNull();
});

test('marking read does not touch resuelta_en', function () {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $planta = crearPlantaConStock('Ficus');
    $alerta = crearAlertaStock($planta, ['resuelta_en' => null]);

    $this->actingAs($admin)
        ->patch(route('alertas-stock.marcar-leida', ['alerta' => $alerta->id, 'estado' => 'abiertas']));

    $alerta->refresh();
    expect($alerta->leida)->toBeTrue()
        ->and($alerta->resuelta_en)->toBeNull();
});

test('marking an already read alert is idempotent', function () {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $planta = crearPlantaConStock('Cactus');
    $alerta = crearAlertaStock($planta, ['leida' => true]);

    $response = $this->actingAs($admin)
        ->patch(route('alertas-stock.marcar-leida', ['alerta' => $alerta->id, 'estado' => 'leidas']));

    $response->assertRedirect(route('alertas-stock.index', ['estado' => 'leidas']))
        ->assertSessionHas('info');

    $alerta->refresh();
    expect($alerta->leida)->toBeTrue()
        ->and($alerta->resuelta_en)->toBeNull();
});

test('resolved alerts do not change when marking them as read', function () {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $planta = crearPlantaConStock('Helecho');
    $alerta = crearAlertaStock($planta, ['leida' => false, 'resuelta_en' => now()]);

    $response = $this->actingAs($admin)
        ->patch(route('alertas-stock.marcar-leida', ['alerta' => $alerta->id, 'estado' => 'resueltas']));

    $response->assertRedirect(route('alertas-stock.index', ['estado' => 'resueltas']))
        ->assertSessionHas('info');

    $alerta->refresh();
    expect($alerta->leida)->toBeFalse()
        ->and($alerta->resuelta_en)->not->toBeNull();
});

test('alert not found returns 404', function () {
    $admin = User::factory()->create(['rol' => 'administrador']);

    $this->actingAs($admin)
        ->patch(route('alertas-stock.marcar-leida', ['alerta' => 999999, 'estado' => 'abiertas']))
        ->assertNotFound();
});

test('resuelta_en in the request is ignored', function () {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $planta = crearPlantaConStock('Lavanda');
    $alerta = crearAlertaStock($planta);

    $this->actingAs($admin)
        ->patch(route('alertas-stock.marcar-leida', ['alerta' => $alerta->id, 'estado' => 'abiertas']), [
            'resuelta_en' => now()->addDay()->toDateTimeString(),
        ]);

    $alerta->refresh();
    expect($alerta->leida)->toBeTrue()
        ->and($alerta->resuelta_en)->toBeNull();
});

test('filters show the correct alerts for each state', function () {
    $admin = User::factory()->create(['rol' => 'administrador']);

    $abierta = crearPlantaConStock('Planta Abierta');
    $noLeida = crearPlantaConStock('Planta No Leída');
    $leida = crearPlantaConStock('Planta Leída');
    $resuelta = crearPlantaConStock('Planta Resuelta');

    crearAlertaStock($abierta, ['leida' => false, 'resuelta_en' => null]);
    crearAlertaStock($noLeida, ['leida' => false, 'resuelta_en' => null]);
    crearAlertaStock($leida, ['leida' => true, 'resuelta_en' => null]);
    crearAlertaStock($resuelta, ['leida' => false, 'resuelta_en' => now()]);

    $this->actingAs($admin)
        ->get(route('alertas-stock.index', ['estado' => 'abiertas']))
        ->assertOk()
        ->assertSeeText('Planta Abierta')
        ->assertSeeText('Planta No Leída')
        ->assertSeeText('Planta Leída')
        ->assertDontSeeText('Planta Resuelta');

    $this->actingAs($admin)
        ->get(route('alertas-stock.index', ['estado' => 'no_leidas']))
        ->assertOk()
        ->assertSeeText('Planta No Leída')
        ->assertDontSeeText('Planta Leída')
        ->assertDontSeeText('Planta Resuelta');

    $this->actingAs($admin)
        ->get(route('alertas-stock.index', ['estado' => 'leidas']))
        ->assertOk()
        ->assertSeeText('Planta Leída')
        ->assertDontSeeText('Planta No Leída')
        ->assertDontSeeText('Planta Resuelta');

    $this->actingAs($admin)
        ->get(route('alertas-stock.index', ['estado' => 'resueltas']))
        ->assertOk()
        ->assertSeeText('Planta Resuelta')
        ->assertDontSeeText('Planta Leída')
        ->assertDontSeeText('Planta No Leída');

    $this->actingAs($admin)
        ->get(route('alertas-stock.index', ['estado' => 'todas']))
        ->assertOk()
        ->assertSeeText('Planta Abierta')
        ->assertSeeText('Planta No Leída')
        ->assertSeeText('Planta Leída')
        ->assertSeeText('Planta Resuelta');
});

test('alerts attached to planta_vendida are excluded from the list', function () {
    $admin = User::factory()->create(['rol' => 'administrador']);
    crearAlertaPlantavendida();

    $this->actingAs($admin)
        ->get(route('alertas-stock.index', ['estado' => 'abiertas']))
        ->assertOk()
        ->assertSeeText('No hay alertas');
});

test('the alerts index does not trigger N+1 queries', function () {
    $admin = User::factory()->create(['rol' => 'administrador']);

    $planta = crearPlantaConStock('Planta única');
    crearAlertaStock($planta);

    DB::enableQueryLog();
    $this->actingAs($admin)->get(route('alertas-stock.index', ['estado' => 'abiertas']))->assertOk();
    $plantQueriesWithOneAlert = collect(DB::getQueryLog())
        ->filter(fn (array $query) => preg_match('/from\s+["`]?plantas["`]?/i', $query['query']) === 1)
        ->count();
    DB::flushQueryLog();

    foreach (range(1, 10) as $index) {
        $planta = crearPlantaConStock("Planta {$index}");
        crearAlertaStock($planta);
    }

    DB::enableQueryLog();
    $this->actingAs($admin)->get(route('alertas-stock.index', ['estado' => 'abiertas']))->assertOk();
    $plantQueriesWithTenAlerts = collect(DB::getQueryLog())
        ->filter(fn (array $query) => preg_match('/from\s+["`]?plantas["`]?/i', $query['query']) === 1)
        ->count();

    expect($plantQueriesWithTenAlerts)->toBe($plantQueriesWithOneAlert)
        ->and($plantQueriesWithTenAlerts)->toBe(1);
});
