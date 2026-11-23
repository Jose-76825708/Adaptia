<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

test('la migración deja la columna canónica sin cambios', function () {
    expect(Schema::hasColumn('perfiles_cliente', 'tamano_adulto'))->toBeTrue()
        ->and(Schema::hasColumn('plantas', 'tamano_adulto'))->toBeTrue();

    $migration = include database_path('migrations/2026_11_23_100000_normalize_tamano_adulto_columns.php');
    $migration->up();

    expect(Schema::hasColumn('perfiles_cliente', 'tamano_adulto'))->toBeTrue()
        ->and(Schema::hasColumn('plantas', 'tamano_adulto'))->toBeTrue();
});

test('la migración renombra una variante existente y conserva sus registros', function () {
    Schema::table('perfiles_cliente', function ($table) {
        $table->renameColumn('tamano_adulto', 'tamaño_adulto');
    });

    $user = User::factory()->create(['rol' => 'cliente']);

    DB::table('perfiles_cliente')->insert([
        'user_id' => $user->id,
        'tamaño_adulto' => 'grande',
        'toxicidad' => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $migration = include database_path('migrations/2026_11_23_100000_normalize_tamano_adulto_columns.php');
    $migration->up();

    expect(Schema::hasColumn('perfiles_cliente', 'tamano_adulto'))->toBeTrue()
        ->and(Schema::hasColumn('perfiles_cliente', 'tamaño_adulto'))->toBeFalse()
        ->and(DB::table('perfiles_cliente')->value('tamano_adulto'))->toBe('grande');
});
