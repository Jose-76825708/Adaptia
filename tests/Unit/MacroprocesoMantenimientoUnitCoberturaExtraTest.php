<?php

use App\Services\DecisionCierreService;
use App\Services\EstadoTareaChecklistService;
use App\Services\EjecucionMantenimientoService;
use App\Services\CasoMantenimientoService;
use App\Services\EvaluadorEstadoAreaService;
use App\Services\GeneradorPlanMantenimientoService;
use App\Services\OrdenadorTareasPlanService;
use App\Services\RecepcionMaterialService;
use App\Services\RecomendacionCompraService;
use App\Services\RequisicionMantenimientoService;
use App\Services\VerificadorDisponibilidadService;
use App\Services\ComparacionEjecucionService;
use Carbon\Carbon;

it('decision cierre permite ambos severidades permitidas', function () {
    $service = new DecisionCierreService();
    expect($service->decidir('baja', 0, 0)['puede_cerrar'])->toBeTrue()
        ->and($service->decidir('media', 0, 0)['puede_cerrar'])->toBeTrue();
});

it('decision cierre lista severidad tareas e incidencias', function () {
    $resultado = (new DecisionCierreService())->decidir('alta', 2, 1);
    expect($resultado['puede_cerrar'])->toBeFalse()
        ->and($resultado['correcciones'])->toHaveCount(3);
});

it('estado tarea completo vacio en curso y pendiente', function () {
    $service = new EstadoTareaChecklistService();
    expect($service->calcularEstado([]))->toBe('pendiente')
        ->and($service->calcularEstado([true]))->toBe('completada')
        ->and($service->calcularEstado([true, false]))->toBe('en_curso');
});

it('ejecucion valida los cinco tipos', function () {
    $service = new EjecucionMantenimientoService(new CasoMantenimientoService());
    foreach (['inicio','avance','incidencia','nota','reapertura'] as $tipo) {
        expect($service->validarTipo($tipo))->toBeTrue();
    }
    expect(fn () => $service->validarTipo('cancelacion'))->toThrow(InvalidArgumentException::class);
});

it('evaluador devuelve fallback sin hallazgos reales', function () {
    $resultado = (new EvaluadorEstadoAreaService())->evaluar(['sensores'=>[],'rangos'=>[],'alertas'=>[]], EvaluadorEstadoAreaService::ESTANDARES);
    expect($resultado['severidad_global'])->toBe('baja')
        ->and($resultado['hallazgos'])->toHaveCount(1)
        ->and($resultado['hallazgos'][0]['tipo'])->toBe('texto');
});

it('evaluador calcula reincidencia media y alta', function () {
    $service = new EvaluadorEstadoAreaService();
    $media = $service->evaluar(['sensores'=>[],'rangos'=>[],'alertas'=>['luz'=>2]], EvaluadorEstadoAreaService::ESTANDARES);
    $alta = $service->evaluar(['sensores'=>[],'rangos'=>[],'alertas'=>['luz'=>4]], EvaluadorEstadoAreaService::ESTANDARES);
    expect(collect($media['hallazgos'])->contains(fn ($h) => $h['severidad']==='media'))->toBeTrue()
        ->and(collect($alta['hallazgos'])->contains(fn ($h) => $h['severidad']==='alta'))->toBeTrue();
});

it('generador cubre todas las plantillas base', function () {
    $service = new GeneradorPlanMantenimientoService();
    foreach (array_keys(GeneradorPlanMantenimientoService::PLANTILLAS) as $tipo) {
        expect($service->derivarTareas($tipo,'baja',[]))->not->toBeEmpty();
    }
});

it('generador eleva prioridad y agrega seguimiento', function () {
    $tareas = (new GeneradorPlanMantenimientoService())->derivarTareas('riego','alta',['humedad_suelo','humedad_suelo','luz']);
    expect($tareas[0]['prioridad'])->toBe('critica')
        ->and(end($tareas)['titulo'])->toBe('Seguimiento de variables afectadas')
        ->and(end($tareas)['checklist'])->toHaveCount(2);
});

it('ordenador respeta dependencia antes que prioridad', function () {
    $resultado = (new OrdenadorTareasPlanService())->ordenar([
        ['codigo'=>'T2','prioridad'=>'critica','criticidad'=>'alta','depende_de_codigo'=>'T1','id'=>2],
        ['codigo'=>'T1','prioridad'=>'baja','criticidad'=>'baja','depende_de_codigo'=>null,'id'=>1],
    ]);
    expect(array_column($resultado,'codigo'))->toBe(['T1','T2']);
});

it('veredicto ejecutable sin recursos faltantes', function () {
    $resultado = (new VerificadorDisponibilidadService())->calcularVeredicto([],[]);
    expect($resultado['estado'])->toBe('ejecutable')->and($resultado['motivo'])->toBeNull();
});

it('veredicto faltan solo materiales', function () {
    $resultado = (new VerificadorDisponibilidadService())->calcularVeredicto([],[
        ['codigo'=>'M-1','nombre'=>'Abono','requerido'=>5,'disponible'=>2,'faltante'=>3,'suficiente'=>false],
    ]);
    expect($resultado['estado'])->toBe('faltan')->and($resultado['faltan_herramientas'])->toBeEmpty();
});

it('veredicto bloqueado prevalece sobre materiales', function () {
    $resultado = (new VerificadorDisponibilidadService())->calcularVeredicto([
        ['codigo'=>'H-1','nombre'=>'Tijeras','requerido'=>2,'disponible'=>1,'faltante'=>1,'suficiente'=>false],
    ],[['codigo'=>'M-1','nombre'=>'Abono','requerido'=>5,'disponible'=>2,'faltante'=>3,'suficiente'=>false]]);
    expect($resultado['estado'])->toBe('bloqueado')->and($resultado['faltan_herramientas'])->toHaveCount(1);
});

it('recomendacion ordena prioridad y codigo', function () {
    $resultado = (new RecomendacionCompraService())->generar([
        ['codigo'=>'M-2','faltante'=>1,'prioridad'=>'media'],
        ['codigo'=>'M-1','faltante'=>1,'prioridad'=>'critica'],
        ['codigo'=>'M-3','faltante'=>1,'prioridad'=>'baja'],
    ]);
    expect(array_column($resultado,'codigo'))->toBe(['M-1','M-2','M-3']);
});

it('requisicion genera solo faltantes positivos', function () {
    $resultado = (new RequisicionMantenimientoService())->generarDesdeFaltantes([
        ['codigo'=>'M-1','faltante'=>2],['codigo'=>'M-2','faltante'=>0],['codigo'=>'M-3','faltante'=>-1],
    ]);
    expect($resultado)->toHaveCount(1)->and($resultado[0]['codigo'])->toBe('M-1');
});

it('requisicion rechaza bloqueado y ejecutable', function () {
    $service = new RequisicionMantenimientoService();
    expect(fn () => $service->generarDesdeVeredicto(['estado'=>'bloqueado','faltan_materiales'=>[],'faltan_herramientas'=>[['codigo'=>'H-1']]]))->toThrow(InvalidArgumentException::class)
        ->and(fn () => $service->generarDesdeVeredicto(['estado'=>'ejecutable','faltan_materiales'=>[],'faltan_herramientas'=>[]]))->toThrow(InvalidArgumentException::class);
});

it('recepcion rechaza cero negativo y exceso', function () {
    $service = new RecepcionMaterialService();
    expect(fn () => $service->calcularRecepcion(5,-1,0,10))->toThrow(InvalidArgumentException::class)
        ->and(fn () => $service->calcularRecepcion(5,0,0,10))->toThrow(InvalidArgumentException::class)
        ->and(fn () => $service->calcularRecepcion(5,6,0,10))->toThrow(InvalidArgumentException::class);
});

it('recepcion parcial y completa calculan stock', function () {
    $service = new RecepcionMaterialService();
    $parcial = $service->calcularRecepcion(5,2,0,10);
    $completa = $service->calcularRecepcion(5,5,0,10);
    expect($parcial['estado_item'])->toBe('parcial')->and($parcial['stock_despues'])->toBe(12.0)
        ->and($completa['estado_item'])->toBe('recibida')->and($completa['stock_despues'])->toBe(15.0);
});

it('comparacion separa completadas pendientes y retrasadas', function () {
    $hoy = Carbon::parse('2026-11-18')->startOfDay();
    $resultado = (new ComparacionEjecucionService())->comparar([
        ['id'=>1,'estado'=>'completada','fecha_estimada'=>'2026-11-10'],
        ['id'=>2,'estado'=>'pendiente','fecha_estimada'=>'2026-11-17'],
        ['id'=>3,'estado'=>'en_curso','fecha_estimada'=>null],
    ],[], $hoy);
    expect($resultado['completadas'])->toHaveCount(1)->and($resultado['pendientes'])->toHaveCount(2)->and($resultado['retrasadas'])->toHaveCount(1);
});

it('comparacion devuelve incidencia abierta y riesgo', function () {
    $resultado = (new ComparacionEjecucionService())->comparar([], [['tipo'=>'incidencia','resuelta_en'=>null]], Carbon::parse('2026-11-18'));
    expect($resultado['incidencias_abiertas'])->toHaveCount(1)->and($resultado['riesgos'])->not->toBeEmpty();
});
