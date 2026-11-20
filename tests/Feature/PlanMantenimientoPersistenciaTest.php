<?php

use App\Models\CasoMantenimiento;
use App\Models\DiagnosticoCaso;
use App\Models\PlanMantenimiento;
use App\Models\TareaPlan;
use App\Models\RecursoCatalogo;
use App\Models\User;
use App\Services\CasoMantenimientoService;
use App\Services\PlanMantenimientoService;
use App\Services\GeneradorPlanMantenimientoService;
use App\Services\OrdenadorTareasPlanService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Database\Seeders\MantenimientoCatalogosSeeder;

beforeEach(function () {
    foreach (['items_checklist','tarea_recursos','tareas_plan','planes_mantenimiento','recursos_catalogo','diagnosticos_caso','casos_mantenimiento','users'] as $tabla) {
        Schema::dropIfExists($tabla);
    }

    Schema::create('users', function (Blueprint $table) {
        $table->id(); $table->string('name'); $table->string('email')->unique();
        $table->timestamp('email_verified_at')->nullable(); $table->string('password');
        $table->string('remember_token',100)->nullable(); $table->string('rol')->nullable(); $table->timestamps();
    });
    Schema::create('casos_mantenimiento', function (Blueprint $table) {
        $table->id(); $table->string('codigo')->unique(); $table->foreignId('solicitante_id')->constrained('users')->onDelete('restrict');
        $table->string('area_verde'); $table->text('descripcion'); $table->date('fecha_deseada')->nullable();
        $table->text('observaciones')->nullable(); $table->string('tipo_mantenimiento'); $table->string('urgencia');
        $table->string('estado')->default('abierto'); $table->timestamps();
    });
    Schema::create('diagnosticos_caso', function (Blueprint $table) {
        $table->id(); $table->foreignId('caso_id')->constrained('casos_mantenimiento')->onDelete('restrict');
        $table->foreignId('autor_id')->constrained('users')->onDelete('restrict'); $table->unsignedInteger('version');
        $table->string('severidad'); $table->text('resumen'); $table->text('justificacion'); $table->timestamp('evaluado_en'); $table->timestamps();
    });
    Schema::create('recursos_catalogo', function (Blueprint $table) {
        $table->id(); $table->string('tipo'); $table->string('codigo')->unique(); $table->string('nombre');
        $table->string('unidad'); $table->decimal('stock_actual',10,2); $table->decimal('stock_minimo',10,2)->nullable();
        $table->timestamps();
    });
    Schema::create('planes_mantenimiento', function (Blueprint $table) {
        $table->id(); $table->foreignId('caso_id')->constrained('casos_mantenimiento')->onDelete('restrict');
        $table->foreignId('autor_id')->constrained('users')->onDelete('restrict'); $table->unsignedInteger('version');
        $table->timestamp('generado_en'); $table->text('descripcion'); $table->timestamps(); $table->unique(['caso_id','version']);
    });
    Schema::create('tareas_plan', function (Blueprint $table) {
        $table->id(); $table->foreignId('plan_id')->constrained('planes_mantenimiento')->onDelete('restrict');
        $table->string('codigo'); $table->string('titulo'); $table->text('descripcion'); $table->string('prioridad');
        $table->string('criticidad'); $table->foreignId('depende_de_tarea_id')->nullable()->constrained('tareas_plan')->onDelete('restrict');
        $table->date('fecha_estimada')->nullable(); $table->unsignedInteger('duracion_dias'); $table->string('estado')->default('pendiente'); $table->timestamps();
        $table->unique(['plan_id','codigo']);
    });
    Schema::create('tarea_recursos', function (Blueprint $table) {
        $table->id(); $table->foreignId('tarea_id')->constrained('tareas_plan')->onDelete('restrict');
        $table->foreignId('recurso_id')->constrained('recursos_catalogo')->onDelete('restrict'); $table->decimal('cantidad',10,2)->default(1); $table->timestamps();
    });
    Schema::create('items_checklist', function (Blueprint $table) {
        $table->id(); $table->foreignId('tarea_id')->constrained('tareas_plan')->onDelete('restrict');
        $table->text('descripcion'); $table->boolean('cumplido')->default(false); $table->timestamps();
    });
});

it('genera y persiste el plan en una version y lleva el caso a planificado', function () {
    (new MantenimientoCatalogosSeeder())->run();
    $autor = User::factory()->create(['rol'=>'administrador']);
    $caso = CasoMantenimiento::create(['codigo'=>'MAN-TEST-PLAN-1','solicitante_id'=>$autor->id,'area_verde'=>'Jardín','descripcion'=>'Necesita riego','tipo_mantenimiento'=>'riego','urgencia'=>'media','estado'=>'diagnosticado']);
    DiagnosticoCaso::create(['caso_id'=>$caso->id,'autor_id'=>$autor->id,'version'=>1,'severidad'=>'baja','resumen'=>'Sin riesgos','justificacion'=>'Sin riesgos','evaluado_en'=>now()]);

    $service = new PlanMantenimientoService(new CasoMantenimientoService(), new GeneradorPlanMantenimientoService(), new OrdenadorTareasPlanService());
    $plan = $service->generarYPersistir($caso, $autor->id);

    expect($plan)->toBeInstanceOf(PlanMantenimiento::class)
        ->and($plan->version)->toBe(1)
        ->and(TareaPlan::where('plan_id',$plan->id)->count())->toBe(2)
        ->and($caso->fresh()->estado)->toBe('planificado');
});

it('rechaza un recurso ausente sin guardar el plan', function () {
    $autor = User::factory()->create(['rol'=>'administrador']);
    $caso = CasoMantenimiento::create(['codigo'=>'MAN-TEST-PLAN-2','solicitante_id'=>$autor->id,'area_verde'=>'Jardín','descripcion'=>'Necesita riego','tipo_mantenimiento'=>'riego','urgencia'=>'media','estado'=>'diagnosticado']);
    DiagnosticoCaso::create(['caso_id'=>$caso->id,'autor_id'=>$autor->id,'version'=>1,'severidad'=>'baja','resumen'=>'Sin riesgos','justificacion'=>'Sin riesgos','evaluado_en'=>now()]);

    $service = new PlanMantenimientoService(new CasoMantenimientoService(), new GeneradorPlanMantenimientoService(), new OrdenadorTareasPlanService());

    expect(fn () => $service->generarYPersistir($caso,$autor->id))->toThrow(InvalidArgumentException::class,'recurso')
        ->and(PlanMantenimiento::count())->toBe(0)
        ->and($caso->fresh()->estado)->toBe('diagnosticado');
});
