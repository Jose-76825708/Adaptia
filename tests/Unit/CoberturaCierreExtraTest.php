<?php

use App\Services\DecisionCierreService;
use App\Services\ComparacionEjecucionService;
use Carbon\Carbon;

it('DecisionCierre impide severidad desconocida', function () {
    $resultado = (new DecisionCierreService())->decidir('critica', 0, 0);

    expect($resultado['puede_cerrar'])->toBeFalse()
        ->and($resultado['correcciones'])->not->toBeEmpty();
});

it('DecisionCierre impide tareas pendientes sin incidencias', function () {
    $resultado = (new DecisionCierreService())->decidir('media', 3, 0);

    expect($resultado['puede_cerrar'])->toBeFalse()
        ->and($resultado['correcciones'])->toContain('Quedan tareas sin completar.');
});

it('DecisionCierre impide incidencias sin tareas pendientes', function () {
    $resultado = (new DecisionCierreService())->decidir('baja', 0, 2);

    expect($resultado['puede_cerrar'])->toBeFalse()
        ->and($resultado['correcciones'])->toContain('Hay incidencias abiertas.');
});

it('Comparacion marca retraso solo antes de hoy', function () {
    $hoy = Carbon::parse('2026-11-18')->startOfDay();
    $resultado = (new ComparacionEjecucionService())->comparar([
        ['id' => 1, 'estado' => 'pendiente', 'fecha_estimada' => '2026-11-17'],
        ['id' => 2, 'estado' => 'pendiente', 'fecha_estimada' => '2026-11-18'],
        ['id' => 3, 'estado' => 'pendiente', 'fecha_estimada' => null],
    ], [], $hoy);

    expect($resultado['retrasadas'])->toHaveCount(1)
        ->and($resultado['pendientes'])->toHaveCount(3)
        ->and($resultado['riesgos'])->not->toBeEmpty();
});
