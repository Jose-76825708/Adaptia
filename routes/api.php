<?php

use App\Http\Controllers\LecturaSensorController;
use Illuminate\Support\Facades\Route;

Route::post('/lecturas', [LecturaSensorController::class, 'store'])
    ->middleware('sensor.token')
    ->name('api.lecturas.store');
