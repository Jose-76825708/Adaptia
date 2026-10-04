<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plantas', function (Blueprint $table) {
            $table->decimal('humedad_suelo_min', 8, 2)->nullable();
            $table->decimal('humedad_suelo_max', 8, 2)->nullable();
            $table->decimal('temperatura_min', 8, 2)->nullable();
            $table->decimal('temperatura_max', 8, 2)->nullable();
            $table->decimal('humedad_ambiental_min', 8, 2)->nullable();
            $table->decimal('humedad_ambiental_max', 8, 2)->nullable();
            $table->decimal('luz_min', 8, 2)->nullable();
            $table->decimal('luz_max', 8, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('plantas', function (Blueprint $table) {
            $table->dropColumn([
                'humedad_suelo_min',
                'humedad_suelo_max',
                'temperatura_min',
                'temperatura_max',
                'humedad_ambiental_min',
                'humedad_ambiental_max',
                'luz_min',
                'luz_max',
            ]);
        });
    }
};
