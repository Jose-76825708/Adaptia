<?php

use App\Models\ProyectoPaisajismo;
use App\Models\User;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('cliente puede abrir formulario y alta valida', function () {
    $cliente=User::factory()->create(['rol'=>'cliente']);

    $this->actingAs($cliente)->get('/paisajismo/solicitudes/crear')
        ->assertOk()->assertSee('Solicitar proyecto');

    $this->actingAs($cliente)->post('/paisajismo/solicitudes',[
        'nombre'=>'Jardín central','ubicacion'=>'Huancayo','descripcion'=>'Diseño integral de jardín residencial','area_m2'=>'120,50','presupuesto_estimado'=>'5000','fecha_deseada'=>now()->addMonth()->format('Y-m-d'),
    ])->assertRedirect('/paisajismo/solicitudes');

    expect(ProyectoPaisajismo::where('solicitante_id',$cliente->id)->count())->toBe(1);
});

it('cliente no puede ver proyectos ajenos y alta invalida retorna errores', function () {
    $cliente=User::factory()->create(['rol'=>'cliente']);
    $otro=User::factory()->create(['rol'=>'cliente']);
    $ajeno=ProyectoPaisajismo::factory()->create(['solicitante_id'=>$otro->id]);

    $this->actingAs($cliente)->get('/paisajismo/solicitudes/'.$ajeno->id.'/propuesta')->assertNotFound();

    $this->actingAs($cliente)->from('/paisajismo/solicitudes/crear')->post('/paisajismo/solicitudes',[
        'nombre'=>'x','ubicacion'=>'x','descripcion'=>'x','area_m2'=>'0',
    ])->assertRedirect('/paisajismo/solicitudes/crear')->assertSessionHasErrors();
});

it('index cliente aisla y pagina por solicitante', function () {
    $cliente=User::factory()->create(['rol'=>'cliente']);
    $otro=User::factory()->create(['rol'=>'cliente']);

    ProyectoPaisajismo::factory()->count(12)->create(['solicitante_id'=>$cliente->id]);
    ProyectoPaisajismo::factory()->create(['solicitante_id'=>$otro->id,'nombre'=>'Proyecto ajeno']);

    $this->actingAs($cliente)->get('/paisajismo/solicitudes')
        ->assertOk()->assertDontSee('Proyecto ajeno')->assertSee('Pagination');
});
