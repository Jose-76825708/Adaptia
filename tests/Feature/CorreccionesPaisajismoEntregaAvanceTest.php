<?php

use App\Models\AvanceInstalacion;
use App\Models\EntregaProyecto;
use App\Models\ProyectoPaisajismo;
use App\Models\User;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('sin avances muestra mensaje y oculta formulario entrega', function () {
    $cliente=User::factory()->create(['rol'=>'cliente']);
    $proyecto=ProyectoPaisajismo::factory()->create(['solicitante_id'=>$cliente->id,'estado'=>'en_instalacion']);

    $admin=User::factory()->create(['rol'=>'administrador']);
    $this->actingAs($admin)->get(route('paisajismo.proyectos.show',$proyecto))
        ->assertOk()->assertSee('Para registrar la entrega, el avance debe llegar al 100%. Avance actual: 0%')->assertDontSee('ent-resumen');
});

it('con avance 99 muestra mensaje y oculta formulario', function () {
    $admin=User::factory()->create(['rol'=>'administrador']);
    $cliente=User::factory()->create(['rol'=>'cliente']);
    $proyecto=ProyectoPaisajismo::factory()->create(['solicitante_id'=>$cliente->id,'estado'=>'en_instalacion']);
    AvanceInstalacion::create(['proyecto_id'=>$proyecto->id,'autor_id'=>$admin->id,'descripcion'=>'Casi completo','porcentaje'=>99]);

    $this->actingAs($admin)->get(route('paisajismo.proyectos.show',$proyecto))
        ->assertOk()->assertSee('Avance actual: 99%')->assertDontSee('ent-resumen');
});

it('con avance 100 muestra formulario entrega', function () {
    $admin=User::factory()->create(['rol'=>'administrador']);
    $cliente=User::factory()->create(['rol'=>'cliente']);
    $proyecto=ProyectoPaisajismo::factory()->create(['solicitante_id'=>$cliente->id,'estado'=>'en_instalacion']);
    AvanceInstalacion::create(['proyecto_id'=>$proyecto->id,'autor_id'=>$admin->id,'descripcion'=>'Completo','porcentaje'=>100]);

    $this->actingAs($admin)->get(route('paisajismo.proyectos.show',$proyecto))
        ->assertOk()->assertSee('ent-resumen')->assertSee('ent-recomendaciones');
});

it('entregado muestra entrega sin formulario', function () {
    $admin=User::factory()->create(['rol'=>'administrador']);
    $cliente=User::factory()->create(['rol'=>'cliente']);
    $proyecto=ProyectoPaisajismo::factory()->create(['solicitante_id'=>$cliente->id,'estado'=>'entregado']);
    EntregaProyecto::create(['proyecto_id'=>$proyecto->id,'autor_id'=>$admin->id,'resumen'=>'Entrega final','recomendaciones'=>'Mantener riego']);

    $this->actingAs($admin)->get(route('paisajismo.proyectos.show',$proyecto))
        ->assertOk()->assertSee('Entrega final')->assertDontSee('ent-resumen');
});
