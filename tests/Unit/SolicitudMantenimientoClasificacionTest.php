<?php

use App\Services\SolicitudMantenimientoService;
use Carbon\Carbon;

test('clasifica una solicitud de riego cuando existe la palabra clave', function () {
    $hoy = Carbon::parse('2026-11-02');

    $resultado = (new SolicitudMantenimientoService)->clasificar([
        'area_verde' => 'Jardín principal',
        'descripcion' => 'El suelo está muy seco y necesita riego urgente.',
        'fecha_deseada' => '2026-11-04',
        'observaciones' => ['La planta parece necesitar agua.'],
    ], $hoy);

    expect($resultado)->toBe([
        'tipo_mantenimiento' => 'riego',
        'urgencia' => 'alta',
    ]);
});

test('clasifica una solicitud de poda cuando la descripcion lo indica', function () {
    $hoy = Carbon::parse('2026-11-02');

    $resultado = (new SolicitudMantenimientoService)->clasificar([
        'area_verde' => 'Patio central',
        'descripcion' => 'Necesito podar algunas ramas largas.',
        'fecha_deseada' => null,
        'observaciones' => [],
    ], $hoy);

    expect($resultado)->toBe([
        'tipo_mantenimiento' => 'poda',
        'urgencia' => 'media',
    ]);
});

test('clasifica control de plagas con prioridad sobre otros tipos', function () {
    $hoy = Carbon::parse('2026-11-02');

    $resultado = (new SolicitudMantenimientoService)->clasificar([
        'area_verde' => 'Jardín',
        'descripcion' => 'Hay plagas y también falta riego.',
        'fecha_deseada' => null,
        'observaciones' => ['Hay insectos en las hojas.'],
    ], $hoy);

    expect($resultado['tipo_mantenimiento'])->toBe('control_plagas');
});

test('devuelve general cuando no hay palabras clave', function () {
    $hoy = Carbon::parse('2026-11-02');

    $resultado = (new SolicitudMantenimientoService)->clasificar([
        'area_verde' => 'Jardín',
        'descripcion' => 'Necesito revisar la zona.',
        'fecha_deseada' => '2026-12-20',
        'observaciones' => [],
    ], $hoy);

    expect($resultado)->toBe([
        'tipo_mantenimiento' => 'general',
        'urgencia' => 'baja',
    ]);
});

test('evalua urgencia alta por fecha dentro de tres dias', function () {
    $hoy = Carbon::parse('2026-11-02');

    $resultado = (new SolicitudMantenimientoService)->clasificar([
        'area_verde' => 'Patio',
        'descripcion' => 'Necesito revisión general del jardín.',
        'fecha_deseada' => '2026-11-04',
        'observaciones' => [],
    ], $hoy);

    expect($resultado['urgencia'])->toBe('alta');
});

test('evalua urgencia baja por fecha mayor a treinta dias sin señales de alta', function () {
    $hoy = Carbon::parse('2026-11-02');

    $resultado = (new SolicitudMantenimientoService)->clasificar([
        'area_verde' => 'Patio',
        'descripcion' => 'Necesito revisión estética del jardín.',
        'fecha_deseada' => '2026-12-20',
        'observaciones' => ['Sin apuro.'],
    ], $hoy);

    expect($resultado['urgencia'])->toBe('baja');
});

test('ignora mayusculas y tildes y evita falsos positivos por subcadenas', function () {
    $hoy = Carbon::parse('2026-11-02');

    $resultado = (new SolicitudMantenimientoService)->clasificar([
        'area_verde' => 'Patio',
        'descripcion' => 'Hay PLAGAS en el área y además se muere la planta.',
        'fecha_deseada' => null,
        'observaciones' => ['No es un arreglo de podadora ni de riego.'],
    ], $hoy);

    expect($resultado['tipo_mantenimiento'])->toBe('control_plagas')
        ->and($resultado['urgencia'])->toBe('alta');
});

test('el resultado debe ser solo tipo_mantenimiento y urgencia', function () {
    $resultado = (new SolicitudMantenimientoService)->clasificar([
        'area_verde' => 'Patio',
        'descripcion' => 'Necesito limpieza.',
        'fecha_deseada' => null,
        'observaciones' => [],
    ], Carbon::parse('2026-11-02'));

    expect(array_keys($resultado))->toBe(['tipo_mantenimiento', 'urgencia']);
});

test('las frases de baja prioridad se evalúan antes que palabras criticas para evitar falsos positivos', function () {
    $hoy = Carbon::parse('2026-11-02');

    $resultado = (new SolicitudMantenimientoService)->clasificar([
        'area_verde' => 'Patio',
        'descripcion' => 'Sin apuro, solo es una revisión estética. No urgente.',
        'fecha_deseada' => '2026-12-20',
        'observaciones' => [],
    ], $hoy);

    expect($resultado['urgencia'])->toBe('baja');
});

test('la urgencia usa los catálogos publicos y siempre devuelve valores permitidos', function () {
    $hoy = Carbon::parse('2026-11-02');

    $resultado = (new SolicitudMantenimientoService)->clasificar([
        'area_verde' => 'Jardín',
        'descripcion' => 'Intento limpieza general.',
        'fecha_deseada' => '2026-11-08',
        'observaciones' => [],
    ], $hoy);

    expect(in_array($resultado['tipo_mantenimiento'], SolicitudMantenimientoService::TIPOS, true))->toBeTrue()
        ->and(in_array($resultado['urgencia'], SolicitudMantenimientoService::URGENCIAS, true))->toBeTrue();
});
