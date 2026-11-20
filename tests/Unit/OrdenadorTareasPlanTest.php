<?php

use App\Services\OrdenadorTareasPlanService;

it('ordena dependencias antes de tareas sucesoras y usa desempate por codigo', function () {
    $tareas = [
        ['codigo' => 'T2', 'prioridad' => 'alta', 'criticidad' => 'alta', 'depende_de_codigo' => 'T1', 'id' => 2],
        ['codigo' => 'T1', 'prioridad' => 'baja', 'criticidad' => 'baja', 'depende_de_codigo' => null, 'id' => 1],
    ];

    $resultado = (new OrdenadorTareasPlanService())->ordenar($tareas);

    expect(array_column($resultado, 'codigo'))->toBe(['T1', 'T2']);
});

it('ordena por prioridad critica antes de alta y por criticidad', function () {
    $tareas = [
        ['codigo' => 'T3', 'prioridad' => 'alta', 'criticidad' => 'alta', 'depende_de_codigo' => null, 'id' => 3],
        ['codigo' => 'T1', 'prioridad' => 'critica', 'criticidad' => 'alta', 'depende_de_codigo' => null, 'id' => 1],
        ['codigo' => 'T2', 'prioridad' => 'alta', 'criticidad' => 'alta', 'depende_de_codigo' => null, 'id' => 2],
    ];

    $resultado = (new OrdenadorTareasPlanService())->ordenar($tareas);

    expect(array_column($resultado, 'codigo'))->toBe(['T1', 'T2', 'T3']);
});
