<?php


use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PlantaController;
use App\Http\Controllers\TipoPlantaController;
use App\Http\Controllers\MovimientoInventarioController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PerfilController;

Route::get('/home', [HomeController::class, 'index'])->name('home');

// Rutas de Autenticación
Route::controller(AuthController::class)->group(function () {
    Route::get('/login', 'showLogin')->name('login');
    Route::post('/login', 'login');
    Route::post('/logout', 'logout')->name('logout');
    Route::get('/register', 'showRegister')->name('register');
    Route::post('/register', 'register');
});

// Rutas Protegidas (Requieren estar autenticado)
Route::middleware(['auth'])->group(function () {
    Route::resource('plantas', PlantaController::class);
    Route::resource('tipoPlantas', TipoPlantaController::class)->except(['show']);
    Route::resource('movimientos-inventario', MovimientoInventarioController::class);
    Route::get('/perfil/edit', [PerfilController::class, 'edit'])->name('perfil.edit');
    Route::post('/perfil/update', [PerfilController::class, 'update'])->name('perfil.update');
});




