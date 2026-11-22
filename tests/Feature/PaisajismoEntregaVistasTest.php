<?php

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('panel entrega usa ids ent y contiene resumen', function () {
    $contenido=file_get_contents(base_path('resources/views/paisajismo/proyectos/paneles/entrega.blade.php'));
    expect($contenido)->toContain('id="ent-panel"')
        ->and($contenido)->toContain('ent-resumen')
        ->and($contenido)->toContain('ent-recomendaciones');
});

it('rutas entrega satisfaccion resumen y mantenimiento existen', function () {
    $uris=collect(app('router')->getRoutes()->getRoutes())->map(fn ($ruta)=>$ruta->uri())->all();
    expect($uris)->toContain('paisajismo/proyectos/{proyecto}/entrega')
        ->and($uris)->toContain('paisajismo/solicitudes/{proyecto}/resumen')
        ->and($uris)->toContain('paisajismo/solicitudes/{proyecto}/satisfaccion')
        ->and($uris)->toContain('paisajismo/solicitudes/{proyecto}/mantenimiento');
});
