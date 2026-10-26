<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('incidencias_recepcion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recepcion_id')->constrained('recepciones')->onDelete('cascade');
            $table->foreignId('planta_id')->nullable()->constrained('plantas')->nullOnDelete();
            $table->unsignedInteger('cantidad_esperada')->default(0);
            $table->unsignedInteger('cantidad_recibida')->default(0);
            $table->integer('diferencia')->default(0);
            $table->enum('motivo', ['faltante', 'sobrante', 'danado', 'otro'])->default('otro');
            $table->enum('estado', ['abierta', 'resuelta'])->default('abierta');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidencias_recepcion');
    }
};
