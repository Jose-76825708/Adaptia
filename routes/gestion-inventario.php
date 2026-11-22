<?php

use App\Http\Controllers\GestionInventarioController;
use Illuminate\Support\Facades\Route;

// Macroproceso: Gestión de inventario (plantas e insumos).
// Los Services validan el carril (rol) y el estado de cada actividad.
Route::middleware(['auth', 'role:administrador,vendedor'])
    ->prefix('gestion-inventario')
    ->name('gestion-inventario.')
    ->controller(GestionInventarioController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/crear', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{gestion}', 'show')->whereNumber('gestion')->name('show');

        Route::post('/{gestion}/verificacion', 'verificar')->whereNumber('gestion')->name('verificar');
        Route::post('/{gestion}/reposicion', 'reposicion')->whereNumber('gestion')->name('reposicion');
        Route::post('/{gestion}/compra', 'compra')->whereNumber('gestion')->name('compra');
        Route::post('/{gestion}/recepcion', 'recepcion')->whereNumber('gestion')->name('recepcion');
        Route::post('/{gestion}/recepcion/verificar', 'verificarRecepcion')->whereNumber('gestion')->name('recepcion.verificar');
        Route::post('/{gestion}/incidencia', 'gestionarIncidencia')->whereNumber('gestion')->name('incidencia');
        Route::post('/{gestion}/ingreso', 'ingreso')->whereNumber('gestion')->name('ingreso');
        Route::post('/{gestion}/almacenamiento', 'almacenar')->whereNumber('gestion')->name('almacenar');
        Route::post('/{gestion}/movimiento', 'movimiento')->whereNumber('gestion')->name('movimiento');
    });

// Catálogo de insumos (mismo estilo que Plantas). Alimenta la gestión de inventario.
Route::middleware(['auth', 'role:administrador,vendedor'])->group(function () {
    Route::resource('insumos', App\Http\Controllers\InsumoController::class)
        ->except(['show'])
        ->parameters(['insumos' => 'id'])
        ->whereNumber('id');
});
