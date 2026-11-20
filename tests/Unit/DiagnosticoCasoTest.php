<?php

use App\Services\EvaluadorEstadoAreaService;

it('genera un diagnostico estructurado con severidad y justificacion', function () {
    $evaluador = new EvaluadorEstadoAreaService();

    $evaluacion = $evaluador->evaluar([
        'sensores' => [[
            'planta_vendida_id' => 1,
            'variables' => [
                'humedad_suelo' => ['ultima' => 20.0, 'minimo' => 18.0, 'maximo' => 22.0, 'promedio' => 20.0, 'cantidad' => 5],
            ],
        ]],
        'rangos' => [1 => ['humedad_suelo' => ['minimo' => 30.0, 'maximo' => 60.0]]],
        'alertas' => ['humedad_suelo' => 2],
    ], EvaluadorEstadoAreaService::ESTANDARES);

    expect($evaluacion)->toHaveKeys(['severidad_global', 'hallazgos', 'variables_afectadas', 'justificacion'])
        ->and($evaluacion['justificacion'])->not->toBeEmpty()
        ->and($evaluacion['hallazgos'])->not->toBeEmpty();
});

it('incluye patrones de deterioro en el diagnostico estructurado', function () {
    $evaluador = new EvaluadorEstadoAreaService();

    $evaluacion = $evaluador->evaluar([
        'sensores' => [[
            'planta_vendida_id' => 1,
            'variables' => [
                'temperatura' => ['ultima' => 35.0, 'minimo' => 34.0, 'maximo' => 35.0, 'promedio' => 34.5, 'cantidad' => 4],
            ],
        ]],
        'rangos' => [1 => ['temperatura' => ['minimo' => 15.0, 'maximo' => 30.0]]],
        'alertas' => [],
    ], EvaluadorEstadoAreaService::ESTANDARES);

    expect(collect($evaluacion['hallazgos'])->pluck('tipo'))->toContain('rango')
        ->and($evaluacion['variables_afectadas'])->toContain('temperatura');
});
