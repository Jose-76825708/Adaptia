<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
    }

    public function down(): void
    {
        Schema::dropIfExists('diagnosticos_caso');
    }
};
