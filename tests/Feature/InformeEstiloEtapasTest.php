<?php

it('el informe usa el shell administrativo y el formato de las etapas', function () {
    $contenido = file_get_contents(base_path('resources/views/mantenimiento/casos/informe.blade.php'));

    expect($contenido)->toContain("@include('partials.sidebar')")
        ->and($contenido)->toContain('bg-[#fbfbfb]')
        ->and($contenido)->toContain('border-[#e4ebdf]')
        ->and($contenido)->toContain('Informe final')
        ->and($contenido)->toContain('Volver al detalle');
});

it('el informe tiene tabla centrada con encabezado verde', function () {
    $contenido = file_get_contents(base_path('resources/views/mantenimiento/casos/informe.blade.php'));

    expect($contenido)->toContain('<table')
        ->and($contenido)->toContain('text-center')
        ->and($contenido)->toContain('bg-[#edf8e7]')
        ->and($contenido)->toContain('text-[#3b5d2d]');
});
