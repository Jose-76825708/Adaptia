<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('propuestas_diseno', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyecto_id')->constrained('proyectos_paisajismo')->onDelete('restrict');
            $table->foreignId('autor_id')->constrained('users')->onDelete('restrict');
            $table->unsignedInteger('version');
            $table->text('concepto');
            $table->decimal('costo_plantas',12,2);
            $table->decimal('costo_mano_obra',12,2);
            $table->decimal('total',12,2);
            $table->string('decision',20)->nullable();
            $table->text('motivo_decision')->nullable();
            $table->timestamp('decidida_en')->nullable();
            $table->timestamps();
            $table->unique(['proyecto_id','version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('propuestas_diseno');
    }
};
