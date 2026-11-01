<?php

use App\Exceptions\AlertasStockBajoExistentesException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('alertas', function (Blueprint $table) {
            $table->foreignId('planta_id')->nullable()->after('planta_vendida_id')->constrained('plantas')->onDelete('cascade');
            $table->foreignId('planta_vendida_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::table('alertas')->whereNull('planta_vendida_id')->whereNotNull('planta_id')->exists()) {
            throw new AlertasStockBajoExistentesException(
                'No se puede revertir esta migración mientras existan alertas de stock bajo asociadas a una planta.'
            );
        }

        Schema::table('alertas', function (Blueprint $table) {
            $table->dropForeign(['planta_id']);
            $table->dropColumn('planta_id');
            $table->foreignId('planta_vendida_id')->nullable(false)->change();
        });
    }
};
