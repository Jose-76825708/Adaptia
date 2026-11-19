<?php

use App\Models\CasoMantenimiento;
use App\Models\InformeCierre;
use App\Models\User;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('cliente dueño sin informe recibe 404', function () {
    $cliente = User::factory()->create(['rol'=>'cliente']);
    $caso = CasoMantenimiento::factory()->create(['solicitante_id'=>$cliente->id,'estado'=>'cerrado']);

    $this->actingAs($cliente)
        ->get(route('mantenimiento.casos.informe',$caso))
        ->assertNotFound();
});

it('cliente no dueño recibe 404 aunque exista informe', function () {
    $dueno = User::factory()->create(['rol'=>'cliente']);
    $otro = User::factory()->create(['rol'=>'cliente']);
    $caso = CasoMantenimiento::factory()->create(['solicitante_id'=>$dueno->id,'estado'=>'cerrado']);
    InformeCierre::create([
        'caso_id'=>$caso->id,'autor_id'=>$dueno->id,'destinatario_id'=>$dueno->id,'version'=>1,
        'resumen'=>'Cierre aprobado','contenido'=>'Texto plano','notificacion_pendiente'=>true,'cerrado_en'=>now(),
    ]);

    $this->actingAs($otro)
        ->get(route('mantenimiento.casos.informe',$caso))
        ->assertNotFound();
});

it('administrador puede abrir informe existente', function () {
    $dueno = User::factory()->create(['rol'=>'cliente']);
    $admin = User::factory()->create(['rol'=>'administrador']);
    $caso = CasoMantenimiento::factory()->create(['solicitante_id'=>$dueno->id,'estado'=>'cerrado']);
    InformeCierre::create([
        'caso_id'=>$caso->id,'autor_id'=>$admin->id,'destinatario_id'=>$dueno->id,'version'=>1,
        'resumen'=>'Cierre aprobado','contenido'=>'Texto plano','notificacion_pendiente'=>true,'cerrado_en'=>now(),
    ]);

    $this->actingAs($admin)
        ->get(route('mantenimiento.casos.informe',$caso))
        ->assertOk()
        ->assertSee('Informe final');
});

it('cliente dueño puede abrir su informe existente', function () {
    $dueno = User::factory()->create(['rol'=>'cliente']);
    $admin = User::factory()->create(['rol'=>'administrador']);
    $caso = CasoMantenimiento::factory()->create(['solicitante_id'=>$dueno->id,'estado'=>'cerrado']);
    InformeCierre::create([
        'caso_id'=>$caso->id,'autor_id'=>$admin->id,'destinatario_id'=>$dueno->id,'version'=>1,
        'resumen'=>'Cierre aprobado','contenido'=>'Texto plano','notificacion_pendiente'=>true,'cerrado_en'=>now(),
    ]);

    $this->actingAs($dueno)
        ->get(route('mantenimiento.casos.informe',$caso))
        ->assertOk()
        ->assertSee('Informe final');
});
