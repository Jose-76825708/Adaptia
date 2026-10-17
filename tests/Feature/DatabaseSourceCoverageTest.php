<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

test('las migraciones pueden revertirse en orden inverso sin dejar tablas principales', function () {
    $migrationCount = DB::table('migrations')->count();

    expect($migrationCount)->toBeGreaterThan(0);

    $exitCode = Artisan::call('migrate:rollback', [
        '--step' => $migrationCount,
        '--force' => true,
    ]);

    expect($exitCode)->toBe(0)
        ->and(Schema::hasTable('users'))->toBeFalse()
        ->and(Schema::hasTable('plantas'))->toBeFalse()
        ->and(Schema::hasTable('alertas'))->toBeFalse();
});

test('DatabaseSeeder crea el usuario de prueba documentado', function () {
    $this->seed(DatabaseSeeder::class);

    expect(DB::table('users')->where('email', 'test@example.com')->value('name'))
        ->toBe('Test User');
});

test('la factory puede crear usuarios sin verificar el correo', function () {
    $user = User::factory()->unverified()->create();

    expect($user->email_verified_at)->toBeNull();
});

test('el comando inspire de consola está registrado y termina correctamente', function () {
    $this->artisan('inspire')->assertExitCode(0);
});
