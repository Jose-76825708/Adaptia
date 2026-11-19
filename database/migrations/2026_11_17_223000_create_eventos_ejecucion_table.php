<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eventos_ejecucion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caso_id')->constrained('casos_mantenimiento')->onDelete('restrict');
            $table->unsignedBigInteger('tarea_id')->nullable();
            $table->string('tipo');
            $table->text('descripcion');
            $table->foreignId('autor_id')->constrained('users')->onDelete('restrict');
            $table->timestamp('fecha');
            $table->timestamp('resuelta_en')->nullable();
            $table->timestamps();
            $table->foreign('tarea_id')->references('id')->on('tareas_plan')->onDelete('restrict');
            $table->index(['caso_id', 'tipo', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eventos_ejecucion');
    }
};
