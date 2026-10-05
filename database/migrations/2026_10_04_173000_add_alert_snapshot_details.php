<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alertas', function (Blueprint $table) {
            $table->decimal('rango_minimo', 10, 2)->nullable()->after('limite');
            $table->decimal('rango_maximo', 10, 2)->nullable()->after('rango_minimo');
            $table->text('mensaje')->nullable()->after('direccion');
        });
    }

    public function down(): void
    {
        Schema::table('alertas', function (Blueprint $table) {
            $table->dropColumn(['rango_minimo', 'rango_maximo', 'mensaje']);
        });
    }
};
