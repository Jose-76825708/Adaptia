<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RecomendationController;
use Illuminate\Support\Facades\Route;

Route::get('/home', [HomeController::class, 'index']);

Route::get('/auth', [AuthController::class, 'index']);

Route::get('/recomendation', [RecomendationController::class, 'index']);

