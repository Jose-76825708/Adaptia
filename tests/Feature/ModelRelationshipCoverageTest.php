<?php

use App\Models\Planta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MonitoreoFixtures;

uses(RefreshDatabase::class);

test('lecturas y alertas resuelven la planta vendida relacionada', function () {
    $cliente = MonitoreoFixtures::cliente();
    $vendedor = MonitoreoFixtures::vendedor();
    $venta = MonitoreoFixtures::venta($cliente, $vendedor, Planta::factory()->create());
    $unidad = MonitoreoFixtures::unidad($venta, $cliente);
    $lectura = MonitoreoFixtures::lectura($unidad, [
        'humedad_suelo' => 40,
        'temperatura' => 23,
        'humedad_ambiental' => 50,
        'luz' => 1500,
    ]);
    $alerta = MonitoreoFixtures::alerta($unidad, [
        'mensaje' => 'Revisar humedad.',
    ]);

    expect($lectura->plantaVendida->is($unidad))->toBeTrue()
        ->and($alerta->plantaVendida->is($unidad))->toBeTrue();
});
