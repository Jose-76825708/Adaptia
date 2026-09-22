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
        Schema::table('perfiles_cliente', function (Blueprint $table) {
            // Renombrar columnas existentes para coincidir con el formulario actualizado
            $table->renameColumn('espacio', 'tamaño_adulto');
            $table->renameColumn('luz', 'luz_requerida');
            $table->renameColumn('mascotas_ninos', 'toxicidad');

            // Añadir las columnas nuevas que faltaban en el formulario
            $table->string('frecuencia_riego')->nullable()->after('luz_requerida');
            $table->string('tipo_ambiente')->nullable()->after('frecuencia_riego');
            $table->string('estetica')->nullable()->after('tipo_ambiente');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('perfiles_cliente', function (Blueprint $table) {
            // Eliminar las columnas nuevas
            $table->dropColumn(['frecuencia_riego', 'tipo_ambiente', 'estetica']);

            // Renombrar columnas de vuelta a los nombres originales
            $table->renameColumn('tamaño_adulto', 'espacio');
            $table->renameColumn('luz_requerida', 'luz');
            $table->renameColumn('toxicidad', 'mascotas_ninos');
        });
    }
};