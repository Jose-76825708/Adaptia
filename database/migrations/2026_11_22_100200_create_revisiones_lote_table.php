<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('revisiones_lote', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lote_id')->constrained('lotes_produccion')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->boolean('apta');
            $table->unsignedInteger('plantas_perdidas')->default(0);
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('revisiones_lote');
    }
};
