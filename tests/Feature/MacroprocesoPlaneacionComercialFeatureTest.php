<?php

use App\Models\CampanaComercial;
use App\Models\Planta;
use App\Models\SolicitudDisponibilidad;
use App\Models\User;
use App\Models\Venta;
use App\Services\PlaneacionComercialService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

function datosCampana(Planta $planta, int $cantidadMeta, ?string $fechaFin = null): array
{
    $inicio = today()->subDays(10);

    return [
        'objetivos' => 'Aumentar las ventas de plantas ornamentales.',
        'presupuesto' => 500,
        'fecha_inicio' => $inicio->toDateString(),
        'fecha_fin' => $fechaFin ?? today()->subDay()->toDateString(),
        'productos' => [
            [
                'planta_id' => $planta->id,
                'cantidad_meta' => $cantidadMeta,
                'descuento_porcentaje' => 10,
            ],
        ],
    ];
}

it('ejecuta disponibilidad, promoción, evaluación y cierre con los roles asignados', function () {
    $vendedor = User::factory()->create(['rol' => 'vendedor']);
    $admin = User::factory()->create(['rol' => 'administrador']);
    $cliente = User::factory()->create(['rol' => 'cliente']);
    $planta = Planta::factory()->create([
        'stock_actual' => 2,
        'stock_minimo' => 1,
        'precio' => 12.50,
    ]);
    $service = app(PlaneacionComercialService::class);

    $campana = $service->crear($vendedor, datosCampana($planta, 5));

    expect($campana->codigo)->toMatch('/^CAM-\d{4}-\d{5,}$/')
        ->and($campana->estado)->toBe('objetivos_definidos');

    $campana = $service->cambiarEstado($campana, 'demanda_analizada', $vendedor);
    $campana = $service->cambiarEstado($campana, 'productos_identificados', $vendedor);
    $campana = $service->cambiarEstado($campana, 'gestion_disponibilidad', $admin);
    $solicitud = SolicitudDisponibilidad::query()->firstOrFail();

    expect($solicitud->faltante)->toBe(3)
        ->and($solicitud->estado)->toBe('pendiente');

    expect(fn () => $service->gestionarSolicitud($solicitud, $admin, 'atendida'))
        ->toThrow(ValidationException::class);

    $planta->update(['stock_actual' => 5]);
    $service->gestionarSolicitud($solicitud, $admin, 'atendida', 'Reposición registrada en inventario.');
    $campana = $service->cambiarEstado($campana, 'productos_identificados', $admin);
    $campana = $service->cambiarEstado($campana, 'oferta_disponible', $admin);
    $campana = $service->cambiarEstado($campana, 'oferta_planificada', $vendedor);
    $campana = $service->cambiarEstado($campana, 'promocion_preparada', $vendedor);
    $campana = $service->cambiarEstado($campana, 'promocion_realizada', $vendedor);

    Venta::create([
        'user_id' => $cliente->id,
        'vendedor_id' => $vendedor->id,
        'planta_id' => $planta->id,
        'cantidad' => 3,
        'fecha' => today()->subDays(5)->setTime(12, 0),
    ]);

    $campana = $service->cambiarEstado($campana, 'resultados_evaluados', $admin);
    $producto = $campana->productos()->firstOrFail();
    expect($producto->cantidad_vendida_evaluada)->toBe(3)
        ->and((float) $producto->porcentaje_cumplimiento)->toBe(60.0)
        ->and((float) $producto->ingresos_estimados)->toBe(37.5)
        ->and($campana->evaluada_en)->not->toBeNull();

    $campana = $service->cambiarEstado($campana, 'cerrada', $admin, [
        'ajustes_siguiente_ciclo' => 'Aumentar la cantidad de plantas disponibles.',
    ]);

    expect($campana->estado)->toBe('cerrada')
        ->and($campana->ajustes_siguiente_ciclo)->toBe('Aumentar la cantidad de plantas disponibles.');
});

it('rechaza roles incorrectos y no planifica con disponibilidad insuficiente', function () {
    $vendedor = User::factory()->create(['rol' => 'vendedor']);
    $cliente = User::factory()->create(['rol' => 'cliente']);
    $admin = User::factory()->create(['rol' => 'administrador']);
    $planta = Planta::factory()->create(['stock_actual' => 1, 'stock_minimo' => 0]);
    $service = app(PlaneacionComercialService::class);
    $campana = $service->crear($vendedor, datosCampana($planta, 4));

    expect(fn () => $service->cambiarEstado($campana, 'demanda_analizada', $cliente))
        ->toThrow(\Illuminate\Auth\Access\AuthorizationException::class);

    $campana = $service->cambiarEstado($campana, 'demanda_analizada', $vendedor);
    $campana = $service->cambiarEstado($campana, 'productos_identificados', $vendedor);
    $campana = $service->cambiarEstado($campana, 'gestion_disponibilidad', $admin);

    expect(fn () => $service->cambiarEstado($campana, 'productos_identificados', $admin))
        ->toThrow(ValidationException::class);
});

it('permite ajustar o descartar productos pero exige disponibilidad antes de planificar', function () {
    $vendedor = User::factory()->create(['rol' => 'vendedor']);
    $admin = User::factory()->create(['rol' => 'administrador']);
    $planta = Planta::factory()->create(['stock_actual' => 2, 'stock_minimo' => 0]);
    $service = app(PlaneacionComercialService::class);
    $campana = $service->crear($vendedor, datosCampana($planta, 5));

    $campana = $service->cambiarEstado($campana, 'demanda_analizada', $vendedor);
    $campana = $service->cambiarEstado($campana, 'productos_identificados', $vendedor);
    $campana = $service->cambiarEstado($campana, 'gestion_disponibilidad', $admin);
    $solicitud = SolicitudDisponibilidad::query()->firstOrFail();
    $service->gestionarSolicitud($solicitud, $admin, 'no_lograda', 'No se pudo reponer.');
    $campana = $service->cambiarEstado($campana, 'oferta_por_ajustar', $admin);

    expect(fn () => $service->cambiarEstado($campana, 'oferta_planificada', $vendedor))
        ->toThrow(ValidationException::class);

    $campana = $service->ajustarOferta($campana, $vendedor, [[
        'planta_id' => $planta->id,
        'cantidad_meta' => 2,
        'descuento_porcentaje' => null,
    ]]);
    $campana = $service->cambiarEstado($campana, 'oferta_planificada', $vendedor);

    expect($campana->estado)->toBe('oferta_planificada')
        ->and($campana->productos()->where('incluida', true)->first()->cantidad_meta)->toBe(2);
});

it('no permite evaluar antes de terminar el periodo comercial', function () {
    $vendedor = User::factory()->create(['rol' => 'vendedor']);
    $admin = User::factory()->create(['rol' => 'administrador']);
    $planta = Planta::factory()->create(['stock_actual' => 5]);
    $service = app(PlaneacionComercialService::class);
    $datos = datosCampana($planta, 2, today()->addDay()->toDateString());
    $campana = CampanaComercial::factory()->create([
        'creador_id' => $vendedor->id,
        'fecha_inicio' => $datos['fecha_inicio'],
        'fecha_fin' => $datos['fecha_fin'],
        'estado' => 'promocion_realizada',
    ]);
    $campana->productos()->create($datos['productos'][0]);

    expect(fn () => $service->cambiarEstado($campana, 'resultados_evaluados', $admin))
        ->toThrow(ValidationException::class);
});
