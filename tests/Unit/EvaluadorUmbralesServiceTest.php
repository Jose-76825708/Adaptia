<?php

use App\Models\Planta;
use App\Services\EvaluadorUmbralesService;

function plantaConRangosCompletos(): Planta
{
    return new Planta([
        'nombre' => 'Aloe Vera',
        'humedad_suelo_min' => 20,
        'humedad_suelo_max' => 60,
        'temperatura_min' => 15,
        'temperatura_max' => 30,
        'humedad_ambiental_min' => 35,
        'humedad_ambiental_max' => 75,
        'luz_min' => 200,
        'luz_max' => 1200,
    ]);
}

test('considera dentro del rango los valores iguales a los límites', function () {
    $desviaciones = app(EvaluadorUmbralesService::class)->evaluar(
        plantaConRangosCompletos(),
        [
            'humedad_suelo' => 20,
            'temperatura' => 30,
            'humedad_ambiental' => 35,
            'luz' => 1200,
        ],
    );

    expect($desviaciones)->toBeEmpty();
});

test('identifica valores debajo o encima de los límites de las cuatro variables', function () {
    $desviaciones = app(EvaluadorUmbralesService::class)->evaluar(
        plantaConRangosCompletos(),
        [
            'humedad_suelo' => 19.5,
            'temperatura' => 30.1,
            'humedad_ambiental' => 34.9,
            'luz' => 1200.1,
        ],
    );

    expect($desviaciones)->toHaveCount(4)
        ->and($desviaciones[0])->toMatchArray([
            'tipo' => 'riego',
            'variable' => 'humedad_suelo',
            'valor_medido' => 19.5,
            'limite' => 20.0,
            'direccion' => 'bajo',
            'rango_esperado' => ['minimo' => 20.0, 'maximo' => 60.0],
            'mensaje' => 'La humedad del suelo está por debajo del rango recomendado para tu Aloe Vera. Medición: 19.5 %; rango esperado: 20–60 %.',
        ])
        ->and($desviaciones[1])->toMatchArray([
            'tipo' => 'cuidado_planta',
            'variable' => 'temperatura',
            'valor_medido' => 30.1,
            'limite' => 30.0,
            'direccion' => 'alto',
            'rango_esperado' => ['minimo' => 15.0, 'maximo' => 30.0],
            'mensaje' => 'La temperatura está por encima del rango recomendado para tu Aloe Vera. Medición: 30.1 °C; rango esperado: 15–30 °C.',
        ])
        ->and($desviaciones[2])->toMatchArray([
            'tipo' => 'cuidado_planta',
            'variable' => 'humedad_ambiental',
            'valor_medido' => 34.9,
            'limite' => 35.0,
            'direccion' => 'bajo',
        ])
        ->and($desviaciones[3])->toMatchArray([
            'tipo' => 'cuidado_planta',
            'variable' => 'luz',
            'valor_medido' => 1200.1,
            'limite' => 1200.0,
            'direccion' => 'alto',
        ]);
});

test('omite variables que no tienen ambos límites configurados', function () {
    $planta = new Planta([
        'nombre' => 'Aloe Vera',
        'humedad_suelo_min' => 20,
        'humedad_suelo_max' => null,
        'temperatura_min' => null,
        'temperatura_max' => 30,
        'humedad_ambiental_min' => 35,
        'humedad_ambiental_max' => 75,
        'luz_min' => null,
        'luz_max' => null,
    ]);

    $desviaciones = app(EvaluadorUmbralesService::class)->evaluar($planta, [
        'humedad_suelo' => 10,
        'temperatura' => 40,
        'humedad_ambiental' => 20,
        'luz' => 5000,
    ]);

    expect($desviaciones)->toBe([
        [
            'tipo' => 'cuidado_planta',
            'variable' => 'humedad_ambiental',
            'valor_medido' => 20.0,
            'limite' => 35.0,
            'direccion' => 'bajo',
            'rango_esperado' => ['minimo' => 35.0, 'maximo' => 75.0],
            'mensaje' => 'La humedad ambiental está por debajo del rango recomendado para tu Aloe Vera. Medición: 20 %; rango esperado: 35–75 %.',
        ],
    ]);
});

test('la humedad del suelo por encima del máximo se clasifica como cuidado de planta', function () {
    $desviaciones = app(EvaluadorUmbralesService::class)->evaluar(
        plantaConRangosCompletos(),
        [
            'humedad_suelo' => 61,
            'temperatura' => 22,
            'humedad_ambiental' => 50,
            'luz' => 500,
        ],
    );

    expect($desviaciones)->toHaveCount(1)
        ->and($desviaciones[0])->toMatchArray([
            'tipo' => 'cuidado_planta',
            'variable' => 'humedad_suelo',
            'valor_medido' => 61.0,
            'limite' => 60.0,
            'direccion' => 'alto',
        ]);
});
