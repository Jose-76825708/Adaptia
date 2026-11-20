<?php

use App\Models\CasoMantenimiento;
use App\Http\Controllers\AbastecimientoMantenimientoController;
use Illuminate\Http\Request;
use App\Services\RequisicionMantenimientoService;

it('generar abastecimiento sin plan no debe lanzar model not found', function () {
    $caso = CasoMantenimiento::make(['estado' => 'diagnosticado']);
    $controlador = new AbastecimientoMantenimientoController();

    expect(fn () => $controlador->generar(
        Request::create('/mantenimiento/casos/1/requisicion/generar', 'POST'),
        $caso,
        new RequisicionMantenimientoService(),
    ))->not->toThrow(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
});
