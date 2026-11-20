<?php

use App\Services\EjecucionMantenimientoService;

beforeEach(function () {
    $this->service = new EjecucionMantenimientoService(new \App\Services\CasoMantenimientoService());
});

it('acepta los tipos inicio avance incidencia nota y reapertura', function () {
    expect($this->service->validarTipo('inicio'))->toBeTrue()
        ->and($this->service->validarTipo('avance'))->toBeTrue()
        ->and($this->service->validarTipo('incidencia'))->toBeTrue()
        ->and($this->service->validarTipo('nota'))->toBeTrue()
        ->and($this->service->validarTipo('reapertura'))->toBeTrue();
});

it('rechaza tipos de evento desconocidos', function () {
    expect(fn () => $this->service->validarTipo('desconocido'))
        ->toThrow(InvalidArgumentException::class, 'tipo de evento');
});
