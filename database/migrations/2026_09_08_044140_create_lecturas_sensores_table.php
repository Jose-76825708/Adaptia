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
        Schema::create('lecturas_sensores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('planta_vendida_id')->constrained('plantas_vendidas')->onDelete('cascade');
            $table->decimal('humedad', 8, 2);
            $table->decimal('temperatura', 8, 2);
            $table->timestamp('fecha_hora')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lecturas_sensores');
    }
};
