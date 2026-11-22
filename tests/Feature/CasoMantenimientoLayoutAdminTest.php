<?php

use App\Models\CasoMantenimiento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['rol' => 'administrador']);
    $this->cliente = User::factory()->create(['rol' => 'cliente']);
});

test('la bandeja y el detalle administrativos usan el layout de administrador y no el encabezado público', function () {
    $caso = CasoMantenimiento::factory()->create([
        'solicitante_id' => $this->cliente->id,
        'estado' => 'abierto',
    ]);

    $response = $this->actingAs($this->admin)->get(route('mantenimiento.casos.index'));
    $response->assertOk();
    $response->assertSee('Casos de mantenimiento');
    $response->assertSee('Personal');
    $response->assertSee('Tipos de plantas');
    $response->assertSee('bg-[#2e6945]', false);
    $response->assertDontSee('Cómo funciona');
    $response->assertDontSee('Nosotros');
    $response->assertDontSee('Empezar');

    $detalle = $this->actingAs($this->admin)->get(route('mantenimiento.casos.show', $caso));
    $detalle->assertOk();
    $detalle->assertSee('Casos de mantenimiento');
    $detalle->assertSee('Personal');
    $detalle->assertDontSee('Cómo funciona');
    $detalle->assertDontSee('Nosotros');
});

test('las pantallas del cliente mantienen su layout público y no muestran menú administrativo', function () {
    $this->actingAs($this->cliente)
        ->get(route('mantenimiento.solicitudes.index'))
        ->assertOk()
        ->assertSee('Cómo funciona')
        ->assertDontSee('Personal')
        ->assertDontSee('bg-[#013623]');

    $this->actingAs($this->cliente)
        ->get(route('mantenimiento.solicitudes.create'))
        ->assertOk()
        ->assertSee('Cómo funciona')
        ->assertDontSee('Personal')
        ->assertDontSee('bg-[#013623]');
});
