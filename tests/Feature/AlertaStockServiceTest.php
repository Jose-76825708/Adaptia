<?php

use App\Exceptions\AlertasStockBajoExistentesException;
use App\Models\Alerta;
use App\Models\Planta;
use App\Models\TipoPlanta;
use App\Services\AlertaStockService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function crearPlantaStockBajo(array $overrides = []): Planta
{
    $tipo = TipoPlanta::firstOrCreate(['nombre' => 'Test Tipo']);

    return Planta::create(array_merge([
        'tipo_planta_id' => $tipo->id,
        'nombre' => 'Aloe Vera',
        'descripcion' => 'Planta de prueba',
        'imagen' => 'aloe.jpg',
        'luz_requerida' => 'alta',
        'frecuencia_riego' => 'semanal',
        'tamaño_adulto' => 'mediana',
        'nivel_cuidado' => 'principiante',
        'tipo_ambiente' => 'interiores',
        'toxicidad' => false,
        'estetica' => 'follaje',
        'precio' => 99.90,
        'stock_actual' => 4,
        'stock_minimo' => 5,
    ], $overrides));
}

test('crea una alerta cuando el stock cae por debajo del mínimo', function () {
    $planta = crearPlantaStockBajo();

    $alerta = app(AlertaStockService::class)->evaluar($planta);

    expect($alerta)->not->toBeNull()
        ->and($alerta->tipo)->toBe('stock_bajo')
        ->and($alerta->planta_id)->toBe($planta->id)
        ->and($alerta->planta_vendida_id)->toBeNull()
        ->and($alerta->variable)->toBe('stock')
        ->and((float) $alerta->valor_medido)->toBe(4.0)
        ->and((float) $alerta->limite)->toBe(5.0)
        ->and((float) $alerta->rango_minimo)->toBe(5.0)
        ->and($alerta->direccion)->toBe('bajo')
        ->and($alerta->mensaje)->toBe("La planta {$planta->nombre} está bajo el stock mínimo. Stock actual: {$planta->stock_actual}, mínimo: {$planta->stock_minimo}.");
});

test('no crea alerta cuando el stock está normal', function () {
    $planta = crearPlantaStockBajo(['stock_actual' => 6, 'stock_minimo' => 5]);

    $alerta = app(AlertaStockService::class)->evaluar($planta);

    expect($alerta)->toBeNull()
        ->and(Alerta::where('planta_id', $planta->id)->count())->toBe(0);
});

test('no duplica la alerta aunque esté marcada como leída', function () {
    $planta = crearPlantaStockBajo();
    $service = app(AlertaStockService::class);

    $alerta = $service->evaluar($planta);
    $alerta->leida = true;
    $alerta->save();

    $segunda = $service->evaluar($planta);

    expect($segunda)->not->toBeNull()
        ->and($segunda->id)->toBe($alerta->id)
        ->and(Alerta::where('planta_id', $planta->id)->count())->toBe(1);
});

test('resuelve la alerta al recuperar el stock y crea otra si vuelve a bajar', function () {
    $planta = crearPlantaStockBajo();
    $service = app(AlertaStockService::class);

    $alerta = $service->evaluar($planta);
    expect($alerta)->not->toBeNull();

    $planta->stock_actual = 5;
    $planta->save();

    $service->evaluar($planta);
    $alerta->refresh();

    expect($alerta->resuelta_en)->not->toBeNull();

    $planta->stock_actual = 4;
    $planta->save();

    $nueva = $service->evaluar($planta);

    expect($nueva)->not->toBeNull()
        ->and($nueva->id)->not->toBe($alerta->id)
        ->and(Alerta::where('planta_id', $planta->id)->count())->toBe(2);
});

test('no genera alerta cuando el stock mínimo es cero', function () {
    $planta = crearPlantaStockBajo(['stock_actual' => 0, 'stock_minimo' => 0]);

    $alerta = app(AlertaStockService::class)->evaluar($planta);

    expect($alerta)->toBeNull()
        ->and(Alerta::where('planta_id', $planta->id)->count())->toBe(0);
});

test('lanza excepción cuando la alerta no tiene exactamente una FK activa', function () {
    $planta = crearPlantaStockBajo();

    expect(fn () => Alerta::create([
        'planta_id' => $planta->id,
        'planta_vendida_id' => 99,
        'tipo' => 'stock_bajo',
        'variable' => 'stock',
        'valor_medido' => 4,
        'limite' => 5,
        'rango_minimo' => 5,
        'direccion' => 'bajo',
        'mensaje' => 'Alerta inválida',
        'leida' => false,
    ]))->toThrow(InvalidArgumentException::class);

    expect(fn () => Alerta::create([
        'planta_id' => null,
        'planta_vendida_id' => null,
        'tipo' => 'stock_bajo',
        'variable' => 'stock',
        'valor_medido' => 4,
        'limite' => 5,
        'rango_minimo' => 5,
        'direccion' => 'bajo',
        'mensaje' => 'Alerta inválida',
        'leida' => false,
    ]))->toThrow(InvalidArgumentException::class);

    $alertaValida = Alerta::create([
        'planta_id' => $planta->id,
        'planta_vendida_id' => null,
        'tipo' => 'stock_bajo',
        'variable' => 'stock',
        'valor_medido' => 4,
        'limite' => 5,
        'rango_minimo' => 5,
        'direccion' => 'bajo',
        'mensaje' => 'Alerta válida',
        'leida' => false,
    ]);

    expect($alertaValida->exists)->toBeTrue()
        ->and($alertaValida->planta_id)->toBe($planta->id)
        ->and($alertaValida->planta_vendida_id)->toBeNull();
});

test('evaluar lanza excepción cuando la planta no está persistida', function () {
    $planta = new Planta([
        'nombre' => 'Sin persistir',
        'stock_actual' => 2,
        'stock_minimo' => 5,
    ]);

    expect(fn () => app(AlertaStockService::class)->evaluar($planta))
        ->toThrow(InvalidArgumentException::class, 'La planta debe existir para evaluar su stock bajo.');
});

test('down de la migración rechaza si existen alertas de stock bajo', function () {
    $planta = crearPlantaStockBajo();
    Alerta::create([
        'planta_id' => $planta->id,
        'planta_vendida_id' => null,
        'tipo' => 'stock_bajo',
        'variable' => 'stock',
        'valor_medido' => 4,
        'limite' => 5,
        'rango_minimo' => 5,
        'direccion' => 'bajo',
        'mensaje' => 'stock bajo',
        'leida' => false,
    ]);

    $migration = include database_path('migrations/2026_11_01_150100_update_alertas_for_stock_bajo.php');

    expect(fn () => $migration->down())
        ->toThrow(AlertasStockBajoExistentesException::class);
});
