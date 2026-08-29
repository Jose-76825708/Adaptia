<?php

use App\Models\Planta;
use App\Services\RecomendacionService;


// TEST DE LA FUNCION COMPTABILIDAD ORDINAL


// PRIMER TEST

test('dos valores ordinales iguales tiene compatibilidad total', function () {

    $service = new RecomendacionService();

    $resultado = $service->compatibilidadOrdinal(
        ['baja', 'media', 'alta'],
        'baja',
        'baja'
    );

    expect($resultado)->toBe(1);

});


// SEGUNDO TEST

test('dos valores ordinales tiene diferencia parcial', function () {

    $service = new RecomendacionService();

    $resultado = $service->compatibilidadOrdinal(
        ['baja', 'media', 'alta'],
        'media',
        'baja'
    );

    expect($resultado)->toBe(0.5);

});

// TERCER TEST

test('dos valores ordinales tiene diferencias totales', function () {

    $service = new RecomendacionService();

    $resultado = $service->compatibilidadOrdinal(
        ['baja', 'media', 'alta'],
        'alta',
        'baja'
    );

    expect($resultado)->toBe(0);

});


// TEST DE LA FUNCIÓN CATEGÓRICA

// PRIMER TEST

test('dos valores categoricos que son exactamente igual', function () {

    $service = new RecomendacionService();

    $resultado = $service->compatibilidadCategoria(
        'tipo_ambiente',
        ['interiores', 'exteriores', 'ambos'],
        'interiores',
        'interiores'
    );

    expect($resultado)->toBe(1);

});

// SEGUNDO TEST

test('dos valores categoricos que son totalmente diferentes', function () {

    $service = new RecomendacionService();

    $resultado = $service->compatibilidadCategoria(
        'tipo_ambiente',
        ['interiores', 'exteriores', 'ambos'],
        'interiores',
        'exteriores'
    );

    expect($resultado)->toBe(0);

});

// TERCER TEST

test('el atributo es tipo de ambiente y el caso sea ambos siempre debe retornar 1', function () {

    $service = new RecomendacionService();

    $resultado = $service->compatibilidadCategoria(
        'tipo_ambiente',
        ['interiores', 'exteriores', 'ambos'],
        'interiores',
        'ambos'
    );

    expect($resultado)->toBe(1);

});


// TEST DE LA FUNCIÓN PARA CALCULAR EL SCORE

// PRIMER TEST
test('una planta cumple TODO perfectamente debe arrojar el 100% de compatibilidad', function () {

    $service = new RecomendacionService();

    $planta = new Planta([
        'luz_requerida' => 'media',
        'frecuencia_riego' => 'semanal',
        'nivel_cuidado' => 'principiante',
        'tamaño_adulto' => 'pequena',
        'tipo_ambiente' => 'interiores',
        'estetica' => 'follaje',
    ]);

    $perfil_usuario = [
        'luz_requerida' => 'media',
        'frecuencia_riego' => 'semanal',
        'nivel_cuidado' => 'principiante',
        'tamaño_adulto' => 'pequena',
        'tipo_ambiente' => 'interiores',
        'estetica' => 'follaje',
    ];

    $resultado = $service->calculaScore($planta, $perfil_usuario);

    expect(round($resultado, 2))->toEqual(1);

});


// SEGUNDO TEST
test('una planta no cumple absolutamente nada debe arrojar el 0% de compatibilidad', function () {

    $service = new RecomendacionService();

    $planta = new Planta([
        'luz_requerida' => 'baja',
        'frecuencia_riego' => 'diario',
        'nivel_cuidado' => 'principiante',
        'tamaño_adulto' => 'pequena',
        'tipo_ambiente' => 'interiores',
        'estetica' => 'follaje',
    ]);

    $perfil_usuario = [
        'luz_requerida' => 'siempre_en_el_sol',
        'frecuencia_riego' => 'mensualmente',
        'nivel_cuidado' => 'experto',
        'tamaño_adulto' => 'grande',
        'tipo_ambiente' => 'exteriores',
        'estetica' => 'flor',
    ];

    $resultado = $service->calculaScore($planta, $perfil_usuario);

    expect(round($resultado, 2))->toEqual(0);

});

// TERCER TEST

test('cuando una planta cumple la mitad del perfil del usuario' , function () {

    $service = new RecomendacionService();

    $planta = new Planta([
        'luz_requerida' => 'baja',
        'frecuencia_riego' => 'diario',
        'nivel_cuidado' => 'principiante',
        'tamaño_adulto' => 'pequena',
        'tipo_ambiente' => 'interiores',
        'estetica' => 'follaje',
    ]);

    $perfil_usuario = [
        'luz_requerida' => 'baja',
        'frecuencia_riego' => 'diario',
        'nivel_cuidado' => 'principiante',
        'tamaño_adulto' => 'grande',
        'tipo_ambiente' => 'exteriores',
        'estetica' => 'flor',
    ];

    $resultado = $service->calculaScore($planta, $perfil_usuario);

    expect(round($resultado, 2))->toEqual(0.5);

});

