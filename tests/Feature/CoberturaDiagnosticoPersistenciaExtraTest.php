<?php

use App\Models\CasoMantenimiento;
use App\Models\DiagnosticoCaso;
use App\Models\User;
use App\Services\CasoMantenimientoService;
use App\Services\DiagnosticoCasoService;
use App\Services\EvidenciaCasoService;
use App\Services\EvaluadorEstadoAreaService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    foreach (['alertas','hallazgos_diagnostico','diagnosticos_caso','evidencias_caso','plantas_vendidas','lecturas_sensores','casos_mantenimiento','users'] as $tabla) {
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
    Schema::create('alertas', function (Blueprint $table) {
        $table->id(); $table->foreignId('planta_vendida_id')->constrained('plantas_vendidas')->onDelete('restrict');
        $table->string('tipo')->nullable(); $table->string('variable')->nullable(); $table->timestamps();
    });

    Schema::create('evidencias_caso', function (Blueprint $table) {
        $table->id(); $table->foreignId('caso_id')->constrained('casos_mantenimiento')->onDelete('restrict'); $table->foreignId('autor_id')->constrained('users')->onDelete('restrict');
        $table->string('tipo'); $table->text('contenido')->nullable(); $table->string('ruta')->nullable(); $table->string('descripcion')->nullable(); $table->timestamps();
    });
    Schema::create('plantas_vendidas', function (Blueprint $table) {
        $table->id(); $table->unsignedBigInteger('venta_id')->nullable(); $table->foreignId('user_id')->constrained('users')->onDelete('restrict'); $table->unsignedBigInteger('sensor_id')->nullable(); $table->timestamps();
    });
    Schema::create('lecturas_sensores', function (Blueprint $table) {
        $table->id(); $table->foreignId('planta_vendida_id')->constrained('plantas_vendidas')->onDelete('restrict');
        $table->decimal('humedad_suelo',10,2)->nullable(); $table->decimal('temperatura',10,2)->nullable(); $table->decimal('humedad_ambiental',10,2)->nullable();
        $table->decimal('luz',10,2)->nullable(); $table->timestamp('fecha_hora'); $table->timestamps();
    });
    Schema::create('diagnosticos_caso', function (Blueprint $table) {
        $table->id(); $table->foreignId('caso_id')->constrained('casos_mantenimiento')->onDelete('restrict'); $table->foreignId('autor_id')->constrained('users')->onDelete('restrict');
        $table->unsignedInteger('version'); $table->string('severidad'); $table->text('resumen'); $table->text('justificacion'); $table->timestamp('evaluado_en'); $table->timestamps();
    });
    Schema::create('hallazgos_diagnostico', function (Blueprint $table) {
        $table->id(); $table->foreignId('diagnostico_id')->constrained('diagnosticos_caso')->onDelete('restrict'); $table->string('tipo');
        $table->string('variable')->nullable(); $table->string('severidad'); $table->text('descripcion'); $table->timestamps();
    });
});

it('rechaza reevaluar un caso cerrado', function () {
    $autor=User::factory()->create(['rol'=>'administrador']);
    $caso=CasoMantenimiento::create(['codigo'=>'MAN-DX-1','solicitante_id'=>$autor->id,'area_verde'=>'Jardín','descripcion'=>'Mantenimiento','tipo_mantenimiento'=>'general','urgencia'=>'baja','estado'=>'cerrado']);
    $service=new DiagnosticoCasoService(new CasoMantenimientoService(),new EvidenciaCasoService(),new EvaluadorEstadoAreaService());

    expect(fn () => $service->evaluarYPersistir($caso,$autor->id))->toThrow(InvalidArgumentException::class,'cerrado');
});

it('permite reevaluar en ejecucion conservando estado', function () {
    $autor=User::factory()->create(['rol'=>'administrador']);
    $caso=CasoMantenimiento::create(['codigo'=>'MAN-DX-2','solicitante_id'=>$autor->id,'area_verde'=>'Jardín','descripcion'=>'Mantenimiento','tipo_mantenimiento'=>'general','urgencia'=>'baja','estado'=>'en_ejecucion']);
    $service=new DiagnosticoCasoService(new CasoMantenimientoService(),new EvidenciaCasoService(),new EvaluadorEstadoAreaService());

    $diagnostico=$service->evaluarYPersistir($caso,$autor->id,now());

    expect($diagnostico)->toBeInstanceOf(DiagnosticoCaso::class)
        ->and($caso->fresh()->estado)->toBe('en_ejecucion')
        ->and($diagnostico->version)->toBe(1);
});

it('rechaza reevaluar estado planificado', function () {
    $autor=User::factory()->create(['rol'=>'administrador']);
    $caso=CasoMantenimiento::create(['codigo'=>'MAN-DX-3','solicitante_id'=>$autor->id,'area_verde'=>'Jardín','descripcion'=>'Mantenimiento','tipo_mantenimiento'=>'general','urgencia'=>'baja','estado'=>'planificado']);
    $service=new DiagnosticoCasoService(new CasoMantenimientoService(),new EvidenciaCasoService(),new EvaluadorEstadoAreaService());

    expect(fn () => $service->evaluarYPersistir($caso,$autor->id))->toThrow(InvalidArgumentException::class,'estado actual');
});
