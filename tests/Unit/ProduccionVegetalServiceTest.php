<?php

use App\Services\ProduccionVegetalService;

it('solo permite las transiciones del flujo de producción', function () {
    expect(ProduccionVegetalService::transicionPermitida('necesidad_identificada', 'especie_seleccionada'))->toBeTrue();
    expect(ProduccionVegetalService::transicionPermitida('en_cuidados', 'en_revision'))->toBeTrue();
    expect(ProduccionVegetalService::transicionPermitida('en_revision', 'en_cuidados'))->toBeTrue();
    expect(ProduccionVegetalService::transicionPermitida('en_revision', 'clasificada'))->toBeTrue();
    expect(ProduccionVegetalService::transicionPermitida('necesidad_identificada', 'cerrado'))->toBeFalse();
    expect(ProduccionVegetalService::transicionPermitida('en_cuidados', 'clasificada'))->toBeFalse();
    expect(ProduccionVegetalService::transicionPermitida('cerrado', 'en_cuidados'))->toBeFalse();
});

it('define transiciones para todos los estados', function () {
    expect(array_keys(ProduccionVegetalService::TRANSICIONES))->toBe(ProduccionVegetalService::ESTADOS);
});

it('calcula las plantas vivas sin bajar de cero', function () {
    expect(ProduccionVegetalService::plantasVivas(10, 3))->toBe(7);
    expect(ProduccionVegetalService::plantasVivas(10, 15))->toBe(0);
});

it('valida que la clasificación sume las plantas vivas', function () {
    expect(ProduccionVegetalService::clasificacionCuadra(9, 6, 2, 1))->toBeTrue();
    expect(ProduccionVegetalService::clasificacionCuadra(9, 6, 2, 2))->toBeFalse();
});

it('solo cuenta como producidas las categorías A y B', function () {
    expect(ProduccionVegetalService::cantidadProducida(6, 2))->toBe(8);
    expect(ProduccionVegetalService::cantidadProducida(0, 0))->toBe(0);
});
