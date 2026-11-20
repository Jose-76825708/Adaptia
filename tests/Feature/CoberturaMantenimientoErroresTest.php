<?php

use App\Models\CasoMantenimiento;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Services\AbastecimientoMantenimientoService;
use App\Services\CasoMantenimientoService;
use App\Services\EjecucionMantenimientoService;
use App\Services\GeneradorPlanMantenimientoService;
use App\Services\OrdenadorTareasPlanService;
use App\Services\PlanMantenimientoService;
use App\Services\VerificadorDisponibilidadService;

it('Plan rechaza estado distinto y ausencia de diagnostico', function () {
    Schema::create('diagnosticos_caso', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('caso_id')->nullable();
        $table->unsignedInteger('version')->default(1);
        $table->timestamps();
    });
    $caso = CasoMantenimiento::make(['estado' => 'abierto', 'tipo_mantenimiento' => 'riego']);
    $service = new PlanMantenimientoService(new CasoMantenimientoService(), new GeneradorPlanMantenimientoService(), new OrdenadorTareasPlanService());

    expect(fn () => $service->generarYPersistir($caso, 1))->toThrow(InvalidArgumentException::class, 'diagnosticado');

    $caso->estado = 'diagnosticado';
    expect(fn () => $service->generarYPersistir($caso, 1))->toThrow(InvalidArgumentException::class, 'diagnóstico vigente');
});

it('Abastecimiento rechaza estado y ausencia de plan', function () {
    $caso = CasoMantenimiento::make(['estado' => 'diagnosticado']);
    $service = new AbastecimientoMantenimientoService(new CasoMantenimientoService(), new VerificadorDisponibilidadService());

    expect(fn () => $service->preparar($caso, 1))->toThrow(InvalidArgumentException::class, 'planificado');
});

it('Ejecucion rechaza inicio duplicado y tarea ajena', function () {
    $caso = CasoMantenimiento::make(['estado' => 'preparado', 'id' => 10]);
    $service = new EjecucionMantenimientoService(new CasoMantenimientoService());

    expect(fn () => $service->registrar($caso, 1, 'avance', 'Avance'))->toThrow(InvalidArgumentException::class, 'inicio')
        ->and(fn () => $service->registrar($caso, 1, 'desconocido', 'Texto'))->toThrow(InvalidArgumentException::class, 'tipo de evento');
});

it('Recepcion rechaza cantidad y pertenencia', function () {
    $caso = CasoMantenimiento::make(['estado' => 'planificado', 'id' => 1]);
    $item = new App\Models\RequisicionItem();

    $service = new App\Services\RecepcionMaterialService();

    expect(fn () => $service->calcularRecepcion(5.0, 0.0))->toThrow(InvalidArgumentException::class, 'mayor que cero')
        ->and(fn () => $service->calcularRecepcion(5.0, 6.0))->toThrow(InvalidArgumentException::class, 'solicitada');
});
