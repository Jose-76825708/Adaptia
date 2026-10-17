<?php

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

test('actualiza tipo y cantidad ajustando solo la diferencia de stock', function () {
    $usuario = User::factory()->create();
    $planta = Planta::factory()->create(['stock_actual' => 5, 'stock_minimo' => 0]);
    $service = app(MovimientoInventarioService::class);
    $movimiento = $service->createMovimiento(
        datosMovimientoInventario($usuario, $planta, 'entrada', 2)
    );

    $service->updateMovimiento((string) $movimiento->id, ['cantidad' => 4]);
    $service->updateMovimiento((string) $movimiento->id, [
        'tipo' => 'salida',
        'cantidad' => 3,
    ]);

    expect($movimiento->fresh()->tipo)->toBe('salida')
        ->and($movimiento->fresh()->cantidad)->toBe(3)
        ->and($planta->fresh()->stock_actual)->toBe(2);
});

test('revierte la actualización si el nuevo efecto requiere stock insuficiente', function () {
    $usuario = User::factory()->create();
    $planta = Planta::factory()->create(['stock_actual' => 4]);
    $movimiento = MovimientoInventario::create(
        datosMovimientoInventario($usuario, $planta, 'entrada', 3)
    );

    expect(fn () => app(MovimientoInventarioService::class)->updateMovimiento(
        (string) $movimiento->id,
        ['tipo' => 'salida', 'cantidad' => 2]
    ))->toThrow(StockInsuficienteException::class);

    expect($movimiento->fresh()->tipo)->toBe('entrada')
        ->and($movimiento->fresh()->cantidad)->toBe(3)
        ->and($planta->fresh()->stock_actual)->toBe(4);
});

test('elimina entradas y salidas revirtiendo el efecto en el stock', function () {
    $usuario = User::factory()->create();
    $plantaEntrada = Planta::factory()->create(['stock_actual' => 3, 'stock_minimo' => 0]);
    $plantaSalida = Planta::factory()->create(['stock_actual' => 5, 'stock_minimo' => 0]);
    $service = app(MovimientoInventarioService::class);
    $entrada = $service->createMovimiento(
        datosMovimientoInventario($usuario, $plantaEntrada, 'entrada', 2)
    );
    $salida = $service->createMovimiento(
        datosMovimientoInventario($usuario, $plantaSalida, 'salida', 2)
    );

    expect($service->deleteMovimiento((string) $entrada->id))->toBeTrue()
        ->and($plantaEntrada->fresh()->stock_actual)->toBe(3)
        ->and($service->deleteMovimiento((string) $salida->id))->toBeTrue()
        ->and($plantaSalida->fresh()->stock_actual)->toBe(5)
        ->and(MovimientoInventario::count())->toBe(0);
});

test('conserva una entrada cuando no puede revertirla por falta de stock', function () {
    $usuario = User::factory()->create();
    $planta = Planta::factory()->create(['stock_actual' => 1]);
    $movimiento = MovimientoInventario::create(
        datosMovimientoInventario($usuario, $planta, 'entrada', 2)
    );

    expect(fn () => app(MovimientoInventarioService::class)->deleteMovimiento(
        (string) $movimiento->id
    ))->toThrow(StockInsuficienteException::class);

    expect($movimiento->fresh())->not->toBeNull()
        ->and($planta->fresh()->stock_actual)->toBe(1);
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

test('registra advertencia al actualizar un movimiento que deja el stock bajo el mínimo', function () {
    Log::spy();
    $usuario = User::factory()->create();
    $planta = Planta::factory()->create(['stock_actual' => 3, 'stock_minimo' => 3]);
    $movimiento = MovimientoInventario::create(
        datosMovimientoInventario($usuario, $planta, 'salida', 1)
    );

    app(MovimientoInventarioService::class)->updateMovimiento((string) $movimiento->id, [
        'cantidad' => 2,
    ]);

    expect(session('stock_warning'))
        ->toBe("Advertencia: El stock de {$planta->nombre} está bajo el mínimo. Actual: 2, Mínimo: 3");
    Log::shouldHaveReceived('warning')
        ->once()
        ->with('Stock bajo detectado tras actualización de movimiento', Mockery::any());
});

test('registra advertencia al eliminar un movimiento que deja el stock bajo el mínimo', function () {
    Log::spy();
    $usuario = User::factory()->create();
    $planta = Planta::factory()->create(['stock_actual' => 1, 'stock_minimo' => 3]);
    $movimiento = MovimientoInventario::create(
        datosMovimientoInventario($usuario, $planta, 'salida', 1)
    );

    app(MovimientoInventarioService::class)->deleteMovimiento((string) $movimiento->id);

    expect(session('stock_warning'))
        ->toBe("Advertencia: El stock de {$planta->nombre} está bajo el mínimo. Actual: 2, Mínimo: 3");
    Log::shouldHaveReceived('warning')
        ->once()
        ->with('Stock bajo detectado tras eliminación de movimiento', Mockery::any());
});
