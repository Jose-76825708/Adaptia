<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('propuesta_plantas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('propuesta_id')->constrained('propuestas_diseno')->onDelete('restrict');
            $table->foreignId('planta_id')->constrained('plantas')->onDelete('restrict');
            $table->unsignedInteger('cantidad');
            $table->decimal('precio_unitario',10,2);
            $table->timestamps();
            $table->unique(['propuesta_id','planta_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('propuesta_plantas');
    }
};
