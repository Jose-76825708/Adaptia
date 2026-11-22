<?php

use App\Models\ProyectoPaisajismo;
use App\Models\User;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('administrador lista filtra pagina y abre detalle', function () {
    $admin=User::factory()->create(['rol'=>'administrador']);
    ProyectoPaisajismo::factory()->count(12)->create(['estado'=>'solicitado']);
    ProyectoPaisajismo::factory()->create(['estado'=>'entregado','nombre'=>'Proyecto entregado']);

    $this->actingAs($admin)->get('/paisajismo/proyectos')->assertOk()->assertSee('Proyecto entregado');
    $this->actingAs($admin)->get('/paisajismo/proyectos?estado=entregado')->assertOk()->assertSee('Proyecto entregado');

    $proyecto=ProyectoPaisajismo::first();
    $this->actingAs($admin)->get('/paisajismo/proyectos/'.$proyecto->id)
        ->assertOk()->assertSee($proyecto->nombre);
});

it('cliente no puede acceder a bandeja administrativa', function () {
    $cliente=User::factory()->create(['rol'=>'cliente']);

    $this->actingAs($cliente)->get('/paisajismo/proyectos')->assertForbidden();
});

it('detalles con nulos no renderizan vacios como arreglo', function () {
    $admin=User::factory()->create(['rol'=>'administrador']);
    $proyecto=ProyectoPaisajismo::factory()->create(['fecha_deseada'=>null,'presupuesto_estimado'=>null]);

    $this->actingAs($admin)->get('/paisajismo/proyectos/'.$proyecto->id)
        ->assertOk()->assertDontSee('[]');
});
