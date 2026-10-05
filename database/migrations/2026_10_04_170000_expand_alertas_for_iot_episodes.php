<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alertas', function (Blueprint $table) {
            $table->enum('tipo', ['riego', 'abono', 'stock_bajo', 'cuidado_planta'])->change();
            $table->string('variable')->nullable()->after('tipo');
            $table->decimal('valor_medido', 10, 2)->nullable()->after('variable');
            $table->decimal('limite', 10, 2)->nullable()->after('valor_medido');
            $table->enum('direccion', ['bajo', 'alto'])->nullable()->after('limite');
            $table->timestamp('resuelta_en')->nullable()->after('leida');
        });
    }

    public function down(): void
    {
        if (DB::table('alertas')->where('tipo', 'cuidado_planta')->exists()) {
            throw new RuntimeException(
                'No se puede revertir esta migración mientras existan alertas de tipo cuidado_planta.'
            );
        }

        Schema::table('alertas', function (Blueprint $table) {
            $table->dropColumn([
                'variable',
                'valor_medido',
                'limite',
                'direccion',
                'resuelta_en',
            ]);
            $table->enum('tipo', ['riego', 'abono', 'stock_bajo'])->change();
        });
    }
};
