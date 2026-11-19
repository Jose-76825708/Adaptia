<?php

use App\Services\EvaluadorEstadoAreaService;

beforeEach(function () {
    $this->evaluador = new EvaluadorEstadoAreaService();
});

it('marca alta cuando el promedio esta fuera del rango', function () {
    $resultado = $this->evaluador->evaluar([
        'sensores' => [[
            'planta_vendida_id' => 1,
            'variables' => [
                'humedad_suelo' => ['ultima' => 20.0, 'minimo' => 18.0, 'maximo' => 22.0, 'promedio' => 20.0, 'cantidad' => 5],
            ],
        ]],
        'rangos' => [1 => ['humedad_suelo' => ['minimo' => 30.0, 'maximo' => 60.0]]],
        'alertas' => [],
    ], [
        'riesgo' => ['misma_planta_alta' => 2],
        'reincidencia' => ['media' => 2, 'alta' => 4],
    ]);

    expect($resultado['severidad_global'])->toBe('media')
        ->and($resultado['hallazgos'])->not->toBeEmpty()
        ->and($resultado['hallazgos'][0]['severidad'])->toBe('alta');
});

it('marca media cuando el promedio esta dentro pero el minimo o maximo salen del rango', function () {
    $resultado = $this->evaluador->evaluar([
        'sensores' => [[
            'planta_vendida_id' => 1,
            'variables' => [
                'temperatura' => ['ultima' => 24.0, 'minimo' => 10.0, 'maximo' => 25.0, 'promedio' => 24.0, 'cantidad' => 4],
            ],
        ]],
        'rangos' => [1 => ['temperatura' => ['minimo' => 18.0, 'maximo' => 28.0]]],
        'alertas' => [],
    ], [
        'riesgo' => ['misma_planta_alta' => 2],
        'reincidencia' => ['media' => 2, 'alta' => 4],
    ]);

    expect($resultado['severidad_global'])->toBe('baja')
        ->and($resultado['hallazgos'][0]['severidad'])->toBe('media');
});

it('marca tendencia media cuando la ultima sale y el promedio sigue dentro', function () {
    $resultado = $this->evaluador->evaluar([
        'sensores' => [[
            'planta_vendida_id' => 1,
            'variables' => [
                'luz' => ['ultima' => 900.0, 'minimo' => 300.0, 'maximo' => 900.0, 'promedio' => 500.0, 'cantidad' => 3],
            ],
        ]],
        'rangos' => [1 => ['luz' => ['minimo' => 100.0, 'maximo' => 800.0]]],
        'alertas' => [],
    ], [
        'riesgo' => ['misma_planta_alta' => 2],
        'reincidencia' => ['media' => 2, 'alta' => 4],
    ]);

    expect($resultado['severidad_global'])->toBe('baja')
        ->and($resultado['hallazgos'][0]['severidad'])->toBe('media');
});

it('omite variables sin ambos limites y agrega un hallazgo de texto cuando no hay hallazgos', function () {
    $resultado = $this->evaluador->evaluar([
        'sensores' => [[
            'planta_vendida_id' => 1,
            'variables' => [
                'humedad_suelo' => ['ultima' => 20.0, 'minimo' => 20.0, 'maximo' => 20.0, 'promedio' => 20.0, 'cantidad' => 1],
            ],
        ]],
        'rangos' => [1 => ['humedad_suelo' => ['minimo' => null, 'maximo' => null]]],
        'alertas' => [],
    ], [
        'riesgo' => ['misma_planta_alta' => 2],
        'reincidencia' => ['media' => 2, 'alta' => 4],
    ]);

    expect($resultado['severidad_global'])->toBe('baja')
        ->and($resultado['hallazgos'])->toHaveCount(1)
        ->and($resultado['hallazgos'][0]['tipo'])->toBe('texto');
});

it('puntua reincidencia e palabras criticas segun los umbrales', function () {
    $resultado = $this->evaluador->evaluar([
        'sensores' => [],
        'rangos' => [],
        'alertas' => [
            'humedad_suelo' => 4,
        ],
        'texto' => [
            'urgente peligro se muere peligro desplomado',
        ],
    ], [
        'riesgo' => ['misma_planta_alta' => 2],
        'reincidencia' => ['media' => 2, 'alta' => 4],
        'texto' => ['criticas' => ['urgente', 'peligro', 'riesgo', 'se muere', 'caído', 'desplomado']],
    ]);

    expect($resultado['severidad_global'])->toBe('alta')
        ->and($resultado['hallazgos'])->toHaveCount(2);
});

it('detecta riesgo con dos variables altas de la misma planta', function () {
    $resultado = $this->evaluador->evaluar([
        'sensores' => [[
            'planta_vendida_id' => 7,
            'variables' => [
                'humedad_suelo' => ['ultima' => 10.0, 'minimo' => 10.0, 'maximo' => 10.0, 'promedio' => 10.0, 'cantidad' => 2],
                'temperatura' => ['ultima' => 50.0, 'minimo' => 50.0, 'maximo' => 50.0, 'promedio' => 50.0, 'cantidad' => 2],
            ],
        ]],
        'rangos' => [7 => [
            'humedad_suelo' => ['minimo' => 30.0, 'maximo' => 60.0],
            'temperatura' => ['minimo' => 15.0, 'maximo' => 30.0],
        ]],
        'alertas' => [],
    ], [
        'riesgo' => ['misma_planta_alta' => 2],
        'reincidencia' => ['media' => 2, 'alta' => 4],
    ]);

    expect($resultado['severidad_global'])->toBe('alta')
        ->and(collect($resultado['hallazgos'])->contains(fn (array $hallazgo) => $hallazgo['tipo'] === 'riesgo'))->toBeTrue();
});
