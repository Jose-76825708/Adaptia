<?php

use App\Exceptions\StockInsuficienteException;
use App\Models\MovimientoInventario;
use App\Models\Planta;
use App\Models\TipoPlanta;
use App\Models\User;
use App\Services\MovimientoInventarioService;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('registra entrada de inventario y aumenta stock', function () {
    $tipo = TipoPlanta::factory()->create();
    $planta = Planta::factory()->create(['tipo_planta_id'=>$tipo->id,'stock_actual'=>5,'stock_minimo'=>2,'precio'=>20]);
    $user = User::factory()->create(['rol'=>'administrador']);

    $movimiento = (new MovimientoInventarioService())->createMovimiento([
        'planta_id'=>$planta->id,'user_id'=>$user->id,'tipo'=>'entrada','cantidad'=>3,'motivo'=>'Compra',
    ]);

    expect($movimiento)->toBeInstanceOf(MovimientoInventario::class)
        ->and($planta->fresh()->stock_actual)->toBe(8);
});

it('rechaza salida sin stock suficiente sin cambiar stock', function () {
    $tipo = TipoPlanta::factory()->create();
    $planta = Planta::factory()->create(['tipo_planta_id'=>$tipo->id,'stock_actual'=>2,'stock_minimo'=>1,'precio'=>20]);
    $user = User::factory()->create(['rol'=>'administrador']);

    expect(fn () => (new MovimientoInventarioService())->createMovimiento([
        'planta_id'=>$planta->id,'user_id'=>$user->id,'tipo'=>'salida','cantidad'=>5,'motivo'=>'Instalación',
    ]))->toThrow(StockInsuficienteException::class)
        ->and($planta->fresh()->stock_actual)->toBe(2);
});

it('registra salida suficiente y desconta stock', function () {
    $tipo = TipoPlanta::factory()->create();
    $planta = Planta::factory()->create(['tipo_planta_id'=>$tipo->id,'stock_actual'=>10,'stock_minimo'=>2,'precio'=>20]);
    $user = User::factory()->create(['rol'=>'administrador']);

    (new MovimientoInventarioService())->createMovimiento([
        'planta_id'=>$planta->id,'user_id'=>$user->id,'tipo'=>'salida','cantidad'=>4,'motivo'=>'Instalación',
    ]);

    expect($planta->fresh()->stock_actual)->toBe(6);
});

it('actualiza movimiento y ajusta stock', function () {
    $tipo = TipoPlanta::factory()->create();
    $planta = Planta::factory()->create(['tipo_planta_id'=>$tipo->id,'stock_actual'=>10,'stock_minimo'=>1,'precio'=>20]);
    $user = User::factory()->create(['rol'=>'administrador']);
    $service = new MovimientoInventarioService();

    $movimiento = $service->createMovimiento([
        'planta_id'=>$planta->id,'user_id'=>$user->id,'tipo'=>'entrada','cantidad'=>5,'motivo'=>'Inicial',
    ]);
    $actualizado = $service->updateMovimiento((string)$movimiento->id,[
        'tipo'=>'entrada','cantidad'=>8,
    ]);

    expect($actualizado->cantidad)->toBe(8)
        ->and($planta->fresh()->stock_actual)->toBe(18);
});

it('elimina movimiento y revierte stock', function () {
    $tipo = TipoPlanta::factory()->create();
    $planta = Planta::factory()->create(['tipo_planta_id'=>$tipo->id,'stock_actual'=>10,'stock_minimo'=>1,'precio'=>20]);
    $user = User::factory()->create(['rol'=>'administrador']);
    $service = new MovimientoInventarioService();

    $movimiento = $service->createMovimiento([
        'planta_id'=>$planta->id,'user_id'=>$user->id,'tipo'=>'entrada','cantidad'=>5,'motivo'=>'Inicial',
    ]);

    expect($service->deleteMovimiento((string)$movimiento->id))->toBeTrue()
        ->and($planta->fresh()->stock_actual)->toBe(10);
});
