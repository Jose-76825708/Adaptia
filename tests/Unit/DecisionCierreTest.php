<?php

use App\Services\DecisionCierreService;

beforeEach(function () {
    $this->service = new DecisionCierreService();
});

it('permite cerrar con severidad baja sin pendientes ni incidencias', function () {
    $resultado = $this->service->decidir('baja', 0, 0);

    expect($resultado['puede_cerrar'])->toBeTrue()
        ->and($resultado['correcciones'])->toBeEmpty();
});

it('permite cerrar con severidad media sin pendientes ni incidencias', function () {
    expect($this->service->decidir('media', 0, 0)['puede_cerrar'])->toBeTrue();
});

it('impide cerrar con severidad alta', function () {
    $resultado = $this->service->decidir('alta', 0, 0);

    expect($resultado['puede_cerrar'])->toBeFalse()
        ->and($resultado['correcciones'])->toContain('La severidad final es alta.');
});

it('impide cerrar con tareas pendientes o incidencias abiertas', function () {
    $resultado = $this->service->decidir('baja', 2, 1);

    expect($resultado['puede_cerrar'])->toBeFalse()
        ->and($resultado['correcciones'])->toHaveCount(2);
});
