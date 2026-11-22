<?php

use App\Models\LoteProduccion;
use App\Models\MovimientoInventario;
use App\Models\Planta;
use App\Models\User;
use App\Services\ProduccionVegetalService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

function prepararLoteEnCuidados(User $admin, Planta $planta, int $sembradas = 10): LoteProduccion
{
    $s = app(ProduccionVegetalService::class);
    $lote = $s->crear($admin, ['motivo' => 'stock_bajo', 'cantidad_meta' => 10]);
    $lote = $s->avanzar($lote, $admin, 'especie_seleccionada', ['planta_id' => $planta->id]);
    $lote = $s->avanzar($lote, $admin, 'material_seleccionado', ['metodo_propagacion' => 'division', 'cantidad_material' => 10]);
    $lote = $s->avanzar($lote, $admin, 'sustrato_preparado', ['sustrato_mezcla' => 'Tierra negra, arena y humus']);
    $lote = $s->avanzar($lote, $admin, 'propagacion_realizada', ['fecha_propagacion' => today()->toDateString()]);
    $lote = $s->avanzar($lote, $admin, 'plantas_colocadas', ['cantidad_sembrada' => $sembradas]);

    return $s->avanzar($lote, $admin, 'en_cuidados');
}

function registrarCuidadoRiego(LoteProduccion $lote, User $admin): void
{
    app(ProduccionVegetalService::class)->registrarCuidado($lote, $admin, [
        'tipo' => 'riego',
        'fecha' => today()->toDateString(),
    ]);
}

it('recorre el flujo completo y envía las plantas producidas a inventario', function () {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $planta = Planta::factory()->create(['stock_actual' => 2, 'stock_minimo' => 5]);
    $s = app(ProduccionVegetalService::class);

    $lote = prepararLoteEnCuidados($admin, $planta);
    expect($lote->codigo)->toStartWith('PRO-' . now()->format('Y') . '-');

    registrarCuidadoRiego($lote, $admin);
    $lote = $s->avanzar($lote->fresh(), $admin, 'en_revision');
    $s->registrarRevision($lote, $admin, ['apta' => 1, 'plantas_perdidas' => 1]);
    $lote = $s->clasificar($lote->fresh(), $admin, [
        'cantidad_categoria_a' => 6,
        'cantidad_categoria_b' => 2,
        'cantidad_descarte' => 1,
    ]);
    expect($lote->estado)->toBe('clasificada');

    $lote = $s->cerrar($lote, $admin);

    expect($lote->estado)->toBe('cerrado')
        ->and($lote->cantidad_producida)->toBe(8)
        ->and($lote->movimiento_inventario_id)->not->toBeNull();
    expect($planta->fresh()->stock_actual)->toBe(10);

    $movimiento = MovimientoInventario::find($lote->movimiento_inventario_id);
    expect($movimiento->tipo)->toBe('entrada')
        ->and($movimiento->cantidad)->toBe(8)
        ->and($movimiento->planta_id)->toBe($planta->id);
});

it('devuelve el lote a cuidados cuando la revisión no está apta', function () {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $planta = Planta::factory()->create();
    $s = app(ProduccionVegetalService::class);

    $lote = prepararLoteEnCuidados($admin, $planta);
    registrarCuidadoRiego($lote, $admin);
    $lote = $s->avanzar($lote->fresh(), $admin, 'en_revision');

    $s->registrarRevision($lote, $admin, ['apta' => 0, 'plantas_perdidas' => 2]);
    $lote = $lote->fresh();

    expect($lote->estado)->toBe('en_cuidados')
        ->and($lote->cantidad_perdida)->toBe(2)
        ->and($lote->revisiones)->toHaveCount(1);
});

it('no deja enviar a revisión sin cuidados registrados', function () {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $lote = prepararLoteEnCuidados($admin, Planta::factory()->create());

    app(ProduccionVegetalService::class)->avanzar($lote, $admin, 'en_revision');
})->throws(InvalidArgumentException::class);

it('rechaza una clasificación que no suma las plantas vivas', function () {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $s = app(ProduccionVegetalService::class);
    $lote = prepararLoteEnCuidados($admin, Planta::factory()->create());
    registrarCuidadoRiego($lote, $admin);
    $lote = $s->avanzar($lote->fresh(), $admin, 'en_revision');
    $s->registrarRevision($lote, $admin, ['apta' => 1]);

    $s->clasificar($lote->fresh(), $admin, [
        'cantidad_categoria_a' => 5,
        'cantidad_categoria_b' => 2,
        'cantidad_descarte' => 1,
    ]);
})->throws(InvalidArgumentException::class);

it('no permite clasificar si la última revisión no fue apta', function () {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $s = app(ProduccionVegetalService::class);
    $lote = prepararLoteEnCuidados($admin, Planta::factory()->create());
    registrarCuidadoRiego($lote, $admin);
    $lote = $s->avanzar($lote->fresh(), $admin, 'en_revision');
    $s->registrarRevision($lote, $admin, ['apta' => 0]);

    $s->clasificar($lote->fresh(), $admin, [
        'cantidad_categoria_a' => 5,
        'cantidad_categoria_b' => 3,
        'cantidad_descarte' => 2,
    ]);
})->throws(InvalidArgumentException::class);

it('no envía a inventario dos veces el mismo lote', function () {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $planta = Planta::factory()->create(['stock_actual' => 0]);
    $s = app(ProduccionVegetalService::class);
    $lote = prepararLoteEnCuidados($admin, $planta);
    registrarCuidadoRiego($lote, $admin);
    $lote = $s->avanzar($lote->fresh(), $admin, 'en_revision');
    $s->registrarRevision($lote, $admin, ['apta' => 1]);
    $lote = $s->clasificar($lote->fresh(), $admin, [
        'cantidad_categoria_a' => 7,
        'cantidad_categoria_b' => 2,
        'cantidad_descarte' => 1,
    ]);
    $s->cerrar($lote, $admin);

    expect(fn () => $s->cerrar($lote, $admin))->toThrow(InvalidArgumentException::class);
    expect($planta->fresh()->stock_actual)->toBe(9);
    expect(MovimientoInventario::count())->toBe(1);
});

it('cierra sin movimiento cuando no hay plantas vendibles', function () {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $planta = Planta::factory()->create(['stock_actual' => 3]);
    $s = app(ProduccionVegetalService::class);
    $lote = prepararLoteEnCuidados($admin, $planta);
    registrarCuidadoRiego($lote, $admin);
    $lote = $s->avanzar($lote->fresh(), $admin, 'en_revision');
    $s->registrarRevision($lote, $admin, ['apta' => 1]);
    $lote = $s->clasificar($lote->fresh(), $admin, [
        'cantidad_categoria_a' => 0,
        'cantidad_categoria_b' => 0,
        'cantidad_descarte' => 10,
    ]);

    $lote = $s->cerrar($lote, $admin);

    expect($lote->estado)->toBe('cerrado')->and($lote->movimiento_inventario_id)->toBeNull();
    expect($planta->fresh()->stock_actual)->toBe(3);
});

it('valida los datos de cada etapa y el orden de las transiciones', function () {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $s = app(ProduccionVegetalService::class);
    $lote = $s->crear($admin, ['motivo' => 'pedido', 'cantidad_meta' => 5]);

    expect(fn () => $s->avanzar($lote, $admin, 'especie_seleccionada', ['planta_id' => 9999]))
        ->toThrow(ValidationException::class);
    expect(fn () => $s->avanzar($lote, $admin, 'plantas_colocadas', ['cantidad_sembrada' => 5]))
        ->toThrow(InvalidArgumentException::class);
    expect(fn () => $s->crear($admin, ['motivo' => 'otro', 'cantidad_meta' => 0]))
        ->toThrow(ValidationException::class);
});

it('solo el administrador opera el macroproceso en el servicio', function () {
    $s = app(ProduccionVegetalService::class);

    foreach (['vendedor', 'cliente'] as $rol) {
        $usuario = User::factory()->create(['rol' => $rol]);
        expect(fn () => $s->crear($usuario, ['motivo' => 'otro', 'cantidad_meta' => 5]))
            ->toThrow(AuthorizationException::class);
    }
});

it('protege las rutas para visitantes, clientes y vendedores', function () {
    $this->get(route('produccion-vegetal.lotes.index'))->assertRedirect(route('login'));

    foreach (['cliente', 'vendedor'] as $rol) {
        $usuario = User::factory()->create(['rol' => $rol]);
        $this->actingAs($usuario)->get(route('produccion-vegetal.lotes.index'))->assertForbidden();
        $this->actingAs($usuario)->get(route('produccion-vegetal.lotes.create'))->assertForbidden();
    }
});

it('permite al administrador crear un lote y avanzarlo desde la interfaz', function () {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $planta = Planta::factory()->create(['stock_actual' => 1, 'stock_minimo' => 4]);

    $this->actingAs($admin)
        ->get(route('produccion-vegetal.lotes.create'))
        ->assertOk()
        ->assertSee('Identificar necesidad')
        ->assertSee($planta->nombre);

    $this->actingAs($admin)
        ->post(route('produccion-vegetal.lotes.store'), ['motivo' => 'stock_bajo', 'cantidad_meta' => 12])
        ->assertRedirect();

    $lote = LoteProduccion::firstOrFail();

    $this->actingAs($admin)
        ->get(route('produccion-vegetal.lotes.index'))
        ->assertOk()
        ->assertSee($lote->codigo);

    $this->actingAs($admin)
        ->post(route('produccion-vegetal.lotes.avanzar', $lote), [
            'destino' => 'especie_seleccionada',
            'planta_id' => $planta->id,
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->actingAs($admin)
        ->get(route('produccion-vegetal.lotes.show', $lote))
        ->assertOk()
        ->assertSee('Especie seleccionada')
        ->assertSee('Seleccionar material');

    // Un salto de etapa se rechaza con mensaje de error, sin romper la página.
    $this->actingAs($admin)
        ->post(route('produccion-vegetal.lotes.cerrar', $lote))
        ->assertSessionHasErrors('produccion');
});
