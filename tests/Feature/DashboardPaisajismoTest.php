<?php

use App\Models\ProyectoPaisajismo;
use App\Models\User;
use App\Services\DashboardOperativoService;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('muestra en home el resumen de proyectos paisajismo por estado', function () {
    $administrador=User::factory()->create(['rol'=>'administrador']);
    $cliente=User::factory()->create(['rol'=>'cliente']);

    ProyectoPaisajismo::factory()->create(['solicitante_id'=>$cliente->id,'estado'=>'solicitado']);
    ProyectoPaisajismo::factory()->create(['solicitante_id'=>$cliente->id,'estado'=>'propuesto']);
    ProyectoPaisajismo::factory()->create(['solicitante_id'=>$cliente->id,'estado'=>'aprobado']);
    ProyectoPaisajismo::factory()->create(['solicitante_id'=>$cliente->id,'estado'=>'en_instalacion']);
    ProyectoPaisajismo::factory()->create(['solicitante_id'=>$cliente->id,'estado'=>'entregado']);

    $this->actingAs($administrador)->get(route('home'))
        ->assertOk()->assertSee('Paisajismo')
        ->assertSee('Solicitados')->assertSee('Propuestos')->assertSee('Aprobados')
        ->assertSee('En instalación')->assertSee('Entregados')
        ->assertViewHas('resumen', fn (array $resumen) =>
            $resumen['paisajismo_por_estado']['solicitado']===1
            && $resumen['paisajismo_por_estado']['propuesto']===1
            && $resumen['paisajismo_por_estado']['aprobado']===1
            && $resumen['paisajismo_por_estado']['en_instalacion']===1
            && $resumen['paisajismo_por_estado']['entregado']===1
        );
});

it('DashboardOperativo incluye cero para estados sin proyectos', function () {
    $resumen=app(DashboardOperativoService::class)->resumenAdministrador();

    expect($resumen['paisajismo_por_estado'])->toBe([
        'solicitado'=>0,'propuesto'=>0,'aprobado'=>0,'en_instalacion'=>0,'entregado'=>0,
    ]);
});
