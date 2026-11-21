<?php

use App\Services\DisponibilidadCampanaService;

it('calcula disponibilidad suficiente y faltantes por planta', function () {
    $resultado = app(DisponibilidadCampanaService::class)->calcularVeredicto([
        ['producto_campana_id' => 1, 'requerido' => 4, 'disponible' => 6],
        ['producto_campana_id' => 2, 'requerido' => 8, 'disponible' => 3],
    ]);

    expect($resultado['suficiente'])->toBeFalse()
        ->and($resultado['productos'][0]['faltante'])->toBe(0)
        ->and($resultado['faltantes'])->toHaveCount(1)
        ->and($resultado['faltantes'][0]['producto_campana_id'])->toBe(2)
        ->and($resultado['faltantes'][0]['faltante'])->toBe(5);
});

it('considera insuficiente una oferta sin productos activos', function () {
    expect(app(DisponibilidadCampanaService::class)->calcularVeredicto([])['suficiente'])->toBeFalse();
});
