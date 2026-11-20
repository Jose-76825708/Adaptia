<?php

it('el panel cierre usa ids cierre y accion de reapertura con motivo', function () {
    $contenido = file_get_contents(base_path('resources/views/mantenimiento/casos/paneles/cierre.blade.php'));

    expect($contenido)->toContain('id="cierre-panel"')
        ->and($contenido)->toContain('for="cierre-motivo"')
        ->and($contenido)->toContain('id="cierre-cerrar"')
        ->and($contenido)->not->toContain('{!!');
});

it('las rutas finales de cierre existen', function () {
    $uris = collect(app('router')->getRoutes()->getRoutes())->map(fn ($ruta) => $ruta->uri())->all();

    expect($uris)->toContain('mantenimiento/casos/{caso}/cierre/cerrar')
        ->and($uris)->toContain('mantenimiento/casos/{caso}/cierre/reabrir')
        ->and($uris)->toContain('mantenimiento/casos/{caso}/informe');
});
