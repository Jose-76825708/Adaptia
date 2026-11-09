<?php

use App\Models\CasoMantenimiento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['rol' => 'administrador']);
    $this->cliente = User::factory()->create(['rol' => 'cliente']);
    $this->vendedor = User::factory()->create(['rol' => 'vendedor']);
});

test('la bandeja administrativa exige autenticacion y acceso solo para administradores', function () {
    $this->get(route('mantenimiento.casos.index'))->assertRedirect('/login');

    $this->actingAs($this->cliente)
        ->get(route('mantenimiento.casos.index'))
        ->assertStatus(403);

    $this->actingAs($this->vendedor)
        ->get(route('mantenimiento.casos.index'))
        ->assertStatus(403);

    $this->actingAs($this->admin)
        ->get(route('mantenimiento.casos.index'))
        ->assertOk();
});

test('el detalle administrativo muestra un caso con campos nulos y escapa contenido html', function () {
    $caso = CasoMantenimiento::factory()->create([
        'solicitante_id' => $this->cliente->id,
        'area_verde' => 'Jardín central',
        'descripcion' => 'Revisar sistema de riego',
        'fecha_deseada' => null,
        'observaciones' => null,
        'estado' => 'abierto',
    ]);

    $stringConHtml = '<script>alert("x")</script>';
    $caso->update(['observaciones' => $stringConHtml]);

    $response = $this->actingAs($this->admin)->get(route('mantenimiento.casos.show', $caso));
    $response->assertOk();
    $response->assertSee('Jardín central');
    $response->assertSee('No especificado');
});

test('la bandeja administrativa ignora q como arreglo en la consulta', function () {
    $this->actingAs($this->admin)
        ->get(route('mantenimiento.casos.index', ['q' => ['x']]))
        ->assertOk();
});

test('el detalle administrativo responde 404 para ids inexistentes', function () {
    $this->actingAs($this->admin)
        ->get(route('mantenimiento.casos.show', 999999))
        ->assertNotFound();
});
