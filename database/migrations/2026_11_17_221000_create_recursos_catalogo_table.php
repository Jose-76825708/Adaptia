<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recursos_catalogo', function (Blueprint $table) {
            $table->id();
            $table->string('tipo');
            $table->string('codigo')->unique();
            $table->string('nombre');
            $table->string('unidad');
            $table->decimal('stock_actual', 10, 2);
            $table->decimal('stock_minimo', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recursos_catalogo');
    }
};
