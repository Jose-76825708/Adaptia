<?php

use App\Models\MovimientoInventario;
use App\Models\Planta;
use App\Models\User;
use App\Services\MovimientoInventarioService;
use App\Services\VentaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MonitoreoFixtures;

uses(RefreshDatabase::class);

test('entrada guarda stock_antes y stock_despues', function () {
    $usuario = User::factory()->create(['rol' => 'administrador']);
    $planta = Planta::factory()->create(['stock_actual' => 5, 'stock_minimo' => 0]);

    $movimiento = app(MovimientoInventarioService::class)->createMovimiento([
        'planta_id' => $planta->id,
        'user_id' => $usuario->id,
        'tipo' => 'entrada',
        'cantidad' => 3,
    ]);

    expect($movimiento->fresh()->stock_antes)->toBe(5)
        ->and($movimiento->fresh()->stock_despues)->toBe(8)
        ->and($planta->fresh()->stock_actual)->toBe(8);
});

test('salida guarda stock_antes y stock_despues', function () {
    $usuario = User::factory()->create(['rol' => 'administrador']);
    $planta = Planta::factory()->create(['stock_actual' => 10, 'stock_minimo' => 0]);

    $movimiento = app(MovimientoInventarioService::class)->createMovimiento([
        'planta_id' => $planta->id,
        'user_id' => $usuario->id,
        'tipo' => 'salida',
        'cantidad' => 4,
    ]);

    expect($movimiento->fresh()->stock_antes)->toBe(10)
        ->and($movimiento->fresh()->stock_despues)->toBe(6)
        ->and($planta->fresh()->stock_actual)->toBe(6);
});

test('motivo es opcional y se guarda cuando se envia', function () {
    $administrador = User::factory()->create(['rol' => 'administrador']);
    $planta = Planta::factory()->create(['stock_actual' => 5, 'stock_minimo' => 0]);

    $this->actingAs($administrador)
        ->post(route('movimientos-inventario.store'), [
            'planta_id' => $planta->id,
            'tipo' => 'entrada',
            'cantidad' => 1,
            'motivo' => 'Reposicion de proveedor',
        ])
        ->assertRedirect(route('movimientos-inventario.index'));

    $this->actingAs($administrador)
        ->post(route('movimientos-inventario.store'), [
            'planta_id' => $planta->id,
            'tipo' => 'entrada',
            'cantidad' => 1,
        ])
        ->assertRedirect(route('movimientos-inventario.index'));

    $movimientos = MovimientoInventario::query()->orderBy('id')->get();

    expect($movimientos)->toHaveCount(2)
        ->and($movimientos[0]->motivo)->toBe('Reposicion de proveedor')
        ->and($movimientos[1]->motivo)->toBeNull();
});

test('motivo de mas de 255 caracteres es rechazado', function () {
    $administrador = User::factory()->create(['rol' => 'administrador']);
    $planta = Planta::factory()->create(['stock_actual' => 5, 'stock_minimo' => 0]);

    $this->actingAs($administrador)
        ->post(route('movimientos-inventario.store'), [
            'planta_id' => $planta->id,
            'tipo' => 'entrada',
            'cantidad' => 1,
            'motivo' => str_repeat('a', 256),
        ])
        ->assertSessionHasErrors('motivo');

    expect(MovimientoInventario::count())->toBe(0)
        ->and($planta->fresh()->stock_actual)->toBe(5);
});

test('stock_antes y stock_despues enviados en el request se ignoran', function () {
    $administrador = User::factory()->create(['rol' => 'administrador']);
    $planta = Planta::factory()->create(['stock_actual' => 5, 'stock_minimo' => 0]);

    $this->actingAs($administrador)
        ->post(route('movimientos-inventario.store'), [
            'planta_id' => $planta->id,
            'tipo' => 'entrada',
            'cantidad' => 2,
            'stock_antes' => 999,
            'stock_despues' => 999,
        ])
        ->assertRedirect(route('movimientos-inventario.index'));

    $movimiento = MovimientoInventario::query()->firstOrFail();

    expect($movimiento->stock_antes)->toBe(5)
        ->and($movimiento->stock_despues)->toBe(7);
});

test('el servicio ignora stock_antes y stock_despues recibidos en los datos', function () {
    $usuario = User::factory()->create(['rol' => 'administrador']);
    $planta = Planta::factory()->create(['stock_actual' => 5, 'stock_minimo' => 0]);

    $movimiento = app(MovimientoInventarioService::class)->createMovimiento([
        'planta_id' => $planta->id,
        'user_id' => $usuario->id,
        'tipo' => 'entrada',
        'cantidad' => 2,
        'stock_antes' => 999,
        'stock_despues' => 999,
    ]);

    expect($movimiento->fresh()->stock_antes)->toBe(5)
        ->and($movimiento->fresh()->stock_despues)->toBe(7);
});

test('registrar una venta guarda stock_antes y stock_despues del movimiento de salida', function () {
    $cliente = MonitoreoFixtures::cliente();
    $vendedor = MonitoreoFixtures::vendedor();
    $planta = Planta::factory()->create(['stock_actual' => 8, 'stock_minimo' => 0]);

    app(VentaService::class)->registrarVenta([
        'user_id' => $cliente->id,
        'planta_id' => $planta->id,
        'cantidad' => 3,
    ], $vendedor);

    $movimiento = MovimientoInventario::query()->firstOrFail();

    expect($movimiento->stock_antes)->toBe(8)
        ->and($movimiento->stock_despues)->toBe(5);
});

test('movimientos historicos con campos NULL no rompen el listado', function () {
    $administrador = User::factory()->create(['rol' => 'administrador']);
    $planta = Planta::factory()->create(['stock_actual' => 5, 'stock_minimo' => 0]);

    MovimientoInventario::create([
        'planta_id' => $planta->id,
        'user_id' => $administrador->id,
        'tipo' => 'entrada',
        'cantidad' => 1,
    ]);

    $movimiento = MovimientoInventario::query()->firstOrFail();

    expect($movimiento->motivo)->toBeNull()
        ->and($movimiento->stock_antes)->toBeNull()
        ->and($movimiento->stock_despues)->toBeNull();

    $this->actingAs($administrador)
        ->get(route('movimientos-inventario.index'))
        ->assertOk();
});
