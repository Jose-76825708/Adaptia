<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proyectos_paisajismo', function (Blueprint $table) {
            $table->id();
            $table->string('codigo',20)->unique();
            $table->foreignId('solicitante_id')->constrained('users')->onDelete('restrict');
            $table->string('nombre',120);
            $table->string('ubicacion',160);
            $table->text('descripcion');
            $table->decimal('area_m2',10,2);
            $table->decimal('presupuesto_estimado',12,2)->nullable();
            $table->date('fecha_deseada')->nullable();
            $table->string('estado',20)->default('solicitado');
            $table->timestamps();

            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proyectos_paisajismo');
    }
};
