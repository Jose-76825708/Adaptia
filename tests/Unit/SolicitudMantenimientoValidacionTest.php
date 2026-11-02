<?php

use App\Services\SolicitudMantenimientoService;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

test('acepta una solicitud normalizada completamente válida', function () {
    $hoy = Carbon::parse('2026-11-02');

    $resultado = (new SolicitudMantenimientoService)->validar([
        'area_verde' => 'Jardín principal',
        'descripcion' => 'Necesito podar y limpiar el área verde.',
        'fecha_deseada' => '2026-11-10',
        'observaciones' => ['Se requiere atención urgente'],
    ], $hoy);

    expect($resultado)->toBe([
        'area_verde' => 'Jardín principal',
        'descripcion' => 'Necesito podar y limpiar el área verde.',
        'fecha_deseada' => '2026-11-10',
        'observaciones' => ['Se requiere atención urgente'],
    ]);
});

test('rechaza campos obligatorios nulos o vacios', function () {
    $hoy = Carbon::parse('2026-11-02');

    try {
        (new SolicitudMantenimientoService)->validar([
            'area_verde' => null,
            'descripcion' => null,
            'fecha_deseada' => null,
            'observaciones' => [],
        ], $hoy);

        $this->fail('Se esperaba ValidationException');
    } catch (ValidationException $e) {
        $errors = $e->errors();

        expect($errors)->toHaveKeys(['area_verde', 'descripcion'])
            ->and($errors['area_verde'][0])->toBe('El campo área verde es obligatorio.')
            ->and($errors['descripcion'][0])->toBe('El campo descripción es obligatorio.');
    }
});

test('rechaza area_verde fuera del rango permitido', function () {
    $hoy = Carbon::parse('2026-11-02');

    try {
        (new SolicitudMantenimientoService)->validar([
            'area_verde' => 'AB',
            'descripcion' => 'Necesito revisión general del jardín.',
            'fecha_deseada' => '2026-11-12',
            'observaciones' => [],
        ], $hoy);

        $this->fail('Se esperaba ValidationException');
    } catch (ValidationException $e) {
        expect($e->errors()['area_verde'][0])->toBe('El campo área verde debe tener entre 3 y 120 caracteres.');
    }
});

test('rechaza descripcion fuera del rango permitido', function () {
    $hoy = Carbon::parse('2026-11-02');

    try {
        (new SolicitudMantenimientoService)->validar([
            'area_verde' => 'Patio central',
            'descripcion' => 'Poca',
            'fecha_deseada' => '2026-11-12',
            'observaciones' => [],
        ], $hoy);

        $this->fail('Se esperaba ValidationException');
    } catch (ValidationException $e) {
        expect($e->errors()['descripcion'][0])->toBe('El campo descripción debe tener entre 10 y 1000 caracteres.');
    }
});

test('rechaza fechas invalidas o anteriores a hoy', function () {
    $hoy = Carbon::parse('2026-11-02');

    try {
        (new SolicitudMantenimientoService)->validar([
            'area_verde' => 'Patio central',
            'descripcion' => 'Necesito revisión general del jardín.',
            'fecha_deseada' => '31/02/2026',
            'observaciones' => [],
        ], $hoy);

        $this->fail('Se esperaba ValidationException');
    } catch (ValidationException $e) {
        expect($e->errors()['fecha_deseada'][0])->toBe('La fecha deseada no es una fecha válida.');
    }

    try {
        (new SolicitudMantenimientoService)->validar([
            'area_verde' => 'Patio central',
            'descripcion' => 'Necesito revisión general del jardín.',
            'fecha_deseada' => '2026-11-01',
            'observaciones' => [],
        ], $hoy);

        $this->fail('Se esperaba ValidationException');
    } catch (ValidationException $e) {
        expect($e->errors()['fecha_deseada'][0])->toBe('La fecha deseada no puede ser anterior a hoy.');
    }
});

test('rechaza observaciones con elementos demasiado largos o no válidos', function () {
    $hoy = Carbon::parse('2026-11-02');

    try {
        (new SolicitudMantenimientoService)->validar([
            'area_verde' => 'Patio central',
            'descripcion' => 'Necesito revisión general del jardín.',
            'fecha_deseada' => '2026-11-12',
            'observaciones' => [str_repeat('a', 501)],
        ], $hoy);

        $this->fail('Se esperaba ValidationException');
    } catch (ValidationException $e) {
        expect($e->errors()['observaciones.0'][0])->toBe('La observación 1 no debe tener más de 500 caracteres.');
    }
});

test('devuelve todos los errores de validacion juntos', function () {
    $hoy = Carbon::parse('2026-11-02');

    try {
        (new SolicitudMantenimientoService)->validar([
            'area_verde' => 'AB',
            'descripcion' => 'Poca',
            'fecha_deseada' => '2026-11-01',
            'observaciones' => [str_repeat('x', 501)],
        ], $hoy);

        $this->fail('Se esperaba ValidationException');
    } catch (ValidationException $e) {
        $errors = $e->errors();

        expect($errors)->toHaveKeys(['area_verde', 'descripcion', 'fecha_deseada', 'observaciones.0']);
    }
});
