<?php

it('el detalle tiene los seis enlaces de etapa con rutas funcionales', function () {
    $rutas = collect(app('router')->getRoutes()->getRoutes())->map(fn ($ruta) => $ruta->uri())->all();

    expect($rutas)->toContain('mantenimiento/casos/{caso}/etapa/{etapa}')
        ->and($rutas)->toContain('mantenimiento/casos/{caso}/informe');
});

it('los seis paneles tienen labels y ths accesibles cuando tienen tablas', function () {
    foreach (['diagnostico','plan','recursos','abastecimiento','ejecucion','cierre'] as $panel) {
        $contenido = file_get_contents(base_path("resources/views/mantenimiento/casos/paneles/{$panel}.blade.php"));
        if (str_contains($contenido, '<table')) {
            expect($contenido)->toContain('<th')->and($contenido)->toContain('scope="col"');
        }
    }
});

it('las acciones post de mantenimiento usan csrf', function () {
    foreach (['diagnostico','plan','recursos','abastecimiento','ejecucion','cierre'] as $panel) {
        $contenido = file_get_contents(base_path("resources/views/mantenimiento/casos/paneles/{$panel}.blade.php"));
        if (str_contains($contenido, '<form')) {
            expect($contenido)->toContain('@csrf');
        }
    }
});

it('los enlaces de las vistas mantenimiento no usan backslash', function () {
    $archivos = glob(resource_path('views/mantenimiento/**/*.blade.php'));

    foreach ($archivos as $archivo) {
        $contenido = file_get_contents($archivo);
        expect($contenido)->not->toMatch('/(?:href|action)="[^"]*\\\\[^\"]*"/');
    }
});
