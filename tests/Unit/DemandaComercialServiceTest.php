<?php

use App\Services\DemandaComercialService;

it('ordena ventas y separa stock bajo y plantas sin ventas', function () {
    $resultado = app(DemandaComercialService::class)->calcularResumen([
        ['planta_id' => 1, 'nombre' => 'Monstera', 'stock_actual' => 1, 'stock_minimo' => 2],
        ['planta_id' => 2, 'nombre' => 'Aloe', 'stock_actual' => 8, 'stock_minimo' => 2],
        ['planta_id' => 3, 'nombre' => 'Helecho', 'stock_actual' => 0, 'stock_minimo' => 1],
    ], [
        ['planta_id' => 1, 'cantidad_vendida' => 2],
        ['planta_id' => 2, 'cantidad_vendida' => 5],
        ['planta_id' => 1, 'cantidad_vendida' => 3],
    ]);

    expect(array_column($resultado['ranking_ventas'], 'planta_id'))->toBe([2, 1])
        ->and($resultado['ranking_ventas'][1]['cantidad_vendida'])->toBe(5)
        ->and(array_column($resultado['stock_bajo'], 'planta_id'))->toBe([1, 3])
        ->and(array_column($resultado['sin_ventas'], 'planta_id'))->toBe([3]);
});
