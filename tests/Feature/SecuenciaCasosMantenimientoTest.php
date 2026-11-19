<?php

it('el detalle ofrece seis botones de secuencia y no incluye todos los paneles directamente', function () {
    $contenido = file_get_contents(base_path('resources/views/mantenimiento/casos/show.blade.php'));

    expect($contenido)->toContain('Diagnosticar')
        ->and($contenido)->toContain('Plan de mantenimiento')
        ->and($contenido)->toContain('Disponibilidad de recursos')
        ->and($contenido)->toContain('Abastecimiento')
        ->and($contenido)->toContain('Ejecución')
        ->and($contenido)->toContain('Cierre / Reapertura')
        ->and($contenido)->not->toContain("@includeIf('mantenimiento.casos.paneles.diagnostico'");
});

it('la vista de etapa tiene volver siguiente o finalizar', function () {
    $contenido = file_get_contents(base_path('resources/views/mantenimiento/casos/etapa.blade.php'));

    expect($contenido)->toContain('Volver')
        ->and($contenido)->toContain('Siguiente')
        ->and($contenido)->toContain('Finalizar');

    $diagnostico = file_get_contents(base_path('resources/views/mantenimiento/casos/paneles/diagnostico.blade.php'));
    $plan = file_get_contents(base_path('resources/views/mantenimiento/casos/paneles/plan.blade.php'));

    expect($diagnostico)->toContain('Guardar diagnóstico')
        ->and($plan)->toContain('Guardar plan');
});
