<?php

use App\Services\SolicitudMantenimientoService;

test('devuelve el contrato completo con observaciones nulas cuando la entrada esta vacia', function () {
    $resultado = (new SolicitudMantenimientoService)->normalizar([]);

    expect($resultado)->toBe([
        'area_verde' => null,
        'descripcion' => null,
        'fecha_deseada' => null,
        'observaciones' => null,
    ]);
});

test('descarta claves no permitidas y conserva solo el contrato', function () {
    $resultado = (new SolicitudMantenimientoService)->normalizar([
        'area_verde' => 'Jardín principal',
        'descripcion' => 'Necesito poda',
        'fecha_deseada' => '2026-05-12',
        'observaciones' => 'Primera nota',
        'user_id' => 99,
        'estado' => 'pendiente',
        'id' => 77,
    ]);

    expect($resultado)->toBe([
        'area_verde' => 'Jardín principal',
        'descripcion' => 'Necesito poda',
        'fecha_deseada' => '2026-05-12',
        'observaciones' => 'Primera nota',
    ]);
});

test('normaliza observaciones ausentes, nulas, vacias y de texto a null o texto recortado', function () {
    $casos = [
        'sin clave' => [[], null],
        'null' => [['observaciones' => null], null],
        'string vacio' => [['observaciones' => ''], null],
        'string espaciado' => [['observaciones' => '   '], null],
        'array vacio' => [['observaciones' => []], null],
        'texto con espacios' => [['observaciones' => '  Necesito revisar  el jardín  '], 'Necesito revisar el jardín'],
        'valor no escalar' => [['observaciones' => ['vacio']], null],
    ];

    foreach ($casos as $nombre => [$entrada, $esperado]) {
        $resultado = (new SolicitudMantenimientoService)->normalizar($entrada + [
            'area_verde' => 'Jardín principal',
            'descripcion' => 'Necesito revisión del área verde.',
            'fecha_deseada' => '2026-11-10',
        ]);

        expect($resultado['observaciones'])->toBe($esperado, $nombre);
    }
});

test('acepta fechas en formato Y-m-d y d/m/Y y normaliza a Y-m-d', function () {
    $resultado = (new SolicitudMantenimientoService)->normalizar([
        'area_verde' => 'Patio',
        'descripcion' => 'Revision',
        'fecha_deseada' => '12/05/2026',
        'observaciones' => null,
    ]);

    expect($resultado['fecha_deseada'])->toBe('2026-05-12');

    $resultado2 = (new SolicitudMantenimientoService)->normalizar([
        'area_verde' => 'Patio',
        'descripcion' => 'Revision',
        'fecha_deseada' => '2026-05-12',
        'observaciones' => null,
    ]);

    expect($resultado2['fecha_deseada'])->toBe('2026-05-12');
});

test('deja una fecha que no puede interpretarse como texto limpio sin convertir', function () {
    $resultado = (new SolicitudMantenimientoService)->normalizar([
        'area_verde' => 'Patio',
        'descripcion' => 'Revision',
        'fecha_deseada' => '31/02/2026',
        'observaciones' => null,
    ]);

    expect($resultado['fecha_deseada'])->toBe('31/02/2026');
});

test('recorta espacios y convierte vacios a null en area_verde y descripcion', function () {
    $resultado = (new SolicitudMantenimientoService)->normalizar([
        'area_verde' => '  Jardín   central  ',
        'descripcion' => "  Necesito\n\n poda  ",
        'fecha_deseada' => '   ',
        'observaciones' => '   ',
    ]);

    expect($resultado)->toBe([
        'area_verde' => 'Jardín central',
        'descripcion' => 'Necesito poda',
        'fecha_deseada' => null,
        'observaciones' => null,
    ]);
});

test('ignora valores no escalares para textos y deja null en los campos de texto', function () {
    $resultado = (new SolicitudMantenimientoService)->normalizar([
        'area_verde' => ['no', 'permitido'],
        'descripcion' => ['objeto'],
        'fecha_deseada' => ['2026-05-11'],
        'observaciones' => ['es correcto'],
    ]);

    expect($resultado)->toBe([
        'area_verde' => null,
        'descripcion' => null,
        'fecha_deseada' => null,
        'observaciones' => null,
    ]);
});

test('es idempotente al llamarse dos veces sobre la misma entrada', function () {
    $entrada = [
        'area_verde' => '  Patio  ',
        'descripcion' => ' Poda  ',
        'fecha_deseada' => '2026-05-21',
        'observaciones' => '   Revisar humedad   ',
    ];

    $primera = (new SolicitudMantenimientoService)->normalizar($entrada);
    $segunda = (new SolicitudMantenimientoService)->normalizar($primera);

    expect($segunda)->toBe($primera)
        ->and($primera)->toBe([
            'area_verde' => 'Patio',
            'descripcion' => 'Poda',
            'fecha_deseada' => '2026-05-21',
            'observaciones' => 'Revisar humedad',
        ]);
});
