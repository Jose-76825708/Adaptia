<?php

use App\Models\Planta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('cliente puede ver el catálogo de plantas y sus tipos', function () {
    $cliente = User::factory()->create(['rol' => 'cliente']);
    $planta = Planta::factory()->create([
        'nombre' => 'Helecho',
        'descripcion' => 'Planta de prueba para el catálogo.',
        'precio' => 25,
    ]);

    $response = $this->actingAs($cliente)->get(route('catalogo.plantas.index'));

    $response->assertOk()
        ->assertViewIs('home.catalogo-plantas')
        ->assertViewHas('plantas', function ($plantas) use ($planta) {
            return $plantas->contains('id', $planta->id)
                && $plantas->firstWhere('id', $planta->id)->relationLoaded('tipoPlanta');
        })
        ->assertSee('Helecho')
        ->assertSee('Ornamentales');
});

test('cliente no puede acceder a las pantallas CRUD', function () {
    $cliente = User::factory()->create(['rol' => 'cliente']);
    $this->actingAs($cliente);

    foreach (['plantas.index', 'tipoPlantas.index', 'movimientos-inventario.index', 'sensores.index'] as $routeName) {
        $this->get(route($routeName))->assertForbidden();
    }
});

test('administrador y vendedor pueden acceder al CRUD de plantas', function () {
    foreach (['administrador', 'vendedor'] as $role) {
        $user = User::factory()->create(['rol' => $role]);

        $this->actingAs($user)
            ->get(route('plantas.index'))
            ->assertOk();
    }
});

test('solo el cliente puede acceder a la ruta de catálogo de cliente', function () {
    foreach (['administrador', 'vendedor'] as $role) {
        $user = User::factory()->create(['rol' => $role]);

        $this->actingAs($user)
            ->get(route('catalogo.plantas.index'))
            ->assertForbidden();
    }
});
