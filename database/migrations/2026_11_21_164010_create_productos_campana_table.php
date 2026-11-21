<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos_campana', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campana_id')->constrained('campanas_comerciales')->cascadeOnDelete();
            $table->foreignId('planta_id')->constrained('plantas')->restrictOnDelete();
            $table->unsignedInteger('cantidad_meta');
            $table->decimal('descuento_porcentaje', 5, 2)->nullable();
            $table->boolean('incluida')->default(true);
            $table->unsignedInteger('cantidad_vendida_evaluada')->nullable();
            $table->decimal('porcentaje_cumplimiento', 10, 2)->nullable();
            $table->decimal('ingresos_estimados', 14, 2)->nullable();
            $table->timestamps();

            $table->unique(['campana_id', 'planta_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos_campana');
    }
};
