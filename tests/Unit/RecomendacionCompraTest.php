<?php

use App\Services\RecomendacionCompraService;

it('ordena recomendaciones por prioridad critica alta media baja', function () {
    $items = [
        ['codigo' => 'M-001', 'faltante' => 2.0, 'prioridad' => 'media'],
        ['codigo' => 'M-002', 'faltante' => 1.0, 'prioridad' => 'critica'],
        ['codigo' => 'M-003', 'faltante' => 3.0, 'prioridad' => 'baja'],
    ];

    $resultado = (new RecomendacionCompraService())->generar($items);

    expect(array_column($resultado, 'codigo'))->toBe(['M-002', 'M-001', 'M-003']);
});
