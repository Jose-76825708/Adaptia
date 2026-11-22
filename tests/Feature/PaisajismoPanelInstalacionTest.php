<?php

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('panel instalacion usa ids ins y tiene iniciar avances', function () {
    $contenido=file_get_contents(base_path('resources/views/paisajismo/proyectos/paneles/instalacion.blade.php'));

    expect($contenido)->toContain('id="ins-panel"')
        ->and($contenido)->toContain('Ins-porcentaje')
        ->and($contenido)->toContain('Iniciar instalación');
});

it('ruta iniciar y avances existen protegidas', function () {
    $uris=collect(app('router')->getRoutes()->getRoutes())->map(fn ($ruta)=>$ruta->uri())->all();

    expect($uris)->toContain('paisajismo/proyectos/{proyecto}/instalacion/iniciar')
        ->and($uris)->toContain('paisajismo/proyectos/{proyecto}/instalacion/avances');
});
