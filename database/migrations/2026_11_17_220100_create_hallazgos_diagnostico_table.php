<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hallazgos_diagnostico', function (Blueprint $table) {
            $table->id();
            $table->foreignId('diagnostico_id')->constrained('diagnosticos_caso')->onDelete('restrict');
            $table->string('tipo');
            $table->string('variable')->nullable();
            $table->string('severidad');
            $table->text('descripcion');
            $table->timestamps();

            $table->index(['diagnostico_id', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hallazgos_diagnostico');
    }
};
