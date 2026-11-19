<?php

use App\Services\GeneradorPlanMantenimientoService;

beforeEach(function () {
    $this->generador = new GeneradorPlanMantenimientoService();
});

it('deriva tareas para riego desde el tipo de mantenimiento', function () {
    $tareas = $this->generador->derivarTareas('riego', 'baja', []);

    expect($tareas)->toHaveCount(2)
        ->and($tareas[0]['codigo'])->toBe('T1')
        ->and($tareas[0]['duracion_dias'])->toBe(1)
        ->and($tareas[1]['depende_de_codigo'])->toBe('T1');
});

it('sube todas las prioridades si el diagnostico es alto', function () {
    $tareas = $this->generador->derivarTareas('general', 'alta', []);

    expect($tareas[0]['prioridad'])->toBe('alta');
});

it('agrega seguimiento por variables afectadas al final', function () {
    $tareas = $this->generador->derivarTareas('poda', 'media', ['temperatura', 'luz']);

    expect(end($tareas)['codigo'])->toBe('T3')
        ->and(end($tareas)['titulo'])->toBe('Seguimiento de variables afectadas')
        ->and(end($tareas)['checklist'])->toHaveCount(2)
        ->and(end($tareas)['recursos'])->toBeEmpty();
});

it('rechaza tipos de mantenimiento no definidos', function () {
    expect(fn () => $this->generador->derivarTareas('desconocido', 'baja', []))
        ->toThrow(InvalidArgumentException::class, 'tipo de mantenimiento');
});
