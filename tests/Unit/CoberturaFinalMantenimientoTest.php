<?php

use App\Services\DecisionCierreService;
use App\Services\EstadoTareaChecklistService;
use App\Services\RequisicionMantenimientoService;
use App\Services\VerificadorDisponibilidadService;
use App\Services\GeneradorPlanMantenimientoService;
use App\Services\EjecucionMantenimientoService;
use App\Services\CasoMantenimientoService;

it('cierre permite baja con cero pendientes', fn () => expect((new DecisionCierreService())->decidir('baja', 0, 0)['puede_cerrar'])->toBeTrue());
it('cierre permite media con cero pendientes', fn () => expect((new DecisionCierreService())->decidir('media', 0, 0)['puede_cerrar'])->toBeTrue());
it('cierre rechaza alta', fn () => expect((new DecisionCierreService())->decidir('alta', 0, 0)['puede_cerrar'])->toBeFalse());

it('checklist sin items queda pendiente', fn () => expect((new EstadoTareaChecklistService())->calcularEstado([]))->toBe('pendiente'));
it('checklist completo queda completada', fn () => expect((new EstadoTareaChecklistService())->calcularEstado([true, true, true]))->toBe('completada'));
it('checklist con falso queda en curso', fn () => expect((new EstadoTareaChecklistService())->calcularEstado([true, false]))->toBe('en_curso'));

it('requisicion rechaza veredicto ejecutable', function () {
    expect(fn () => (new RequisicionMantenimientoService())->generarDesdeVeredicto(['estado' => 'ejecutable', 'faltan_materiales' => []]))
        ->toThrow(InvalidArgumentException::class);
});

it('veredicto devuelve ejecutable sin faltantes', function () {
    $resultado = (new VerificadorDisponibilidadService())->calcularVeredicto([], []);
    expect($resultado['estado'])->toBe('ejecutable')->and($resultado['motivo'])->toBeNull();
});

it('generador rechaza tipo desconocido', fn () => expect(fn () => (new GeneradorPlanMantenimientoService())->derivarTareas('otro', 'baja', []))->toThrow(InvalidArgumentException::class));

it('ejecucion rechaza tipo desconocido', fn () => expect(fn () => (new EjecucionMantenimientoService(new CasoMantenimientoService()))->validarTipo('otro'))->toThrow(InvalidArgumentException::class));
