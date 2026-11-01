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
            $table->string('motivo', 255)->nullable()->after('cantidad');
            $table->integer('stock_antes')->nullable()->after('motivo');
            $table->integer('stock_despues')->nullable()->after('stock_antes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movimientos_inventario', function (Blueprint $table) {
            $table->dropColumn(['motivo', 'stock_antes', 'stock_despues']);
        });
    }
};
