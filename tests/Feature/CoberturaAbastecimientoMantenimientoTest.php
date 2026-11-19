<?php

use App\Models\CasoMantenimiento;
use App\Models\RecursoCatalogo;
use App\Models\RequisicionItem;
use App\Models\RequisicionMantenimiento;
use App\Models\User;
use App\Services\AbastecimientoMantenimientoService;
use App\Services\CasoMantenimientoService;
use App\Services\RequisicionMantenimientoService;
use App\Services\VerificadorDisponibilidadService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    foreach (['movimientos_material','requisicion_items','requisiciones_mantenimiento','recursos_catalogo','casos_mantenimiento','users'] as $tabla) {
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
    Schema::create('recursos_catalogo', function (Blueprint $table) {
        $table->id(); $table->string('tipo'); $table->string('codigo')->unique(); $table->string('nombre'); $table->string('unidad');
        $table->decimal('stock_actual',10,2); $table->decimal('stock_minimo',10,2)->nullable(); $table->timestamps();
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
        $table->id(); $table->unsignedBigInteger('requisicion_item_id')->nullable(); $table->foreignId('caso_id')->constrained('casos_mantenimiento')->onDelete('restrict');
        $table->foreignId('recurso_id')->constrained('recursos_catalogo')->onDelete('restrict'); $table->string('tipo'); $table->decimal('stock_antes',10,2);
        $table->decimal('stock_despues',10,2); $table->decimal('cantidad',10,2); $table->text('motivo'); $table->foreignId('autor_id')->constrained('users')->onDelete('restrict'); $table->timestamps();
    });
});

it('rechaza requisicion bloqueada y ejecutable sin faltantes', function () {
    $service=new RequisicionMantenimientoService();

    expect(fn () => $service->generarDesdeVeredicto(['estado'=>'bloqueado','faltan_materiales'=>[],'faltan_herramientas'=>[['codigo'=>'H-1']]]))
        ->toThrow(InvalidArgumentException::class,'bloqueado')
        ->and(fn () => $service->generarDesdeVeredicto(['estado'=>'ejecutable','faltan_materiales'=>[],'faltan_herramientas'=>[]]))
        ->toThrow(InvalidArgumentException::class,'No faltan materiales');
});

it('detecta una requisicion abierta por caso', function () {
    $autor=User::factory()->create(['rol'=>'administrador']);
    $caso=CasoMantenimiento::create(['codigo'=>'MAN-AB-1','solicitante_id'=>$autor->id,'area_verde'=>'Jardín','descripcion'=>'Mantenimiento','tipo_mantenimiento'=>'riego','urgencia'=>'media','estado'=>'planificado']);
    RequisicionMantenimiento::create(['caso_id'=>$caso->id,'autor_id'=>$autor->id,'estado'=>'pendiente','motivo'=>null]);

    expect((new RequisicionMantenimientoService())->hayRequisicionAbierta($caso))->toBeTrue();
});

it('rechaza preparar caso fuera de planificado', function () {
    $caso=CasoMantenimiento::make(['estado'=>'diagnosticado']);
    $service=new AbastecimientoMantenimientoService(new CasoMantenimientoService(),new VerificadorDisponibilidadService());

    expect(fn () => $service->preparar($caso,1))->toThrow(InvalidArgumentException::class,'planificado');
});
