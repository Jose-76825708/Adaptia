<?php

use App\Services\EvaluadorEstadoAreaService;
use App\Services\GeneradorPlanMantenimientoService;
use App\Services\OrdenadorTareasPlanService;
use App\Services\VerificadorDisponibilidadService;
use App\Services\RecomendacionCompraService;
use App\Services\ComparacionEjecucionService;
use App\Services\DecisionCierreService;
use App\Services\RecepcionMaterialService;
use Carbon\Carbon;

it('severidad baja suma uno por hallazgo', function () {
    $resultado=(new EvaluadorEstadoAreaService())->evaluar(['sensores'=>[],'rangos'=>[],'alertas'=>[]],EvaluadorEstadoAreaService::ESTANDARES);
    expect($resultado['severidad_global'])->toBe('baja');
});

it('plantillas cubren seguimiento por variables', function () {
    $tareas=(new GeneradorPlanMantenimientoService())->derivarTareas('general','baja',['luz','temperatura']);
    expect(end($tareas)['titulo'])->toBe('Seguimiento de variables afectadas');
});

it('ordenador acepta tareas sin dependencias conocidas', function () {
    $resultado=(new OrdenadorTareasPlanService())->ordenar([['codigo'=>'T9','prioridad'=>'baja','criticidad'=>'baja','depende_de_codigo'=>'T8','id'=>9]]);
    expect($resultado)->toHaveCount(1);
});

it('veredicto bloqueado prevalece sobre faltantes materiales', function () {
    $resultado=(new VerificadorDisponibilidadService())->calcularVeredicto([
        ['codigo'=>'H-1','suficiente'=>false,'faltante'=>1],
    ],[['codigo'=>'M-1','suficiente'=>false,'faltante'=>2]]);
    expect($resultado['estado'])->toBe('bloqueado');
});

it('recomendacion usa desempate por codigo', function () {
    $resultado=(new RecomendacionCompraService())->generar([
        ['codigo'=>'M-2','prioridad'=>'alta'],['codigo'=>'M-1','prioridad'=>'alta'],
    ]);
    expect(array_column($resultado,'codigo'))->toBe(['M-1','M-2']);
});

it('comparacion devuelve riesgos con incidencia abierta', function () {
    $resultado=(new ComparacionEjecucionService())->comparar([],[['tipo'=>'incidencia','resuelta_en'=>null]],Carbon::parse('2026-11-18'));
    expect($resultado['incidencias_abiertas'])->toHaveCount(1)->and($resultado['riesgos'])->not->toBeEmpty();
});

it('decision cierre combina todas las correcciones', function () {
    $resultado=(new DecisionCierreService())->decidir('alta',2,3);
    expect($resultado['puede_cerrar'])->toBeFalse()->and($resultado['correcciones'])->toHaveCount(3);
});

it('recepcion acepta completado exacto', function () {
    $resultado=(new RecepcionMaterialService())->calcularRecepcion(5,5,0,10);
    expect($resultado['estado_item'])->toBe('recibida')->and($resultado['stock_despues'])->toBe(15.0);
});
