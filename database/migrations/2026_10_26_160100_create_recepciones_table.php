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
        Schema::create('recepciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_almacen_id')->constrained('users')->onDelete('cascade');
            $table->enum('tipo', ['compra', 'entrega', 'devolucion']);
            $table->enum('estado', ['pendiente', 'conforme', 'no_conforme'])->default('pendiente');
            $table->dateTime('fecha_recepcion');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recepciones');
    }
};
