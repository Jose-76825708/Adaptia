<?php

use App\Models\GestionInventario;
use App\Models\MovimientoInventario;
use App\Models\Planta;
use App\Models\RecursoCatalogo;
use App\Models\User;
use App\Services\GestionInventarioService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

function gi_servicio(): GestionInventarioService
{
    return app(GestionInventarioService::class);
}

function gi_usuario(string $rol): User
{
    return User::factory()->create(['rol' => $rol]);
}

function gi_planta(int $stock, int $minimo = 1): Planta
{
    return Planta::factory()->create(['stock_actual' => $stock, 'stock_minimo' => $minimo]);
}

function gi_insumo(float $stock): RecursoCatalogo
{
    return RecursoCatalogo::factory()->create(['tipo' => 'material', 'unidad' => 'kg', 'stock_actual' => $stock]);
}

it('cierra con salida cuando el stock de la planta es suficiente', function () {
    $vendedor = gi_usuario('vendedor');
    $planta = gi_planta(10);

    $gestion = gi_servicio()->crear($vendedor, ['tipo_item' => 'planta', 'planta_id' => $planta->id, 'cantidad_requerida' => 4, 'motivo' => 'Pedido de cliente']);
    expect($gestion->estado)->toBe('existencias_revisadas')
        ->and($gestion->codigo)->toBe(sprintf('INV-%s-%05d', now()->format('Y'), $gestion->id));

    $gestion = gi_servicio()->verificarEstado($gestion, $vendedor, ['cantidad_fisica' => 10, 'cantidad_buen_estado' => 9]);
    expect($gestion->estado)->toBe('stock_suficiente')
        ->and((float) $gestion->cantidad_disponible)->toBe(9.0);

    $gestion = gi_servicio()->registrarMovimiento($gestion, $vendedor);

    expect($gestion->estado)->toBe('cerrada')
        ->and($gestion->resultado)->toBe('stock_suficiente')
        ->and($gestion->cerrada_en)->not->toBeNull()
        ->and($planta->fresh()->stock_actual)->toBe(6)
        ->and(MovimientoInventario::query()->where('planta_id', $planta->id)->where('tipo', 'salida')->where('cantidad', 4)->exists())->toBeTrue()
        ->and($gestion->movimientos)->toHaveCount(1)
        ->and((float) $gestion->movimientos->first()->stock_despues)->toBe(6.0);
});

it('completa la reposición de un insumo con una incidencia de faltante y nueva recepción', function () {
    $vendedor = gi_usuario('vendedor');
    $admin = gi_usuario('administrador');
    $insumo = gi_insumo(2);
    $servicio = gi_servicio();

    $gestion = $servicio->crear($vendedor, ['tipo_item' => 'insumo', 'recurso_id' => $insumo->id, 'cantidad_requerida' => 10]);
    $gestion = $servicio->verificarEstado($gestion, $vendedor, ['cantidad_fisica' => 2, 'cantidad_buen_estado' => 2]);
    expect($gestion->estado)->toBe('stock_insuficiente')
        ->and((float) $gestion->faltante)->toBe(8.0);

    $gestion = $servicio->gestionarReposicion($gestion, $admin, ['cantidad_reposicion' => 8, 'proveedor' => 'Agroinsumos Huancayo', 'costo_estimado' => 120]);
    $gestion = $servicio->registrarCompra($gestion, $admin, ['numero_orden' => 'OC-001', 'fecha_compra' => today()->toDateString(), 'costo_total' => 118.5]);
    expect($gestion->estado)->toBe('compra_realizada');

    $gestion = $servicio->registrarRecepcion($gestion, $vendedor, ['cantidad_recibida' => 6]);
    $gestion = $servicio->verificarRecepcion($gestion, $vendedor, ['conforme' => false, 'tipo_incidencia' => 'faltante', 'descripcion' => 'El proveedor envió menos sacos.']);
    expect($gestion->estado)->toBe('en_incidencia')
        ->and((float) $gestion->incidencias->first()->cantidad_afectada)->toBe(2.0)
        ->and((float) $gestion->recepciones->first()->cantidad_aceptada)->toBe(6.0);

    $gestion = $servicio->gestionarIncidencia($gestion, $admin, ['resolucion' => 'El proveedor enviará los 2 kg restantes.']);
    expect($gestion->estado)->toBe('incidencia_gestionada')
        ->and($gestion->incidencias->first()->estado)->toBe('gestionada');

    $gestion = $servicio->registrarRecepcion($gestion, $vendedor, ['cantidad_recibida' => 2]);
    $gestion = $servicio->verificarRecepcion($gestion, $vendedor, ['conforme' => true]);
    expect($gestion->estado)->toBe('recepcion_conforme');

    $gestion = $servicio->registrarIngreso($gestion, $vendedor, ['documento_ingreso' => 'GR-778']);
    expect($gestion->estado)->toBe('ingreso_registrado')
        ->and((float) $gestion->cantidad_ingresada)->toBe(8.0);

    $gestion = $servicio->almacenar($gestion, $vendedor, ['ubicacion' => 'Almacén 1 - Estante B']);
    expect($gestion->estado)->toBe('almacenado');
    expect((float) $insumo->fresh()->stock_actual)->toBe(2.0);

    $gestion = $servicio->registrarMovimiento($gestion, $vendedor);

    expect($gestion->estado)->toBe('cerrada')
        ->and($gestion->resultado)->toBe('reposicion_completada')
        ->and((float) $insumo->fresh()->stock_actual)->toBe(10.0)
        ->and($gestion->movimientos)->toHaveCount(1)
        ->and($gestion->movimientos->first()->tipo)->toBe('entrada')
        ->and((float) $gestion->movimientos->first()->stock_antes)->toBe(2.0)
        ->and($gestion->recepciones)->toHaveCount(2);
});

it('repone plantas con unidades dañadas y suma al stock solo lo aceptado', function () {
    $vendedor = gi_usuario('vendedor');
    $admin = gi_usuario('administrador');
    $planta = gi_planta(1);
    $servicio = gi_servicio();

    $gestion = $servicio->crear($vendedor, ['tipo_item' => 'planta', 'planta_id' => $planta->id, 'cantidad_requerida' => 6]);
    $gestion = $servicio->verificarEstado($gestion, $vendedor, ['cantidad_fisica' => 1, 'cantidad_buen_estado' => 1]);
    $gestion = $servicio->gestionarReposicion($gestion, $admin, ['cantidad_reposicion' => 5, 'proveedor' => 'Vivero Mantaro']);
    $gestion = $servicio->registrarCompra($gestion, $admin, ['numero_orden' => 'OC-010', 'fecha_compra' => today()->toDateString()]);
    $gestion = $servicio->registrarRecepcion($gestion, $vendedor, ['cantidad_recibida' => 5]);
    $gestion = $servicio->verificarRecepcion($gestion, $vendedor, ['conforme' => false, 'tipo_incidencia' => 'danado', 'cantidad_afectada' => 2, 'descripcion' => 'Dos plantas con hojas quebradas.']);
    expect((float) $gestion->recepciones->first()->cantidad_aceptada)->toBe(3.0);

    $gestion = $servicio->gestionarIncidencia($gestion, $admin, ['resolucion' => 'Cambio de 2 plantas.']);
    $gestion = $servicio->registrarRecepcion($gestion, $vendedor, ['cantidad_recibida' => 2]);
    $gestion = $servicio->verificarRecepcion($gestion, $vendedor, ['conforme' => true]);
    $gestion = $servicio->registrarIngreso($gestion, $vendedor, []);
    $gestion = $servicio->almacenar($gestion, $vendedor, ['ubicacion' => 'Mesa 3']);
    $gestion = $servicio->registrarMovimiento($gestion, $vendedor);

    expect($gestion->estado)->toBe('cerrada')
        ->and($planta->fresh()->stock_actual)->toBe(6)
        ->and(MovimientoInventario::query()->where('planta_id', $planta->id)->where('tipo', 'entrada')->value('cantidad'))->toBe(5);
});

it('respeta los carriles: compras es solo del administrador y almacén admite a ambos roles', function () {
    $vendedor = gi_usuario('vendedor');
    $admin = gi_usuario('administrador');
    $cliente = gi_usuario('cliente');
    $planta = gi_planta(0);
    $servicio = gi_servicio();

    expect(fn () => $servicio->crear($cliente, ['tipo_item' => 'planta', 'planta_id' => $planta->id, 'cantidad_requerida' => 1]))
        ->toThrow(AuthorizationException::class);

    $gestion = $servicio->crear($admin, ['tipo_item' => 'planta', 'planta_id' => $planta->id, 'cantidad_requerida' => 3]);
    $gestion = $servicio->verificarEstado($gestion, $vendedor, ['cantidad_fisica' => 0, 'cantidad_buen_estado' => 0]);

    expect($gestion->estado)->toBe('stock_insuficiente')
        ->and(fn () => $servicio->gestionarReposicion($gestion, $vendedor, ['cantidad_reposicion' => 3, 'proveedor' => 'X']))
        ->toThrow(AuthorizationException::class)
        ->and(fn () => $servicio->verificarEstado($gestion, $cliente, []))
        ->toThrow(AuthorizationException::class);
});

it('rechaza actividades fuera de su estado y gestiones ya cerradas', function () {
    $vendedor = gi_usuario('vendedor');
    $gestion = GestionInventario::factory()->create(['estado' => 'existencias_revisadas']);

    expect(fn () => gi_servicio()->registrarMovimiento($gestion, $vendedor))->toThrow(InvalidArgumentException::class)
        ->and(fn () => gi_servicio()->almacenar($gestion, $vendedor, ['ubicacion' => 'X']))->toThrow(InvalidArgumentException::class);

    $cerrada = GestionInventario::factory()->create(['estado' => 'cerrada']);
    expect(fn () => gi_servicio()->verificarEstado($cerrada, $vendedor, []))->toThrow(InvalidArgumentException::class, 'cerrada');
});

it('valida los datos de cada actividad', function () {
    $vendedor = gi_usuario('vendedor');
    $admin = gi_usuario('administrador');
    $planta = gi_planta(10);
    $servicio = gi_servicio();

    expect(fn () => $servicio->crear($vendedor, ['tipo_item' => 'planta', 'cantidad_requerida' => 2]))->toThrow(ValidationException::class)
        ->and(fn () => $servicio->crear($vendedor, ['tipo_item' => 'planta', 'planta_id' => $planta->id, 'cantidad_requerida' => 2.5]))->toThrow(ValidationException::class)
        ->and(fn () => $servicio->crear($vendedor, ['tipo_item' => 'planta', 'planta_id' => $planta->id, 'cantidad_requerida' => 0]))->toThrow(ValidationException::class)
        ->and(fn () => $servicio->crear($vendedor, ['tipo_item' => 'insumo', 'recurso_id' => RecursoCatalogo::factory()->create(['tipo' => 'herramienta'])->id, 'cantidad_requerida' => 1]))->toThrow(ValidationException::class);

    $gestion = $servicio->crear($vendedor, ['tipo_item' => 'planta', 'planta_id' => $planta->id, 'cantidad_requerida' => 20]);
    expect(fn () => $servicio->verificarEstado($gestion, $vendedor, ['cantidad_fisica' => 5, 'cantidad_buen_estado' => 6]))->toThrow(InvalidArgumentException::class)
        ->and(fn () => $servicio->verificarEstado($gestion, $vendedor, ['cantidad_fisica' => 5.5, 'cantidad_buen_estado' => 5]))->toThrow(ValidationException::class);

    $gestion = $servicio->verificarEstado($gestion, $vendedor, ['cantidad_fisica' => 10, 'cantidad_buen_estado' => 10]);
    expect((float) $gestion->faltante)->toBe(10.0)
        ->and(fn () => $servicio->gestionarReposicion($gestion, $admin, ['cantidad_reposicion' => 9, 'proveedor' => 'X']))->toThrow(ValidationException::class)
        ->and(fn () => $servicio->gestionarReposicion($gestion, $admin, ['cantidad_reposicion' => 10]))->toThrow(ValidationException::class);

    $gestion = $servicio->gestionarReposicion($gestion, $admin, ['cantidad_reposicion' => 10, 'proveedor' => 'Vivero']);
    expect(fn () => $servicio->registrarCompra($gestion, $admin, ['numero_orden' => 'OC', 'fecha_compra' => today()->addDay()->toDateString()]))->toThrow(ValidationException::class)
        ->and(fn () => $servicio->registrarCompra($gestion, $admin, ['fecha_compra' => today()->toDateString()]))->toThrow(ValidationException::class);
});

it('no permite marcar conforme una recepción que no coincide y exige describir la incidencia', function () {
    $vendedor = gi_usuario('vendedor');
    $admin = gi_usuario('administrador');
    $planta = gi_planta(0);
    $servicio = gi_servicio();

    $gestion = $servicio->crear($vendedor, ['tipo_item' => 'planta', 'planta_id' => $planta->id, 'cantidad_requerida' => 4]);
    $gestion = $servicio->verificarEstado($gestion, $vendedor, ['cantidad_fisica' => 0, 'cantidad_buen_estado' => 0]);
    $gestion = $servicio->gestionarReposicion($gestion, $admin, ['cantidad_reposicion' => 4, 'proveedor' => 'Vivero']);
    $gestion = $servicio->registrarCompra($gestion, $admin, ['numero_orden' => 'OC-2', 'fecha_compra' => today()->toDateString()]);
    $gestion = $servicio->registrarRecepcion($gestion, $vendedor, ['cantidad_recibida' => 3]);

    expect(fn () => $servicio->verificarRecepcion($gestion, $vendedor, ['conforme' => true]))->toThrow(InvalidArgumentException::class)
        ->and(fn () => $servicio->verificarRecepcion($gestion, $vendedor, ['conforme' => false, 'tipo_incidencia' => 'faltante']))->toThrow(ValidationException::class)
        ->and(fn () => $servicio->verificarRecepcion($gestion, $vendedor, ['conforme' => false, 'descripcion' => 'Falta una']))->toThrow(ValidationException::class)
        ->and(fn () => $servicio->verificarRecepcion($gestion, $vendedor, ['conforme' => false, 'tipo_incidencia' => 'exceso', 'descripcion' => 'x']))->toThrow(InvalidArgumentException::class);

    expect($gestion->fresh()->estado)->toBe('material_recibido')
        ->and($gestion->recepciones()->whereNull('conforme')->count())->toBe(1);
});

it('informa cuando el stock cambió y ya no alcanza para registrar la salida', function () {
    $vendedor = gi_usuario('vendedor');
    $planta = gi_planta(5);
    $servicio = gi_servicio();

    $gestion = $servicio->crear($vendedor, ['tipo_item' => 'planta', 'planta_id' => $planta->id, 'cantidad_requerida' => 5]);
    $gestion = $servicio->verificarEstado($gestion, $vendedor, ['cantidad_fisica' => 5, 'cantidad_buen_estado' => 5]);
    $planta->update(['stock_actual' => 2]);

    expect(fn () => $servicio->registrarMovimiento($gestion, $vendedor))->toThrow(InvalidArgumentException::class, 'Stock insuficiente');
    expect($gestion->fresh()->estado)->toBe('stock_suficiente')
        ->and($planta->fresh()->stock_actual)->toBe(2)
        ->and($gestion->movimientos()->count())->toBe(0);
});

it('lista gestiones con filtros por estado, tipo y búsqueda', function () {
    $planta = gi_planta(5);
    $insumo = gi_insumo(5);
    $planta->update(['nombre' => 'Planta de filtro única']);
    $insumo->update(['nombre' => 'Insumo de búsqueda única']);

    GestionInventario::factory()->create(['planta_id' => $planta->id, 'estado' => 'stock_suficiente']);
    GestionInventario::factory()->create(['tipo_item' => 'insumo', 'planta_id' => null, 'recurso_id' => $insumo->id, 'estado' => 'cerrada']);

    expect(gi_servicio()->listar([])->total())->toBe(2)
        ->and(gi_servicio()->listar(['estado' => 'cerrada'])->total())->toBe(1)
        ->and(gi_servicio()->listar(['tipo_item' => 'planta'])->total())->toBe(1)
        ->and(gi_servicio()->listar(['q' => $insumo->nombre])->total())->toBe(1)
        ->and(gi_servicio()->listar(['estado' => 'inventado'])->total())->toBe(2);
});
