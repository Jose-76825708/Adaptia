<?php

use App\Exceptions\AlertasCuidadoPlantaExistentesException;
use App\Models\Alerta;
use App\Models\Planta;
use App\Models\PlantaVendida;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

test('impide revertir la migración mientras existan alertas de cuidado de planta', function () {
    $cliente = User::factory()->create(['rol' => 'cliente']);
    $vendedor = User::factory()->create(['rol' => 'vendedor']);
    $venta = Venta::create([
        'user_id' => $cliente->id,
        'vendedor_id' => $vendedor->id,
        'planta_id' => Planta::factory()->create()->id,
        'cantidad' => 1,
    ]);
    $unidad = PlantaVendida::create([
        'venta_id' => $venta->id,
        'user_id' => $cliente->id,
    ]);
    Alerta::create([
        'planta_vendida_id' => $unidad->id,
        'tipo' => 'cuidado_planta',
    ]);
    $migration = include database_path(
        'migrations/2026_10_04_170000_expand_alertas_for_iot_episodes.php'
    );

    expect(fn () => $migration->down())
        ->toThrow(AlertasCuidadoPlantaExistentesException::class);
});

test('revierte los campos de episodio cuando no existen alertas de cuidado de planta', function () {
    $migration = include database_path(
        'migrations/2026_10_04_170000_expand_alertas_for_iot_episodes.php'
    );

    $migration->down();

    expect(Schema::hasColumn('alertas', 'variable'))->toBeFalse()
        ->and(Schema::hasColumn('alertas', 'valor_medido'))->toBeFalse()
        ->and(Schema::hasColumn('alertas', 'resuelta_en'))->toBeFalse();
});
