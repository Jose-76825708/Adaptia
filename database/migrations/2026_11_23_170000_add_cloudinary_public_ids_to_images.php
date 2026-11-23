<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plantas', function (Blueprint $table) {
            $table->string('imagen_public_id')->nullable()->after('imagen');
            $table->string('imagen_ruta_local')->nullable()->after('imagen_public_id');
        });

        Schema::table('evidencias_caso', function (Blueprint $table) {
            $table->string('public_id')->nullable()->after('ruta');
            $table->string('ruta_local')->nullable()->after('public_id');
        });
    }

    public function down(): void
    {
        Schema::table('evidencias_caso', function (Blueprint $table) {
            $table->dropColumn(['public_id', 'ruta_local']);
        });

        Schema::table('plantas', function (Blueprint $table) {
            $table->dropColumn(['imagen_public_id', 'imagen_ruta_local']);
        });
    }
};
