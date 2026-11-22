<?php

use App\Services\GestionInventarioService;

it('declara solo las transiciones previstas por el macroproceso de inventario', function () {
    $servicio = new GestionInventarioService(
        new App\Services\SuficienciaInventarioService(),
        new App\Services\MovimientoGestionInventarioService(new App\Services\MovimientoInventarioService()),
    );

    expect($servicio->puedeTransicionar('existencias_revisadas', 'stock_suficiente'))->toBeTrue()
        ->and($servicio->puedeTransicionar('existencias_revisadas', 'stock_insuficiente'))->toBeTrue()
        ->and($servicio->puedeTransicionar('stock_insuficiente', 'reposicion_gestionada'))->toBeTrue()
        ->and($servicio->puedeTransicionar('material_recibido', 'en_incidencia'))->toBeTrue()
        ->and($servicio->puedeTransicionar('incidencia_gestionada', 'material_recibido'))->toBeTrue()
        ->and($servicio->puedeTransicionar('almacenado', 'cerrada'))->toBeTrue()
        ->and($servicio->puedeTransicionar('stock_suficiente', 'reposicion_gestionada'))->toBeFalse()
        ->and($servicio->puedeTransicionar('en_incidencia', 'recepcion_conforme'))->toBeFalse()
        ->and($servicio->puedeTransicionar('cerrada', 'existencias_revisadas'))->toBeFalse()
        ->and($servicio->puedeTransicionar('desconocido', 'cerrada'))->toBeFalse();
});

it('define transiciones y carriles para todos los estados', function () {
    expect(array_keys(GestionInventarioService::TRANSICIONES))->toEqual(GestionInventarioService::ESTADOS);

    foreach (GestionInventarioService::ESTADOS as $estado) {
        foreach (GestionInventarioService::TRANSICIONES[$estado] as $destino) {
            expect(GestionInventarioService::ESTADOS)->toContain($destino);
        }
        if ($estado !== 'cerrada') {
            expect(GestionInventarioService::ROLES_POR_CARRIL)->toHaveKey(GestionInventarioService::CARRIL_POR_ESTADO[$estado]);
        }
    }
});

it('solo usa los roles que ya existen en Adaptia', function () {
    $roles = array_unique(array_merge(...array_values(GestionInventarioService::ROLES_POR_CARRIL)));
    sort($roles);

    expect($roles)->toBe(['administrador', 'vendedor']);
});

it('tiene un mensaje de estado para cada estado', function () {
    expect(array_keys(GestionInventarioService::MENSAJES_ESTADO))->toEqual(GestionInventarioService::ESTADOS);
});
