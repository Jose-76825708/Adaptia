<?php

use App\Services\ComparacionEjecucionService;
use Carbon\Carbon;

beforeEach(function () {
    $this->hoy = Carbon::parse('2026-11-17')->startOfDay();
    $this->service = new ComparacionEjecucionService();
});

it('compara tareas completadas pendientes y retrasadas', function () {
    $resultado = $this->service->comparar([
        ['id' => 1, 'estado' => 'completada', 'fecha_estimada' => '2026-11-10'],
        ['id' => 2, 'estado' => 'pendiente', 'fecha_estimada' => '2026-11-15'],
        ['id' => 3, 'estado' => 'en_curso', 'fecha_estimada' => '2026-11-20'],
    ], [], $this->hoy);

    expect($resultado['completadas'])->toHaveCount(1)
        ->and($resultado['pendientes'])->toHaveCount(2)
        ->and($resultado['retrasadas'])->toHaveCount(1);
});

it('detecta incidencias abiertas como riesgo', function () {
    $resultado = $this->service->comparar([], [
        ['id' => 1, 'tipo' => 'incidencia', 'resuelta_en' => null],
        ['id' => 2, 'tipo' => 'incidencia', 'resuelta_en' => '2026-11-16'],
    ], $this->hoy);

    expect($resultado['incidencias_abiertas'])->toHaveCount(1)
        ->and($resultado['riesgos'])->not->toBeEmpty();
});
