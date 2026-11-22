<?php

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

use App\Models\User;
use App\Models\ProyectoPaisajismo;

it('panel diseno usa ids prefijo dis y formulario con 10 filas', function () {
    $contenido=file_get_contents(base_path('resources/views/paisajismo/proyectos/paneles/diseno.blade.php'));
    expect($contenido)->toContain('id="dis-panel"')
        ->and($contenido)->toContain('@for ($i=0;$i<10;$i++)')
        ->and($contenido)->toContain('name="dis_lineas[');
});

it('cliente ve 404 si proyecto ajeno o sin propuesta', function () {
    $cliente=User::factory()->create(['rol'=>'cliente']);
    $otro=User::factory()->create(['rol'=>'cliente']);
    $proyecto=ProyectoPaisajismo::factory()->create(['solicitante_id'=>$otro->id,'estado'=>'solicitado']);

    $this->actingAs($cliente)->get(route('paisajismo.solicitudes.propuesta',$proyecto))->assertNotFound();
});
