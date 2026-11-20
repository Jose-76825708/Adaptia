<?php

it('las etapas muestran mensajes de exito y error', function () {
    $contenido = file_get_contents(base_path('resources/views/mantenimiento/casos/etapa.blade.php'));

    expect($contenido)->toContain('etapa-exito')
        ->and($contenido)->toContain('etapa-errores');
});

it('abastecimiento usa selector de requisicion y no entrada manual libre', function () {
    $contenido = file_get_contents(base_path('resources/views/mantenimiento/casos/paneles/abastecimiento.blade.php'));

    expect($contenido)->toContain('name="item_id"')
        ->and($contenido)->toContain('<select')
        ->and($contenido)->not->toContain('type="number" min="1"');
});

it('las tablas de las seis vistas usan encabezado verde centrado', function () {
    foreach ([
        'diagnostico', 'plan', 'recursos', 'abastecimiento', 'ejecucion', 'cierre',
    ] as $panel) {
        $contenido = file_get_contents(base_path("resources/views/mantenimiento/casos/paneles/{$panel}.blade.php"));
        if (str_contains($contenido, '<table')) {
            expect($contenido)->toContain('text-center')
                ->and($contenido)->toContain('bg-[#edf8e7]')
                ->and($contenido)->toContain('text-[#3b5d2d]');
        }
    }
});
