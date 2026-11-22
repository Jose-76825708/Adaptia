<?php

use App\Models\User;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('organiza la navegación administrativa por macroprocesos', function () {
    $administrador = User::factory()->create(['rol' => 'administrador']);

    $this->actingAs($administrador)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('Macroprocesos estratégicos')
        ->assertSee('Macroprocesos operativos (misionales)')
        ->assertSee('Macroprocesos de apoyo')
        ->assertSee('Planeación comercial')
        ->assertSee('Producción vegetal')
        ->assertSee('Proyectos de paisajismo')
        ->assertSee('Casos de mantenimiento')
        ->assertSee('Gestión de inventario')
        ->assertSee('Personal');
});

it('organiza la navegación del vendedor y oculta enlaces exclusivos del administrador', function () {
    $vendedor = User::factory()->create(['rol' => 'vendedor']);

    $this->actingAs($vendedor)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('Macroprocesos estratégicos')
        ->assertSee('Macroprocesos operativos (misionales)')
        ->assertSee('Macroprocesos de apoyo')
        ->assertSee('Planeación comercial')
        ->assertSee('Ventas')
        ->assertSee('Gestión de inventario')
        ->assertSee('Asignar sensores')
        ->assertDontSee('Proyectos de paisajismo')
        ->assertDontSee('Casos de mantenimiento')
        ->assertDontSee('Personal');
});
