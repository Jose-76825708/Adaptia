<?php

use App\Models\CasoMantenimiento;
use App\Services\CasoMantenimientoService;
use App\Services\VerificadorDisponibilidadService;

it('rechaza marcar preparado si el caso no esta planificado', function () {
    $caso = CasoMantenimiento::make(['estado' => 'diagnosticado']);
    $service = new \App\Services\AbastecimientoMantenimientoService(
        new CasoMantenimientoService(),
        new VerificadorDisponibilidadService(),
    );

    expect(fn () => $service->preparar($caso, 1))
        ->toThrow(InvalidArgumentException::class, 'planificado');
});

it('permite calcular preparado solo si el veredicto es ejecutable', function () {
    $caso = CasoMantenimiento::make(['estado' => 'planificado']);
    $veredicto = (new VerificadorDisponibilidadService())->calcularVeredicto(
        [['codigo' => 'H-001', 'suficiente' => true]],
        [['codigo' => 'M-001', 'suficiente' => true]],
    );

    expect($veredicto['estado'])->toBe('ejecutable')
        ->and($caso->estado)->toBe('planificado');
});
