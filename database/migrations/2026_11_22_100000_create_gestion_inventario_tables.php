<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gestiones_inventario', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('tipo_item'); // planta | insumo
            $table->foreignId('planta_id')->nullable()->constrained('plantas')->restrictOnDelete();
            $table->foreignId('recurso_id')->nullable()->constrained('recursos_catalogo')->restrictOnDelete();
            $table->decimal('cantidad_requerida', 10, 2);
            $table->text('motivo')->nullable();
            $table->decimal('stock_sistema', 10, 2);
            $table->string('estado')->index();
            $table->foreignId('creador_id')->constrained('users')->restrictOnDelete();

            // Verificación de estado y cantidades
            $table->decimal('cantidad_fisica', 10, 2)->nullable();
            $table->decimal('cantidad_buen_estado', 10, 2)->nullable();
            $table->decimal('cantidad_disponible', 10, 2)->nullable();
            $table->decimal('faltante', 10, 2)->nullable();
            $table->text('observaciones_verificacion')->nullable();

            // Gestión de reposición y compra / abastecimiento
            $table->decimal('cantidad_reposicion', 10, 2)->nullable();
            $table->string('proveedor')->nullable();
            $table->decimal('costo_estimado', 12, 2)->nullable();
            $table->string('numero_orden')->nullable();
            $table->date('fecha_compra')->nullable();
            $table->decimal('costo_total', 12, 2)->nullable();

            // Ingreso, almacenamiento y cierre
            $table->decimal('cantidad_ingresada', 10, 2)->nullable();
            $table->string('documento_ingreso')->nullable();
            $table->string('ubicacion')->nullable();
            $table->string('resultado')->nullable();
            $table->timestamp('cerrada_en')->nullable();
            $table->timestamps();
        });

        Schema::create('recepciones_inventario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gestion_id')->constrained('gestiones_inventario')->restrictOnDelete();
            $table->decimal('cantidad_recibida', 10, 2);
            $table->text('observaciones')->nullable();
            $table->boolean('conforme')->nullable();
            $table->decimal('cantidad_aceptada', 10, 2)->nullable();
            $table->foreignId('autor_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('verificada_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verificada_en')->nullable();
            $table->timestamps();
        });

        Schema::create('incidencias_inventario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gestion_id')->constrained('gestiones_inventario')->restrictOnDelete();
            $table->foreignId('recepcion_id')->constrained('recepciones_inventario')->restrictOnDelete();
            $table->string('tipo'); // faltante | exceso | danado | incorrecto
            $table->decimal('cantidad_afectada', 10, 2);
            $table->text('descripcion');
            $table->string('estado')->default('abierta');
            $table->text('resolucion')->nullable();
            $table->foreignId('autor_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('gestionada_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('gestionada_en')->nullable();
            $table->timestamps();
            $table->index(['gestion_id', 'estado']);
        });

        Schema::create('movimientos_gestion_inventario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gestion_id')->constrained('gestiones_inventario')->restrictOnDelete();
            $table->string('tipo'); // entrada | salida
            $table->foreignId('planta_id')->nullable()->constrained('plantas')->restrictOnDelete();
            $table->foreignId('recurso_id')->nullable()->constrained('recursos_catalogo')->restrictOnDelete();
            $table->foreignId('movimiento_inventario_id')->nullable()->constrained('movimientos_inventario')->nullOnDelete();
            $table->decimal('cantidad', 10, 2);
            $table->decimal('stock_antes', 10, 2);
            $table->decimal('stock_despues', 10, 2);
            $table->text('motivo');
            $table->foreignId('autor_id')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_gestion_inventario');
        Schema::dropIfExists('incidencias_inventario');
        Schema::dropIfExists('recepciones_inventario');
        Schema::dropIfExists('gestiones_inventario');
    }
};
