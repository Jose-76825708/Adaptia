<?php

use App\Http\Controllers\ProduccionVegetalController;
use Illuminate\Support\Facades\Route;

// Macroproceso: Producción vegetal (lotes). Solo administrador.
Route::middleware(['auth', 'role:administrador'])
    ->prefix('produccion-vegetal/lotes')
    ->name('produccion-vegetal.lotes.')
    ->controller(ProduccionVegetalController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/crear', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{lote}', 'show')->whereNumber('lote')->name('show');
        Route::post('/{lote}/avanzar', 'avanzar')->whereNumber('lote')->name('avanzar');
        Route::post('/{lote}/cuidados', 'registrarCuidado')->whereNumber('lote')->name('cuidados');
        Route::post('/{lote}/revisiones', 'registrarRevision')->whereNumber('lote')->name('revisiones');
        Route::post('/{lote}/clasificar', 'clasificar')->whereNumber('lote')->name('clasificar');
        Route::post('/{lote}/cerrar', 'cerrar')->whereNumber('lote')->name('cerrar');
    });
