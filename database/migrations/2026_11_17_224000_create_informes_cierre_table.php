<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('informes_cierre', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caso_id')->constrained('casos_mantenimiento')->onDelete('restrict');
            $table->foreignId('autor_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('destinatario_id')->constrained('users')->onDelete('restrict');
            $table->unsignedInteger('version');
            $table->text('resumen');
            $table->text('contenido');
            $table->boolean('notificacion_pendiente')->default(true);
            $table->timestamp('cerrado_en');
            $table->timestamps();
            $table->unique(['caso_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('informes_cierre');
    }
};
