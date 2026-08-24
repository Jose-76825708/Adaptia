<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;
   use App\Http\Controllers\PlantaController;
use App\Http\Controllers\TipoPlantaController;

Route::get('/home', [HomeController::class, 'index']);

Route::resource('plantas',PlantaController::class);

Route::resource('tipoPlantas',TipoPlantaController::class);