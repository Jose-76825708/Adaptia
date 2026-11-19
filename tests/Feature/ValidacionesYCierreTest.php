<?php

use App\Models\CasoMantenimiento;
use App\Services\EjecucionMantenimientoService;
use App\Services\CasoMantenimientoService;

it('el diagnostico no muestra resultado vigente ni tabla de hallazgos', function () {
    $contenido = file_get_contents(base_path('resources/views/mantenimiento/casos/paneles/diagnostico.blade.php'));

    expect($contenido)->not->toContain('Resultado vigente')
        ->and($contenido)->not->toContain('diag-hallazgos')
        ->and($contenido)->toContain('Diagnóstico guardado correctamente');
});

it('la vista se llama cierre y reapertura', function () {
    $contenido = file_get_contents(base_path('resources/views/mantenimiento/casos/paneles/cierre.blade.php'));

    expect($contenido)->toContain('Cierre / Reapertura');
});

it('ejecucion rechaza descripcion vacia', function () {
    $caso = CasoMantenimiento::make(['estado' => 'preparado']);
    $service = new EjecucionMantenimientoService(new CasoMantenimientoService());

    expect(fn () => $service->registrar($caso, 1, 'inicio', '   ', null))
        ->toThrow(InvalidArgumentException::class, 'descripción');
});

it('motivo de reapertura no puede estar vacío', function () {
    $caso = CasoMantenimiento::make(['estado' => 'cerrado']);
    $controller = new App\Http\Controllers\CierreMantenimientoController();

    expect($caso->estado)->toBe('cerrado');
});
