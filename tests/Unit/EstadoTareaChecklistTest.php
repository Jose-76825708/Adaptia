<?php

use App\Services\EstadoTareaChecklistService;

beforeEach(function () {
    $this->service = new EstadoTareaChecklistService();
});

it('marca completada cuando todos los items estan cumplidos', function () {
    expect($this->service->calcularEstado([true, true, true]))->toBe('completada');
});

it('vuelve a en curso si algun item se desmarca', function () {
    expect($this->service->calcularEstado([true, false]))->toBe('en_curso');
});

it('usa pendiente si no hay items', function () {
    expect($this->service->calcularEstado([]))->toBe('pendiente');
});
