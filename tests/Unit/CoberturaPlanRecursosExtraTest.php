<?php

use App\Services\GeneradorPlanMantenimientoService;
use App\Services\OrdenadorTareasPlanService;
use App\Services\VerificadorDisponibilidadService;

it('deriva plantillas para todos los tipos', function () {
    $generador = new GeneradorPlanMantenimientoService();

    foreach (GeneradorPlanMantenimientoService::PLANTILLAS as $tipo => $plantilla) {
        expect($generador->derivarTareas($tipo, 'baja', []))->not->toBeEmpty();
    }
});

it('deriva seguimiento con variables duplicadas sin duplicarlas', function () {
    $generador = new GeneradorPlanMantenimientoService();
    $tareas = $generador->derivarTareas('general', 'media', ['luz', 'luz', 'temperatura']);

    expect(end($tareas)['checklist'])->toHaveCount(2);
});

it('ordena tareas encadenadas sin perder dependencias', function () {
    $ordenador = new OrdenadorTareasPlanService();
    $resultado = $ordenador->ordenar([
        ['codigo' => 'T3', 'prioridad' => 'baja', 'criticidad' => 'baja', 'depende_de_codigo' => 'T2', 'id' => 3],
        ['codigo' => 'T1', 'prioridad' => 'alta', 'criticidad' => 'alta', 'depende_de_codigo' => null, 'id' => 1],
        ['codigo' => 'T2', 'prioridad' => 'media', 'criticidad' => 'media', 'depende_de_codigo' => 'T1', 'id' => 2],
    ]);

    expect(array_column($resultado, 'codigo'))->toBe(['T1', 'T2', 'T3']);
});

it('ordena tareas sin dependencia por codigo', function () {
    $ordenador = new OrdenadorTareasPlanService();
    $resultado = $ordenador->ordenar([
        ['codigo' => 'T3', 'prioridad' => 'baja', 'criticidad' => 'baja', 'depende_de_codigo' => null, 'id' => 3],
        ['codigo' => 'T1', 'prioridad' => 'baja', 'criticidad' => 'baja', 'depende_de_codigo' => null, 'id' => 1],
    ]);

    expect(array_column($resultado, 'codigo'))->toBe(['T1', 'T3']);
});

it('suma materiales y exige maximo para herramientas', function () {
    $service = new VerificadorDisponibilidadService();

    $materiales = $service->consultarMateriales([
        ['codigo' => 'M-1', 'requerido' => 3, 'disponible' => 1, 'nombre' => 'Abono'],
        ['codigo' => 'M-1', 'requerido' => 2, 'disponible' => 1, 'nombre' => 'Abono'],
    ]);
    $herramientas = $service->consultarHerramientas([
        ['codigo' => 'H-1', 'requerido' => 2, 'disponible' => 2, 'nombre' => 'Tijeras'],
    ]);

    expect($materiales)->toHaveCount(2)
        ->and($herramientas[0]['suficiente'])->toBeTrue();
});
