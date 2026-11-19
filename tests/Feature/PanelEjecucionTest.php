<?php

it('el panel ejecucion contiene checklist eventos y resolucion', function () {
    $contenido = file_get_contents(base_path('resources/views/mantenimiento/casos/paneles/ejecucion.blade.php'));

    expect($contenido)->toContain('id="ejec-panel"')
        ->and($contenido)->toContain('id="ejec-checklist"')
        ->and($contenido)->toContain('id="ejec-evento"')
        ->and($contenido)->not->toContain('{!!');
});

it('las rutas finales de ejecucion existen', function () {
    $uris = collect(app('router')->getRoutes()->getRoutes())->map(fn ($ruta) => $ruta->uri())->all();

    expect($uris)->toContain('mantenimiento/casos/{caso}/ejecucion/evento')
        ->and($uris)->toContain('mantenimiento/casos/{caso}/ejecucion/checklist/{item}')
        ->and($uris)->toContain('mantenimiento/casos/{caso}/ejecucion/incidencias/{evento}/resolver');
});
