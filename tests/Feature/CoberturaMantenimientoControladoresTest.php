<?php

use App\Http\Controllers\AbastecimientoMantenimientoController;
use App\Http\Controllers\CierreMantenimientoController;
use App\Http\Controllers\EjecucionMantenimientoController;
use App\Http\Controllers\PlanMantenimientoController;
use App\Models\CasoMantenimiento;
use App\Models\EventoEjecucion;
use App\Models\InformeCierre;
use App\Models\ItemChecklist;
use App\Models\TareaPlan;
use App\Models\User;
use App\Services\AbastecimientoMantenimientoService;
use App\Services\CasoMantenimientoService;
use App\Services\CierreMantenimientoService;
use App\Services\ComparacionEjecucionService;
use App\Services\DecisionCierreService;
use App\Services\DiagnosticoCasoService;
use App\Services\EjecucionMantenimientoService;
use App\Services\EstadoTareaChecklistService;
use App\Services\EvidenciaCasoService;
use App\Services\EvaluadorEstadoAreaService;
use App\Services\RecepcionMaterialService;
use App\Services\RecomendacionCompraService;
use App\Services\VerificadorDisponibilidadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

beforeEach(function () {
    Schema::dropIfExists('informes_cierre');
    Schema::dropIfExists('eventos_ejecucion');
    Schema::dropIfExists('items_checklist');
    Schema::dropIfExists('tareas_plan');
    Schema::dropIfExists('planes_mantenimiento');
    Schema::dropIfExists('requisicion_items');
    Schema::dropIfExists('requisiciones_mantenimiento');
    Schema::dropIfExists('movimientos_material');
    Schema::dropIfExists('recursos_catalogo');
    Schema::dropIfExists('casos_mantenimiento');
    Schema::dropIfExists('users');

    Schema::create('users', function (Blueprint $table) {
        $table->id(); $table->string('name'); $table->string('email')->unique(); $table->timestamp('email_verified_at')->nullable();
        $table->string('password'); $table->string('remember_token',100)->nullable(); $table->string('rol')->nullable(); $table->timestamps();
    });
    Schema::create('casos_mantenimiento', function (Blueprint $table) {
        $table->id(); $table->string('codigo')->unique(); $table->foreignId('solicitante_id')->constrained('users')->onDelete('restrict');
        $table->string('area_verde'); $table->text('descripcion'); $table->date('fecha_deseada')->nullable(); $table->text('observaciones')->nullable();
        $table->string('tipo_mantenimiento'); $table->string('urgencia'); $table->string('estado'); $table->timestamps();
    });
});

it('RecomendacionCompra cubre prioridad desconocida y sin items', function () {
    $service = new RecomendacionCompraService();

    expect($service->generar([]))->toBe([])
        ->and($service->generar([
            ['codigo' => 'M-1', 'prioridad' => 'desconocida'],
            ['codigo' => 'M-2', 'prioridad' => 'alta'],
        ]))->toHaveCount(2);
});

it('VerificadorDisponibilidad cubre herramientas, materiales y veredicto ejecutable', function () {
    $service = new VerificadorDisponibilidadService();

    expect($service->consultarHerramientas([]))->toBe([])
        ->and($service->consultarMateriales([]))->toBe([])
        ->and($service->calcularVeredicto([], [])['estado'])->toBe('ejecutable');
});

it('EstadoTareaChecklist rechaza checklist fuera de ejecucion', function () {
    $caso = CasoMantenimiento::make(['estado' => 'preparado']);
    $item = ItemChecklist::make(['cumplido' => false]);
    $service = new EstadoTareaChecklistService();

    expect(fn () => $service->actualizarItem($caso, $item, true))
        ->toThrow(InvalidArgumentException::class, 'ejecución');
});

it('Cierre valida motivo y rechaza reapertura fuera de cerrado', function () {
    $caso = CasoMantenimiento::make(['estado' => 'en_ejecucion']);
    $service = new CierreMantenimientoService(
        new CasoMantenimientoService(),
        new DiagnosticoCasoService(new CasoMantenimientoService(), new EvidenciaCasoService(), new EvaluadorEstadoAreaService()),
        new ComparacionEjecucionService(),
        new DecisionCierreService(),
    );

    expect($service->validarMotivoReapertura('Revisión técnica'))->toBe('Revisión técnica')
        ->and(fn () => $service->reabrir($caso, 1, 'Revisión técnica'))
        ->toThrow(InvalidArgumentException::class, 'cerrado');
});
