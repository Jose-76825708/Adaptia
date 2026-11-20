<?php

use App\Services\EvidenciaCasoService;

it('resume lecturas vacias con una estructura estable', function () {
    $service = new EvidenciaCasoService();

    expect($service->resumirLecturas([]))->toBe([
        'cantidad' => 0,
        'ultima' => null,
        'minimo' => null,
        'maximo' => null,
        'promedio' => null,
    ]);
});

it('resume una sola lectura sin perder la ultima medicion', function () {
    $service = new EvidenciaCasoService();

    expect($service->resumirLecturas([
        ['humedad_suelo' => 40],
    ]))->toBe([
        'cantidad' => 1,
        'ultima' => 40.0,
        'minimo' => 40.0,
        'maximo' => 40.0,
        'promedio' => 40.0,
    ]);
});

it('ignora valores nulos y calcula promedio con los valores validos', function () {
    $service = new EvidenciaCasoService();

    expect($service->resumirLecturas([
        ['humedad_suelo' => 20],
        ['humedad_suelo' => null],
        ['humedad_suelo' => 30],
        ['humedad_suelo' => 50],
    ]))->toBe([
        'cantidad' => 3,
        'ultima' => 50.0,
        'minimo' => 20.0,
        'maximo' => 50.0,
        'promedio' => 33.333333333333336,
    ]);
});
