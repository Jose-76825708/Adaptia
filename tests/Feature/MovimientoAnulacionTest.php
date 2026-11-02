<?php

use App\Exceptions\MovimientoNoAnulableException;
use App\Exceptions\StockInsuficienteException;
use App\Models\Alerta;
use App\Models\MovimientoInventario;
use App\Models\Planta;
use App\Models\User;
use App\Services\MovimientoInventarioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

test('anular una entrada crea una salida inversa y baja el stock', function () {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $planta = Planta::factory()->create(['stock_actual' => 5, 'stock_minimo' => 0]);
    $service = app(MovimientoInventarioService::class);
    $entrada = $service->createMovimiento([
        'planta_id' => $planta->id, 'user_id' => $admin->id, 'tipo' => 'entrada', 'cantidad' => 4,
    ]);

    $anulacion = $service->anularMovimiento((string) $entrada->id, 'Error de digitación', $admin->id);

    expect($anulacion->tipo)->toBe('salida')
        ->and($anulacion->cantidad)->toBe(4)
        ->and($anulacion->movimiento_original_id)->toBe($entrada->id)
        ->and($planta->fresh()->stock_actual)->toBe(5)
        ->and(MovimientoInventario::count())->toBe(2);
});

test('anular una salida crea una entrada inversa y devuelve el stock', function () {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $planta = Planta::factory()->create(['stock_actual' => 5, 'stock_minimo' => 0]);
    $service = app(MovimientoInventarioService::class);
    $salida = $service->createMovimiento([
        'planta_id' => $planta->id, 'user_id' => $admin->id, 'tipo' => 'salida', 'cantidad' => 2,
    ]);

    $anulacion = $service->anularMovimiento((string) $salida->id, 'Salida duplicada', $admin->id);

    expect($anulacion->tipo)->toBe('entrada')
        ->and($anulacion->cantidad)->toBe(2)
        ->and($planta->fresh()->stock_actual)->toBe(5);
});

test('la anulación guarda stock_antes y stock_despues', function () {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $planta = Planta::factory()->create(['stock_actual' => 5, 'stock_minimo' => 0]);
    $service = app(MovimientoInventarioService::class);
    $entrada = $service->createMovimiento([
        'planta_id' => $planta->id, 'user_id' => $admin->id, 'tipo' => 'entrada', 'cantidad' => 4,
    ]);

    $anulacion = $service->anularMovimiento((string) $entrada->id, 'Error de digitación', $admin->id);

    expect($anulacion->fresh()->stock_antes)->toBe(9)
        ->and($anulacion->fresh()->stock_despues)->toBe(5);
});

test('la anulación guarda el motivo y el usuario que anula', function () {
    $autor = User::factory()->create(['rol' => 'vendedor']);
    $admin = User::factory()->create(['rol' => 'administrador']);
    $planta = Planta::factory()->create(['stock_actual' => 5, 'stock_minimo' => 0]);
    $service = app(MovimientoInventarioService::class);
    $entrada = $service->createMovimiento([
        'planta_id' => $planta->id, 'user_id' => $autor->id, 'tipo' => 'entrada', 'cantidad' => 1,
    ]);

    $anulacion = $service->anularMovimiento((string) $entrada->id, 'Cantidad mal contada', $admin->id);

    expect($anulacion->fresh()->motivo)->toBe('Cantidad mal contada')
        ->and($anulacion->fresh()->user_id)->toBe($admin->id)
        ->and($entrada->fresh()->user_id)->toBe($autor->id);
});

test('no se puede anular dos veces el mismo movimiento', function () {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $planta = Planta::factory()->create(['stock_actual' => 5, 'stock_minimo' => 0]);
    $service = app(MovimientoInventarioService::class);
    $entrada = $service->createMovimiento([
        'planta_id' => $planta->id, 'user_id' => $admin->id, 'tipo' => 'entrada', 'cantidad' => 2,
    ]);
    $service->anularMovimiento((string) $entrada->id, 'Primera anulación', $admin->id);

    expect(fn () => $service->anularMovimiento((string) $entrada->id, 'Segunda anulación', $admin->id))
        ->toThrow(MovimientoNoAnulableException::class, 'El movimiento ya fue anulado.');

    expect(MovimientoInventario::count())->toBe(2)
        ->and($planta->fresh()->stock_actual)->toBe(5);
});

test('no se puede anular una anulación', function () {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $planta = Planta::factory()->create(['stock_actual' => 5, 'stock_minimo' => 0]);
    $service = app(MovimientoInventarioService::class);
    $entrada = $service->createMovimiento([
        'planta_id' => $planta->id, 'user_id' => $admin->id, 'tipo' => 'entrada', 'cantidad' => 2,
    ]);
    $anulacion = $service->anularMovimiento((string) $entrada->id, 'Anulación', $admin->id);

    expect(fn () => $service->anularMovimiento((string) $anulacion->id, 'Anular la anulación', $admin->id))
        ->toThrow(MovimientoNoAnulableException::class);

    expect(MovimientoInventario::count())->toBe(2)
        ->and($planta->fresh()->stock_actual)->toBe(5);
});

test('anular una entrada sin stock suficiente se rechaza y no cambia nada', function () {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $planta = Planta::factory()->create(['stock_actual' => 1, 'stock_minimo' => 0]);
    $entrada = MovimientoInventario::create([
        'planta_id' => $planta->id, 'user_id' => $admin->id, 'tipo' => 'entrada', 'cantidad' => 3,
    ]);

    expect(fn () => app(MovimientoInventarioService::class)
        ->anularMovimiento((string) $entrada->id, 'Corrección', $admin->id))
        ->toThrow(StockInsuficienteException::class);

    expect(MovimientoInventario::count())->toBe(1)
        ->and($planta->fresh()->stock_actual)->toBe(1);
});

test('anular una entrada que deja el stock bajo el mínimo abre una alerta', function () {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $planta = Planta::factory()->create(['stock_actual' => 3, 'stock_minimo' => 4]);
    $service = app(MovimientoInventarioService::class);
    $entrada = $service->createMovimiento([
        'planta_id' => $planta->id, 'user_id' => $admin->id, 'tipo' => 'entrada', 'cantidad' => 3,
    ]);

    expect(Alerta::where('planta_id', $planta->id)->where('tipo', 'stock_bajo')->count())->toBe(0);

    $service->anularMovimiento((string) $entrada->id, 'Entrada errónea', $admin->id);

    expect(Alerta::where('planta_id', $planta->id)->where('tipo', 'stock_bajo')->count())->toBe(1);
});

test('anular una salida que recupera el stock resuelve la alerta abierta', function () {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $planta = Planta::factory()->create(['stock_actual' => 4, 'stock_minimo' => 3]);
    $service = app(MovimientoInventarioService::class);
    $salida = $service->createMovimiento([
        'planta_id' => $planta->id, 'user_id' => $admin->id, 'tipo' => 'salida', 'cantidad' => 2,
    ]);
    $alerta = Alerta::where('planta_id', $planta->id)->where('tipo', 'stock_bajo')->first();

    expect($alerta)->not->toBeNull()
        ->and($alerta->resuelta_en)->toBeNull();

    $service->anularMovimiento((string) $salida->id, 'Salida errónea', $admin->id);

    expect($alerta->fresh()->resuelta_en)->not->toBeNull();
});

test('anular que deja el stock bajo el mínimo registra advertencia y log', function () {
    Log::spy();
    $admin = User::factory()->create(['rol' => 'administrador']);
    $planta = Planta::factory()->create(['stock_actual' => 3, 'stock_minimo' => 4, 'nombre' => 'Monstera']);
    $service = app(MovimientoInventarioService::class);
    $entrada = $service->createMovimiento([
        'planta_id' => $planta->id, 'user_id' => $admin->id, 'tipo' => 'entrada', 'cantidad' => 3,
    ]);

    $service->anularMovimiento((string) $entrada->id, 'Entrada errónea', $admin->id);

    expect(session('stock_warning'))
        ->toBe('Advertencia: El stock de Monstera está bajo el mínimo. Actual: 3, Mínimo: 4');
    Log::shouldHaveReceived('warning')
        ->once()
        ->with('Stock bajo detectado tras anulación de movimiento', Mockery::any());
});

test('el administrador anula un movimiento por HTTP', function () {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $planta = Planta::factory()->create(['stock_actual' => 5, 'stock_minimo' => 0]);
    $entrada = app(MovimientoInventarioService::class)->createMovimiento([
        'planta_id' => $planta->id, 'user_id' => $admin->id, 'tipo' => 'entrada', 'cantidad' => 2,
    ]);

    $this->actingAs($admin)
        ->post(route('movimientos-inventario.anular', $entrada->id), ['motivo' => 'Error de registro'])
        ->assertRedirect(route('movimientos-inventario.index'))
        ->assertSessionHas('success', 'Movimiento anulado correctamente.');

    expect(MovimientoInventario::count())->toBe(2)
        ->and($planta->fresh()->stock_actual)->toBe(5);
});

test('el motivo de anulación es obligatorio', function () {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $planta = Planta::factory()->create(['stock_actual' => 5, 'stock_minimo' => 0]);
    $entrada = app(MovimientoInventarioService::class)->createMovimiento([
        'planta_id' => $planta->id, 'user_id' => $admin->id, 'tipo' => 'entrada', 'cantidad' => 2,
    ]);

    $this->actingAs($admin)
        ->from(route('movimientos-inventario.index'))
        ->post(route('movimientos-inventario.anular', $entrada->id), [])
        ->assertRedirect(route('movimientos-inventario.index'))
        ->assertSessionHasErrors('motivo');

    expect(MovimientoInventario::count())->toBe(1)
        ->and($planta->fresh()->stock_actual)->toBe(7);
});

test('un vendedor no puede anular movimientos', function () {
    $vendedor = User::factory()->create(['rol' => 'vendedor']);
    $planta = Planta::factory()->create(['stock_actual' => 5, 'stock_minimo' => 0]);
    $entrada = MovimientoInventario::create([
        'planta_id' => $planta->id, 'user_id' => $vendedor->id, 'tipo' => 'entrada', 'cantidad' => 1,
    ]);

    $this->actingAs($vendedor)
        ->post(route('movimientos-inventario.anular', $entrada->id), ['motivo' => 'Intento'])
        ->assertForbidden();

    expect(MovimientoInventario::count())->toBe(1);
});

test('un invitado es redirigido a login al intentar anular', function () {
    $this->post('/movimientos-inventario/1/anular', ['motivo' => 'Intento'])
        ->assertRedirect(route('login'));
});

test('anular un movimiento ya anulado por HTTP muestra el error', function () {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $planta = Planta::factory()->create(['stock_actual' => 5, 'stock_minimo' => 0]);
    $service = app(MovimientoInventarioService::class);
    $entrada = $service->createMovimiento([
        'planta_id' => $planta->id, 'user_id' => $admin->id, 'tipo' => 'entrada', 'cantidad' => 2,
    ]);
    $service->anularMovimiento((string) $entrada->id, 'Primera', $admin->id);

    $this->actingAs($admin)
        ->post(route('movimientos-inventario.anular', $entrada->id), ['motivo' => 'Segunda'])
        ->assertRedirect(route('movimientos-inventario.index'))
        ->assertSessionHas('error', 'El movimiento ya fue anulado.');

    expect(MovimientoInventario::count())->toBe(2);
});

test('la ruta de edición, actualización y borrado ya no existe', function (string $nombre) {
    expect(Route::has($nombre))->toBeFalse();
})->with([
    'movimientos-inventario.edit',
    'movimientos-inventario.update',
    'movimientos-inventario.destroy',
]);

test('el listado muestra motivo y stock antes y después', function () {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $planta = Planta::factory()->create(['stock_actual' => 8, 'stock_minimo' => 0]);
    MovimientoInventario::create([
        'planta_id' => $planta->id, 'user_id' => $admin->id, 'tipo' => 'entrada', 'cantidad' => 3,
        'motivo' => 'Reposicion de proveedor', 'stock_antes' => 5, 'stock_despues' => 8,
    ]);

    $this->actingAs($admin)
        ->get(route('movimientos-inventario.index'))
        ->assertOk()
        ->assertSeeText('Reposicion de proveedor')
        ->assertSeeText('5 → 8');
});

test('el listado marca el movimiento anulado y su anulación', function () {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $planta = Planta::factory()->create(['stock_actual' => 5, 'stock_minimo' => 0]);
    $service = app(MovimientoInventarioService::class);
    $entrada = $service->createMovimiento([
        'planta_id' => $planta->id, 'user_id' => $admin->id, 'tipo' => 'entrada', 'cantidad' => 2,
    ]);
    $service->anularMovimiento((string) $entrada->id, 'Error', $admin->id);

    $this->actingAs($admin)
        ->get(route('movimientos-inventario.index'))
        ->assertOk()
        ->assertSeeText('Anulado')
        ->assertSeeText('Anulación del movimiento '.$entrada->id);
});

test('el administrador ve el botón de anular en el listado', function () {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $planta = Planta::factory()->create(['stock_actual' => 5, 'stock_minimo' => 0]);
    $entrada = MovimientoInventario::create([
        'planta_id' => $planta->id, 'user_id' => $admin->id, 'tipo' => 'entrada', 'cantidad' => 1,
    ]);

    $this->actingAs($admin)
        ->get(route('movimientos-inventario.index'))
        ->assertOk()
        ->assertSee(route('movimientos-inventario.anular', $entrada->id));
});

test('el vendedor no ve el botón de anular en el listado', function () {
    $vendedor = User::factory()->create(['rol' => 'vendedor']);
    $planta = Planta::factory()->create(['stock_actual' => 5, 'stock_minimo' => 0]);
    $entrada = MovimientoInventario::create([
        'planta_id' => $planta->id, 'user_id' => $vendedor->id, 'tipo' => 'entrada', 'cantidad' => 1,
    ]);

    $this->actingAs($vendedor)
        ->get(route('movimientos-inventario.index'))
        ->assertOk()
        ->assertDontSee(route('movimientos-inventario.anular', $entrada->id));
});
