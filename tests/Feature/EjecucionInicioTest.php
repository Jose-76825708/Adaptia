<?php

use App\Models\CasoMantenimiento;
use App\Models\PlanMantenimiento;
use App\Models\TareaPlan;
use App\Models\User;
use App\Services\CasoMantenimientoService;
use App\Services\EjecucionMantenimientoService;

it('rechaza iniciar desde un estado distinto de preparado', function () {
    $caso = CasoMantenimiento::make(['estado' => 'planificado']);
    $service = new EjecucionMantenimientoService(new CasoMantenimientoService());

    expect(fn () => $service->registrar($caso, 1, 'inicio', 'Inicio de mantenimiento'))
        ->toThrow(InvalidArgumentException::class, 'preparado');
});

it('rechaza un evento distinto de inicio antes de comenzar', function () {
    $caso = CasoMantenimiento::make(['estado' => 'preparado']);
    $service = new EjecucionMantenimientoService(new CasoMantenimientoService());

    expect(fn () => $service->registrar($caso, 1, 'avance', 'Avance inicial'))
        ->toThrow(InvalidArgumentException::class, 'inicio');
});
