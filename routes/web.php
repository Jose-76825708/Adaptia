<?php

use App\Http\Controllers\AbastecimientoMantenimientoController;
use App\Http\Controllers\AsistenteController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CasoMantenimientoController;
use App\Http\Controllers\CatalogoClienteController;
use App\Http\Controllers\CierreMantenimientoController;
use App\Http\Controllers\DiagnosticoCasoController;
use App\Http\Controllers\EjecucionMantenimientoController;
use App\Http\Controllers\GestionInventarioController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InsumoController;
use App\Http\Controllers\MovimientoInventarioController;
use App\Http\Controllers\PaisajismoDisenoController;
use App\Http\Controllers\PaisajismoEntregaController;
use App\Http\Controllers\PaisajismoInstalacionController;
use App\Http\Controllers\PaisajismoProyectoController;
use App\Http\Controllers\PaisajismoSolicitudController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\PersonalController;
use App\Http\Controllers\PlaneacionComercialController;
use App\Http\Controllers\PlanMantenimientoController;
use App\Http\Controllers\PlantaController;
use App\Http\Controllers\PlantaVendidaController;
use App\Http\Controllers\SensorController;
use App\Http\Controllers\SolicitudMantenimientoController;
use App\Http\Controllers\TipoPlantaController;
use App\Http\Controllers\VentaController;
use Illuminate\Support\Facades\Route;

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
    Route::get('/mantenimiento/solicitudes/crear', [SolicitudMantenimientoController::class, 'create'])
        ->name('mantenimiento.solicitudes.create');
    Route::post('/mantenimiento/solicitudes', [SolicitudMantenimientoController::class, 'store'])
        ->name('mantenimiento.solicitudes.store');
    Route::get('/mantenimiento/solicitudes', [SolicitudMantenimientoController::class, 'index'])
        ->name('mantenimiento.solicitudes.index');
});

Route::middleware(['auth', 'role:administrador'])->group(function () {
    Route::get('/mantenimiento/casos', [CasoMantenimientoController::class, 'index'])
        ->name('mantenimiento.casos.index');
    Route::get('/mantenimiento/casos/{caso}', [CasoMantenimientoController::class, 'show'])
        ->name('mantenimiento.casos.show');
    Route::get('/mantenimiento/casos/{caso}/etapa/{etapa}', [CasoMantenimientoController::class, 'etapa'])
        ->name('mantenimiento.casos.show.etapa');
});

Route::middleware(['auth', 'role:administrador'])->group(function () {
    Route::post('/mantenimiento/casos/{caso}/diagnostico/notas', [DiagnosticoCasoController::class, 'registrarNota'])
        ->name('mantenimiento.casos.diagnostico.notas');
    Route::post('/mantenimiento/casos/{caso}/diagnostico/fotos', [DiagnosticoCasoController::class, 'registrarFoto'])
        ->name('mantenimiento.casos.diagnostico.fotos');
    Route::post('/mantenimiento/casos/{caso}/diagnostico/evaluar', [DiagnosticoCasoController::class, 'evaluar'])
        ->name('mantenimiento.casos.diagnostico.evaluar');
    Route::post('/mantenimiento/casos/{caso}/diagnostico/guardar', [DiagnosticoCasoController::class, 'guardar'])
        ->name('mantenimiento.casos.diagnostico.guardar');
    Route::post('/mantenimiento/casos/{caso}/diagnostico/fotos/{evidencia}/eliminar', [DiagnosticoCasoController::class, 'eliminarFoto'])
        ->name('mantenimiento.casos.diagnostico.fotos.eliminar');

    Route::post('/mantenimiento/casos/{caso}/plan/generar', [PlanMantenimientoController::class, 'generar'])
        ->name('mantenimiento.casos.plan.generar');

    Route::post('/mantenimiento/casos/{caso}/requisicion/generar', [AbastecimientoMantenimientoController::class, 'generar'])
        ->name('mantenimiento.casos.requisicion.generar');
    Route::post('/mantenimiento/casos/{caso}/requisicion/recibir', [AbastecimientoMantenimientoController::class, 'recibir'])
        ->name('mantenimiento.casos.requisicion.recibir');
    Route::post('/mantenimiento/casos/{caso}/abastecimiento/preparar', [AbastecimientoMantenimientoController::class, 'preparar'])
        ->name('mantenimiento.casos.abastecimiento.preparar');

    Route::post('/mantenimiento/casos/{caso}/ejecucion/evento', [EjecucionMantenimientoController::class, 'registrarEvento'])
        ->name('mantenimiento.casos.ejecucion.evento');
    Route::post('/mantenimiento/casos/{caso}/ejecucion/checklist/{item}', [EjecucionMantenimientoController::class, 'actualizarChecklist'])
        ->name('mantenimiento.casos.ejecucion.checklist');
    Route::post('/mantenimiento/casos/{caso}/ejecucion/incidencias/{evento}/resolver', [EjecucionMantenimientoController::class, 'resolverIncidencia'])
        ->name('mantenimiento.casos.ejecucion.incidencia.resolver');

    Route::post('/mantenimiento/casos/{caso}/cierre/cerrar', [CierreMantenimientoController::class, 'cerrar'])
        ->name('mantenimiento.casos.cierre.cerrar');
    Route::post('/mantenimiento/casos/{caso}/cierre/reabrir', [CierreMantenimientoController::class, 'reabrir'])
        ->name('mantenimiento.casos.cierre.reabrir');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/mantenimiento/casos/{caso}/informe', [CierreMantenimientoController::class, 'informe'])
        ->name('mantenimiento.casos.informe');
});

Route::middleware(['auth', 'role:administrador,vendedor'])
    ->prefix('planeacion-comercial')
    ->name('planeacion-comercial.')
    ->controller(PlaneacionComercialController::class)
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
    Route::get('/asistente', [AsistenteController::class, 'index'])
        ->name('asistente.index');
    Route::post('/asistente/chat', [AsistenteController::class, 'chat'])
        ->middleware('throttle:10,1')
        ->name('asistente.chat');
    Route::post('/asistente/nueva-conversacion', [AsistenteController::class, 'nuevaConversacion'])
        ->middleware('throttle:10,1')
        ->name('asistente.nueva-conversacion');
});
Route::middleware(['auth', 'role:administrador'])->group(function () {
    Route::post('/paisajismo/proyectos/{proyecto}/diseno/propuesta', [PaisajismoDisenoController::class, 'propuesta'])->name('paisajismo.proyectos.diseno.propuesta');
    Route::post('/paisajismo/proyectos/{proyecto}/diseno/decision', [PaisajismoDisenoController::class, 'decision'])->name('paisajismo.proyectos.diseno.decision');
    Route::post('/paisajismo/proyectos/{proyecto}/instalacion/iniciar', [PaisajismoInstalacionController::class, 'iniciar'])->name('paisajismo.proyectos.instalacion.iniciar');
    Route::post('/paisajismo/proyectos/{proyecto}/instalacion/avances', [PaisajismoInstalacionController::class, 'avance'])->name('paisajismo.proyectos.instalacion.avances');
    Route::post('/paisajismo/proyectos/{proyecto}/entrega', [PaisajismoEntregaController::class, 'entrega'])->name('paisajismo.proyectos.entrega');
    Route::get('/paisajismo/proyectos', [PaisajismoProyectoController::class, 'index'])
        ->name('paisajismo.proyectos.index');
    Route::get('/paisajismo/proyectos/{proyecto}', [PaisajismoProyectoController::class, 'show'])
        ->name('paisajismo.proyectos.show');
});

Route::middleware(['auth', 'role:cliente'])->group(function () {
    Route::get('/paisajismo/solicitudes', [PaisajismoSolicitudController::class, 'index'])
        ->name('paisajismo.solicitudes.index');
    Route::get('/paisajismo/solicitudes/crear', [PaisajismoSolicitudController::class, 'create'])
        ->name('paisajismo.solicitudes.create');
    Route::post('/paisajismo/solicitudes', [PaisajismoSolicitudController::class, 'store'])
        ->name('paisajismo.solicitudes.store');
    Route::get('/paisajismo/solicitudes/{proyecto}', [PaisajismoSolicitudController::class, 'show'])
        ->whereNumber('proyecto')
        ->name('paisajismo.solicitudes.show');
    Route::get('/paisajismo/solicitudes/{proyecto}/propuesta', [PaisajismoDisenoController::class, 'propuestaCliente'])
        ->name('paisajismo.solicitudes.propuesta');
    Route::get('/paisajismo/solicitudes/{proyecto}/resumen', [PaisajismoEntregaController::class, 'resumen'])->name('paisajismo.solicitudes.resumen');
    Route::post('/paisajismo/solicitudes/{proyecto}/satisfaccion', [PaisajismoEntregaController::class, 'satisfaccion'])->name('paisajismo.solicitudes.satisfaccion');
    Route::post('/paisajismo/solicitudes/{proyecto}/mantenimiento', [PaisajismoEntregaController::class, 'mantenimiento'])->name('paisajismo.solicitudes.mantenimiento');
});

// Macroproceso: Gestión de inventario
Route::middleware(['auth', 'role:administrador,vendedor'])->group(function () {
    Route::get('/gestion-inventario', [GestionInventarioController::class, 'index'])->name('gestion-inventario.index');
    Route::get('/gestion-inventario/crear', [GestionInventarioController::class, 'create'])->name('gestion-inventario.create');
    Route::post('/gestion-inventario', [GestionInventarioController::class, 'store'])->name('gestion-inventario.store');
    Route::get('/gestion-inventario/{gestion}', [GestionInventarioController::class, 'show'])->whereNumber('gestion')->name('gestion-inventario.show');
    Route::post('/gestion-inventario/{gestion}/verificacion', [GestionInventarioController::class, 'verificar'])->whereNumber('gestion')->name('gestion-inventario.verificar');
    Route::post('/gestion-inventario/{gestion}/reposicion', [GestionInventarioController::class, 'reposicion'])->whereNumber('gestion')->name('gestion-inventario.reposicion');
    Route::post('/gestion-inventario/{gestion}/compra', [GestionInventarioController::class, 'compra'])->whereNumber('gestion')->name('gestion-inventario.compra');
    Route::post('/gestion-inventario/{gestion}/recepcion', [GestionInventarioController::class, 'recepcion'])->whereNumber('gestion')->name('gestion-inventario.recepcion');
    Route::post('/gestion-inventario/{gestion}/recepcion/verificar', [GestionInventarioController::class, 'verificarRecepcion'])->whereNumber('gestion')->name('gestion-inventario.recepcion.verificar');
    Route::post('/gestion-inventario/{gestion}/incidencia', [GestionInventarioController::class, 'gestionarIncidencia'])->whereNumber('gestion')->name('gestion-inventario.incidencia');
    Route::post('/gestion-inventario/{gestion}/ingreso', [GestionInventarioController::class, 'ingreso'])->whereNumber('gestion')->name('gestion-inventario.ingreso');
    Route::post('/gestion-inventario/{gestion}/almacenamiento', [GestionInventarioController::class, 'almacenar'])->whereNumber('gestion')->name('gestion-inventario.almacenar');
    Route::post('/gestion-inventario/{gestion}/movimiento', [GestionInventarioController::class, 'movimiento'])->whereNumber('gestion')->name('gestion-inventario.movimiento');

    Route::resource('insumos', InsumoController::class)
        ->except(['show'])
        ->parameters(['insumos' => 'id'])
        ->whereNumber('id');
});
require __DIR__.'/produccion-vegetal.php';
