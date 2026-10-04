<?php



use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PlantaController;
use App\Http\Controllers\TipoPlantaController;
use App\Http\Controllers\MovimientoInventarioController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\SensorController;
use App\Http\Controllers\CatalogoClienteController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\PlantaVendidaController;

Route::get('/home', [HomeController::class, 'index'])->name('home');

// Rutas de Autenticación
Route::controller(AuthController::class)->group(function () {
    Route::get('/login', 'showLogin')->name('login');
    Route::post('/login', 'login');
    Route::post('/logout', 'logout')->name('logout');
    Route::get('/register', 'showRegister')->name('register');
    Route::post('/register', 'register');
});

// CRUDs disponibles para administración y vendedores.
Route::middleware(['auth', 'role:administrador,vendedor'])->group(function () {
    Route::resource('plantas', PlantaController::class);
    Route::resource('tipoPlantas', TipoPlantaController::class)->except(['show']);
    Route::resource('movimientos-inventario', MovimientoInventarioController::class);
    Route::resource('sensores', SensorController::class)->except(['show']);
});

// Consulta y registro de ventas exclusivos para vendedores.
Route::middleware(['auth', 'role:vendedor'])->group(function () {
    Route::get('/ventas', [VentaController::class, 'index'])->name('ventas.index');
    Route::get('/ventas/create', [VentaController::class, 'create'])->name('ventas.create');
    Route::post('/ventas', [VentaController::class, 'store'])->name('ventas.store');
    Route::get('/plantas-vendidas', [PlantaVendidaController::class, 'index'])
        ->name('plantas-vendidas.index');
    Route::post('/plantas-vendidas/{unidad}/sensor', [PlantaVendidaController::class, 'asignarSensor'])
        ->name('plantas-vendidas.asignar-sensor');
});

// Catálogo de solo lectura para clientes.
Route::middleware(['auth', 'role:cliente'])->group(function () {
    Route::get('/catalogo-plantas', [CatalogoClienteController::class, 'index'])
        ->name('catalogo.plantas.index');
});

// Funciones del perfil e historial disponibles solo para clientes.
Route::middleware(['auth', 'role:cliente'])->group(function () {
    Route::get('/perfil/edit', [PerfilController::class, 'edit'])->name('perfil.edit');
    Route::post('/perfil/update', [PerfilController::class, 'update'])->name('perfil.update');
    Route::get('/historial-alertas', [HomeController::class, 'historialAlertas'])
        ->name('historial-alertas');
});