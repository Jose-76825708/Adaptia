<?php

use App\Services\ProyectoPaisajismoService;
use App\Services\DisenoProyectoService;
use App\Services\InstalacionProyectoService;
use App\Services\EntregaProyectoService;
use Carbon\Carbon;

it('normaliza proyecto con coma decimal y fecha relativa', function () {
    $hoy=Carbon::parse('2026-11-20')->startOfDay();
    $service=new ProyectoPaisajismoService();
    $normalizado=$service->normalizar([
        'nombre'=>'  Jardín principal  ','ubicacion'=>'  Huancayo  ','descripcion'=>'  Diseñar área verde  ',
        'area_m2'=>'125,50','presupuesto_estimado'=>'15000,50','fecha_deseada'=>'21/11/2026',
    ]);

    expect($normalizado['nombre'])->toBe('Jardín principal')
        ->and($normalizado['area_m2'])->toBe('125.50')
        ->and($normalizado['presupuesto_estimado'])->toBe('15000.50')
        ->and($normalizado['fecha_deseada'])->toBe('2026-11-21');
});

it('valida proyecto con todos los errores juntos', function () {
    $hoy=Carbon::parse('2026-11-20')->startOfDay();
    $errores=(new ProyectoPaisajismoService())->validar([
        'nombre'=>'x','ubicacion'=>'x','descripcion'=>'x','area_m2'=>'0','presupuesto_estimado'=>'-1','fecha_deseada'=>'2019-01-01','extra'=>'x',
    ],$hoy);

    expect($errores)->toHaveKeys(['nombre','ubicacion','descripcion','area_m2','presupuesto_estimado','fecha_deseada'])
        ->and($errores)->not->toHaveKey('extra');
});

it('normaliza lineas acepta coma cantidad y elimina vacias', function () {
    $service=new DisenoProyectoService();
    $lineas=$service->normalizarLineas([
        ['planta_id'=>'1','cantidad'=>'2'],
        ['planta_id'=>null,'cantidad'=>null],
        ['planta_id'=>'2','cantidad'=>'3'],
    ]);

    expect($lineas)->toHaveCount(2);
});

it('validacion de lineas rechaza duplicados y cantidad inválida', function () {
    $errores=(new DisenoProyectoService())->validarLineas([
        ['planta_id'=>1,'cantidad'=>0],
        ['planta_id'=>1,'cantidad'=>2],
    ],[]);

    expect($errores)->not->toBeEmpty();
});

it('cotizacion suma plantas mano obra y aviso presupuesto', function () {
    $lineas=[
        ['planta_id'=>1,'nombre'=>'Rosas','cantidad'=>2,'precio_unitario'=>10,'subtotal'=>20],
        ['planta_id'=>2,'nombre'=>'Pasto','cantidad'=>3,'precio_unitario'=>5,'subtotal'=>15],
    ];
    $resultado=(new DisenoProyectoService())->cotizar($lineas,100,20);

    expect($resultado['costo_plantas'])->toBe(35.0)
        ->and($resultado['costo_mano_obra'])->toBe(1500.0)
        ->and($resultado['total'])->toBe(1535.0)
        ->and($resultado['excede_presupuesto'])->toBeTrue();
});

it('instalacion detecta suficientes y faltantes', function () {
    $resultado=(new InstalacionProyectoService())->verificarDisponibilidad([
        ['planta_id'=>1,'nombre'=>'Rosas','requerido'=>5,'disponible'=>10],
        ['planta_id'=>2,'nombre'=>'Pasto','requerido'=>8,'disponible'=>3],
    ]);

    expect($resultado['estado'])->toBe('insuficiente')
        ->and($resultado['lineas'][1]['faltante'])->toBe(5);
});

it('entrega valida resumen recomendaciones y 100 por ciento', function () {
    $service=new EntregaProyectoService();

    expect($service->validarEntrega('Resumen válido para el cliente','Recomendaciones válidas para el cuidado',100))->toBeEmpty()
        ->and($service->validarEntrega('x','x',99))->not->toBeEmpty();
});

it('entrega valida satisfaccion entre uno y cinco', function () {
    $service=new EntregaProyectoService();
    expect($service->validarSatisfaccion(5,'Excelente'))->toBeEmpty()
        ->and($service->validarSatisfaccion(0,''))->not->toBeEmpty()
        ->and($service->validarSatisfaccion(6,'Demasiado'))->not->toBeEmpty();
});
