<?php

it('el panel abastecimiento usa ids abas y no usa html inseguro', function () {
    $contenido = file_get_contents(base_path('resources/views/mantenimiento/casos/paneles/abastecimiento.blade.php'));

    expect($contenido)->toContain('id="abas-panel"')
        ->and($contenido)->toContain('id="abas-requisicion"')
        ->and($contenido)->toContain('id="abas-recomendaciones"')
        ->and($contenido)->not->toContain('{!!');
});

it('las rutas finales de abastecimiento existen', function () {
    $uris = collect(app('router')->getRoutes()->getRoutes())->map(fn ($ruta) => $ruta->uri())->all();

    expect($uris)->toContain('mantenimiento/casos/{caso}/requisicion/generar')
        ->and($uris)->toContain('mantenimiento/casos/{caso}/requisicion/recibir')
        ->and($uris)->toContain('mantenimiento/casos/{caso}/abastecimiento/preparar');
});
