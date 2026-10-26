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
        Schema::create('detalle_recepciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recepcion_id')->constrained('recepciones')->onDelete('cascade');
            $table->foreignId('planta_id')->constrained('plantas')->onDelete('cascade');
            $table->unsignedInteger('cantidad_esperada');
            $table->unsignedInteger('cantidad_recibida');
            $table->string('unidad');
            $table->enum('estado_item', ['pendiente', 'conforme', 'no_conforme'])->default('pendiente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_recepciones');
    }
};
