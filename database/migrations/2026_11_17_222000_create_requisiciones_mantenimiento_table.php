<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('requisiciones_mantenimiento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caso_id')->constrained('casos_mantenimiento')->onDelete('restrict');
            $table->foreignId('autor_id')->constrained('users')->onDelete('restrict');
            $table->string('estado');
            $table->text('motivo')->nullable();
            $table->timestamps();
            $table->index(['caso_id', 'estado']);
        });

        Schema::create('requisicion_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requisicion_id')->constrained('requisiciones_mantenimiento')->onDelete('restrict');
            $table->foreignId('recurso_id')->constrained('recursos_catalogo')->onDelete('restrict');
            $table->decimal('cantidad_solicitada', 10, 2);
            $table->decimal('cantidad_recibida', 10, 2)->default(0);
            $table->string('estado');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requisicion_items');
        Schema::dropIfExists('requisiciones_mantenimiento');
    }
};
