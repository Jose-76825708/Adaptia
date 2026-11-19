<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planes_mantenimiento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caso_id')->constrained('casos_mantenimiento')->onDelete('restrict');
            $table->foreignId('autor_id')->constrained('users')->onDelete('restrict');
            $table->unsignedInteger('version');
            $table->timestamp('generado_en');
            $table->text('descripcion');
            $table->timestamps();
            $table->unique(['caso_id', 'version']);
        });

        Schema::create('tareas_plan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('planes_mantenimiento')->onDelete('restrict');
            $table->string('codigo');
            $table->string('titulo');
            $table->text('descripcion');
            $table->string('prioridad');
            $table->string('criticidad');
            $table->unsignedBigInteger('depende_de_tarea_id')->nullable();
            $table->date('fecha_estimada')->nullable();
            $table->unsignedInteger('duracion_dias');
            $table->string('estado')->default('pendiente');
            $table->timestamps();
            $table->foreign('depende_de_tarea_id')->references('id')->on('tareas_plan')->onDelete('restrict');
            $table->unique(['plan_id', 'codigo']);
        });

        Schema::create('tarea_recursos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tarea_id')->constrained('tareas_plan')->onDelete('restrict');
            $table->foreignId('recurso_id')->constrained('recursos_catalogo')->onDelete('restrict');
            $table->decimal('cantidad', 10, 2)->default(1);
            $table->timestamps();
        });

        Schema::create('items_checklist', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tarea_id')->constrained('tareas_plan')->onDelete('restrict');
            $table->text('descripcion');
            $table->boolean('cumplido')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items_checklist');
        Schema::dropIfExists('tarea_recursos');
        Schema::dropIfExists('tareas_plan');
        Schema::dropIfExists('planes_mantenimiento');
    }
};
