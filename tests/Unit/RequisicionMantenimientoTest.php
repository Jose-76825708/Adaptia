<?php

use App\Services\RequisicionMantenimientoService;

beforeEach(function () {
    $this->service = new RequisicionMantenimientoService();
});

it('genera una requisicion solo para materiales faltantes', function () {
    $resultado = $this->service->generarDesdeFaltantes([
        ['codigo' => 'M-001', 'faltante' => 3.0, 'nombre' => 'Abono'],
    ]);

    expect($resultado)->toHaveCount(1)
        ->and($resultado[0]['codigo'])->toBe('M-001')
        ->and($resultado[0]['cantidad_solicitada'])->toBe(3.0);
});

it('rechaza generar requisicion con veredicto bloqueado', function () {
    expect(fn () => $this->service->generarDesdeVeredicto([
        'estado' => 'bloqueado',
        'faltan_materiales' => [['codigo' => 'M-001', 'faltante' => 2.0]],
        'faltan_herramientas' => [['codigo' => 'H-001']],
    ]))->toThrow(InvalidArgumentException::class, 'bloqueado');
});

it('rechaza generar requisicion si el veredicto no tiene materiales faltantes', function () {
    expect(fn () => $this->service->generarDesdeVeredicto([
        'estado' => 'ejecutable',
        'faltan_materiales' => [],
        'faltan_herramientas' => [],
    ]))->toThrow(InvalidArgumentException::class, 'No faltan materiales');
});
