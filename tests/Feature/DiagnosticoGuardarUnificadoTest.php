<?php

it('el panel de diagnostico tiene un unico guardado con nota y foto', function () {
    $contenido = file_get_contents(base_path('resources/views/mantenimiento/casos/paneles/diagnostico.blade.php'));

    expect($contenido)->toContain('Guardar diagnóstico')
        ->and($contenido)->toContain('mantenimiento.casos.diagnostico.guardar')
        ->and($contenido)->not->toContain('Registrar nota')
        ->and($contenido)->not->toContain('Registrar foto')
        ->and($contenido)->toContain('enctype="multipart/form-data"');
});

it('la ruta unificada de guardado existe', function () {
    $uris = collect(app('router')->getRoutes()->getRoutes())->map(fn ($ruta) => $ruta->uri())->all();

    expect($uris)->toContain('mantenimiento/casos/{caso}/diagnostico/guardar');
});

it('la vista de etapa muestra mensajes de exito y error con formato', function () {
    $contenido = file_get_contents(base_path('resources/views/mantenimiento/casos/etapa.blade.php'));

    expect($contenido)->toContain('etapa-exito')
        ->and($contenido)->toContain('etapa-errores');
});

it('el panel de abastecimiento elimina el boton generar requisicion sin contexto', function () {
    $contenido = file_get_contents(base_path('resources/views/mantenimiento/casos/paneles/abastecimiento.blade.php'));

    expect($contenido)->not->toContain('Generar requisición')
        ->and($contenido)->toContain('Registrar recepción')
        ->and($contenido)->toContain('Marcar preparado');
});
