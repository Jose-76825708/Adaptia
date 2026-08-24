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
        Schema::create('plantas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_planta_id')->constrained();
            $table->string('nombre');
            $table->text('descripcion');
            $table->string('imagen');
            $table->enum('luz_requerida',['baja','media','alta','siempre_en_el_sol']);
            $table->enum('frecuencia_riego',['diario','cada_3_dias','semanal','quincenal','mensualmente']);
            $table->enum('tamaño_adulto',['pequena','mediana','grande']);
            $table->enum('nivel_cuidado',['principiante','intermedio','experto']);
            $table->enum('tipo_ambiente',['interiores','exteriores','ambos']);
            $table->boolean('toxicidad');
            $table->enum('estetica',['follaje','flor','colgantes','suculentas']);
            $table->decimal('precio');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plantas');
    }
};
