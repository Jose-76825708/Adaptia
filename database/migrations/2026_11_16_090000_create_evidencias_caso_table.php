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
        Schema::create('evidencias_caso', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caso_id')->constrained('casos_mantenimiento')->onDelete('restrict');
            $table->foreignId('autor_id')->constrained('users')->onDelete('restrict');
            $table->string('tipo');
            $table->text('contenido')->nullable();
            $table->string('ruta')->nullable();
            $table->string('descripcion')->nullable();
            $table->timestamps();

            $table->index(['caso_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evidencias_caso');
    }
};
