<?php

use App\Services\InstalacionProyectoService;

it('verifica disponible suficiente y faltantes', function () {
    $resultado=(new InstalacionProyectoService())->verificarDisponibilidad([
        ['planta_id'=>1,'nombre'=>'Rosas','requerido'=>5,'disponible'=>10],
        ['planta_id'=>2,'nombre'=>'Pasto','requerido'=>8,'disponible'=>3],
    ]);

    expect($resultado['estado'])->toBe('insuficiente')
        ->and($resultado['lineas'][0]['faltante'])->toBe(0)
        ->and($resultado['lineas'][1]['faltante'])->toBe(5);
});

it('verifica estado suficiente sin faltantes', function () {
    $resultado=(new InstalacionProyectoService())->verificarDisponibilidad([
        ['planta_id'=>1,'nombre'=>'Rosas','requerido'=>2,'disponible'=>5],
    ]);

    expect($resultado['estado'])->toBe('suficiente')
        ->and($resultado['lineas'][0]['faltante'])->toBe(0);
});
