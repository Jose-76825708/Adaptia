<?php

use App\Services\CasoMantenimientoService;

beforeEach(function () {
    $this->service = new CasoMantenimientoService;
});

test('estados incluyen el ciclo base del caso', function () {
    expect(CasoMantenimientoService::ESTADOS)->toBe([
        'abierto',
        'diagnosticado',
        'planificado',
        'preparado',
        'en_ejecucion',
        'cerrado',
    ]);
});

test('puedeTransicionar retorna true para avance legal', function () {
    expect($this->service->puedeTransicionar('abierto', 'diagnosticado'))->toBeTrue()
        ->and($this->service->puedeTransicionar('diagnosticado', 'planificado'))->toBeTrue()
        ->and($this->service->puedeTransicionar('cerrado', 'en_ejecucion'))->toBeTrue();
});

test('puedeTransicionar retorna false para retrocesos y estados no permitidos', function () {
    expect($this->service->puedeTransicionar('diagnosticado', 'abierto'))->toBeFalse()
        ->and($this->service->puedeTransicionar('abierto', 'cerrado'))->toBeFalse()
        ->and($this->service->puedeTransicionar('planificado', 'diagnosticado'))->toBeFalse()
        ->and($this->service->puedeTransicionar('invalido', 'abierto'))->toBeFalse();
});

test('transiciones permitidas siguen la secuencia lineal mas la reapertura', function () {
    expect(CasoMantenimientoService::TRANSICIONES)->toBe([
        'abierto' => ['diagnosticado'],
        'diagnosticado' => ['planificado'],
        'planificado' => ['preparado'],
        'preparado' => ['en_ejecucion'],
        'en_ejecucion' => ['cerrado'],
        'cerrado' => ['en_ejecucion'],
    ]);
});
