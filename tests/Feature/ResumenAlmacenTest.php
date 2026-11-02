<?php

use App\Models\Alerta;
use App\Models\MovimientoInventario;
use App\Models\Planta;
use App\Models\User;
use App\Services\MovimientoInventarioService;
use App\Services\ResumenAlmacenService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\MonitoreoFixtures;

uses(RefreshDatabase::class);

function plantaConStock(int $actual, int $minimo): Planta
{
    return Planta::factory()->create(['stock_actual' => $actual, 'stock_minimo' => $minimo]);
}

function alertaStock(Planta $planta, array $atributos = []): Alerta
{
    return Alerta::create(array_merge([
        'planta_id' => $planta->id,
        'tipo' => 'stock_bajo',
        'variable' => 'stock',
        'valor_medido' => $planta->stock_actual,
        'limite' => $planta->stock_minimo,
        'leida' => false,
    ], $atributos));
}

function movimientoEn(Planta $planta, User $usuario, string $fecha, string $tipo = 'entrada'): MovimientoInventario
{
    $movimiento = new MovimientoInventario();
    $movimiento->forceFill([
        'planta_id' => $planta->id,
        'user_id' => $usuario->id,
        'tipo' => $tipo,
        'cantidad' => 1,
        'created_at' => Carbon::parse($fecha),
        'updated_at' => Carbon::parse($fecha),
    ])->save();

    return $movimiento;
}

function resumen(): array
{
    return app(ResumenAlmacenService::class)->obtenerResumen();
}

test('administrador y vendedor acceden al panel resumen', function (string $rol) {
    $usuario = User::factory()->create(['rol' => $rol]);

    $this->actingAs($usuario)
        ->get(route('almacen.resumen'))
        ->assertOk()
        ->assertViewIs('almacen.resumen')
        ->assertViewHas('resumen');
})->with(['administrador', 'vendedor']);

test('cliente recibe 403 e invitado es enviado al login', function () {
    $cliente = User::factory()->create(['rol' => 'cliente']);

    $this->actingAs($cliente)->get(route('almacen.resumen'))->assertForbidden();

    auth()->logout();

    $this->get(route('almacen.resumen'))->assertRedirect(route('login'));
});

test('stock total suma el stock actual de todas las plantas', function () {
    plantaConStock(10, 2);
    plantaConStock(5, 0);
    plantaConStock(0, 3);

    expect(resumen()['stock_total'])->toBe(15);
});

test('sin plantas el panel muestra ceros y sin movimientos', function () {
    $r = resumen();

    expect($r['stock_total'])->toBe(0)
        ->and($r['plantas_bajo_minimo'])->toBe(0)
        ->and($r['alertas_abiertas'])->toBe(0)
        ->and($r['alertas_no_leidas'])->toBe(0)
        ->and($r['movimientos_recientes'])->toBeEmpty();

    $admin = User::factory()->create(['rol' => 'administrador']);
    $this->actingAs($admin)->get(route('almacen.resumen'))
        ->assertOk()
        ->assertSee('Aún no hay movimientos de inventario registrados.');
});

test('plantas bajo mínimo usa la misma regla que las alertas', function () {
    plantaConStock(4, 5);   // bajo el mínimo: cuenta
    plantaConStock(5, 5);   // justo en el mínimo: no cuenta
    plantaConStock(9, 5);   // sobre el mínimo: no cuenta
    plantaConStock(0, 0);   // sin mínimo definido: no cuenta

    expect(resumen()['plantas_bajo_minimo'])->toBe(1);
});

test('alertas abiertas excluyen resueltas y alertas de IoT', function () {
    $abierta = alertaStock(plantaConStock(1, 5));
    alertaStock(plantaConStock(1, 5), ['leida' => true]);
    alertaStock(plantaConStock(1, 5), ['resuelta_en' => now()]);

    $cliente = MonitoreoFixtures::cliente();
    $venta = MonitoreoFixtures::venta($cliente, MonitoreoFixtures::vendedor(), plantaConStock(3, 1));
    MonitoreoFixtures::alerta(MonitoreoFixtures::unidad($venta, $cliente));
    // Una alerta ligada a una planta vendida (IoT) nunca cuenta como alerta de stock, aunque su tipo sea stock_bajo.
    MonitoreoFixtures::alerta(MonitoreoFixtures::unidad($venta, $cliente), ['tipo' => 'stock_bajo']);

    $r = resumen();

    expect($abierta->exists)->toBeTrue()
        ->and($r['alertas_abiertas'])->toBe(2)
        ->and($r['alertas_no_leidas'])->toBe(1);
});

test('movimientos recientes son los 5 más nuevos, el más reciente primero', function () {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $planta = plantaConStock(10, 1);

    $ids = [];
    foreach (range(1, 7) as $dia) {
        $ids[$dia] = movimientoEn($planta, $admin, sprintf('2026-10-%02d 09:00:00', $dia))->id;
    }

    $recientes = resumen()['movimientos_recientes'];

    expect($recientes)->toHaveCount(5)
        ->and($recientes->pluck('id')->all())->toBe([$ids[7], $ids[6], $ids[5], $ids[4], $ids[3]]);
});

test('movimientos recientes incluyen la anulación y marcan el original como anulado', function () {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $planta = plantaConStock(10, 1);
    $service = app(MovimientoInventarioService::class);

    $original = $service->createMovimiento([
        'planta_id' => $planta->id,
        'user_id' => $admin->id,
        'tipo' => 'entrada',
        'cantidad' => 3,
    ]);
    $anulacion = $service->anularMovimiento((string) $original->id, 'Error de digitación', $admin->id);

    $recientes = resumen()['movimientos_recientes'];

    expect($recientes->pluck('id')->all())->toBe([$anulacion->id, $original->id]);

    $this->actingAs($admin)->get(route('almacen.resumen'))
        ->assertOk()
        ->assertSee('Anulación')
        ->assertSee('Anulado');
});

test('el número de consultas no crece con la cantidad de movimientos', function () {
    $admin = User::factory()->create(['rol' => 'administrador']);

    $contar = function () use ($admin) {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->actingAs($admin)->get(route('almacen.resumen'))->assertOk();
        $total = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $total;
    };

    $planta = plantaConStock(10, 1);
    movimientoEn($planta, $admin, '2026-10-01 09:00:00');
    $conUno = $contar();

    foreach (range(2, 5) as $dia) {
        movimientoEn(plantaConStock(10, 1), $admin, sprintf('2026-10-%02d 09:00:00', $dia));
    }

    expect($contar())->toBe($conUno);
});

test('el panel refleja un movimiento que deja una planta bajo el mínimo', function () {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $planta = plantaConStock(6, 5);

    expect(resumen()['plantas_bajo_minimo'])->toBe(0)
        ->and(resumen()['alertas_abiertas'])->toBe(0);

    app(MovimientoInventarioService::class)->createMovimiento([
        'planta_id' => $planta->id,
        'user_id' => $admin->id,
        'tipo' => 'salida',
        'cantidad' => 2,
    ]);

    $r = resumen();

    expect($r['stock_total'])->toBe(4)
        ->and($r['plantas_bajo_minimo'])->toBe(1)
        ->and($r['alertas_abiertas'])->toBe(1)
        ->and($r['alertas_no_leidas'])->toBe(1)
        ->and($r['movimientos_recientes'])->toHaveCount(1);
});

test('el enlace a alertas solo aparece para el administrador', function () {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $vendedor = User::factory()->create(['rol' => 'vendedor']);

    $this->actingAs($admin)->get(route('almacen.resumen'))
        ->assertSee('Ver alertas');

    $this->actingAs($vendedor)->get(route('almacen.resumen'))
        ->assertOk()
        ->assertDontSee('Ver alertas');
});
