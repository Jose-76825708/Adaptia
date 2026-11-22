<?php

use App\Models\ProyectoPaisajismo;
use App\Models\PropuestaDiseno;
use App\Models\User;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('panel diseno muestra historial rechazado y motivo', function () {
    $admin=User::factory()->create(['rol'=>'administrador']);
    $cliente=User::factory()->create(['rol'=>'cliente']);
    $proyecto=ProyectoPaisajismo::factory()->create(['solicitante_id'=>$cliente->id,'estado'=>'solicitado']);
    PropuestaDiseno::factory()->create(['proyecto_id'=>$proyecto->id,'autor_id'=>$admin->id,'version'=>1,'decision'=>'rechazada','motivo_decision'=>'Excede el presupuesto disponible.']);

    $this->actingAs($admin)->get(route('paisajismo.proyectos.show',$proyecto))
        ->assertOk()->assertSee('Versión 1')->assertSee('Rechazada')->assertSee('Excede el presupuesto disponible.')
        ->assertSee('Crea una nueva versión');
});

it('historial muestra v1 rechazada y v2 aprobada', function () {
    $admin=User::factory()->create(['rol'=>'administrador']);
    $cliente=User::factory()->create(['rol'=>'cliente']);
    $proyecto=ProyectoPaisajismo::factory()->create(['solicitante_id'=>$cliente->id,'estado'=>'aprobado']);
    PropuestaDiseno::factory()->create(['proyecto_id'=>$proyecto->id,'autor_id'=>$admin->id,'version'=>1,'decision'=>'rechazada','motivo_decision'=>'Motivo de la primera versión.']);
    PropuestaDiseno::factory()->create(['proyecto_id'=>$proyecto->id,'autor_id'=>$admin->id,'version'=>2,'decision'=>'aprobada']);

    $this->actingAs($admin)->get(route('paisajismo.proyectos.show',$proyecto))
        ->assertOk()->assertSee('Versión 1')->assertSee('Versión 2')->assertSee('Aprobada')->assertSee('Motivo de la primera versión.');
});

it('una sola version pendiente no muestra aviso de rechazo', function () {
    $admin=User::factory()->create(['rol'=>'administrador']);
    $cliente=User::factory()->create(['rol'=>'cliente']);
    $proyecto=ProyectoPaisajismo::factory()->create(['solicitante_id'=>$cliente->id,'estado'=>'propuesto']);
    PropuestaDiseno::factory()->create(['proyecto_id'=>$proyecto->id,'autor_id'=>$admin->id,'version'=>1,'decision'=>null]);

    $this->actingAs($admin)->get(route('paisajismo.proyectos.show',$proyecto))
        ->assertOk()->assertSee('Pendiente')->assertDontSee('Crea una nueva versión');
});
