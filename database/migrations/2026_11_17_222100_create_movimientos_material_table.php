<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos_material', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requisicion_item_id')->nullable()->constrained('requisicion_items')->onDelete('restrict');
            $table->foreignId('caso_id')->constrained('casos_mantenimiento')->onDelete('restrict');
            $table->foreignId('recurso_id')->constrained('recursos_catalogo')->onDelete('restrict');
            $table->string('tipo')->default('entrada');
            $table->decimal('stock_antes', 10, 2);
            $table->decimal('stock_despues', 10, 2);
            $table->decimal('cantidad', 10, 2);
            $table->text('motivo');
            $table->foreignId('autor_id')->constrained('users')->onDelete('restrict');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_material');
    }
};
