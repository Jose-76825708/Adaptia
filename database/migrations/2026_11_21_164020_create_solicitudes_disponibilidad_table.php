<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitudes_disponibilidad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_campana_id')->constrained('productos_campana')->cascadeOnDelete();
            $table->unsignedInteger('faltante');
            $table->string('estado')->default('pendiente')->index();
            $table->text('notas')->nullable();
            $table->foreignId('gestionada_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('gestionada_en')->nullable();
            $table->timestamps();

            $table->unique('producto_campana_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes_disponibilidad');
    }
};
