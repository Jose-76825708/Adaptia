<?php

use App\Models\ProyectoPaisajismo;
use App\Models\PropuestaDiseno;
use App\Models\User;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('lista muestra detalle, area, fecha y acciones segun propuesta y entrega', function () {
    $cliente=User::factory()->create(['rol'=>'cliente']);
    $proyecto=ProyectoPaisajismo::factory()->create(['solicitante_id'=>$cliente->id,'estado'=>'entregado','area_m2'=>42.25]);
    PropuestaDiseno::factory()->create(['proyecto_id'=>$proyecto->id,'autor_id'=>User::factory()->create(['rol'=>'administrador'])->id,'version'=>1]);

    $response=$this->actingAs($cliente)->get(route('paisajismo.solicitudes.index'));
    $response->assertOk()->assertSee('Área (m²)')->assertSee('Fecha de solicitud')->assertSee('Ver detalle')->assertSee('Ver propuesta')->assertSee('Ver resumen');
});

it('detalle existe para dueño y 404 para ajeno e inexistente', function () {
    $dueno=User::factory()->create(['rol'=>'cliente']);
    $otro=User::factory()->create(['rol'=>'cliente']);
    $proyecto=ProyectoPaisajismo::factory()->create(['solicitante_id'=>$dueno->id]);

    $this->actingAs($dueno)->get(route('paisajismo.solicitudes.show',$proyecto))->assertOk()->assertSee($proyecto->nombre);
    $this->actingAs($otro)->get(route('paisajismo.solicitudes.show',$proyecto))->assertNotFound();
    $this->actingAs($dueno)->get('/paisajismo/solicitudes/999999')->assertNotFound();
    $this->actingAs($dueno)->get('/paisajismo/solicitudes/crear')->assertOk()->assertSee('Solicitar proyecto');
});

it('pagina propuesta muestra version fecha y decision con motivo', function () {
    $dueno=User::factory()->create(['rol'=>'cliente']);
    $admin=User::factory()->create(['rol'=>'administrador']);
    $proyecto=ProyectoPaisajismo::factory()->create(['solicitante_id'=>$dueno->id,'estado'=>'propuesto']);
    $propuesta=PropuestaDiseno::factory()->create(['proyecto_id'=>$proyecto->id,'autor_id'=>$admin->id,'version'=>2,'decision'=>'rechazada','motivo_decision'=>'El presupuesto no cubre el diseño completo.']);

    $this->actingAs($dueno)->get(route('paisajismo.solicitudes.propuesta',$proyecto))
        ->assertOk()->assertSee('Versión 2')->assertSee('Rechazada')->assertSee('El presupuesto no cubre el diseño completo');
});
