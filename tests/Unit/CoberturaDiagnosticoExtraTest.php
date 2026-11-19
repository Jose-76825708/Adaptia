<?php

use App\Services\EvaluadorEstadoAreaService;

beforeEach(function () {
    $this->evaluador = new EvaluadorEstadoAreaService();
});

it('reconoce rango minimo alto y tendencia alta', function () {
    $resultado = $this->evaluador->evaluar([
        'sensores' => [[
            'planta_vendida_id' => 1,
            'variables' => [
                'humedad_suelo' => ['ultima' => 5, 'minimo' => 5, 'maximo' => 5, 'promedio' => 5, 'cantidad' => 3],
            ],
        ]],
        'rangos' => [1 => ['humedad_suelo' => ['minimo' => 20, 'maximo' => 60]]],
        'alertas' => [],
    ], EvaluadorEstadoAreaService::ESTANDARES);

    expect($resultado['severidad_global'])->toBeIn(['baja', 'media', 'alta'])
        ->and($resultado['variables_afectadas'])->toContain('humedad_suelo');
});

it('reconoce reincidencia media con dos alertas', function () {
    $resultado = $this->evaluador->evaluar([
        'sensores' => [], 'rangos' => [], 'alertas' => ['temperatura' => 2],
    ], EvaluadorEstadoAreaService::ESTANDARES);

    expect(collect($resultado['hallazgos'])->pluck('severidad'))->toContain('media');
});

it('reconoce reincidencia alta con cuatro alertas', function () {
    $resultado = $this->evaluador->evaluar([
        'sensores' => [], 'rangos' => [], 'alertas' => ['luz' => 4],
    ], EvaluadorEstadoAreaService::ESTANDARES);

    expect(collect($resultado['hallazgos'])->pluck('severidad'))->toContain('alta');
});

it('reconoce texto medio por una palabra critica', function () {
    $resultado = $this->evaluador->evaluar([
        'sensores' => [], 'rangos' => [], 'alertas' => [], 'texto' => ['riesgo en la planta'],
    ], EvaluadorEstadoAreaService::ESTANDARES);

    expect(collect($resultado['hallazgos'])->pluck('severidad'))->toContain('media');
});

it('reconoce texto alto por tres palabras criticas', function () {
    $resultado = $this->evaluador->evaluar([
        'sensores' => [], 'rangos' => [], 'alertas' => [],
        'texto' => ['urgente peligro se muere'],
    ], EvaluadorEstadoAreaService::ESTANDARES);

    expect(collect($resultado['hallazgos'])->pluck('severidad'))->toContain('alta');
});

it('omite rangos incompletos', function () {
    $resultado = $this->evaluador->evaluar([
        'sensores' => [[
            'planta_vendida_id' => 1,
            'variables' => ['luz' => ['ultima' => 10, 'promedio' => 10, 'minimo' => 10, 'maximo' => 10, 'cantidad' => 1]],
        ]],
        'rangos' => [1 => ['luz' => ['minimo' => null, 'maximo' => null]]],
        'alertas' => [],
    ], EvaluadorEstadoAreaService::ESTANDARES);

    expect($resultado['variables_afectadas'])->toBeEmpty()
        ->and($resultado['severidad_global'])->toBe('baja');
});
