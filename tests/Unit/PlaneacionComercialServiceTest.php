<?php

use App\Services\PlaneacionComercialService;

it('declara solo las transiciones previstas para el macroproceso comercial', function () {
    $service = app(PlaneacionComercialService::class);

    expect($service->puedeTransicionar('objetivos_definidos', 'demanda_analizada'))->toBeTrue()
        ->and($service->puedeTransicionar('productos_identificados', 'gestion_disponibilidad'))->toBeTrue()
        ->and($service->puedeTransicionar('gestion_disponibilidad', 'oferta_por_ajustar'))->toBeTrue()
        ->and($service->puedeTransicionar('oferta_por_ajustar', 'oferta_planificada'))->toBeTrue()
        ->and($service->puedeTransicionar('cerrada', 'objetivos_definidos'))->toBeFalse()
        ->and($service->puedeTransicionar('estado_desconocido', 'cerrada'))->toBeFalse();
});
