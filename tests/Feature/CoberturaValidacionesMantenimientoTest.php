<?php

use App\Models\CasoMantenimiento;
use App\Models\RequisicionItem;
use App\Models\RequisicionMantenimiento;
use App\Models\RecursoCatalogo;
use App\Models\User;
use App\Services\EjecucionMantenimientoService;
use App\Services\CasoMantenimientoService;
use App\Services\RecepcionMaterialService;
use App\Services\CierreMantenimientoService;
use App\Services\DiagnosticoCasoService;
use App\Services\EvidenciaCasoService;
use App\Services\EvaluadorEstadoAreaService;
use App\Services\ComparacionEjecucionService;
use App\Services\DecisionCierreService;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('ejecucion rechaza descripcion vacia', function () {
    $caso=CasoMantenimiento::factory()->make(['estado'=>'preparado']);
    $service=new EjecucionMantenimientoService(new CasoMantenimientoService());

    expect(fn () => $service->registrar($caso,1,'inicio','   ',null))->toThrow(InvalidArgumentException::class,'descripción');
});

it('recepcion rechaza cantidad cero y excedida', function () {
    $service=new RecepcionMaterialService();

    expect(fn () => $service->calcularRecepcion(5,0,0,10))->toThrow(InvalidArgumentException::class,'mayor que cero')
        ->and(fn () => $service->calcularRecepcion(5,6,0,10))->toThrow(InvalidArgumentException::class,'solicitada');
});

it('reapertura rechaza motivo vacio', function () {
    $caso=CasoMantenimiento::factory()->make(['estado'=>'cerrado']);
    $service=new CierreMantenimientoService(new CasoMantenimientoService(),new DiagnosticoCasoService(new CasoMantenimientoService(),new EvidenciaCasoService(),new EvaluadorEstadoAreaService()),new ComparacionEjecucionService(),new DecisionCierreService());

    expect(fn () => $service->validarMotivoReapertura('   '))->toThrow(InvalidArgumentException::class,'motivo');
});

it('requisicion rechaza veredicto bloqueado', function () {
    $service=new App\Services\RequisicionMantenimientoService();

    expect(fn () => $service->generarDesdeVeredicto(['estado'=>'bloqueado','faltan_materiales'=>[],'faltan_herramientas'=>[['codigo'=>'H-1']]]))
        ->toThrow(InvalidArgumentException::class,'bloqueado');
});

it('requisicion rechaza veredicto ejecutable sin faltantes', function () {
    $service=new App\Services\RequisicionMantenimientoService();

    expect(fn () => $service->generarDesdeVeredicto(['estado'=>'ejecutable','faltan_materiales'=>[],'faltan_herramientas'=>[]]))
        ->toThrow(InvalidArgumentException::class,'No faltan materiales');
});
