<?php

use App\Models\Alerta;
use App\Exceptions\StockInsuficienteException;
use App\Models\MovimientoInventario;
use App\Models\Planta;
use App\Models\User;
use App\Services\MovimientoInventarioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;

uses(RefreshDatabase::class);

function datosMovimientoInventario(User $usuario, Planta $planta, string $tipo, int $cantidad): array
{
    return [
        'planta_id' => $planta->id,
        'user_id' => $usuario->id,
        'tipo' => $tipo,
        'cantidad' => $cantidad,
    ];
}

test('obtiene movimientos con sus relaciones', function () {
    $usuario = User::factory()->create();
    $planta = Planta::factory()->create();
    $movimiento = MovimientoInventario::create(
        datosMovimientoInventario($usuario, $planta, 'entrada', 2)
    );

    $service = app(MovimientoInventarioService::class);
    $movimientos = $service->getAll();
    $encontrado = $service->getById((string) $movimiento->id);

    expect($movimientos)->toHaveCount(1)
        ->and($movimientos->first()->relationLoaded('planta'))->toBeTrue()
        ->and($movimientos->first()->relationLoaded('user'))->toBeTrue()
        ->and($encontrado->relationLoaded('planta'))->toBeTrue()
        ->and($encontrado->relationLoaded('user'))->toBeTrue();
});

test('crea entradas y salidas actualizando el stock', function () {
    $usuario = User::factory()->create();
    $planta = Planta::factory()->create(['stock_actual' => 4, 'stock_minimo' => 1]);
    $service = app(MovimientoInventarioService::class);

    $entrada = $service->createMovimiento(
        datosMovimientoInventario($usuario, $planta, 'entrada', 3)
    );
    $salida = $service->createMovimiento(
        datosMovimientoInventario($usuario, $planta, 'salida', 2)
    );

    expect($planta->fresh()->stock_actual)->toBe(5)
        ->and($entrada->tipo)->toBe('entrada')
        ->and($salida->tipo)->toBe('salida')
        ->and(MovimientoInventario::count())->toBe(2);
});

test('revierte la creación si una salida excede el stock disponible', function () {
    $usuario = User::factory()->create();
    $planta = Planta::factory()->create(['stock_actual' => 2]);

    expect(fn () => app(MovimientoInventarioService::class)->createMovimiento(
        datosMovimientoInventario($usuario, $planta, 'salida', 3)
    ))->toThrow(StockInsuficienteException::class);

    expect(MovimientoInventario::count())->toBe(0)
        ->and($planta->fresh()->stock_actual)->toBe(2);
});

test('registra advertencia y log cuando una entrada deja el stock bajo el mínimo', function () {
    Log::spy();
    $usuario = User::factory()->create();
    $planta = Planta::factory()->create([
        'stock_actual' => 1,
        'stock_minimo' => 3,
        'nombre' => 'Monstera',
    ]);

    app(MovimientoInventarioService::class)->createMovimiento(
        datosMovimientoInventario($usuario, $planta, 'entrada', 1)
    );

    expect(session('stock_warning'))
        ->toBe('Advertencia: El stock de Monstera está bajo el mínimo. Actual: 2, Mínimo: 3');
    Log::shouldHaveReceived('warning')
        ->once()
        ->with('Stock bajo detectado tras movimiento de inventario', Mockery::any());
});

test('genera alerta persistente al registrar una entrada bajo el mínimo', function () {
    Log::spy();
    $usuario = User::factory()->create();
    $planta = Planta::factory()->create([
        'stock_actual' => 1,
        'stock_minimo' => 3,
        'nombre' => 'Monstera',
    ]);

    app(MovimientoInventarioService::class)->createMovimiento(
        datosMovimientoInventario($usuario, $planta, 'entrada', 1)
    );

    expect(session('stock_warning'))
        ->toBe('Advertencia: El stock de Monstera está bajo el mínimo. Actual: 2, Mínimo: 3')
        ->and(Alerta::where('planta_id', $planta->id)->where('tipo', 'stock_bajo')->count())->toBe(1);
    Log::shouldHaveReceived('warning')
        ->once()
        ->with('Stock bajo detectado tras movimiento de inventario', Mockery::any());
});

test('no duplica alertas en dos bajas consecutivas sobre el mismo stock crítico', function () {
    $usuario = User::factory()->create();
    $planta = Planta::factory()->create(['stock_actual' => 3, 'stock_minimo' => 2]);

    app(MovimientoInventarioService::class)->createMovimiento(
        datosMovimientoInventario($usuario, $planta, 'salida', 2)
    );
    app(MovimientoInventarioService::class)->createMovimiento(
        datosMovimientoInventario($usuario, $planta, 'salida', 1)
    );

    expect(Alerta::where('planta_id', $planta->id)->where('tipo', 'stock_bajo')->count())->toBe(1);
});
