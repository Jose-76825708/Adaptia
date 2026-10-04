<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sensores', function (Blueprint $table) {
            $table->string('token_hash', 64)->nullable()->unique();
        });
    }

    public function down(): void
    {
        Schema::table('sensores', function (Blueprint $table) {
            $table->dropUnique(['token_hash']);
            $table->dropColumn('token_hash');
        });
    }
};
