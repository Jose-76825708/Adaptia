<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campanas_comerciales', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->text('objetivos');
            $table->decimal('presupuesto', 12, 2);
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->string('estado')->index();
            $table->foreignId('creador_id')->constrained('users')->cascadeOnDelete();
            $table->text('ajustes_siguiente_ciclo')->nullable();
            $table->timestamp('evaluada_en')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campanas_comerciales');
    }
};
