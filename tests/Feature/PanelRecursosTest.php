<?php

it('el panel recursos usa ids rec y muestra disponibilidad', function () {
    $contenido = file_get_contents(base_path('resources/views/mantenimiento/casos/paneles/recursos.blade.php'));

    expect($contenido)->toContain('id="rec-panel"')
        ->and($contenido)->toContain('id="rec-listado"')
        ->and($contenido)->toContain('id="rec-veredicto"')
        ->and($contenido)->not->toContain('{!!');
});
