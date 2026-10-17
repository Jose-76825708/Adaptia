<?php

namespace Tests\Support;

use App\Models\Alerta;
use App\Models\LecturaSensor;
use App\Models\Planta;
use App\Models\PlantaVendida;
use App\Models\Sensor;
use App\Models\User;
use App\Models\Venta;

final class MonitoreoFixtures
{
    public static function cliente(): User
    {
        return User::factory()->create(['rol' => 'cliente']);
    }

    public static function vendedor(): User
    {
        return User::factory()->create(['rol' => 'vendedor']);
    }

    public static function venta(User $cliente, User $vendedor, Planta $planta, int $cantidad = 1): Venta
    {
        return Venta::create([
            'user_id' => $cliente->id,
            'vendedor_id' => $vendedor->id,
            'planta_id' => $planta->id,
            'cantidad' => $cantidad,
        ]);
    }

    public static function sensor(string $identificador, array $attributes = []): Sensor
    {
        return Sensor::create(array_merge([
            'identificador_fisico' => $identificador,
            'estado' => 'activo',
        ], $attributes));
    }

    public static function unidad(Venta $venta, User $cliente, ?Sensor $sensor = null): PlantaVendida
    {
        return PlantaVendida::create([
            'venta_id' => $venta->id,
            'user_id' => $cliente->id,
            'sensor_id' => $sensor?->id,
        ]);
    }

    public static function lectura(PlantaVendida $unidad, array $attributes = []): LecturaSensor
    {
        return LecturaSensor::create(array_merge([
            'planta_vendida_id' => $unidad->id,
            'humedad_suelo' => 40,
            'temperatura' => 23,
            'humedad_ambiental' => 50,
            'luz' => 1500,
            'fecha_hora' => now(),
        ], $attributes));
    }

    public static function alerta(PlantaVendida $unidad, array $attributes = []): Alerta
    {
        return Alerta::create(array_merge([
            'planta_vendida_id' => $unidad->id,
            'tipo' => 'riego',
        ], $attributes));
    }
}
