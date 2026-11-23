<?php

use App\Models\CasoMantenimiento;
use App\Models\EventoEjecucion;
use App\Models\InformeCierre;
use App\Models\ItemChecklist;
use App\Models\PlanMantenimiento;
use App\Models\TareaPlan;
use App\Models\User;
use App\Services\CasoMantenimientoService;
use App\Services\CierreMantenimientoService;
use App\Services\ComparacionEjecucionService;
use App\Services\DecisionCierreService;
use App\Services\DiagnosticoCasoService;
use App\Services\EvidenciaCasoService;
use App\Services\EvaluadorEstadoAreaService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    foreach (['informes_cierre','eventos_ejecucion','items_checklist','tareas_plan','planes_mantenimiento','hallazgos_diagnostico','diagnosticos_caso','evidencias_caso','casos_mantenimiento','users'] as $tabla) {
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
    Schema::create('evidencias_caso', function (Blueprint $table) {
        $table->id(); $table->foreignId('caso_id')->constrained('casos_mantenimiento')->onDelete('restrict'); $table->foreignId('autor_id')->constrained('users')->onDelete('restrict');
        $table->string('tipo'); $table->text('contenido')->nullable(); $table->string('ruta')->nullable(); $table->string('public_id')->nullable(); $table->string('ruta_local')->nullable(); $table->string('descripcion')->nullable(); $table->timestamps();
    });
    Schema::create('diagnosticos_caso', function (Blueprint $table) {
        $table->id(); $table->foreignId('caso_id')->constrained('casos_mantenimiento')->onDelete('restrict'); $table->foreignId('autor_id')->constrained('users')->onDelete('restrict');
        $table->unsignedInteger('version'); $table->string('severidad'); $table->text('resumen'); $table->text('justificacion'); $table->timestamp('evaluado_en'); $table->timestamps();
    });
    Schema::create('hallazgos_diagnostico', function (Blueprint $table) {
        $table->id(); $table->foreignId('diagnostico_id')->constrained('diagnosticos_caso')->onDelete('restrict');
        $table->string('tipo'); $table->string('variable')->nullable(); $table->string('severidad'); $table->text('descripcion'); $table->timestamps();
    });
    Schema::create('planes_mantenimiento', function (Blueprint $table) {
        $table->id(); $table->foreignId('caso_id')->constrained('casos_mantenimiento')->onDelete('restrict'); $table->foreignId('autor_id')->constrained('users')->onDelete('restrict');
        $table->unsignedInteger('version'); $table->timestamp('generado_en'); $table->text('descripcion'); $table->timestamps();
    });
    Schema::create('tareas_plan', function (Blueprint $table) {
        $table->id(); $table->foreignId('plan_id')->constrained('planes_mantenimiento')->onDelete('restrict'); $table->string('codigo'); $table->string('titulo');
        $table->text('descripcion'); $table->string('prioridad'); $table->string('criticidad'); $table->unsignedBigInteger('depende_de_tarea_id')->nullable();
        $table->date('fecha_estimada')->nullable(); $table->unsignedInteger('duracion_dias'); $table->string('estado')->default('pendiente'); $table->timestamps();
    });
    Schema::create('items_checklist', function (Blueprint $table) {
        $table->id(); $table->foreignId('tarea_id')->constrained('tareas_plan')->onDelete('restrict'); $table->text('descripcion'); $table->boolean('cumplido')->default(false); $table->timestamps();
    });
    Schema::create('eventos_ejecucion', function (Blueprint $table) {
        $table->id(); $table->foreignId('caso_id')->constrained('casos_mantenimiento')->onDelete('restrict'); $table->unsignedBigInteger('tarea_id')->nullable();
        $table->string('tipo'); $table->text('descripcion'); $table->foreignId('autor_id')->constrained('users')->onDelete('restrict'); $table->timestamp('fecha');
        $table->timestamp('resuelta_en')->nullable(); $table->timestamps();
    });
    Schema::create('informes_cierre', function (Blueprint $table) {
        $table->id(); $table->foreignId('caso_id')->constrained('casos_mantenimiento')->onDelete('restrict'); $table->foreignId('autor_id')->constrained('users')->onDelete('restrict');
        $table->foreignId('destinatario_id')->constrained('users')->onDelete('restrict'); $table->unsignedInteger('version'); $table->text('resumen');
        $table->text('contenido'); $table->boolean('notificacion_pendiente')->default(true); $table->timestamp('cerrado_en'); $table->timestamps();
    });
});

it('rechaza cerrar caso fuera de ejecucion', function () {
    $autor=User::factory()->create(['rol'=>'administrador']);
    $caso=CasoMantenimiento::create(['codigo'=>'MAN-CR-1','solicitante_id'=>$autor->id,'area_verde'=>'Jardín','descripcion'=>'Mantenimiento','tipo_mantenimiento'=>'general','urgencia'=>'baja','estado'=>'planificado']);
    $service=new CierreMantenimientoService(new CasoMantenimientoService(),new DiagnosticoCasoService(new CasoMantenimientoService(),new EvidenciaCasoService(),new EvaluadorEstadoAreaService()),new ComparacionEjecucionService(),new DecisionCierreService());

    expect(fn () => $service->cerrar($caso,$autor->id))->toThrow(InvalidArgumentException::class,'ejecución');
});

it('reabre caso cerrado con motivo y evento reapertura', function () {
    $autor=User::factory()->create(['rol'=>'administrador']);
    $caso=CasoMantenimiento::create(['codigo'=>'MAN-CR-2','solicitante_id'=>$autor->id,'area_verde'=>'Jardín','descripcion'=>'Mantenimiento','tipo_mantenimiento'=>'general','urgencia'=>'baja','estado'=>'cerrado']);
    $service=new CierreMantenimientoService(new CasoMantenimientoService(),new DiagnosticoCasoService(new CasoMantenimientoService(),new EvidenciaCasoService(),new EvaluadorEstadoAreaService()),new ComparacionEjecucionService(),new DecisionCierreService());

    $service->reabrir($caso,$autor->id,'Se encontró una nueva incidencia',now());

    expect($caso->fresh()->estado)->toBe('en_ejecucion')
        ->and(EventoEjecucion::where('caso_id',$caso->id)->where('tipo','reapertura')->count())->toBe(1);
});

it('rechaza reapertura sin motivo', function () {
    $caso=CasoMantenimiento::make(['estado'=>'cerrado']);
    $service=new CierreMantenimientoService(new CasoMantenimientoService(),new DiagnosticoCasoService(new CasoMantenimientoService(),new EvidenciaCasoService(),new EvaluadorEstadoAreaService()),new ComparacionEjecucionService(),new DecisionCierreService());

    expect(fn () => $service->validarMotivoReapertura('   '))->toThrow(InvalidArgumentException::class,'motivo');
});
