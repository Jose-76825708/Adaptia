<?php

use App\Models\DiagnosticoCaso;
use App\Models\HallazgoDiagnostico;
use App\Models\PlanMantenimiento;
use App\Models\TareaPlan;
use App\Models\TareaRecurso;
use App\Models\RecursoCatalogo;
use App\Models\ItemChecklist;
use App\Models\RequisicionMantenimiento;
use App\Models\RequisicionItem;
use App\Models\MovimientoMaterial;
use App\Models\EventoEjecucion;
use App\Models\InformeCierre;

it('los modelos de mantenimiento tienen nombres de tabla correctos', function () {
    expect((new DiagnosticoCaso())->getTable())->toBe('diagnosticos_caso')
        ->and((new HallazgoDiagnostico())->getTable())->toBe('hallazgos_diagnostico')
        ->and((new PlanMantenimiento())->getTable())->toBe('planes_mantenimiento')
        ->and((new TareaPlan())->getTable())->toBe('tareas_plan')
        ->and((new TareaRecurso())->getTable())->toBe('tarea_recursos')
        ->and((new RecursoCatalogo())->getTable())->toBe('recursos_catalogo')
        ->and((new ItemChecklist())->getTable())->toBe('items_checklist')
        ->and((new RequisicionMantenimiento())->getTable())->toBe('requisiciones_mantenimiento')
        ->and((new RequisicionItem())->getTable())->toBe('requisicion_items')
        ->and((new MovimientoMaterial())->getTable())->toBe('movimientos_material')
        ->and((new EventoEjecucion())->getTable())->toBe('eventos_ejecucion')
        ->and((new InformeCierre())->getTable())->toBe('informes_cierre');
});

it('todos los modelos tienen fillable explicito', function () {
    $modelos = [
        new DiagnosticoCaso(), new HallazgoDiagnostico(), new PlanMantenimiento(),
        new TareaPlan(), new TareaRecurso(), new RecursoCatalogo(), new ItemChecklist(),
        new RequisicionMantenimiento(), new RequisicionItem(), new MovimientoMaterial(),
        new EventoEjecucion(), new InformeCierre(),
    ];

    foreach ($modelos as $modelo) {
        expect($modelo->getFillable())->not->toBeEmpty();
    }
});
