<?php

use App\Models\CasoMantenimiento;
use App\Models\DiagnosticoCaso;
use App\Models\User;
use App\Services\CasoMantenimientoService;
use App\Services\DiagnosticoCasoService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    foreach (['lecturas_sensores', 'alertas', 'plantas_vendidas', 'hallazgos_diagnostico', 'diagnosticos_caso', 'evidencias_caso', 'casos_mantenimiento', 'users'] as $tabla) {
        Schema::dropIfExists($tabla);
    }

    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->timestamp('email_verified_at')->nullable();
        $table->string('password');
        $table->string('remember_token', 100)->nullable();
        $table->string('rol')->nullable();
        $table->timestamps();
    });

    Schema::create('casos_mantenimiento', function (Blueprint $table) {
        $table->id();
        $table->string('codigo')->unique();
        $table->foreignId('solicitante_id')->constrained('users')->onDelete('restrict');
        $table->string('area_verde');
        $table->text('descripcion');
        $table->date('fecha_deseada')->nullable();
        $table->text('observaciones')->nullable();
        $table->string('tipo_mantenimiento');
        $table->string('urgencia');
        $table->string('estado')->default('abierto');
        $table->timestamps();
    });

    Schema::create('plantas_vendidas', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('venta_id');
        $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
        $table->unsignedBigInteger('sensor_id')->nullable();
        $table->timestamps();
    });

    Schema::create('lecturas_sensores', function (Blueprint $table) {
        $table->id();
        $table->foreignId('planta_vendida_id')->constrained('plantas_vendidas')->onDelete('restrict');
        $table->decimal('humedad_suelo', 10, 2)->nullable();
        $table->decimal('temperatura', 10, 2)->nullable();
        $table->decimal('humedad_ambiental', 10, 2)->nullable();
        $table->decimal('luz', 10, 2)->nullable();
        $table->timestamp('fecha_hora');
        $table->timestamps();
    });

    Schema::create('alertas', function (Blueprint $table) {
        $table->id();
        $table->foreignId('planta_vendida_id')->constrained('plantas_vendidas')->onDelete('restrict');
        $table->string('tipo')->nullable();
        $table->string('variable')->nullable();
        $table->timestamps();
    });

    Schema::create('evidencias_caso', function (Blueprint $table) {
        $table->id();
        $table->foreignId('caso_id')->constrained('casos_mantenimiento')->onDelete('restrict');
        $table->foreignId('autor_id')->constrained('users')->onDelete('restrict');
        $table->string('tipo');
        $table->text('contenido')->nullable();
        $table->string('ruta')->nullable();
        $table->string('descripcion')->nullable();
        $table->timestamps();
    });

    Schema::create('diagnosticos_caso', function (Blueprint $table) {
        $table->id();
        $table->foreignId('caso_id')->constrained('casos_mantenimiento')->onDelete('restrict');
        $table->foreignId('autor_id')->constrained('users')->onDelete('restrict');
        $table->unsignedInteger('version');
        $table->string('severidad');
        $table->text('resumen');
        $table->text('justificacion');
        $table->timestamp('evaluado_en');
        $table->timestamps();
        $table->unique(['caso_id', 'version']);
    });

    Schema::create('hallazgos_diagnostico', function (Blueprint $table) {
        $table->id();
        $table->foreignId('diagnostico_id')->constrained('diagnosticos_caso')->onDelete('restrict');
        $table->string('tipo');
        $table->string('variable')->nullable();
        $table->string('severidad');
        $table->text('descripcion');
        $table->timestamps();
    });
});

test('persiste el diagnostico y lleva el caso abierto a diagnosticado', function () {
    $autor = User::factory()->create(['rol' => 'administrador']);
    $caso = CasoMantenimiento::create([
        'codigo' => 'MAN-TEST-00001',
        'solicitante_id' => $autor->id,
        'area_verde' => 'Jardín principal',
        'descripcion' => 'Hojas caídas',
        'tipo_mantenimiento' => 'general',
        'urgencia' => 'media',
        'estado' => 'abierto',
    ]);

    $servicio = new DiagnosticoCasoService(new CasoMantenimientoService(), new \App\Services\EvidenciaCasoService(), new \App\Services\EvaluadorEstadoAreaService());
    $diagnostico = $servicio->evaluarYPersistir($caso, $autor->id);

    expect($diagnostico)->toBeInstanceOf(DiagnosticoCaso::class)
        ->and($diagnostico->version)->toBe(1)
        ->and($diagnostico->severidad)->toBeIn(['baja', 'media', 'alta'])
        ->and($caso->fresh()->estado)->toBe('diagnosticado')
        ->and(DiagnosticoCaso::count())->toBe(1);
});

test('una reevaluacion crea una version nueva sin cambiar el estado diagnosticado', function () {
    $autor = User::factory()->create(['rol' => 'administrador']);
    $caso = CasoMantenimiento::create([
        'codigo' => 'MAN-TEST-00002',
        'solicitante_id' => $autor->id,
        'area_verde' => 'Patio',
        'descripcion' => 'Riego irregular',
        'tipo_mantenimiento' => 'riego',
        'urgencia' => 'baja',
        'estado' => 'abierto',
    ]);

    $servicio = new DiagnosticoCasoService(new CasoMantenimientoService(), new \App\Services\EvidenciaCasoService(), new \App\Services\EvaluadorEstadoAreaService());
    $primero = $servicio->evaluarYPersistir($caso, $autor->id);
    $segundo = $servicio->evaluarYPersistir($caso->fresh(), $autor->id);

    expect($primero->version)->toBe(1)
        ->and($segundo->version)->toBe(2)
        ->and($caso->fresh()->estado)->toBe('diagnosticado')
        ->and(DiagnosticoCaso::count())->toBe(2);
});

test('rechaza evaluar un caso cerrado', function () {
    $autor = User::factory()->create(['rol' => 'administrador']);
    $caso = CasoMantenimiento::create([
        'codigo' => 'MAN-TEST-00003',
        'solicitante_id' => $autor->id,
        'area_verde' => 'Azotea',
        'descripcion' => 'Área dañada',
        'tipo_mantenimiento' => 'general',
        'urgencia' => 'alta',
        'estado' => 'cerrado',
    ]);

    $servicio = new DiagnosticoCasoService(new CasoMantenimientoService(), new \App\Services\EvidenciaCasoService(), new \App\Services\EvaluadorEstadoAreaService());

    expect(fn () => $servicio->evaluarYPersistir($caso, $autor->id))
        ->toThrow(\InvalidArgumentException::class, 'cerrado');
});
