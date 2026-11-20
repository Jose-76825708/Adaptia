<?php

use App\Services\CierreMantenimientoService;

it('rechaza reapertura sin motivo obligatorio', function () {
    $service = new CierreMantenimientoService(
        new \App\Services\CasoMantenimientoService(),
        new \App\Services\DiagnosticoCasoService(new \App\Services\CasoMantenimientoService(), new \App\Services\EvidenciaCasoService(), new \App\Services\EvaluadorEstadoAreaService()),
        new \App\Services\ComparacionEjecucionService(),
        new \App\Services\DecisionCierreService(),
    );

    expect(fn () => $service->validarMotivoReapertura('   '))->toThrow(InvalidArgumentException::class, 'motivo');
});
