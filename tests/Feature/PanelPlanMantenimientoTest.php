<?php

it('el panel plan usa ids plan y no incluye checklist', function () {
    $contenido = file_get_contents(base_path('resources/views/mantenimiento/casos/paneles/plan.blade.php'));

    expect($contenido)->toContain('id="plan-panel"')
        ->and($contenido)->toContain('for="plan-generar"')
        ->and($contenido)->not->toContain('checklist')
        ->and($contenido)->not->toContain('{!!');
});

it('la ruta plan generar existe al final', function () {
    $uris = collect(app('router')->getRoutes()->getRoutes())->map(fn ($ruta) => $ruta->uri())->all();

    expect($uris)->toContain('mantenimiento/casos/{caso}/plan/generar');
});
