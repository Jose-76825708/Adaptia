<?php

use App\Services\SuficienciaInventarioService;

beforeEach(function () {
    $this->servicio = new SuficienciaInventarioService();
});

it('considera suficiente el stock cuando lo que está en buen estado cubre lo requerido', function () {
    $resultado = $this->servicio->evaluar(5, 10, 8, 10);

    expect($resultado['suficiente'])->toBeTrue()
        ->and($resultado['disponible'])->toBe(8.0)
        ->and($resultado['faltante'])->toBe(0.0);
});

it('calcula el faltante cuando lo disponible no alcanza', function () {
    $resultado = $this->servicio->evaluar(10, 6, 4, 6);

    expect($resultado['suficiente'])->toBeFalse()
        ->and($resultado['disponible'])->toBe(4.0)
        ->and($resultado['faltante'])->toBe(6.0);
});

it('limita lo disponible al stock registrado en el sistema', function () {
    $resultado = $this->servicio->evaluar(10, 20, 20, 7);

    expect($resultado['disponible'])->toBe(7.0)
        ->and($resultado['faltante'])->toBe(3.0)
        ->and($resultado['diferencia_fisica_sistema'])->toBe(13.0);
});

it('rechaza cantidades inválidas al evaluar el stock', function (float $requerida, float $fisica, float $buena, float $sistema) {
    expect(fn () => $this->servicio->evaluar($requerida, $fisica, $buena, $sistema))
        ->toThrow(InvalidArgumentException::class);
})->with([
    'requerida cero' => [0, 5, 5, 5],
    'conteo negativo' => [5, -1, 0, 5],
    'buen estado mayor al conteo' => [5, 4, 5, 5],
    'stock negativo' => [5, 5, 5, -1],
]);

it('detecta si lo recibido completa lo solicitado', function () {
    $resultado = $this->servicio->evaluarRecepcion(10, 0, 10);

    expect($resultado['coincide'])->toBeTrue()
        ->and($resultado['tipo_sugerido'])->toBeNull();
});

it('suma lo aceptado antes con lo recibido ahora', function () {
    $resultado = $this->servicio->evaluarRecepcion(10, 6, 4);

    expect($resultado['total'])->toBe(10.0)
        ->and($resultado['coincide'])->toBeTrue();
});

it('sugiere faltante o exceso según la diferencia', function () {
    expect($this->servicio->evaluarRecepcion(10, 0, 7)['tipo_sugerido'])->toBe('faltante')
        ->and($this->servicio->evaluarRecepcion(10, 0, 12)['tipo_sugerido'])->toBe('exceso');
});

it('clasifica un faltante aceptando todo lo recibido', function () {
    $evaluacion = $this->servicio->evaluarRecepcion(10, 0, 6);

    expect($this->servicio->clasificarIncidencia('faltante', $evaluacion, 6))
        ->toBe(['cantidad_afectada' => 4.0, 'cantidad_aceptada' => 6.0]);
});

it('clasifica un exceso aceptando solo lo solicitado', function () {
    $evaluacion = $this->servicio->evaluarRecepcion(10, 0, 13);

    expect($this->servicio->clasificarIncidencia('exceso', $evaluacion, 13))
        ->toBe(['cantidad_afectada' => 3.0, 'cantidad_aceptada' => 10.0]);
});

it('clasifica dañados o incorrectos con las unidades indicadas', function () {
    $evaluacion = $this->servicio->evaluarRecepcion(10, 0, 10);

    expect($this->servicio->clasificarIncidencia('danado', $evaluacion, 10, 3))
        ->toBe(['cantidad_afectada' => 3.0, 'cantidad_aceptada' => 7.0])
        ->and($this->servicio->clasificarIncidencia('incorrecto', $evaluacion, 10, 10))
        ->toBe(['cantidad_afectada' => 10.0, 'cantidad_aceptada' => 0.0]);
});

it('rechaza incidencias que no corresponden a la diferencia real', function () {
    $completa = $this->servicio->evaluarRecepcion(10, 0, 10);

    expect(fn () => $this->servicio->clasificarIncidencia('faltante', $completa, 10))->toThrow(InvalidArgumentException::class)
        ->and(fn () => $this->servicio->clasificarIncidencia('exceso', $completa, 10))->toThrow(InvalidArgumentException::class)
        ->and(fn () => $this->servicio->clasificarIncidencia('danado', $completa, 10))->toThrow(InvalidArgumentException::class)
        ->and(fn () => $this->servicio->clasificarIncidencia('danado', $completa, 10, 11))->toThrow(InvalidArgumentException::class)
        ->and(fn () => $this->servicio->clasificarIncidencia('otro', $completa, 10, 1))->toThrow(InvalidArgumentException::class);
});

it('distingue cantidades enteras de decimales', function () {
    expect($this->servicio->esEntera(4.0))->toBeTrue()
        ->and($this->servicio->esEntera(4.5))->toBeFalse();
});
