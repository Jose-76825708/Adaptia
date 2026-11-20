<?php

use App\Models\CasoMantenimiento;
use App\Models\MovimientoMaterial;
use App\Models\RecursoCatalogo;
use App\Models\RequisicionItem;
use App\Models\RequisicionMantenimiento;
use App\Models\User;
use App\Services\RecepcionMaterialService;
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
        $table->string('tipo_mantenimiento'); $table->string('urgencia'); $table->string('estado')->default('planificado'); $table->timestamps();
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
        $table->id(); $table->foreignId('requisicion_item_id')->nullable()->constrained('requisicion_items')->onDelete('restrict');
        $table->foreignId('caso_id')->constrained('casos_mantenimiento')->onDelete('restrict'); $table->foreignId('recurso_id')->constrained('recursos_catalogo')->onDelete('restrict');
        $table->string('tipo'); $table->decimal('stock_antes',10,2); $table->decimal('stock_despues',10,2); $table->decimal('cantidad',10,2);
        $table->text('motivo'); $table->foreignId('autor_id')->constrained('users')->onDelete('restrict'); $table->timestamps();
    });
});

it('registra recepción parcial y trazabilidad del stock', function () {
    $autor = User::factory()->create(['rol'=>'administrador']);
    $caso = CasoMantenimiento::create(['codigo'=>'MAN-REC-1','solicitante_id'=>$autor->id,'area_verde'=>'Jardín','descripcion'=>'Mantenimiento','tipo_mantenimiento'=>'fertilizacion','urgencia'=>'alta','estado'=>'planificado']);
    $recurso = RecursoCatalogo::create(['tipo'=>'material','codigo'=>'M-001','nombre'=>'Abono','unidad'=>'kg','stock_actual'=>5,'stock_minimo'=>2]);
    $requisicion = RequisicionMantenimiento::create(['caso_id'=>$caso->id,'autor_id'=>$autor->id,'estado'=>'pendiente','motivo'=>null]);
    $item = RequisicionItem::create(['requisicion_id'=>$requisicion->id,'recurso_id'=>$recurso->id,'cantidad_solicitada'=>5,'cantidad_recibida'=>0,'estado'=>'pendiente']);

    $movimiento = (new RecepcionMaterialService())->recibir($caso,$item,2.0,$autor->id);

    expect($movimiento)->toBeInstanceOf(MovimientoMaterial::class)
        ->and((float)$movimiento->stock_antes)->toBe(5.0)
        ->and((float)$movimiento->stock_despues)->toBe(7.0)
        ->and($item->fresh()->estado)->toBe('parcial')
        ->and($requisicion->fresh()->estado)->toBe('parcial')
        ->and($recurso->fresh()->stock_actual)->toBe('7.00');
});
