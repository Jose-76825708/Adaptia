<?php

it('panel diseno conserva concepto filas planta cantidad decision y motivo con old', function () {
    $contenido=file_get_contents(base_path('resources/views/paisajismo/proyectos/paneles/diseno.blade.php'));

    expect($contenido)->toContain("old('dis_concepto")
        ->and($contenido)->toContain("old('dis_lineas")
        ->and($contenido)->toContain("old('dis_decision")
        ->and($contenido)->toContain("old('dis_motivo")
        ->and($contenido)->not->toContain("name=\"concepto\"")
        ->and($contenido)->not->toContain("name=\"decision\"");
});

it('panel instalacion conserva descripcion y porcentaje con nombres ins', function () {
    $contenido=file_get_contents(base_path('resources/views/paisajismo/proyectos/paneles/instalacion.blade.php'));

    expect($contenido)->toContain("old('ins_descripcion")
        ->and($contenido)->toContain("old('ins_porcentaje")
        ->and($contenido)->not->toContain("name=\"descripcion\"")
        ->and($contenido)->not->toContain("name=\"porcentaje\"");
});

it('panel entrega conserva resumen y recomendaciones con nombres ent', function () {
    $contenido=file_get_contents(base_path('resources/views/paisajismo/proyectos/paneles/entrega.blade.php'));

    expect($contenido)->toContain("old('ent_resumen")
        ->and($contenido)->toContain("old('ent_recomendaciones")
        ->and($contenido)->not->toContain("name=\"resumen\"")
        ->and($contenido)->not->toContain("name=\"recomendaciones\"");
});
