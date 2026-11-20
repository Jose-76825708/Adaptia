<?php

it('la vista ejecucion usa texto para tarea y no exige tarea para inicio', function () {
    $contenido = file_get_contents(base_path('resources/views/mantenimiento/casos/paneles/ejecucion.blade.php'));

    expect($contenido)->toContain('name="tarea"')
        ->and($contenido)->toContain('type="text"')
        ->and($contenido)->not->toContain('name="tarea_id"');
});

it('el controlador acepta tarea textual sin convertirla a id', function () {
    $contenido = file_get_contents(base_path('app/Http/Controllers/EjecucionMantenimientoController.php'));

    expect($contenido)->not->toContain('(int) $request->input(\'tarea_id\')')
        ->and($contenido)->toContain('$request->input(\'tarea\', \'\')');
});
