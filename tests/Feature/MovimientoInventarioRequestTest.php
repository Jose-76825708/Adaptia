<?php

use App\Models\MovimientoInventario;
use App\Models\Planta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('store ignora user_id del request y guarda Auth::id()', function () {
    $administrador = User::factory()->create(['rol' => 'administrador']);
    $planta = Planta::factory()->create(['stock_actual' => 5, 'stock_minimo' => 0]);

    $this->actingAs($administrador)
        ->post(route('movimientos-inventario.store'), [
            'planta_id' => $planta->id,
            'user_id' => 999,
            'tipo' => 'entrada',
            'cantidad' => 2,
        ])
        ->assertRedirect(route('movimientos-inventario.index'));

    $movimiento = MovimientoInventario::query()->firstOrFail();

    expect($movimiento->user_id)->toBe($administrador->id)
        ->and($movimiento->planta_id)->toBe($planta->id)
        ->and($movimiento->cantidad)->toBe(2);
});

test('update conserva el user_id original del movimiento', function () {
    $vendedor = User::factory()->create(['rol' => 'vendedor']);
    $otroUsuario = User::factory()->create(['rol' => 'administrador']);
    $planta = Planta::factory()->create(['stock_actual' => 7, 'stock_minimo' => 0]);
    $movimiento = MovimientoInventario::create([
        'planta_id' => $planta->id,
        'user_id' => $vendedor->id,
        'tipo' => 'entrada',
        'cantidad' => 2,
    ]);

    $this->actingAs($otroUsuario)
        ->put(route('movimientos-inventario.update', $movimiento), [
            'planta_id' => $planta->id,
            'user_id' => $otroUsuario->id,
            'tipo' => 'salida',
            'cantidad' => 1,
        ])
        ->assertRedirect(route('movimientos-inventario.index'));

    expect($movimiento->fresh()->user_id)->toBe($vendedor->id)
        ->and($movimiento->fresh()->tipo)->toBe('salida')
        ->and($movimiento->fresh()->cantidad)->toBe(1);
});

test('store rechaza datos inválidos del movimiento de inventario', function () {
    $administrador = User::factory()->create(['rol' => 'administrador']);
    $planta = Planta::factory()->create(['stock_actual' => 5, 'stock_minimo' => 0]);

    $this->actingAs($administrador)
        ->from(route('movimientos-inventario.create'))
        ->post(route('movimientos-inventario.store'), [
            'planta_id' => $planta->id,
            'user_id' => $administrador->id,
            'tipo' => 'invalido',
            'cantidad' => 0,
        ])
        ->assertRedirect(route('movimientos-inventario.create'))
        ->assertSessionHasErrors(['tipo', 'cantidad']);
});

test('store devuelve 403 para usuarios no autorizados y redirige a login para invitados', function () {
    $cliente = User::factory()->create(['rol' => 'cliente']);
    $planta = Planta::factory()->create(['stock_actual' => 5, 'stock_minimo' => 0]);

    $this->actingAs($cliente)
        ->post(route('movimientos-inventario.store'), [
            'planta_id' => $planta->id,
            'user_id' => $cliente->id,
            'tipo' => 'entrada',
            'cantidad' => 1,
        ])
        ->assertForbidden();

    auth()->logout();

    $this->post(route('movimientos-inventario.store'), [
        'planta_id' => $planta->id,
        'user_id' => 99,
        'tipo' => 'entrada',
        'cantidad' => 1,
    ])->assertRedirect(route('login'));
});
