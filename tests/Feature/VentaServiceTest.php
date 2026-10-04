<?php

use App\Models\MovimientoInventario;
use App\Models\Planta;
use App\Models\PlantaVendida;
use App\Models\User;
use App\Models\Venta;
use App\Services\VentaService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

function datosVenta(User $cliente, Planta $planta, int $cantidad): array
{
    return [
        'user_id' => $cliente->id,
        'planta_id' => $planta->id,
        'cantidad' => $cantidad,
    ];
}

test('obtiene ventas con sus relaciones y datos de formulario filtrados', function () {
    $cliente = User::factory()->create(['rol' => 'cliente']);
    $vendedor = User::factory()->create(['rol' => 'vendedor']);
    $otroVendedor = User::factory()->create(['rol' => 'vendedor']);
    $plantaDisponible = Planta::factory()->create(['stock_actual' => 4]);
    $plantaAgotada = Planta::factory()->create(['stock_actual' => 0]);

    Venta::create([
        'user_id' => $cliente->id,
        'vendedor_id' => $vendedor->id,
        'planta_id' => $plantaDisponible->id,
        'cantidad' => 1,
    ]);

    $service = app(VentaService::class);
    $ventas = $service->getAll();
    $datosFormulario = $service->getDatosFormulario();

    expect($ventas)->toHaveCount(1)
        ->and($ventas->first()->relationLoaded('cliente'))->toBeTrue()
        ->and($ventas->first()->relationLoaded('vendedor'))->toBeTrue()
        ->and($ventas->first()->relationLoaded('planta'))->toBeTrue()
        ->and($datosFormulario['clientes']->pluck('id')->all())->toBe([$cliente->id])
        ->and($datosFormulario['plantas']->pluck('id')->all())->toBe([$plantaDisponible->id])
        ->and($datosFormulario['plantas']->pluck('id')->contains($plantaAgotada->id))->toBeFalse()
        ->and($datosFormulario['clientes']->pluck('id')->contains($otroVendedor->id))->toBeFalse();
});

test('registra venta, descuenta stock y crea movimiento de salida con el vendedor', function () {
    $cliente = User::factory()->create(['rol' => 'cliente']);
    $vendedor = User::factory()->create(['rol' => 'vendedor']);
    $planta = Planta::factory()->create([
        'stock_actual' => 8,
        'stock_minimo' => 2,
    ]);

    $venta = app(VentaService::class)->registrarVenta(
        datosVenta($cliente, $planta, 3),
        $vendedor
    );

    expect($venta->user_id)->toBe($cliente->id)
        ->and($venta->vendedor_id)->toBe($vendedor->id)
        ->and($venta->planta_id)->toBe($planta->id)
        ->and($venta->cantidad)->toBe(3)
        ->and($planta->fresh()->stock_actual)->toBe(5)
        ->and(PlantaVendida::query()->count())->toBe(3)
        ->and(PlantaVendida::query()->where('venta_id', $venta->id)->count())->toBe(3)
        ->and(PlantaVendida::query()->where('user_id', $cliente->id)->count())->toBe(3)
        ->and(PlantaVendida::query()->whereNotNull('sensor_id')->count())->toBe(0)
        ->and(MovimientoInventario::query()->first()->only([
            'planta_id',
            'user_id',
            'tipo',
            'cantidad',
        ]))->toBe([
            'planta_id' => $planta->id,
            'user_id' => $vendedor->id,
            'tipo' => 'salida',
            'cantidad' => 3,
        ]);
});

test('rechaza registrar la venta para un usuario que no sea cliente', function () {
    $usuario = User::factory()->create(['rol' => 'vendedor']);
    $vendedor = User::factory()->create(['rol' => 'vendedor']);
    $planta = Planta::factory()->create(['stock_actual' => 8]);

    expect(fn () => app(VentaService::class)->registrarVenta(
        datosVenta($usuario, $planta, 2),
        $vendedor
    ))->toThrow(ValidationException::class);

    expect(Venta::count())->toBe(0)
        ->and(MovimientoInventario::count())->toBe(0)
        ->and($planta->fresh()->stock_actual)->toBe(8);
});

test('rechaza la venta cuando no hay stock suficiente sin modificar datos', function () {
    $cliente = User::factory()->create(['rol' => 'cliente']);
    $vendedor = User::factory()->create(['rol' => 'vendedor']);
    $planta = Planta::factory()->create(['stock_actual' => 1]);

    expect(fn () => app(VentaService::class)->registrarVenta(
        datosVenta($cliente, $planta, 2),
        $vendedor
    ))->toThrow(ValidationException::class);

    expect(Venta::count())->toBe(0)
        ->and(MovimientoInventario::count())->toBe(0)
        ->and($planta->fresh()->stock_actual)->toBe(1);
});

test('revierte venta y stock si falla la creación del movimiento', function () {
    $cliente = User::factory()->create(['rol' => 'cliente']);
    $vendedor = User::factory()->create(['rol' => 'vendedor']);
    $planta = Planta::factory()->create(['stock_actual' => 8]);

    DB::listen(function (QueryExecuted $query) {
        if (str_contains(strtolower($query->sql), 'insert into "movimientos_inventario"')) {
            throw new RuntimeException('Fallo simulado al crear movimiento.');
        }
    });

    expect(fn () => app(VentaService::class)->registrarVenta(
        datosVenta($cliente, $planta, 3),
        $vendedor
    ))->toThrow(RuntimeException::class, 'Fallo simulado al crear movimiento.');

    expect(Venta::count())->toBe(0)
        ->and(MovimientoInventario::count())->toBe(0)
        ->and(PlantaVendida::count())->toBe(0)
        ->and($planta->fresh()->stock_actual)->toBe(8);
});

test('revierte venta, stock y movimiento si falla la creación de una unidad vendida', function () {
    $cliente = User::factory()->create(['rol' => 'cliente']);
    $vendedor = User::factory()->create(['rol' => 'vendedor']);
    $planta = Planta::factory()->create(['stock_actual' => 8]);

    DB::listen(function (QueryExecuted $query) {
        if (str_contains(strtolower($query->sql), 'insert into "plantas_vendidas"')) {
            throw new RuntimeException('Fallo simulado al crear planta vendida.');
        }
    });

    expect(fn () => app(VentaService::class)->registrarVenta(
        datosVenta($cliente, $planta, 3),
        $vendedor
    ))->toThrow(RuntimeException::class, 'Fallo simulado al crear planta vendida.');

    expect(Venta::count())->toBe(0)
        ->and(MovimientoInventario::count())->toBe(0)
        ->and(PlantaVendida::count())->toBe(0)
        ->and($planta->fresh()->stock_actual)->toBe(8);
});

test('solo un vendedor puede invocar el registro de venta', function () {
    $cliente = User::factory()->create(['rol' => 'cliente']);
    $usuario = User::factory()->create(['rol' => 'administrador']);
    $planta = Planta::factory()->create(['stock_actual' => 8]);

    expect(fn () => app(VentaService::class)->registrarVenta(
        datosVenta($cliente, $planta, 2),
        $usuario
    ))->toThrow(AuthorizationException::class);
});

test('emite advertencia cuando la venta deja el stock bajo el mínimo', function () {
    $cliente = User::factory()->create(['rol' => 'cliente']);
    $vendedor = User::factory()->create(['rol' => 'vendedor']);
    $planta = Planta::factory()->create([
        'stock_actual' => 3,
        'stock_minimo' => 2,
    ]);

    app(VentaService::class)->registrarVenta(
        datosVenta($cliente, $planta, 2),
        $vendedor
    );

    expect(session()->get('stock_warning'))
        ->toBe("Advertencia: El stock de {$planta->nombre} está bajo el mínimo. Actual: 1, Mínimo: 2");
});
