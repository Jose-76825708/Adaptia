<?php

use App\Models\CasoMantenimiento;
use App\Models\EventoEjecucion;
use App\Models\ItemChecklist;
use App\Models\PlanMantenimiento;
use App\Models\TareaPlan;
use App\Models\User;
use App\Services\CasoMantenimientoService;
use App\Services\EjecucionMantenimientoService;
use App\Services\EstadoTareaChecklistService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    foreach (['eventos_ejecucion','items_checklist','tareas_plan','planes_mantenimiento','casos_mantenimiento','users'] as $tabla) {
        Schema::dropIfExists($tabla);
    }

    Schema::create('users', function (Blueprint $table) {
        $table->id(); $table->string('name'); $table->string('email')->unique(); $table->timestamp('email_verified_at')->nullable();
        $table->string('password'); $table->string('remember_token',100)->nullable(); $table->string('rol')->nullable(); $table->timestamps();
    });
    Schema::create('casos_mantenimiento', function (Blueprint $table) {
        $table->id(); $table->string('codigo')->unique(); $table->foreignId('solicitante_id')->constrained('users')->onDelete('restrict');
        $table->string('area_verde'); $table->text('descripcion'); $table->date('fecha_deseada')->nullable(); $table->text('observaciones')->nullable();
        $table->string('tipo_mantenimiento'); $table->string('urgencia'); $table->string('estado'); $table->timestamps();
    });
    Schema::create('planes_mantenimiento', function (Blueprint $table) {
        $table->id(); $table->foreignId('caso_id')->constrained('casos_mantenimiento')->onDelete('restrict');
        $table->foreignId('autor_id')->constrained('users')->onDelete('restrict'); $table->unsignedInteger('version');
        $table->timestamp('generado_en'); $table->text('descripcion'); $table->timestamps();
    });
    Schema::create('tareas_plan', function (Blueprint $table) {
        $table->id(); $table->foreignId('plan_id')->constrained('planes_mantenimiento')->onDelete('restrict');
        $table->string('codigo'); $table->string('titulo'); $table->text('descripcion'); $table->string('prioridad'); $table->string('criticidad');
        $table->unsignedBigInteger('depende_de_tarea_id')->nullable(); $table->date('fecha_estimada')->nullable(); $table->unsignedInteger('duracion_dias');
        $table->string('estado')->default('pendiente'); $table->timestamps();
    });
    Schema::create('items_checklist', function (Blueprint $table) {
        $table->id(); $table->foreignId('tarea_id')->constrained('tareas_plan')->onDelete('restrict');
        $table->text('descripcion'); $table->boolean('cumplido')->default(false); $table->timestamps();
    });
    Schema::create('eventos_ejecucion', function (Blueprint $table) {
        $table->id(); $table->foreignId('caso_id')->constrained('casos_mantenimiento')->onDelete('restrict');
        $table->unsignedBigInteger('tarea_id')->nullable(); $table->string('tipo'); $table->text('descripcion');
        $table->foreignId('autor_id')->constrained('users')->onDelete('restrict'); $table->timestamp('fecha'); $table->timestamp('resuelta_en')->nullable(); $table->timestamps();
    });
});

it('inicia ejecución y genera fecha estimada por tarea', function () {
    $autor=User::factory()->create(['rol'=>'administrador']);
    $caso=CasoMantenimiento::create(['codigo'=>'MAN-EJ-1','solicitante_id'=>$autor->id,'area_verde'=>'Jardín','descripcion'=>'Mantenimiento','tipo_mantenimiento'=>'riego','urgencia'=>'media','estado'=>'preparado']);
    $plan=PlanMantenimiento::create(['caso_id'=>$caso->id,'autor_id'=>$autor->id,'version'=>1,'generado_en'=>now(),'descripcion'=>'Plan']);
    TareaPlan::create(['plan_id'=>$plan->id,'codigo'=>'T1','titulo'=>'Revisar humedad','descripcion'=>'Revisión','prioridad'=>'alta','criticidad'=>'media','depende_de_tarea_id'=>null,'fecha_estimada'=>null,'duracion_dias'=>2,'estado'=>'pendiente']);

    $evento=(new EjecucionMantenimientoService(new CasoMantenimientoService()))->registrar($caso,$autor->id,'inicio','Inicio del mantenimiento',null,now()->startOfDay());

    expect($evento->tipo)->toBe('inicio')->and($caso->fresh()->estado)->toBe('en_ejecucion')
        ->and(TareaPlan::first()->fecha_estimada)->not->toBeNull();
});

it('completa tarea al marcar todos los items checklist', function () {
    $autor=User::factory()->create(['rol'=>'administrador']);
    $caso=CasoMantenimiento::create(['codigo'=>'MAN-EJ-2','solicitante_id'=>$autor->id,'area_verde'=>'Jardín','descripcion'=>'Mantenimiento','tipo_mantenimiento'=>'riego','urgencia'=>'media','estado'=>'en_ejecucion']);
    $plan=PlanMantenimiento::create(['caso_id'=>$caso->id,'autor_id'=>$autor->id,'version'=>1,'generado_en'=>now(),'descripcion'=>'Plan']);
    $tarea=TareaPlan::create(['plan_id'=>$plan->id,'codigo'=>'T1','titulo'=>'Revisar','descripcion'=>'Revisión','prioridad'=>'alta','criticidad'=>'media','depende_de_tarea_id'=>null,'fecha_estimada'=>null,'duracion_dias'=>1,'estado'=>'pendiente']);
    $item1=ItemChecklist::create(['tarea_id'=>$tarea->id,'descripcion'=>'Uno','cumplido'=>false]);
    $item2=ItemChecklist::create(['tarea_id'=>$tarea->id,'descripcion'=>'Dos','cumplido'=>false]);

    $service=new EstadoTareaChecklistService();
    $service->actualizarItem($caso,$item1,true);
    $estado=$service->actualizarItem($caso,$item2,true);

    expect($estado)->toBe('completada')->and($tarea->fresh()->estado)->toBe('completada');
});

it('registra y resuelve una incidencia abierta', function () {
    $autor=User::factory()->create(['rol'=>'administrador']);
    $caso=CasoMantenimiento::create(['codigo'=>'MAN-EJ-3','solicitante_id'=>$autor->id,'area_verde'=>'Jardín','descripcion'=>'Mantenimiento','tipo_mantenimiento'=>'general','urgencia'=>'alta','estado'=>'en_ejecucion']);

    $service=new EjecucionMantenimientoService(new CasoMantenimientoService());
    $incidencia=$service->registrar($caso,$autor->id,'incidencia','Pérdida de agua',null,now());
    $resuelta=$service->resolverIncidencia($caso,$incidencia->id,$autor->id,now());

    expect($incidencia->tipo)->toBe('incidencia')->and($resuelta->resuelta_en)->not->toBeNull();
});
