<?php

use App\Services\GeneradorPlanMantenimientoService;

it('cada tarea derivada tiene entre dos y tres items de checklist', function () {
    $generador = new GeneradorPlanMantenimientoService();

    foreach (GeneradorPlanMantenimientoService::PLANTILLAS as $tipo => $plantilla) {
        $tareas = $generador->derivarTareas($tipo, 'baja', []);
        foreach ($tareas as $tarea) {
            expect(count($tarea['checklist']))->toBeGreaterThanOrEqual(2)
                ->and(count($tarea['checklist']))->toBeLessThanOrEqual(3);
        }
    }
});

it('el seguimiento de variables crea un item por variable unica', function () {
    $tareas = (new GeneradorPlanMantenimientoService())->derivarTareas('general', 'media', ['luz', 'luz', 'temperatura']);
    $seguimiento = end($tareas);

    expect($seguimiento['checklist'])->toHaveCount(2)
        ->and($seguimiento['checklist'])->toContain('Revisar luz')
        ->and($seguimiento['checklist'])->toContain('Revisar temperatura');
});
