<?php

use App\Services\RequisicionMantenimientoService;
use App\Services\RecepcionMaterialService;
use App\Services\ComparacionEjecucionService;
use App\Services\EjecucionMantenimientoService;
use App\Services\CasoMantenimientoService;
use Carbon\Carbon;

it('Requisicion genera solo faltantes positivos', function () {
    $service = new RequisicionMantenimientoService();
    $items = $service->generarDesdeFaltantes([
        ['codigo' => 'M-1', 'faltante' => 2],
        ['codigo' => 'M-2', 'faltante' => 0],
        ['codigo' => 'M-3', 'faltante' => -1],
    ]);

    expect($items)->toHaveCount(1)
        ->and($items[0]['codigo'])->toBe('M-1');
});

it('Recepcion rechaza cantidad negativa y completa parcial', function () {
    $service = new RecepcionMaterialService();

    expect(fn () => $service->calcularRecepcion(5, -1, 0, 10))->toThrow(InvalidArgumentException::class)
        ->and($service->calcularRecepcion(5, 2, 3, 10)['estado_item'])->toBe('recibida');
});

it('Comparacion calcula sin pendientes ni incidencias', function () {
    $service = new ComparacionEjecucionService();
    $resultado = $service->comparar([
        ['id' => 1, 'estado' => 'completada', 'fecha_estimada' => null],
    ], [], Carbon::parse('2026-11-18')->startOfDay());

    expect($resultado['completadas'])->toHaveCount(1)
        ->and($resultado['pendientes'])->toBeEmpty()
        ->and($resultado['retrasadas'])->toBeEmpty()
        ->and($resultado['riesgos'])->toBeEmpty();
});

it('Ejecucion acepta todos los tipos permitidos y rechaza otro', function () {
    $service = new EjecucionMantenimientoService(new CasoMantenimientoService());

    foreach (['inicio', 'avance', 'incidencia', 'nota', 'reapertura'] as $tipo) {
        expect($service->validarTipo($tipo))->toBeTrue();
    }

    expect(fn () => $service->validarTipo('otro'))->toThrow(InvalidArgumentException::class);
});
