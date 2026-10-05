<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lecturas_sensores', function (Blueprint $table) {
            $table->renameColumn('humedad', 'humedad_suelo');
            $table->decimal('humedad_ambiental', 8, 2)->after('temperatura');
            $table->decimal('luz', 10, 2)->after('humedad_ambiental');
        });
    }

    public function down(): void
    {
        Schema::table('lecturas_sensores', function (Blueprint $table) {
            $table->dropColumn(['humedad_ambiental', 'luz']);
            $table->renameColumn('humedad_suelo', 'humedad');
        });
    }
};
