<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('avances_instalacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyecto_id')->constrained('proyectos_paisajismo')->onDelete('restrict');
            $table->foreignId('autor_id')->constrained('users')->onDelete('restrict');
            $table->text('descripcion');
            $table->unsignedTinyInteger('porcentaje');
            $table->timestamps();
            $table->index(['proyecto_id','porcentaje']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avances_instalacion');
    }
};
