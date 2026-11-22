<?php

use App\Models\ProyectoPaisajismo;
use App\Models\User;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('panel entrega usa ids ent y rutas existen', function () {
    $contenido=file_get_contents(base_path('resources/views/paisajismo/proyectos/paneles/entrega.blade.php'));
    expect($contenido)->toContain('id="ent-panel"')
        ->and($contenido)->toContain('ent-resumen')
        ->and($contenido)->toContain('ent-recomendaciones');

    $uris=collect(app('router')->getRoutes()->getRoutes())->map(fn ($ruta)=>$ruta->uri())->all();
    expect($uris)->toContain('paisajismo/proyectos/{proyecto}/entrega')
        ->and($uris)->toContain('paisajismo/solicitudes/{proyecto}/resumen')
        ->and($uris)->toContain('paisajismo/solicitudes/{proyecto}/satisfaccion')
        ->and($uris)->toContain('paisajismo/solicitudes/{proyecto}/mantenimiento');
});

it('resumen y mantenimiento rechazan proyecto no entregado', function () {
    $cliente=User::factory()->create(['rol'=>'cliente']);
    $proyecto=ProyectoPaisajismo::factory()->create(['solicitante_id'=>$cliente->id,'estado'=>'en_instalacion']);

    $this->actingAs($cliente)->get(route('paisajismo.solicitudes.resumen',$proyecto))->assertNotFound();
    $this->actingAs($cliente)->post(route('paisajismo.solicitudes.mantenimiento',$proyecto))->assertNotFound();
});
