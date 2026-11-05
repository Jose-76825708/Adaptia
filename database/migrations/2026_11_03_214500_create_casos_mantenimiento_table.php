<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('casos_mantenimiento', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->foreignId('solicitante_id')->constrained('users');
            $table->string('area_verde', 120);
            $table->text('descripcion');
            $table->date('fecha_deseada')->nullable();
            $table->text('observaciones')->nullable();
            $table->string('tipo_mantenimiento', 50);
            $table->string('urgencia', 20);
            $table->string('estado', 30)->default('abierto');
            $table->index('estado');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('casos_mantenimiento');
    }
};
