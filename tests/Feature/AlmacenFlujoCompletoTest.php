<?php

use App\Models\Alerta;
use App\Models\MovimientoInventario;
use App\Models\Planta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
 * INT-017-BOLT-001: pruebas del flujo del almacén.
 * Solo prueba comportamiento existente (no modifica código de producción).
 * 1) Dos flujos completos por HTTP que cruzan movimientos, ventas, alertas,
 *    listado y panel resumen.
 * 2) Una matriz de acceso rol x ruta.
 */

test('flujo completo: venta, alerta, panel, anulación y resolución de la alerta', function () {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $vendedor = User::factory()->create(['rol' => 'vendedor']);
    $cliente = User::factory()->create(['rol' => 'cliente']);
    $planta = Planta::factory()->create(['nombre' => 'Monstera', 'stock_actual' => 6, 'stock_minimo' => 4]);

    // 1. El vendedor registra una salida manual de 1 (6 -> 5): sin alerta.
    $this->actingAs($vendedor)
        ->post(route('movimientos-inventario.store'), [
            'planta_id' => $planta->id,
            'tipo' => 'salida',
            'cantidad' => 1,
            'motivo' => 'Muestra para exhibición',
        ])
        ->assertRedirect(route('movimientos-inventario.index'));

    expect($planta->fresh()->stock_actual)->toBe(5)
        ->and(Alerta::count())->toBe(0);

    // 2. El vendedor vende 2 (5 -> 3): queda bajo el mínimo y se abre la alerta.
    $this->post(route('ventas.store'), [
        'user_id' => $cliente->id,
        'planta_id' => $planta->id,
        'cantidad' => 2,
    ])->assertRedirect(route('ventas.index'));

    $alerta = Alerta::where('planta_id', $planta->id)->where('tipo', 'stock_bajo')->firstOrFail();

    expect($planta->fresh()->stock_actual)->toBe(3)
        ->and($alerta->resuelta_en)->toBeNull()
        ->and($alerta->leida)->toBeFalse()
        ->and(MovimientoInventario::count())->toBe(2);

    // 3. El panel (vendedor) refleja la situación.
    $this->get(route('almacen.resumen'))
        ->assertOk()
        ->assertViewHas('resumen', fn (array $resumen) => $resumen['stock_total'] === 3
            && $resumen['plantas_bajo_minimo'] === 1
            && $resumen['alertas_abiertas'] === 1
            && $resumen['alertas_no_leidas'] === 1
            && $resumen['movimientos_recientes']->count() === 2);

    // 4. El listado filtra por tipo.
    expect($this->get(route('movimientos-inventario.index', ['tipo' => 'salida']))
        ->assertOk()->viewData('movimientos')->total())->toBe(2);
    expect($this->get(route('movimientos-inventario.index', ['tipo' => 'entrada']))
        ->assertOk()->viewData('movimientos')->total())->toBe(0);

    // 5. El vendedor no puede anular.
    $manual = MovimientoInventario::where('motivo', 'Muestra para exhibición')->firstOrFail();
    $this->post(route('movimientos-inventario.anular', $manual->id), ['motivo' => 'Intento'])
        ->assertForbidden();

    // 6. El administrador ve la alerta y la marca como leída (no se resuelve).
    $this->actingAs($admin)
        ->get(route('alertas-stock.index'))
        ->assertOk();

    $this->patch(route('alertas-stock.marcar-leida', $alerta->id))
        ->assertRedirect(route('alertas-stock.index', ['estado' => 'abiertas']));

    expect($alerta->fresh()->leida)->toBeTrue()
        ->and($alerta->fresh()->resuelta_en)->toBeNull();

    // 7. El administrador anula la salida manual (3 -> 4): ya no es menor que el mínimo.
    $this->post(route('movimientos-inventario.anular', $manual->id), ['motivo' => 'Salida registrada por error'])
        ->assertRedirect(route('movimientos-inventario.index'))
        ->assertSessionHas('success', 'Movimiento anulado correctamente.');

    expect($planta->fresh()->stock_actual)->toBe(4)
        ->and($alerta->fresh()->resuelta_en)->not->toBeNull()
        ->and($alerta->fresh()->leida)->toBeTrue()
        ->and(MovimientoInventario::count())->toBe(3);

    // 8. El panel queda sin alertas abiertas ni plantas bajo el mínimo.
    $this->get(route('almacen.resumen'))
        ->assertOk()
        ->assertViewHas('resumen', fn (array $resumen) => $resumen['stock_total'] === 4
            && $resumen['plantas_bajo_minimo'] === 0
            && $resumen['alertas_abiertas'] === 0
            && $resumen['movimientos_recientes']->count() === 3);

    // 9. No se puede anular dos veces el mismo movimiento.
    $this->post(route('movimientos-inventario.anular', $manual->id), ['motivo' => 'Otra vez'])
        ->assertRedirect(route('movimientos-inventario.index'))
        ->assertSessionHas('error', 'El movimiento ya fue anulado.');

    expect(MovimientoInventario::count())->toBe(3);
});

test('flujo completo: una entrada recupera el stock y resuelve la alerta', function () {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $vendedor = User::factory()->create(['rol' => 'vendedor']);
    $planta = Planta::factory()->create(['nombre' => 'Ficus', 'stock_actual' => 6, 'stock_minimo' => 5]);

    // El vendedor registra una salida de 2 (6 -> 4): se abre la alerta.
    $this->actingAs($vendedor)
        ->post(route('movimientos-inventario.store'), [
            'planta_id' => $planta->id,
            'tipo' => 'salida',
            'cantidad' => 2,
        ])
        ->assertRedirect(route('movimientos-inventario.index'));

    $alerta = Alerta::where('planta_id', $planta->id)->where('tipo', 'stock_bajo')->firstOrFail();
    expect($alerta->resuelta_en)->toBeNull();

    // El administrador registra una entrada de 3 (4 -> 7): la alerta se resuelve sola.
    $this->actingAs($admin)
        ->post(route('movimientos-inventario.store'), [
            'planta_id' => $planta->id,
            'tipo' => 'entrada',
            'cantidad' => 3,
            'motivo' => 'Reposición de proveedor',
        ])
        ->assertRedirect(route('movimientos-inventario.index'));

    expect($planta->fresh()->stock_actual)->toBe(7)
        ->and($alerta->fresh()->resuelta_en)->not->toBeNull();

    $this->get(route('almacen.resumen'))
        ->assertOk()
        ->assertViewHas('resumen', fn (array $resumen) => $resumen['stock_total'] === 7
            && $resumen['plantas_bajo_minimo'] === 0
            && $resumen['alertas_abiertas'] === 0
            && $resumen['movimientos_recientes']->count() === 2);

    expect($this->get(route('alertas-stock.index', ['estado' => 'abiertas']))
        ->assertOk()->viewData('alertas')->count())->toBe(0);
    expect($this->get(route('alertas-stock.index', ['estado' => 'resueltas']))
        ->assertOk()->viewData('alertas')->count())->toBe(1);
});

dataset('matriz_almacen', function () {
    $permisos = [
        'movimientos-inventario.index' => ['GET', ['administrador', 'vendedor']],
        'movimientos-inventario.create' => ['GET', ['administrador', 'vendedor']],
        'movimientos-inventario.store' => ['POST', ['administrador', 'vendedor']],
        'movimientos-inventario.anular' => ['POST', ['administrador']],
        'almacen.resumen' => ['GET', ['administrador', 'vendedor']],
        'alertas-stock.index' => ['GET', ['administrador']],
        'alertas-stock.marcar-leida' => ['PATCH', ['administrador']],
        'ventas.index' => ['GET', ['vendedor']],
        'ventas.create' => ['GET', ['vendedor']],
        'ventas.store' => ['POST', ['vendedor']],
    ];

    foreach ($permisos as $ruta => [$metodo, $permitidos]) {
        foreach (['invitado', 'cliente', 'vendedor', 'administrador'] as $rol) {
            $esperado = in_array($rol, $permitidos, true)
                ? 'permitido'
                : ($rol === 'invitado' ? 'login' : '403');

            yield "{$rol} {$metodo} {$ruta}" => [$rol, $ruta, $metodo, $esperado];
        }
    }
});

test('matriz de acceso por rol al almacén', function (string $rol, string $ruta, string $metodo, string $esperado) {
    $planta = Planta::factory()->create(['stock_actual' => 10, 'stock_minimo' => 0]);
    $clienteVenta = User::factory()->create(['rol' => 'cliente']);
    $autor = User::factory()->create(['rol' => 'administrador']);
    $movimiento = MovimientoInventario::create([
        'planta_id' => $planta->id,
        'user_id' => $autor->id,
        'tipo' => 'entrada',
        'cantidad' => 1,
    ]);
    $alerta = Alerta::create([
        'planta_id' => $planta->id,
        'tipo' => 'stock_bajo',
        'variable' => 'stock',
        'valor_medido' => 1,
        'limite' => 5,
        'leida' => false,
    ]);

    $parametros = match ($ruta) {
        'movimientos-inventario.anular' => ['id' => $movimiento->id],
        'alertas-stock.marcar-leida' => ['alerta' => $alerta->id],
        default => [],
    };

    $datos = match ($ruta) {
        'movimientos-inventario.store' => ['planta_id' => $planta->id, 'tipo' => 'entrada', 'cantidad' => 1],
        'movimientos-inventario.anular' => ['motivo' => 'Prueba de matriz'],
        'ventas.store' => ['user_id' => $clienteVenta->id, 'planta_id' => $planta->id, 'cantidad' => 1],
        default => [],
    };

    if ($rol !== 'invitado') {
        $this->actingAs(User::factory()->create(['rol' => $rol]));
    }

    $respuesta = $this->call($metodo, route($ruta, $parametros), $datos);

    match ($esperado) {
        'login' => $respuesta->assertRedirect(route('login')),
        '403' => $respuesta->assertForbidden(),
        default => $metodo === 'GET'
            ? $respuesta->assertOk()
            : expect($respuesta->isRedirect() && $respuesta->headers->get('Location') !== route('login'))->toBeTrue(),
    };
})->with('matriz_almacen');
