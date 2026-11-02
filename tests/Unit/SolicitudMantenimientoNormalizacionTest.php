<?php

use App\Services\SolicitudMantenimientoService;

test('devuelve el contrato completo con valores nulos cuando la entrada esta vacia', function () {
    $resultado = (new SolicitudMantenimientoService)->normalizar([]);

    expect($resultado)->toBe([
        'area_verde' => null,
        'descripcion' => null,
        'fecha_deseada' => null,
        'observaciones' => [],
    ]);
});

test('descarta claves no permitidas y conserva solo el contrato', function () {
    $resultado = (new SolicitudMantenimientoService)->normalizar([
        'area_verde' => 'Jardín principal',
        'descripcion' => 'Necesito poda',
        'fecha_deseada' => '2026-05-12',
        'observaciones' => ['Primera nota'],
        'user_id' => 99,
        'estado' => 'pendiente',
        'id' => 77,
    ]);

    expect($resultado)->toBe([
        'area_verde' => 'Jardín principal',
        'descripcion' => 'Necesito poda',
        'fecha_deseada' => '2026-05-12',
        'observaciones' => ['Primera nota'],
    ]);
});

test('normaliza textos recortando espacios y convirtiendo vacios a null', function () {
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
        'observaciones' => [],
    ]);
});

test('acepta fechas en formato Y-m-d y d/m/Y y normaliza a Y-m-d', function () {
    $resultado = (new SolicitudMantenimientoService)->normalizar([
        'area_verde' => 'Patio',
        'descripcion' => 'Revision',
        'fecha_deseada' => '12/05/2026',
        'observaciones' => [],
    ]);

    expect($resultado['fecha_deseada'])->toBe('2026-05-12');

    $resultado2 = (new SolicitudMantenimientoService)->normalizar([
        'area_verde' => 'Patio',
        'descripcion' => 'Revision',
        'fecha_deseada' => '2026-05-12',
        'observaciones' => [],
    ]);

    expect($resultado2['fecha_deseada'])->toBe('2026-05-12');
});

test('deja una fecha que no puede interpretarse como texto limpio sin convertir', function () {
    $resultado = (new SolicitudMantenimientoService)->normalizar([
        'area_verde' => 'Patio',
        'descripcion' => 'Revision',
        'fecha_deseada' => '31/02/2026',
        'observaciones' => [],
    ]);

    expect($resultado['fecha_deseada'])->toBe('31/02/2026');
});

test('limita observaciones a seis items y las normaliza en array', function () {
    $resultado = (new SolicitudMantenimientoService)->normalizar([
        'area_verde' => 'Patio',
        'descripcion' => 'Revision',
        'fecha_deseada' => null,
        'observaciones' => ['uno', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete'],
    ]);

    expect($resultado['observaciones'])->toHaveCount(6)
        ->and($resultado['observaciones'])->toBe(['uno', 'dos', 'tres', 'cuatro', 'cinco', 'seis']);
});

test('ignora valores no escalares para textos y deja null', function () {
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
        'observaciones' => ['es correcto'],
    ]);
});

test('es idempotente al llamarse dos veces sobre la misma entrada', function () {
    $entrada = [
        'area_verde' => '  Patio  ',
        'descripcion' => ' Poda  ',
        'fecha_deseada' => '2026-05-21',
        'observaciones' => ['A', 'B'],
    ];

    $primera = (new SolicitudMantenimientoService)->normalizar($entrada);
    $segunda = (new SolicitudMantenimientoService)->normalizar($primera);

    expect($segunda)->toBe($primera)
        ->and($primera)->toBe([
            'area_verde' => 'Patio',
            'descripcion' => 'Poda',
            'fecha_deseada' => '2026-05-21',
            'observaciones' => ['A', 'B'],
        ]);
});
