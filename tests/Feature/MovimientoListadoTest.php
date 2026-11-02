<?php

use App\Models\MovimientoInventario;
use App\Models\Planta;
use App\Models\User;
use App\Services\MovimientoInventarioService;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function crearMovimientoListado(
    Planta $planta,
    string $tipo = 'entrada',
    string $fecha = '2026-10-15 10:00:00',
    ?User $usuario = null,
): MovimientoInventario {
    $usuario ??= User::factory()->create(['rol' => 'administrador']);

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

function idsListados($respuesta): array
{
    return $respuesta->viewData('movimientos')->getCollection()->pluck('id')->all();
}

test('administrador y vendedor acceden al listado paginado', function (string $rol) {
    $usuario = User::factory()->create(['rol' => $rol]);

    $respuesta = $this->actingAs($usuario)->get(route('movimientos-inventario.index'));

    $respuesta->assertOk()->assertViewIs('movimientos-inventario.index');
    expect($respuesta->viewData('movimientos'))->toBeInstanceOf(LengthAwarePaginator::class);
})->with(['administrador', 'vendedor']);

test('cliente recibe 403 e invitado es enviado al login', function () {
    $cliente = User::factory()->create(['rol' => 'cliente']);

    $this->actingAs($cliente)->get(route('movimientos-inventario.index'))->assertForbidden();

    auth()->logout();

    $this->get(route('movimientos-inventario.index'))->assertRedirect(route('login'));
});

test('pagina de a 15 movimientos y muestra los más recientes primero', function () {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $planta = Planta::factory()->create();

    foreach (range(1, 17) as $dia) {
        crearMovimientoListado($planta, 'entrada', sprintf('2026-10-%02d 09:00:00', $dia), $admin);
    }

    $primera = $this->actingAs($admin)->get(route('movimientos-inventario.index'));
    $paginador = $primera->viewData('movimientos');

    expect($paginador->total())->toBe(17)
        ->and($paginador->perPage())->toBe(15)
        ->and($paginador->count())->toBe(15)
        ->and($paginador->first()->created_at->format('Y-m-d'))->toBe('2026-10-17')
        ->and($paginador->last()->created_at->format('Y-m-d'))->toBe('2026-10-03');

    $segunda = $this->actingAs($admin)->get(route('movimientos-inventario.index', ['page' => 2]));

    expect($segunda->viewData('movimientos')->count())->toBe(2)
        ->and($segunda->viewData('movimientos')->first()->created_at->format('Y-m-d'))->toBe('2026-10-02');
});

test('sin filtros lista todos los movimientos', function () {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $planta = Planta::factory()->create();
    crearMovimientoListado($planta, 'entrada');
    crearMovimientoListado($planta, 'salida');

    $respuesta = $this->actingAs($admin)->get(route('movimientos-inventario.index'));

    expect($respuesta->viewData('movimientos')->total())->toBe(2);
});

test('filtra por planta', function () {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $rosa = Planta::factory()->create(['nombre' => 'Rosa']);
    $cactus = Planta::factory()->create(['nombre' => 'Cactus']);
    $deRosa = crearMovimientoListado($rosa);
    crearMovimientoListado($cactus);

    $respuesta = $this->actingAs($admin)
        ->get(route('movimientos-inventario.index', ['planta_id' => $rosa->id]));

    expect(idsListados($respuesta))->toBe([$deRosa->id]);
});

test('filtra por tipo de movimiento', function (string $tipo) {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $planta = Planta::factory()->create();
    $entrada = crearMovimientoListado($planta, 'entrada');
    $salida = crearMovimientoListado($planta, 'salida');
    $esperado = $tipo === 'entrada' ? $entrada : $salida;

    $respuesta = $this->actingAs($admin)
        ->get(route('movimientos-inventario.index', ['tipo' => $tipo]));

    expect(idsListados($respuesta))->toBe([$esperado->id]);
})->with(['entrada', 'salida']);

test('el filtro desde incluye todo el día indicado y excluye los anteriores', function () {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $planta = Planta::factory()->create();
    crearMovimientoListado($planta, 'entrada', '2026-10-04 23:59:59');
    $inicioDia = crearMovimientoListado($planta, 'entrada', '2026-10-05 00:00:00');
    $posterior = crearMovimientoListado($planta, 'entrada', '2026-10-06 12:00:00');

    $respuesta = $this->actingAs($admin)
        ->get(route('movimientos-inventario.index', ['desde' => '2026-10-05']));

    expect(idsListados($respuesta))->toBe([$posterior->id, $inicioDia->id]);
});

test('el filtro hasta incluye todo el día indicado y excluye los posteriores', function () {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $planta = Planta::factory()->create();
    $anterior = crearMovimientoListado($planta, 'entrada', '2026-10-04 08:00:00');
    $finDia = crearMovimientoListado($planta, 'entrada', '2026-10-05 23:59:59');
    crearMovimientoListado($planta, 'entrada', '2026-10-06 00:00:00');

    $respuesta = $this->actingAs($admin)
        ->get(route('movimientos-inventario.index', ['hasta' => '2026-10-05']));

    expect(idsListados($respuesta))->toBe([$finDia->id, $anterior->id]);
});

test('combina planta, tipo y rango de fechas con AND', function () {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $rosa = Planta::factory()->create();
    $cactus = Planta::factory()->create();
    $buscado = crearMovimientoListado($rosa, 'salida', '2026-10-10 10:00:00');
    crearMovimientoListado($rosa, 'entrada', '2026-10-10 10:00:00');
    crearMovimientoListado($cactus, 'salida', '2026-10-10 10:00:00');
    crearMovimientoListado($rosa, 'salida', '2026-09-01 10:00:00');
    crearMovimientoListado($rosa, 'salida', '2026-11-01 10:00:00');

    $respuesta = $this->actingAs($admin)->get(route('movimientos-inventario.index', [
        'planta_id' => $rosa->id,
        'tipo' => 'salida',
        'desde' => '2026-10-01',
        'hasta' => '2026-10-31',
    ]));

    expect(idsListados($respuesta))->toBe([$buscado->id]);
});

test('rechaza filtros inválidos y vuelve al listado con el error', function (array $filtros, string $campo) {
    $admin = User::factory()->create(['rol' => 'administrador']);

    $this->actingAs($admin)
        ->get(route('movimientos-inventario.index', $filtros))
        ->assertRedirect(route('movimientos-inventario.index'))
        ->assertSessionHasErrors($campo);
})->with([
    'tipo desconocido' => [['tipo' => 'traslado'], 'tipo'],
    'planta inexistente' => [['planta_id' => 9999], 'planta_id'],
    'planta no numérica' => [['planta_id' => 'abc'], 'planta_id'],
    'fecha inicial inválida' => [['desde' => '05/10/2026'], 'desde'],
    'fecha final inválida' => [['hasta' => 'ayer'], 'hasta'],
    'hasta anterior a desde' => [['desde' => '2026-10-10', 'hasta' => '2026-10-01'], 'hasta'],
    'página cero' => [['page' => 0], 'page'],
]);

test('hasta sin desde es un filtro válido', function () {
    $admin = User::factory()->create(['rol' => 'administrador']);

    $this->actingAs($admin)
        ->get(route('movimientos-inventario.index', ['hasta' => '2026-10-10']))
        ->assertOk()
        ->assertSessionHasNoErrors();
});

test('los filtros enviados en blanco se ignoran', function () {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $planta = Planta::factory()->create();
    crearMovimientoListado($planta);

    $respuesta = $this->actingAs($admin)->get(route('movimientos-inventario.index', [
        'planta_id' => '', 'tipo' => '', 'desde' => '', 'hasta' => '',
    ]));

    $respuesta->assertOk()->assertSessionHasNoErrors();
    expect($respuesta->viewData('movimientos')->total())->toBe(1);
});

test('los enlaces de paginación conservan los filtros', function () {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $planta = Planta::factory()->create();

    foreach (range(1, 16) as $i) {
        crearMovimientoListado($planta, 'salida', '2026-10-15 10:00:00', $admin);
    }
    crearMovimientoListado($planta, 'entrada');

    $respuesta = $this->actingAs($admin)
        ->get(route('movimientos-inventario.index', ['tipo' => 'salida']));

    $respuesta->assertOk()
        ->assertSee('tipo=salida', false)
        ->assertSee('page=2', false)
        ->assertSee('Mostrando 1–15 de 16 movimientos', false);
});

test('el formulario conserva los valores filtrados', function () {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $planta = Planta::factory()->create(['nombre' => 'Orquídea']);

    $this->actingAs($admin)
        ->get(route('movimientos-inventario.index', [
            'planta_id' => $planta->id,
            'tipo' => 'salida',
            'desde' => '2026-10-01',
            'hasta' => '2026-10-31',
        ]))
        ->assertOk()
        ->assertSee('value="2026-10-01"', false)
        ->assertSee('value="2026-10-31"', false)
        ->assertSeeInOrder(['<option value="' . $planta->id . '" selected', 'Orquídea'], false)
        ->assertSeeInOrder(['<option value="salida" selected', 'Salida'], false);
});

test('muestra mensajes vacíos distintos con y sin filtros', function () {
    $admin = User::factory()->create(['rol' => 'administrador']);

    $this->actingAs($admin)
        ->get(route('movimientos-inventario.index'))
        ->assertSee('No hay movimientos de inventario registrados.');

    $this->actingAs($admin)
        ->get(route('movimientos-inventario.index', ['tipo' => 'entrada']))
        ->assertSee('No hay movimientos que coincidan con los filtros aplicados.');
});

test('el número de consultas no crece con la cantidad de movimientos', function () {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $planta = Planta::factory()->create();

    $contarConsultas = function () use ($admin): int {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->actingAs($admin)->get(route('movimientos-inventario.index'))->assertOk();
        $total = count(DB::getQueryLog());
        DB::disableQueryLog();

        return $total;
    };

    crearMovimientoListado($planta, 'entrada', '2026-10-15 10:00:00', $admin);
    crearMovimientoListado($planta, 'salida', '2026-10-15 11:00:00', $admin);
    $conPocos = $contarConsultas();

    foreach (range(1, 10) as $i) {
        crearMovimientoListado(Planta::factory()->create(), 'entrada');
    }
    $conMuchos = $contarConsultas();

    expect($conMuchos)->toBe($conPocos);
});

test('el servicio ignora claves de filtro desconocidas y respeta el tamaño de página', function () {
    $planta = Planta::factory()->create();
    crearMovimientoListado($planta, 'entrada');
    crearMovimientoListado($planta, 'salida');
    crearMovimientoListado($planta, 'salida');

    $servicio = app(MovimientoInventarioService::class);
    $paginador = $servicio->listarPaginado(['rol' => 'administrador', 'tipo' => 'salida'], 1);

    expect($paginador->total())->toBe(2)
        ->and($paginador->perPage())->toBe(1)
        ->and($paginador->count())->toBe(1)
        ->and($paginador->url(2))->toContain('tipo=salida')
        ->and($paginador->url(2))->not->toContain('rol=');
});

test('el servicio devuelve las plantas del filtro ordenadas por nombre', function () {
    Planta::factory()->create(['nombre' => 'Zamioculca']);
    Planta::factory()->create(['nombre' => 'Aloe']);
    Planta::factory()->create(['nombre' => 'Monstera']);

    $nombres = app(MovimientoInventarioService::class)->plantasParaFiltro()->pluck('nombre')->all();

    expect($nombres)->toBe(['Aloe', 'Monstera', 'Zamioculca']);
});
