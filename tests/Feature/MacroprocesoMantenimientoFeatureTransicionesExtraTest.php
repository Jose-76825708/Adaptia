<?php

use App\Models\CasoMantenimiento;
use App\Models\DiagnosticoCaso;
use App\Models\PlanMantenimiento;
use App\Models\TareaPlan;
use App\Models\User;
use App\Models\EventoEjecucion;
use App\Models\InformeCierre;
use App\Services\CasoMantenimientoService;
use App\Services\EjecucionMantenimientoService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('el estado abierto puede avanzar a diagnosticado con el servicio canonico', function () {
    $user=User::factory()->create(['rol'=>'administrador']);
    $caso=CasoMantenimiento::factory()->create(['solicitante_id'=>$user->id,'estado'=>'abierto']);
    $servicio=new CasoMantenimientoService();

    $actualizado=$servicio->cambiarEstado($caso,'diagnosticado');

    expect($actualizado->estado)->toBe('diagnosticado')
        ->and($caso->fresh()->estado)->toBe('diagnosticado');
});

it('el estado diagnosticado puede avanzar a planificado', function () {
    $caso=CasoMantenimiento::factory()->create(['estado'=>'diagnosticado']);
    $actualizado=(new CasoMantenimientoService())->cambiarEstado($caso,'planificado');

    expect($actualizado->estado)->toBe('planificado');
});

it('el estado planificado puede avanzar a preparado', function () {
    $caso=CasoMantenimiento::factory()->create(['estado'=>'planificado']);
    $actualizado=(new CasoMantenimientoService())->cambiarEstado($caso,'preparado');

    expect($actualizado->estado)->toBe('preparado');
});

it('un evento inicio pasa preparado a en ejecucion', function () {
    $admin=User::factory()->create(['rol'=>'administrador']);
    $caso=CasoMantenimiento::factory()->create(['solicitante_id'=>$admin->id,'estado'=>'preparado']);
    $plan=PlanMantenimiento::create(['caso_id'=>$caso->id,'autor_id'=>$admin->id,'version'=>1,'generado_en'=>now(),'descripcion'=>'Plan']);

    TareaPlan::create(['plan_id'=>$plan->id,'codigo'=>'T1','titulo'=>'Revisión','descripcion'=>'Revisión del área','prioridad'=>'media','criticidad'=>'media','depende_de_tarea_id'=>null,'fecha_estimada'=>null,'duracion_dias'=>1,'estado'=>'pendiente']);

    $evento=(new EjecucionMantenimientoService(new CasoMantenimientoService()))->registrar($caso,$admin->id,'inicio','Inicio de mantenimiento',null,now()->startOfDay());

    expect($evento->tipo)->toBe('inicio')
        ->and($caso->fresh()->estado)->toBe('en_ejecucion')
        ->and(TareaPlan::first()->fecha_estimada)->not->toBeNull();
});

it('no permite inicio desde estado planificado', function () {
    $caso=CasoMantenimiento::factory()->create(['estado'=>'planificado']);

    expect(fn () => (new EjecucionMantenimientoService(new CasoMantenimientoService()))->registrar($caso,1,'inicio','Inicio'))->toThrow(InvalidArgumentException::class,'preparado');
});

it('no permite avance antes del inicio', function () {
    $caso=CasoMantenimiento::factory()->create(['estado'=>'preparado']);

    expect(fn () => (new EjecucionMantenimientoService(new CasoMantenimientoService()))->registrar($caso,1,'avance','Avance'))->toThrow(InvalidArgumentException::class,'inicio');
});

it('una incidencia puede resolverse con resuelta_en', function () {
    $autor=User::factory()->create(['rol'=>'administrador']);
    $caso=CasoMantenimiento::factory()->create(['solicitante_id'=>$autor->id,'estado'=>'en_ejecucion']);
    $incidencia=EventoEjecucion::create(['caso_id'=>$caso->id,'tarea_id'=>null,'tipo'=>'incidencia','descripcion'=>'Incidencia','autor_id'=>$autor->id,'fecha'=>now(),'resuelta_en'=>null]);

    $resuelta=(new EjecucionMantenimientoService(new CasoMantenimientoService()))->resolverIncidencia($caso,$incidencia->id,$autor->id,now());

    expect($resuelta->resuelta_en)->not->toBeNull();
});

it('reapertura crea evento y pasa cerrado a en ejecucion', function () {
    $autor=User::factory()->create(['rol'=>'administrador']);
    $caso=CasoMantenimiento::factory()->create(['solicitante_id'=>$autor->id,'estado'=>'cerrado']);

    $caso->estado='cerrado'; $caso->save();
    $service=new App\Services\CierreMantenimientoService(new CasoMantenimientoService(),new App\Services\DiagnosticoCasoService(new CasoMantenimientoService(),new App\Services\EvidenciaCasoService(),new App\Services\EvaluadorEstadoAreaService()),new App\Services\ComparacionEjecucionService(),new App\Services\DecisionCierreService());
    $service->reabrir($caso,$autor->id,'Se encontró otra incidencia',now());

    expect($caso->fresh()->estado)->toBe('en_ejecucion')
        ->and(EventoEjecucion::where('caso_id',$caso->id)->where('tipo','reapertura')->count())->toBe(1);
});
