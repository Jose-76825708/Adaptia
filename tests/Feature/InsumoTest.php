<?php

use App\Models\GestionInventario;
use App\Models\RecursoCatalogo;
use App\Models\User;
use App\Services\InsumoService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function ins_datos(array $extra = []): array
{
    return array_merge(['nombre' => 'Humus de lombriz', 'unidad' => 'kg', 'stock_actual' => 12, 'stock_minimo' => 5], $extra);
}

it('protege la pantalla de insumos contra visitantes y clientes', function () {
    $this->get(route('insumos.index'))->assertRedirect(route('login'));
    $this->actingAs(User::factory()->create(['rol' => 'cliente']))->get(route('insumos.index'))->assertForbidden();
});

it('lista solo insumos (materiales) y avisa los de stock bajo', function () {
    RecursoCatalogo::factory()->create(['tipo' => 'material', 'nombre' => 'Tierra negra', 'stock_actual' => 1, 'stock_minimo' => 5]);
    RecursoCatalogo::factory()->create(['tipo' => 'material', 'nombre' => 'Perlita', 'stock_actual' => 20, 'stock_minimo' => 5]);
    RecursoCatalogo::factory()->create(['tipo' => 'herramienta', 'nombre' => 'Pala de jardín']);

    foreach (['vendedor', 'administrador'] as $rol) {
        $this->actingAs(User::factory()->create(['rol' => $rol]))
            ->get(route('insumos.index'))
            ->assertOk()
            ->assertSee('Gestión de Insumos')
            ->assertSee('Tierra negra')
            ->assertSee('Perlita')
            ->assertSee('Insumos con Stock Bajo')
            ->assertSee('Revisar existencias')
            ->assertDontSee('Pala de jardín');
    }
});

it('crea un insumo de tipo material con código automático', function () {
    $vendedor = User::factory()->create(['rol' => 'vendedor']);
    RecursoCatalogo::factory()->create(['tipo' => 'material', 'codigo' => 'M-007']);

    $this->actingAs($vendedor)->get(route('insumos.create'))->assertOk()->assertSee('Crear Insumo');
    $this->actingAs($vendedor)->post(route('insumos.store'), ins_datos())
        ->assertRedirect(route('insumos.index'))->assertSessionHas('success');

    $nuevo = RecursoCatalogo::query()->where('nombre', 'Humus de lombriz')->firstOrFail();
    expect($nuevo->tipo)->toBe('material')
        ->and($nuevo->codigo)->toBe('M-008')
        ->and((float) $nuevo->stock_actual)->toBe(12.0)
        ->and((float) $nuevo->stock_minimo)->toBe(5.0);
});

it('valida los datos del insumo', function (array $cambio, string $campo) {
    RecursoCatalogo::factory()->create(['tipo' => 'material', 'nombre' => 'Existente']);

    $this->actingAs(User::factory()->create(['rol' => 'vendedor']))
        ->post(route('insumos.store'), ins_datos($cambio))
        ->assertSessionHasErrors($campo);

    expect(RecursoCatalogo::query()->where('nombre', 'Humus de lombriz')->exists())->toBeFalse();
})->with([
    'sin nombre' => [['nombre' => ''], 'nombre'],
    'nombre repetido' => [['nombre' => 'Existente'], 'nombre'],
    'sin unidad' => [['unidad' => ''], 'unidad'],
    'stock negativo' => [['stock_actual' => -1], 'stock_actual'],
    'mínimo no numérico' => [['stock_minimo' => 'abc'], 'stock_minimo'],
]);

it('edita un insumo sin cambiar su código y permite conservar su nombre', function () {
    $insumo = RecursoCatalogo::factory()->create(['tipo' => 'material', 'codigo' => 'M-050', 'nombre' => 'Compost', 'stock_actual' => 3]);
    $admin = User::factory()->create(['rol' => 'administrador']);

    $this->actingAs($admin)->get(route('insumos.edit', $insumo->id))->assertOk()->assertSee('Compost');
    $this->actingAs($admin)->put(route('insumos.update', $insumo->id), ins_datos(['nombre' => 'Compost', 'stock_actual' => 9]))
        ->assertRedirect(route('insumos.index'))->assertSessionHas('success');

    expect($insumo->fresh()->codigo)->toBe('M-050')
        ->and((float) $insumo->fresh()->stock_actual)->toBe(9.0);
});

it('no abre ni edita herramientas desde la pantalla de insumos', function () {
    $herramienta = RecursoCatalogo::factory()->create(['tipo' => 'herramienta']);
    $admin = User::factory()->create(['rol' => 'administrador']);

    $this->actingAs($admin)->get(route('insumos.edit', $herramienta->id))->assertNotFound();
    $this->actingAs($admin)->delete(route('insumos.destroy', $herramienta->id))->assertNotFound();
    expect(RecursoCatalogo::query()->whereKey($herramienta->id)->exists())->toBeTrue();
});

it('elimina un insumo sin uso y bloquea el que ya tiene gestiones', function () {
    $admin = User::factory()->create(['rol' => 'administrador']);
    $libre = RecursoCatalogo::factory()->create(['tipo' => 'material']);
    $usado = RecursoCatalogo::factory()->create(['tipo' => 'material', 'nombre' => 'Insumo usado']);
    GestionInventario::factory()->create(['tipo_item' => 'insumo', 'planta_id' => null, 'recurso_id' => $usado->id]);

    $this->actingAs($admin)->delete(route('insumos.destroy', $libre->id))
        ->assertRedirect(route('insumos.index'))->assertSessionHas('success');
    expect(RecursoCatalogo::query()->whereKey($libre->id)->exists())->toBeFalse();

    $this->actingAs($admin)->delete(route('insumos.destroy', $usado->id))
        ->assertRedirect(route('insumos.index'))->assertSessionHasErrors('insumo');
    expect(RecursoCatalogo::query()->whereKey($usado->id)->exists())->toBeTrue();
});

it('un insumo creado en la pantalla aparece en la gestión de inventario y se puede gestionar', function () {
    $vendedor = User::factory()->create(['rol' => 'vendedor']);

    $this->actingAs($vendedor)->post(route('insumos.store'), ins_datos(['nombre' => 'Cascarilla', 'stock_actual' => 8]));
    $insumo = RecursoCatalogo::query()->where('nombre', 'Cascarilla')->firstOrFail();

    $this->actingAs($vendedor)->get(route('gestion-inventario.create'))->assertOk()->assertSee('Cascarilla');

    $this->actingAs($vendedor)->post(route('gestion-inventario.store'), ['tipo_item' => 'insumo', 'recurso_id' => $insumo->id, 'cantidad_requerida' => 3])->assertRedirect();
    $gestion = GestionInventario::query()->firstOrFail();
    $this->actingAs($vendedor)->post(route('gestion-inventario.verificar', $gestion), ['cantidad_fisica' => 8, 'cantidad_buen_estado' => 8]);
    $this->actingAs($vendedor)->post(route('gestion-inventario.movimiento', $gestion))->assertSessionHas('success');

    expect((float) $insumo->fresh()->stock_actual)->toBe(5.0);
});

it('genera códigos consecutivos ignorando los que no siguen el formato', function () {
    RecursoCatalogo::factory()->create(['tipo' => 'material', 'codigo' => 'M-003']);
    RecursoCatalogo::factory()->create(['tipo' => 'herramienta', 'codigo' => 'H-009']);
    RecursoCatalogo::factory()->create(['tipo' => 'material', 'codigo' => 'ABC']);

    expect(app(InsumoService::class)->generarCodigo())->toBe('M-004');
});
