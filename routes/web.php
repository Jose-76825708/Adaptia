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
use App\Http\Controllers\PersonalController;

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

// Gestión de credenciales IoT exclusiva para administradores.
Route::middleware(['auth', 'role:administrador'])->group(function () {
    Route::post('/sensores/{id}/credencial', [SensorController::class, 'generarCredencial'])
        ->name('sensores.credencial.generar');
});

// Creación de cuentas de personal exclusiva para administradores.
Route::middleware(['auth', 'role:administrador'])->group(function () {
    Route::get('/personal/create', [PersonalController::class, 'create'])
        ->name('personal.create');
    Route::post('/personal', [PersonalController::class, 'store'])
        ->name('personal.store');
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
    Route::get('/cliente/monitoreo/inicio', [HomeController::class, 'datosMonitoreoInicio'])
        ->name('cliente.monitoreo.inicio');
    Route::get('/cliente/monitoreo/historial', [HomeController::class, 'datosMonitoreoHistorial'])
        ->name('cliente.monitoreo.historial');
});

Route::middleware(['auth', 'role:cliente'])->group(function () {
    Route::get('/mantenimiento/solicitudes/crear', [\App\Http\Controllers\SolicitudMantenimientoController::class, 'create'])
        ->name('mantenimiento.solicitudes.create');
    Route::post('/mantenimiento/solicitudes', [\App\Http\Controllers\SolicitudMantenimientoController::class, 'store'])
        ->name('mantenimiento.solicitudes.store');
    Route::get('/mantenimiento/solicitudes', [\App\Http\Controllers\SolicitudMantenimientoController::class, 'index'])
        ->name('mantenimiento.solicitudes.index');
});

Route::middleware(['auth', 'role:administrador'])->group(function () {
    Route::get('/mantenimiento/casos', [\App\Http\Controllers\CasoMantenimientoController::class, 'index'])
        ->name('mantenimiento.casos.index');
    Route::get('/mantenimiento/casos/{caso}', [\App\Http\Controllers\CasoMantenimientoController::class, 'show'])
        ->name('mantenimiento.casos.show');
    Route::get('/mantenimiento/casos/{caso}/etapa/{etapa}', [\App\Http\Controllers\CasoMantenimientoController::class, 'etapa'])
        ->name('mantenimiento.casos.show.etapa');
});

Route::middleware(['auth', 'role:administrador'])->group(function () {
    Route::post('/mantenimiento/casos/{caso}/diagnostico/notas', [\App\Http\Controllers\DiagnosticoCasoController::class, 'registrarNota'])
        ->name('mantenimiento.casos.diagnostico.notas');
    Route::post('/mantenimiento/casos/{caso}/diagnostico/fotos', [\App\Http\Controllers\DiagnosticoCasoController::class, 'registrarFoto'])
        ->name('mantenimiento.casos.diagnostico.fotos');
    Route::post('/mantenimiento/casos/{caso}/diagnostico/evaluar', [\App\Http\Controllers\DiagnosticoCasoController::class, 'evaluar'])
        ->name('mantenimiento.casos.diagnostico.evaluar');
    Route::post('/mantenimiento/casos/{caso}/diagnostico/guardar', [\App\Http\Controllers\DiagnosticoCasoController::class, 'guardar'])
        ->name('mantenimiento.casos.diagnostico.guardar');
    Route::post('/mantenimiento/casos/{caso}/diagnostico/fotos/{evidencia}/eliminar', [\App\Http\Controllers\DiagnosticoCasoController::class, 'eliminarFoto'])
        ->name('mantenimiento.casos.diagnostico.fotos.eliminar');

    Route::post('/mantenimiento/casos/{caso}/plan/generar', [\App\Http\Controllers\PlanMantenimientoController::class, 'generar'])
        ->name('mantenimiento.casos.plan.generar');

    Route::post('/mantenimiento/casos/{caso}/requisicion/generar', [\App\Http\Controllers\AbastecimientoMantenimientoController::class, 'generar'])
        ->name('mantenimiento.casos.requisicion.generar');
    Route::post('/mantenimiento/casos/{caso}/requisicion/recibir', [\App\Http\Controllers\AbastecimientoMantenimientoController::class, 'recibir'])
        ->name('mantenimiento.casos.requisicion.recibir');
    Route::post('/mantenimiento/casos/{caso}/abastecimiento/preparar', [\App\Http\Controllers\AbastecimientoMantenimientoController::class, 'preparar'])
        ->name('mantenimiento.casos.abastecimiento.preparar');

    Route::post('/mantenimiento/casos/{caso}/ejecucion/evento', [\App\Http\Controllers\EjecucionMantenimientoController::class, 'registrarEvento'])
        ->name('mantenimiento.casos.ejecucion.evento');
    Route::post('/mantenimiento/casos/{caso}/ejecucion/checklist/{item}', [\App\Http\Controllers\EjecucionMantenimientoController::class, 'actualizarChecklist'])
        ->name('mantenimiento.casos.ejecucion.checklist');
    Route::post('/mantenimiento/casos/{caso}/ejecucion/incidencias/{evento}/resolver', [\App\Http\Controllers\EjecucionMantenimientoController::class, 'resolverIncidencia'])
        ->name('mantenimiento.casos.ejecucion.incidencia.resolver');

    Route::post('/mantenimiento/casos/{caso}/cierre/cerrar', [\App\Http\Controllers\CierreMantenimientoController::class, 'cerrar'])
        ->name('mantenimiento.casos.cierre.cerrar');
    Route::post('/mantenimiento/casos/{caso}/cierre/reabrir', [\App\Http\Controllers\CierreMantenimientoController::class, 'reabrir'])
        ->name('mantenimiento.casos.cierre.reabrir');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/mantenimiento/casos/{caso}/informe', [\App\Http\Controllers\CierreMantenimientoController::class, 'informe'])
        ->name('mantenimiento.casos.informe');
});

Route::middleware(['auth', 'role:administrador,vendedor'])
    ->prefix('planeacion-comercial')
    ->name('planeacion-comercial.')
    ->controller(\App\Http\Controllers\PlaneacionComercialController::class)
    ->group(function () {
        Route::get('/campanas', 'index')->name('campanas.index');
        Route::get('/campanas/crear', 'create')->middleware('role:vendedor')->name('campanas.create');
        Route::post('/campanas', 'store')->middleware('role:vendedor')->name('campanas.store');
        Route::get('/campanas/{campana}', 'show')->name('campanas.show');
        Route::post('/campanas/{campana}/transicion', 'transicionar')->name('campanas.transicionar');
        Route::post('/campanas/{campana}/oferta', 'ajustarOferta')->name('campanas.oferta.ajustar');
        Route::post('/solicitudes/{solicitud}/disponibilidad', 'gestionarDisponibilidad')
            ->name('solicitudes.disponibilidad');
        Route::get('/demanda', 'demanda')->name('demanda');
    });

Route::middleware(['auth', 'role:vendedor,administrador'])->group(function () {
    Route::get('/asistente', [\App\Http\Controllers\AsistenteController::class, 'index'])
        ->name('asistente.index');
    Route::post('/asistente/chat', [\App\Http\Controllers\AsistenteController::class, 'chat'])
        ->middleware('throttle:10,1')
        ->name('asistente.chat');
    Route::post('/asistente/nueva-conversacion', [\App\Http\Controllers\AsistenteController::class, 'nuevaConversacion'])
        ->middleware('throttle:10,1')
        ->name('asistente.nueva-conversacion');
});
