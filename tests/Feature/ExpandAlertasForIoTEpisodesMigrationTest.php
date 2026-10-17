<?php

use App\Exceptions\AlertasCuidadoPlantaExistentesException;
use App\Models\Planta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\Support\MonitoreoFixtures;

uses(RefreshDatabase::class);

test('impide revertir la migración mientras existan alertas de cuidado de planta', function () {
    $cliente = MonitoreoFixtures::cliente();
    $vendedor = MonitoreoFixtures::vendedor();
    $venta = MonitoreoFixtures::venta($cliente, $vendedor, Planta::factory()->create());
    $unidad = MonitoreoFixtures::unidad($venta, $cliente);
    MonitoreoFixtures::alerta($unidad, [
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
