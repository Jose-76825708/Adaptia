<?php

use App\Services\EvaluacionCampanaService;

it('calcula ventas contra meta e ingresos aproximados por precio actual', function () {
    $resultados = app(EvaluacionCampanaService::class)->calcularResultados([
        ['producto_campana_id' => 10, 'planta_id' => 1, 'cantidad_meta' => 8, 'precio_actual' => 12.5],
        ['producto_campana_id' => 11, 'planta_id' => 2, 'cantidad_meta' => 4, 'precio_actual' => 9],
    ], [
        ['planta_id' => 1, 'cantidad_vendida' => 3],
        ['planta_id' => 1, 'cantidad_vendida' => 2],
    ]);

    expect($resultados[0]['cantidad_vendida'])->toBe(5)
        ->and($resultados[0]['porcentaje_cumplimiento'])->toBe(62.5)
        ->and($resultados[0]['ingresos_estimados'])->toBe(62.5)
        ->and($resultados[1]['cantidad_vendida'])->toBe(0)
        ->and($resultados[1]['porcentaje_cumplimiento'])->toBe(0.0);
});
