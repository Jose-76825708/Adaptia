<?php

use App\Services\VerificadorDisponibilidadService;

beforeEach(function () {
    $this->service = new VerificadorDisponibilidadService();
});

it('marca faltan cuando solo faltan materiales', function () {
    $herramientas = $this->service->consultarHerramientas([
        ['codigo' => 'H-001', 'requerido' => 1, 'disponible' => 2],
    ]);
    $materiales = $this->service->consultarMateriales([
        ['codigo' => 'M-001', 'requerido' => 8, 'disponible' => 5],
    ]);

    $veredicto = $this->service->calcularVeredicto($herramientas, $materiales);

    expect($veredicto['estado'])->toBe('faltan')
        ->and($veredicto['faltan_materiales'])->toHaveCount(1)
        ->and($veredicto['faltan_herramientas'])->toBeEmpty();
});

it('marca bloqueado cuando falta una herramienta aunque tambien falten materiales', function () {
    $herramientas = $this->service->consultarHerramientas([
        ['codigo' => 'H-002', 'requerido' => 3, 'disponible' => 1],
    ]);
    $materiales = $this->service->consultarMateriales([
        ['codigo' => 'M-002', 'requerido' => 4, 'disponible' => 1],
    ]);

    $veredicto = $this->service->calcularVeredicto($herramientas, $materiales);

    expect($veredicto['estado'])->toBe('bloqueado')
        ->and($veredicto['faltan_herramientas'])->toHaveCount(1);
});

it('marca ejecutable cuando todo alcanza', function () {
    $herramientas = $this->service->consultarHerramientas([
        ['codigo' => 'H-001', 'requerido' => 1, 'disponible' => 2],
    ]);
    $materiales = $this->service->consultarMateriales([
        ['codigo' => 'M-001', 'requerido' => 3, 'disponible' => 10],
    ]);

    $veredicto = $this->service->calcularVeredicto($herramientas, $materiales);

    expect($veredicto['estado'])->toBe('ejecutable')
        ->and($veredicto['motivo'])->toBeNull();
});
