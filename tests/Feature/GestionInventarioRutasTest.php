<?php

use App\Models\GestionInventario;
use App\Models\MovimientoInventario;
use App\Models\Planta;
use App\Models\RecursoCatalogo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('protege las rutas contra visitantes y clientes', function () {
    $gestion = GestionInventario::factory()->create();

    $this->get(route('gestion-inventario.index'))->assertRedirect(route('login'));
    $this->post(route('gestion-inventario.verificar', $gestion), [])->assertRedirect(route('login'));

    $cliente = User::factory()->create(['rol' => 'cliente']);
    $this->actingAs($cliente)->get(route('gestion-inventario.index'))->assertForbidden();
    $this->actingAs($cliente)->get(route('gestion-inventario.show', $gestion))->assertForbidden();
    $this->actingAs($cliente)->post(route('gestion-inventario.verificar', $gestion), [])->assertForbidden();
});

it('muestra bandeja, formulario y detalle a vendedor y administrador', function () {
    $gestion = GestionInventario::factory()->create();

    foreach (['vendedor', 'administrador'] as $rol) {
        $usuario = User::factory()->create(['rol' => $rol]);

        $this->actingAs($usuario)->get(route('gestion-inventario.index'))
            ->assertOk()->assertSee('Gestión de inventario')->assertSee($gestion->codigo);
        $this->actingAs($usuario)->get(route('gestion-inventario.create'))
            ->assertOk()->assertSee('Revisar existencias');
        $this->actingAs($usuario)->get(route('gestion-inventario.show', $gestion))
            ->assertOk()->assertSee('Estado del proceso')->assertDontSee('Etapas del macroproceso')->assertSee('Verificar estado y cantidades');
    }

    $this->actingAs(User::factory()->create(['rol' => 'vendedor']))
        ->get(route('home'))->assertOk()->assertSee('Gestión de inventario');
});

it('muestra al vendedor que la etapa de compras no le corresponde', function () {
    $gestion = GestionInventario::factory()->create(['estado' => 'stock_insuficiente', 'faltante' => 3]);

    $this->actingAs(User::factory()->create(['rol' => 'vendedor']))
        ->get(route('gestion-inventario.show', $gestion))
        ->assertOk()->assertSee('Compras y administración')->assertDontSee('Gestionar reposición');

    $this->actingAs(User::factory()->create(['rol' => 'administrador']))
        ->get(route('gestion-inventario.show', $gestion))
        ->assertOk()->assertSee('Gestionar reposición');
});

it('recorre el flujo completo por HTTP, incluida la incidencia, y actualiza el stock', function () {
    $vendedor = User::factory()->create(['rol' => 'vendedor']);
    $admin = User::factory()->create(['rol' => 'administrador']);
    $planta = Planta::factory()->create(['stock_actual' => 1, 'stock_minimo' => 1]);

    $this->actingAs($vendedor)->post(route('gestion-inventario.store'), [
        'tipo_item' => 'planta', 'planta_id' => $planta->id, 'cantidad_requerida' => 4, 'motivo' => 'Campaña',
    ])->assertRedirect();
    $gestion = GestionInventario::query()->firstOrFail();

    $this->actingAs($vendedor)->post(route('gestion-inventario.verificar', $gestion), ['cantidad_fisica' => 1, 'cantidad_buen_estado' => 1])
        ->assertRedirect(route('gestion-inventario.show', $gestion))->assertSessionHas('success');
    expect($gestion->fresh()->estado)->toBe('stock_insuficiente');

    $this->actingAs($vendedor)->post(route('gestion-inventario.reposicion', $gestion), ['cantidad_reposicion' => 3, 'proveedor' => 'Vivero'])
        ->assertForbidden();

    $this->actingAs($admin)->post(route('gestion-inventario.reposicion', $gestion), ['cantidad_reposicion' => 3, 'proveedor' => 'Vivero'])->assertSessionHas('success');
    $this->actingAs($admin)->post(route('gestion-inventario.compra', $gestion), ['numero_orden' => 'OC-9', 'fecha_compra' => today()->toDateString()])->assertSessionHas('success');
    $this->actingAs($vendedor)->post(route('gestion-inventario.recepcion', $gestion), ['cantidad_recibida' => 2])->assertSessionHas('success');
    $this->actingAs($vendedor)->post(route('gestion-inventario.recepcion.verificar', $gestion), [
        'conforme' => '0', 'tipo_incidencia' => 'faltante', 'descripcion' => 'Llegaron 2 de 3.',
    ])->assertSessionHas('success');
    expect($gestion->fresh()->estado)->toBe('en_incidencia');

    $this->actingAs($admin)->post(route('gestion-inventario.incidencia', $gestion), ['resolucion' => 'Enviarán 1 más.'])->assertSessionHas('success');
    $this->actingAs($vendedor)->post(route('gestion-inventario.recepcion', $gestion), ['cantidad_recibida' => 1])->assertSessionHas('success');
    $this->actingAs($vendedor)->post(route('gestion-inventario.recepcion.verificar', $gestion), ['conforme' => '1'])->assertSessionHas('success');
    $this->actingAs($vendedor)->post(route('gestion-inventario.ingreso', $gestion), ['documento_ingreso' => 'GR-1'])->assertSessionHas('success');
    $this->actingAs($vendedor)->post(route('gestion-inventario.almacenar', $gestion), ['ubicacion' => 'Mesa 1'])->assertSessionHas('success');
    $this->actingAs($vendedor)->post(route('gestion-inventario.movimiento', $gestion))->assertSessionHas('success');

    expect($gestion->fresh()->estado)->toBe('cerrada')
        ->and($planta->fresh()->stock_actual)->toBe(4)
        ->and(MovimientoInventario::query()->where('planta_id', $planta->id)->where('tipo', 'entrada')->value('cantidad'))->toBe(3);

    $this->actingAs($admin)->get(route('gestion-inventario.show', $gestion))
        ->assertOk()->assertSee('Diferencias e incidencias')->assertSee('Mesa 1')->assertSee('Recepciones');
});

it('devuelve errores de validación y de estado sin cambiar la gestión', function () {
    $vendedor = User::factory()->create(['rol' => 'vendedor']);
    $gestion = GestionInventario::factory()->create();

    $this->actingAs($vendedor)->from(route('gestion-inventario.show', $gestion))
        ->post(route('gestion-inventario.verificar', $gestion), ['cantidad_fisica' => 3, 'cantidad_buen_estado' => 5])
        ->assertRedirect(route('gestion-inventario.show', $gestion))
        ->assertSessionHasErrors('gestion');

    $this->actingAs($vendedor)->from(route('gestion-inventario.show', $gestion))
        ->post(route('gestion-inventario.verificar', $gestion), ['cantidad_fisica' => 'abc', 'cantidad_buen_estado' => 1])
        ->assertSessionHasErrors('cantidad_fisica');

    $this->actingAs($vendedor)->from(route('gestion-inventario.show', $gestion))
        ->post(route('gestion-inventario.movimiento', $gestion))
        ->assertSessionHasErrors('gestion');

    expect($gestion->fresh()->estado)->toBe('existencias_revisadas');
});

it('crea revisiones de insumos y rechaza herramientas', function () {
    $vendedor = User::factory()->create(['rol' => 'vendedor']);
    $material = RecursoCatalogo::factory()->create(['tipo' => 'material', 'stock_actual' => 4]);
    $herramienta = RecursoCatalogo::factory()->create(['tipo' => 'herramienta']);

    $this->actingAs($vendedor)->post(route('gestion-inventario.store'), ['tipo_item' => 'insumo', 'recurso_id' => $material->id, 'cantidad_requerida' => 2.5])
        ->assertRedirect();
    expect(GestionInventario::query()->first()->recurso_id)->toBe($material->id);

    $this->actingAs($vendedor)->post(route('gestion-inventario.store'), ['tipo_item' => 'insumo', 'recurso_id' => $herramienta->id, 'cantidad_requerida' => 1])
        ->assertSessionHasErrors('recurso_id');
});

it('renderiza el detalle en todas las etapas para ambos roles', function (string $estado) {
    $gestion = GestionInventario::factory()->create([
        'estado' => $estado,
        'faltante' => 3,
        'cantidad_reposicion' => 3,
        'cantidad_ingresada' => 3,
    ]);

    foreach (['vendedor', 'administrador'] as $rol) {
        $this->actingAs(User::factory()->create(['rol' => $rol]))
            ->get(route('gestion-inventario.show', $gestion))
            ->assertOk()
            ->assertSee('Estado del proceso')
            ->assertSee('Siguiente paso');
    }
})->with(App\Services\GestionInventarioService::ESTADOS);

it('muestra un mensaje simple con el estado del proceso', function () {
    $gestion = GestionInventario::factory()->create(['estado' => 'compra_realizada', 'cantidad_reposicion' => 3]);

    $this->actingAs(User::factory()->create(['rol' => 'vendedor']))
        ->get(route('gestion-inventario.show', $gestion))
        ->assertOk()
        ->assertSee('Estado del proceso')
        ->assertSee('Compra realizada.')
        ->assertSee('La compra está hecha. Falta recibir el material.')
        ->assertDontSee('Stock suficiente');
});

it('preselecciona el insumo cuando se llega desde la pantalla de insumos', function () {
    $insumo = RecursoCatalogo::factory()->create(['tipo' => 'material', 'nombre' => 'Humus especial']);

    $this->actingAs(User::factory()->create(['rol' => 'vendedor']))
        ->get(route('gestion-inventario.create', ['tipo_item' => 'insumo', 'recurso_id' => $insumo->id]))
        ->assertOk()
        ->assertSee('<option value="insumo" selected>', false)
        ->assertSee('<option value="' . $insumo->id . '" selected>', false);
});
