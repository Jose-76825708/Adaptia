<?php

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
        if (DB::getDriverName() === 'sqlite') {
            $this->rebuildUsersTableForSqlite();

            return;
        }

        DB::statement("ALTER TABLE users MODIFY COLUMN rol ENUM('cliente', 'vendedor', 'administrador', 'almacenista') NOT NULL DEFAULT 'cliente'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->rebuildUsersTableForSqlite(false);

            return;
        }

        DB::statement("ALTER TABLE users MODIFY COLUMN rol ENUM('cliente', 'vendedor', 'administrador') NOT NULL DEFAULT 'cliente'");
    }

    private function rebuildUsersTableForSqlite(bool $withAlmacenista = true): void
    {
        $originalTable = 'users';
        $backupTable = 'users_backup_almacenista';

        DB::statement('DROP INDEX IF EXISTS users_email_unique');
        Schema::rename($originalTable, $backupTable);

        Schema::create($originalTable, function (Blueprint $table) use ($withAlmacenista) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('rol')->default('cliente');
            $table->rememberToken();
            $table->timestamps();
        });

        DB::statement('CREATE UNIQUE INDEX users_email_unique ON users (email)');

        $roles = $withAlmacenista
            ? "CASE WHEN rol IN ('cliente', 'vendedor', 'administrador', 'almacenista') THEN rol ELSE 'cliente' END"
            : "CASE WHEN rol IN ('cliente', 'vendedor', 'administrador') THEN rol ELSE 'cliente' END";

        DB::statement("INSERT INTO {$originalTable} (id, name, email, email_verified_at, password, rol, remember_token, created_at, updated_at)
            SELECT id, name, email, email_verified_at, password, {$roles}, remember_token, created_at, updated_at
            FROM {$backupTable}");

        Schema::dropIfExists($backupTable);
    }
};
