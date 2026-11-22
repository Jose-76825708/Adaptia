<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lotes_produccion', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->foreignId('responsable_id')->constrained('users');
            $table->foreignId('planta_id')->nullable()->constrained('plantas');
            $table->string('motivo');
            $table->unsignedInteger('cantidad_meta');
            $table->text('observaciones')->nullable();
            $table->string('estado')->default('necesidad_identificada');
            $table->string('metodo_propagacion')->nullable();
            $table->unsignedInteger('cantidad_material')->nullable();
            $table->text('sustrato_mezcla')->nullable();
            $table->date('fecha_propagacion')->nullable();
            $table->unsignedInteger('cantidad_sembrada')->nullable();
            $table->unsignedInteger('cantidad_perdida')->default(0);
            $table->unsignedInteger('cantidad_categoria_a')->nullable();
            $table->unsignedInteger('cantidad_categoria_b')->nullable();
            $table->unsignedInteger('cantidad_descarte')->nullable();
            $table->unsignedInteger('cantidad_producida')->nullable();
            $table->foreignId('movimiento_inventario_id')->nullable()
                ->constrained('movimientos_inventario')->nullOnDelete();
            $table->timestamp('cerrado_en')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lotes_produccion');
    }
};
