<?php

use App\Services\VerificadorDisponibilidadService;

it('considera disponible una herramienta con stock suficiente', function () {
    $service = new VerificadorDisponibilidadService();

    $resultado = $service->consultarHerramientas([
        ['codigo' => 'H-001', 'requerido' => 1, 'disponible' => 2, 'nombre' => 'Manguera'],
    ]);

    expect($resultado)->toHaveCount(1)
        ->and($resultado[0]['suficiente'])->toBeTrue();
});

it('detecta una herramienta insuficiente como faltante', function () {
    $service = new VerificadorDisponibilidadService();

    $resultado = $service->consultarHerramientas([
        ['codigo' => 'H-002', 'requerido' => 3, 'disponible' => 2, 'nombre' => 'Tijeras'],
    ]);

    expect($resultado[0]['faltante'])->toBe(1.0)
        ->and($resultado[0]['suficiente'])->toBeFalse();
});
