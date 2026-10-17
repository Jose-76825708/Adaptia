<?php

use App\Models\MovimientoInventario;
use App\Models\PerfilCliente;
use App\Models\Planta;
use App\Models\TipoPlanta;
use App\Models\User;
use App\Services\TipoPlantaService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('administrador gestiona tipos de planta y persiste los cambios', function () {
    $administrador = User::factory()->create(['rol' => 'administrador']);
    $tipo = TipoPlanta::create(['nombre' => 'Suculentas']);

    $this->actingAs($administrador)
        ->get(route('tipoPlantas.index'))
        ->assertOk()
        ->assertViewIs('tipoPlantas.index')
        ->assertSee('Suculentas');

    $this->get(route('tipoPlantas.create'))
        ->assertOk()
        ->assertViewIs('tipoPlantas.create');

    $this->post(route('tipoPlantas.store'), ['nombre' => 'Aromáticas'])
        ->assertRedirect(route('tipoPlantas.index'));
    $aromaticas = TipoPlanta::query()->where('nombre', 'Aromáticas')->firstOrFail();

    $this->get(route('tipoPlantas.edit', $aromaticas))
        ->assertOk()
        ->assertViewIs('tipoPlantas.edit')
        ->assertSee('Aromáticas');

    $this->put(route('tipoPlantas.update', $aromaticas), ['nombre' => 'Hierbas'])
        ->assertRedirect(route('tipoPlantas.index'));
    expect($aromaticas->fresh()->nombre)->toBe('Hierbas');

    $this->delete(route('tipoPlantas.destroy', $aromaticas))
        ->assertRedirect(route('tipoPlantas.index'));
    expect(TipoPlanta::find($aromaticas->id))->toBeNull();

    $this->post(route('tipoPlantas.store'), ['nombre' => ''])
        ->assertSessionHasErrors('nombre');
});

test('TipoPlantaService realiza CRUD y devuelve 404 para un identificador inexistente', function () {
    $service = app(TipoPlantaService::class);
    $tipo = $service->createTipo(['nombre' => 'Tropicales']);

    expect($service->getAll()->pluck('nombre')->all())->toContain('Tropicales')
        ->and($service->getTipoById((string) $tipo->id)->nombre)->toBe('Tropicales')
        ->and($service->updateTipo((string) $tipo->id, ['nombre' => 'De sombra'])->nombre)
        ->toBe('De sombra')
        ->and($service->deleteTipo((string) $tipo->id))->toBeTrue();

    expect(fn () => $service->getTipoById('999999'))
        ->toThrow(ModelNotFoundException::class);
});

test('cliente consulta y actualiza su perfil conservando solo sus preferencias', function () {
    $cliente = User::factory()->create(['rol' => 'cliente']);
    $otroCliente = User::factory()->create(['rol' => 'cliente']);
    $perfilAjeno = PerfilCliente::create([
        'user_id' => $otroCliente->id,
        'tamaño_adulto' => 'grande',
        'luz_requerida' => 'alta',
        'toxicidad' => true,
        'nivel_cuidado' => 'experto',
        'frecuencia_riego' => 'diario',
        'tipo_ambiente' => 'exteriores',
        'estetica' => 'flor',
    ]);

    $this->actingAs($cliente)
        ->get(route('perfil.edit'))
        ->assertOk()
        ->assertViewIs('perfiles.edit')
        ->assertViewHas('perfil', null);

    $preferencias = [
        'tamaño_adulto' => 'mediana',
        'luz_requerida' => 'media',
        'frecuencia_riego' => 'semanal',
        'tipo_ambiente' => 'interiores',
        'nivel_cuidado' => 'principiante',
        'estetica' => 'follaje',
    ];

    $this->postJson(route('perfil.update'), $preferencias)
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Tu perfil ha sido actualizado correctamente.',
        ]);

    $perfil = PerfilCliente::query()->where('user_id', $cliente->id)->firstOrFail();
    expect($perfil->toArray())
        ->toMatchArray($preferencias)
        ->and((bool) $perfil->toArray()['toxicidad'])->toBeFalse()
        ->and($perfil->user->is($cliente))->toBeTrue()
        ->and($perfilAjeno->fresh()->tamaño_adulto)->toBe('grande');

    $this->get(route('perfil.edit'))
        ->assertOk()
        ->assertViewHas('perfil', fn ($value) => $value->is($perfil));

    $this->postJson(route('perfil.update'), $preferencias + ['toxicidad' => '1'])
        ->assertOk();
    expect((bool) $perfil->fresh()->toxicidad)->toBeTrue();

    $this->postJson(route('perfil.update'), array_merge($preferencias, [
        'estetica' => 'invalid',
    ]))->assertUnprocessable()
        ->assertJsonValidationErrors('estetica');
    expect($perfil->fresh()->estetica)->toBe('follaje');
});

test('administrador gestiona movimientos desde el CRUD y valida las cantidades', function () {
    $administrador = User::factory()->create(['rol' => 'administrador']);
    $planta = Planta::factory()->create(['stock_actual' => 5, 'stock_minimo' => 0]);
    $this->actingAs($administrador);

    $this->get(route('movimientos-inventario.index'))
        ->assertOk()
        ->assertViewIs('movimientos-inventario.index');
    $this->get(route('movimientos-inventario.create'))
        ->assertOk()
        ->assertViewIs('movimientos-inventario.create');

    $payload = [
        'planta_id' => $planta->id,
        'user_id' => $administrador->id,
        'tipo' => 'entrada',
        'cantidad' => 2,
    ];
    $this->post(route('movimientos-inventario.store'), $payload)
        ->assertRedirect(route('movimientos-inventario.index'))
        ->assertSessionHas('success', 'Movimiento de inventario registrado correctamente.');

    $movimiento = MovimientoInventario::query()->firstOrFail();
    expect($planta->fresh()->stock_actual)->toBe(7);

    $this->get(route('movimientos-inventario.edit', $movimiento))
        ->assertOk()
        ->assertViewIs('movimientos-inventario.edit')
        ->assertViewHas('movimiento', fn ($value) => $value->is($movimiento));

    $this->put(route('movimientos-inventario.update', $movimiento), array_merge($payload, ['cantidad' => 3]))
        ->assertRedirect(route('movimientos-inventario.index'))
        ->assertSessionHas('success', 'Movimiento de inventario actualizado correctamente.');
    expect($movimiento->fresh()->cantidad)->toBe(3)
        ->and($planta->fresh()->stock_actual)->toBe(8);

    $this->delete(route('movimientos-inventario.destroy', $movimiento))
        ->assertRedirect(route('movimientos-inventario.index'))
        ->assertSessionHas('success', 'Movimiento de inventario eliminado correctamente.');
    expect(MovimientoInventario::count())->toBe(0)
        ->and($planta->fresh()->stock_actual)->toBe(5);

    $invalidPayload = $payload;
    $invalidPayload['cantidad'] = 0;
    $this->from(route('movimientos-inventario.create'))
        ->post(route('movimientos-inventario.store'), $invalidPayload)
        ->assertRedirect(route('movimientos-inventario.create'))
        ->assertSessionHasErrors('cantidad');
});
