<?php

use App\Models\CasoMantenimiento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->cliente = User::factory()->create(['rol' => 'cliente']);
});

test('el cliente puede ver el formulario de solicitud de mantenimiento', function () {
    $this->actingAs($this->cliente)
        ->get(route('mantenimiento.solicitudes.create'))
        ->assertOk()
        ->assertSee('Crear solicitud')
        ->assertSee('area_verde')
        ->assertSee('descripcion')
        ->assertSee('fecha_deseada')
        ->assertSee('observaciones');
});

test('el cliente puede crear una solicitud valida y redirige al listado con el codigo del caso', function () {
    $response = $this->actingAs($this->cliente)
        ->from(route('mantenimiento.solicitudes.create'))
        ->post(route('mantenimiento.solicitudes.store'), [
            'area_verde' => 'Jardín principal',
            'descripcion' => 'Necesito revisión general del jardín y riego urgente.',
            'fecha_deseada' => '2026-11-12',
            'observaciones' => 'Falta humedad puntual en la zona sur.',
        ]);

    $response->assertRedirect(route('mantenimiento.solicitudes.index'))
        ->assertSessionHas('success');

    $caso = CasoMantenimiento::first();

    expect($caso)->not->toBeNull()
        ->and($caso->solicitante_id)->toBe($this->cliente->id)
        ->and($caso->estado)->toBe('abierto')
        ->and(session('success'))->toContain($caso->codigo);
});

test('el cliente ignora user_id y estado inyectados en el POST', function () {
    $this->actingAs($this->cliente)
        ->post(route('mantenimiento.solicitudes.store'), [
            'user_id' => 999,
            'estado' => 'cerrado',
            'area_verde' => 'Patio central',
            'descripcion' => 'Necesito poda para las ramas laterales del patio.',
            'fecha_deseada' => null,
            'observaciones' => 'Hay ramas largas.',
        ]);

    $caso = CasoMantenimiento::first();

    expect($caso)->not->toBeNull()
        ->and($caso->solicitante_id)->toBe($this->cliente->id)
        ->and($caso->estado)->toBe('abierto');
});

test('una solicitud invalida vuelve al formulario con errores en español y sin crear casos', function () {
    $response = $this->actingAs($this->cliente)
        ->from(route('mantenimiento.solicitudes.create'))
        ->post(route('mantenimiento.solicitudes.store'), [
            'area_verde' => 'Ja',
            'descripcion' => 'Corto',
            'fecha_deseada' => '2026-10-30',
            'observaciones' => str_repeat('x', 501),
        ]);

    $response->assertRedirect(route('mantenimiento.solicitudes.create'))
        ->assertSessionHasErrors(['area_verde', 'descripcion', 'fecha_deseada', 'observaciones'])
        ->assertSessionHas('errors');

    expect(CasoMantenimiento::count())->toBe(0);
});

test('invitado, vendedor y administrador no pueden acceder a la interfaz del cliente', function () {
    $vendedor = User::factory()->create(['rol' => 'vendedor']);
    $administrador = User::factory()->create(['rol' => 'administrador']);

    $this->get(route('mantenimiento.solicitudes.create'))->assertRedirect('/login');

    $this->actingAs($vendedor)
        ->get(route('mantenimiento.solicitudes.index'))
        ->assertForbidden();

    $this->actingAs($administrador)
        ->get(route('mantenimiento.solicitudes.create'))
        ->assertForbidden();
});
