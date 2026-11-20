<?php

use App\Models\CasoMantenimiento;
use App\Models\DiagnosticoCaso;
use App\Models\EvidenciaCaso;
use App\Models\EventoEjecucion;
use App\Models\InformeCierre;
use App\Models\PlanMantenimiento;
use App\Models\RecursoCatalogo;
use App\Models\RequisicionItem;
use App\Models\RequisicionMantenimiento;
use App\Models\TareaPlan;
use App\Models\ItemChecklist;
use App\Models\User;
use App\Services\CasoMantenimientoService;
use App\Services\CierreMantenimientoService;
use App\Services\ComparacionEjecucionService;
use App\Services\DecisionCierreService;
use App\Services\DiagnosticoCasoService;
use App\Services\EjecucionMantenimientoService;
use App\Services\EstadoTareaChecklistService;
use App\Services\EvidenciaCasoService;
use App\Services\EvaluadorEstadoAreaService;
use App\Services\RequisicionMantenimientoService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

beforeEach(function () {
    Storage::fake('public');

    foreach ([
        'lecturas_sensores','plantas_vendidas','informes_cierre','eventos_ejecucion','items_checklist','tarea_recursos','tareas_plan','planes_mantenimiento',
        'movimientos_material','requisicion_items','requisiciones_mantenimiento','recursos_catalogo','hallazgos_diagnostico',
        'diagnosticos_caso','evidencias_caso','casos_mantenimiento','users',
    ] as $tabla) {
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
    Schema::create('plantas_vendidas', function (Blueprint $table) {
        $table->id(); $table->unsignedBigInteger('venta_id')->nullable(); $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
        $table->unsignedBigInteger('sensor_id')->nullable(); $table->timestamps();
    });
    Schema::create('lecturas_sensores', function (Blueprint $table) {
        $table->id(); $table->foreignId('planta_vendida_id')->constrained('plantas_vendidas')->onDelete('restrict');
        $table->decimal('humedad_suelo',10,2)->nullable(); $table->decimal('temperatura',10,2)->nullable();
        $table->decimal('humedad_ambiental',10,2)->nullable(); $table->decimal('luz',10,2)->nullable(); $table->timestamp('fecha_hora'); $table->timestamps();
    });

    Schema::create('evidencias_caso', function (Blueprint $table) {
        $table->id(); $table->foreignId('caso_id')->constrained('casos_mantenimiento')->onDelete('restrict');
        $table->foreignId('autor_id')->constrained('users')->onDelete('restrict'); $table->string('tipo'); $table->text('contenido')->nullable();
        $table->string('ruta')->nullable(); $table->string('descripcion')->nullable(); $table->timestamps();
    });
    Schema::create('diagnosticos_caso', function (Blueprint $table) {
        $table->id(); $table->foreignId('caso_id')->constrained('casos_mantenimiento')->onDelete('restrict');
        $table->foreignId('autor_id')->constrained('users')->onDelete('restrict'); $table->unsignedInteger('version');
        $table->string('severidad'); $table->text('resumen'); $table->text('justificacion'); $table->timestamp('evaluado_en'); $table->timestamps();
    });
    Schema::create('hallazgos_diagnostico', function (Blueprint $table) {
        $table->id(); $table->foreignId('diagnostico_id')->constrained('diagnosticos_caso')->onDelete('restrict');
        $table->string('tipo'); $table->string('variable')->nullable(); $table->string('severidad'); $table->text('descripcion'); $table->timestamps();
    });
    Schema::create('recursos_catalogo', function (Blueprint $table) {
        $table->id(); $table->string('tipo'); $table->string('codigo')->unique(); $table->string('nombre'); $table->string('unidad');
        $table->decimal('stock_actual',10,2); $table->decimal('stock_minimo',10,2)->nullable(); $table->timestamps();
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
    Schema::create('tarea_recursos', function (Blueprint $table) {
        $table->id(); $table->foreignId('tarea_id')->constrained('tareas_plan')->onDelete('restrict');
        $table->foreignId('recurso_id')->constrained('recursos_catalogo')->onDelete('restrict'); $table->decimal('cantidad',10,2); $table->timestamps();
    });
    Schema::create('items_checklist', function (Blueprint $table) {
        $table->id(); $table->foreignId('tarea_id')->constrained('tareas_plan')->onDelete('restrict');
        $table->text('descripcion'); $table->boolean('cumplido')->default(false); $table->timestamps();
    });
    Schema::create('requisiciones_mantenimiento', function (Blueprint $table) {
        $table->id(); $table->foreignId('caso_id')->constrained('casos_mantenimiento')->onDelete('restrict');
        $table->foreignId('autor_id')->constrained('users')->onDelete('restrict'); $table->string('estado'); $table->text('motivo')->nullable(); $table->timestamps();
    });
    Schema::create('requisicion_items', function (Blueprint $table) {
        $table->id(); $table->foreignId('requisicion_id')->constrained('requisiciones_mantenimiento')->onDelete('restrict');
        $table->foreignId('recurso_id')->constrained('recursos_catalogo')->onDelete('restrict'); $table->decimal('cantidad_solicitada',10,2);
        $table->decimal('cantidad_recibida',10,2)->default(0); $table->string('estado'); $table->timestamps();
    });
    Schema::create('movimientos_material', function (Blueprint $table) {
        $table->id(); $table->foreignId('requisicion_item_id')->nullable(); $table->foreignId('caso_id')->constrained('casos_mantenimiento')->onDelete('restrict');
        $table->foreignId('recurso_id')->constrained('recursos_catalogo')->onDelete('restrict'); $table->string('tipo');
        $table->decimal('stock_antes',10,2); $table->decimal('stock_despues',10,2); $table->decimal('cantidad',10,2); $table->text('motivo');
        $table->foreignId('autor_id')->constrained('users')->onDelete('restrict'); $table->timestamps();
    });
    Schema::create('eventos_ejecucion', function (Blueprint $table) {
        $table->id(); $table->foreignId('caso_id')->constrained('casos_mantenimiento')->onDelete('restrict');
        $table->unsignedBigInteger('tarea_id')->nullable(); $table->string('tipo'); $table->text('descripcion');
        $table->foreignId('autor_id')->constrained('users')->onDelete('restrict'); $table->timestamp('fecha'); $table->timestamp('resuelta_en')->nullable(); $table->timestamps();
    });
    Schema::create('informes_cierre', function (Blueprint $table) {
        $table->id(); $table->foreignId('caso_id')->constrained('casos_mantenimiento')->onDelete('restrict');
        $table->foreignId('autor_id')->constrained('users')->onDelete('restrict'); $table->foreignId('destinatario_id')->constrained('users')->onDelete('restrict');
        $table->unsignedInteger('version'); $table->text('resumen'); $table->text('contenido'); $table->boolean('notificacion_pendiente')->default(true);
        $table->timestamp('cerrado_en'); $table->timestamps();
    });
});

it('evidencia registra nota y elimina foto del disco', function () {
    $autor = User::factory()->create(['rol'=>'administrador']);
    $caso = CasoMantenimiento::create(['codigo'=>'MAN-EV-1','solicitante_id'=>$autor->id,'area_verde'=>'Patio','descripcion'=>'Necesito revisión','tipo_mantenimiento'=>'general','urgencia'=>'media','estado'=>'abierto']);
    $service = new EvidenciaCasoService();

    $nota = $service->registrarNota($caso,$autor->id,'Hay hojas secas');
    $foto = $service->registrarFoto($caso,$autor->id,UploadedFile::fake()->create('evidencia.jpg',100,'image/jpeg'),'Evidencia');

    expect($nota->tipo)->toBe('nota')->and($foto->tipo)->toBe('foto');

    $service->eliminarFoto($caso,$foto);
    expect(EvidenciaCaso::whereKey($foto->id)->exists())->toBeFalse();
});

it('evidencia rechaza foto ajena y caso cerrado', function () {
    $autor = User::factory()->create(['rol'=>'administrador']);
    $caso = CasoMantenimiento::create(['codigo'=>'MAN-EV-2','solicitante_id'=>$autor->id,'area_verde'=>'Patio','descripcion'=>'Necesito revisión','tipo_mantenimiento'=>'general','urgencia'=>'media','estado'=>'abierto']);
    $otro = CasoMantenimiento::create(['codigo'=>'MAN-EV-3','solicitante_id'=>$autor->id,'area_verde'=>'Otro','descripcion'=>'Necesito revisión','tipo_mantenimiento'=>'general','urgencia'=>'baja','estado'=>'abierto']);
    $foto = EvidenciaCaso::create(['caso_id'=>$otro->id,'autor_id'=>$autor->id,'tipo'=>'foto','contenido'=>null,'ruta'=>'evidencias/x.jpg','descripcion'=>'Foto']);

    expect(fn () => (new EvidenciaCasoService())->eliminarFoto($caso,$foto))->toThrow(Illuminate\Validation\ValidationException::class);

    $caso->estado='cerrado'; $caso->save();
    expect(fn () => (new EvidenciaCasoService())->registrarNota($caso,$autor->id,'Texto válido'))->toThrow(Illuminate\Validation\ValidationException::class);
});

it('evidencia compila paquete vacio sin lecturas ni evidencia', function () {
    $autor = User::factory()->create(['rol'=>'cliente']);
    $caso = CasoMantenimiento::create(['codigo'=>'MAN-EV-4','solicitante_id'=>$autor->id,'area_verde'=>'Jardín','descripcion'=>'Sin sensores','tipo_mantenimiento'=>'general','urgencia'=>'baja','estado'=>'abierto']);

    $paquete = (new EvidenciaCasoService())->compilar($caso, now());

    expect($paquete)->toHaveKeys(['caso','evidencias','sensores'])
        ->and($paquete['evidencias']['notas'])->toBeEmpty()
        ->and($paquete['sensores'])->toBeEmpty();
});
