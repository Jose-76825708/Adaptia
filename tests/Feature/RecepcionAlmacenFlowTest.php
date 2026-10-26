<?php

use App\Models\DetalleRecepcion;
use App\Models\IncidenciaRecepcion;
use App\Models\Planta;
use App\Models\User;
use App\Services\RecepcionAprobacionService;
use App\Services\RecepcionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('aprobar una recepción conforme incrementa el stock real', function () {
    $usuario = User::factory()->create(['rol' => 'almacenista']);
    $planta = Planta::factory()->create([
        'nombre' => 'Monstera',
        'stock_actual' => 5,
        'stock_minimo' => 1,
    ]);

    $recepcion = app(RecepcionService::class)->crear([
        'usuario_almacen_id' => $usuario->id,
        'tipo' => 'compra',
        'estado' => 'pendiente',
        'fecha_recepcion' => now()->format('Y-m-d\TH:i'),
        'observaciones' => 'Compra de materiales de jardinería',
        'detalles' => [
            [
                'planta_id' => $planta->id,
                'cantidad_esperada' => 4,
                'cantidad_recibida' => 4,
                'unidad' => 'unidad',
            ],
        ],
    ]);

    $aprobada = app(RecepcionAprobacionService::class)->aprobar($recepcion);

    expect($aprobada->estado)->toBe('conforme')
        ->and($planta->fresh()->stock_actual)->toBe(9)
        ->and(DetalleRecepcion::first()->estado_item)->toBe('conforme');
});

test('rechazar una recepción con diferencia crea incidencia y no modifica el stock', function () {
    $usuario = User::factory()->create(['rol' => 'almacenista']);
    $planta = Planta::factory()->create([
        'nombre' => 'Ficus',
        'stock_actual' => 5,
        'stock_minimo' => 1,
    ]);

    $recepcion = app(RecepcionService::class)->crear([
        'usuario_almacen_id' => $usuario->id,
        'tipo' => 'entrega',
        'estado' => 'pendiente',
        'fecha_recepcion' => now()->format('Y-m-d\TH:i'),
        'observaciones' => 'Falta material en la entrega',
        'detalles' => [
            [
                'planta_id' => $planta->id,
                'cantidad_esperada' => 3,
                'cantidad_recibida' => 1,
                'unidad' => 'unidad',
            ],
        ],
    ]);

    $rechazada = app(RecepcionAprobacionService::class)->rechazar($recepcion, 'Faltan dos unidades');

    expect($rechazada->estado)->toBe('no_conforme')
        ->and($planta->fresh()->stock_actual)->toBe(5)
        ->and(IncidenciaRecepcion::count())->toBe(1)
        ->and(IncidenciaRecepcion::first()->motivo)->toBe('faltante')
        ->and(IncidenciaRecepcion::first()->diferencia)->toBe(-2);
});
