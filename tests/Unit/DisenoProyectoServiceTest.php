<?php

use App\Services\DisenoProyectoService;

it('normaliza lineas y elimina vacias', function () {
    $lineas=(new DisenoProyectoService())->normalizarLineas([
        ['planta_id'=>'1','cantidad'=>'2'],
        ['planta_id'=>null,'cantidad'=>null],
        ['planta_id'=>'2','cantidad'=>'3'],
    ]);

    expect($lineas)->toHaveCount(2)->and($lineas[0]['cantidad'])->toBe(2);
});

it('valida cantidad duplicados y rango', function () {
    $errores=(new DisenoProyectoService())->validarLineas([
        ['planta_id'=>1,'cantidad'=>0],
        ['planta_id'=>1,'cantidad'=>2],
        ['planta_id'=>2,'cantidad'=>1001],
    ],[]);

    expect($errores)->not->toBeEmpty();
});

it('cotiza con tarifa y detecta exceso', function () {
    $resultado=(new DisenoProyectoService())->cotizar([
        ['planta_id'=>1,'nombre'=>'Rosas','cantidad'=>2,'precio_unitario'=>10,'subtotal'=>20],
    ],100,20);

    expect($resultado['costo_plantas'])->toBe(20.0)
        ->and($resultado['costo_mano_obra'])->toBe(1500.0)
        ->and($resultado['total'])->toBe(1520.0)
        ->and($resultado['excede_presupuesto'])->toBeTrue();
});
