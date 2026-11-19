<?php

use App\Services\RecepcionMaterialService;

it('calcula la entrada parcial y stock actualizado', function () {
    $resultado = (new RecepcionMaterialService())->calcularRecepcion(5.0, 2.0, 0.0, 5.0);

    expect($resultado['stock_despues'])->toBe(7.0)
        ->and($resultado['estado_item'])->toBe('parcial')
        ->and($resultado['recibida'])->toBe(2.0);
});

it('marca recibida al completar la cantidad solicitada', function () {
    $resultado = (new RecepcionMaterialService())->calcularRecepcion(5.0, 5.0, 0.0, 5.0);

    expect($resultado['estado_item'])->toBe('recibida')
        ->and($resultado['stock_despues'])->toBe(10.0);
});

it('rechaza recibir mas cantidad que la solicitada', function () {
    expect(fn () => (new RecepcionMaterialService())->calcularRecepcion(5.0, 6.0))
        ->toThrow(InvalidArgumentException::class, 'solicitada');
});
