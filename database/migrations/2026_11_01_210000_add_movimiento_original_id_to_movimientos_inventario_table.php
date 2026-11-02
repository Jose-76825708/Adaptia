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
        Schema::table('movimientos_inventario', function (Blueprint $table) {
            $table->foreignId('movimiento_original_id')
                ->nullable()
                ->unique()
                ->after('stock_despues')
                ->constrained('movimientos_inventario')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movimientos_inventario', function (Blueprint $table) {
            $table->dropForeign(['movimiento_original_id']);
            $table->dropUnique(['movimiento_original_id']);
            $table->dropColumn('movimiento_original_id');
        });
    }
};
